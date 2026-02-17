<?php

namespace App\Services;

use App\Models\Membership;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MembershipService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllMemberships()
    {
        return Membership::orderBy('sort_order')->orderBy('name')->get();
    }

    public function getActiveMemberships()
    {
        return Membership::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createMembership(array $data): Membership
    {
        // Handle file upload if present
        if (isset($data['membership_file']) && $data['membership_file'] instanceof UploadedFile) {
            $data['file_path'] = $data['membership_file']->store('memberships', 'public');
            unset($data['membership_file']);
        }

        // Convert API headers array to JSON if present
        if (isset($data['api_headers']) && is_array($data['api_headers'])) {
            $data['api_headers'] = json_encode($data['api_headers']);
        }

        $membership = Membership::create($data);
        
        // Log the creation
        $this->auditService->logCreated($membership, $data);
        
        return $membership;
    }

    public function updateMembership(Membership $membership, array $data): Membership
    {
        $oldData = $membership->toArray();
        
        // Handle file upload if present
        if (isset($data['membership_file']) && $data['membership_file'] instanceof UploadedFile) {
            // Delete old file if exists
            if ($membership->file_path) {
                Storage::disk('public')->delete($membership->file_path);
            }
            $data['file_path'] = $data['membership_file']->store('memberships', 'public');
            unset($data['membership_file']);
        }

        // Convert API headers array to JSON if present
        if (isset($data['api_headers']) && is_array($data['api_headers'])) {
            $data['api_headers'] = json_encode($data['api_headers']);
        }

        $membership->update($data);
        
        // Log the update
        $this->auditService->logUpdated($membership, $oldData, $membership->fresh()->toArray());
        
        return $membership->fresh();
    }

    public function deleteMembership(Membership $membership): bool
    {
        // Delete associated file if exists
        if ($membership->file_path) {
            Storage::disk('public')->delete($membership->file_path);
        }

        // Log the deletion
        $this->auditService->logDeleted($membership);

        return $membership->delete();
    }

    public function toggleActive(Membership $membership): Membership
    {
        $membership->is_active = !$membership->is_active;
        $membership->save();
        
        // Log the toggle
        $this->auditService->logToggled($membership, $membership->is_active);
        
        return $membership;
    }

    public function verifyMembershipNumber(Membership $membership, string $membershipNumber): bool
    {
        if ($membership->isUploadFile()) {
            return $this->verifyAgainstFile($membership, $membershipNumber);
        }

        if ($membership->isThirdPartyApi()) {
            return $this->verifyAgainstApi($membership, $membershipNumber);
        }

        return false;
    }

    private function verifyAgainstFile(Membership $membership, string $membershipNumber): bool
    {
        if (!$membership->file_path || !Storage::disk('public')->exists($membership->file_path)) {
            return false;
        }

        $filePath = Storage::disk('public')->path($membership->file_path);
        $fileContent = file_get_contents($filePath);
        
        // Simple line-by-line search (can be enhanced for CSV parsing)
        $lines = explode("\n", $fileContent);
        foreach ($lines as $line) {
            if (trim($line) === trim($membershipNumber)) {
                return true;
            }
        }

        return false;
    }

    private function verifyAgainstApi(Membership $membership, string $membershipNumber): bool
    {
        if (!$membership->api_endpoint) {
            return false;
        }

        try {
            $headers = $membership->getApiHeadersArray();
            
            // Add API key to headers if present
            if ($membership->api_key) {
                $headers['Authorization'] = 'Bearer ' . $membership->api_key;
            }

            // Make API call (this is a basic implementation)
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->get($membership->api_endpoint, [
                    'membership_number' => $membershipNumber
                ]);

            return $response->successful() && $response->json('valid', false);
        } catch (\Exception $e) {
            \Log::error('Membership API verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getMembershipCodes(Membership $membership, ?string $search = null)
    {
        $query = $membership->codes()->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('code', 'like', '%' . $search . '%');
        }

        return $query->paginate(15);
    }

    public function addMembershipCode(Membership $membership, array $data)
    {
        $code = $membership->codes()->create($data);
        
        // Log the code addition
        $this->auditService->logCreated($code, $data);
        
        return $code;
    }

    public function deleteMembershipCode(int $codeId): bool
    {
        $code = \App\Models\MembershipCode::findOrFail($codeId);
        
        // Log the deletion
        $this->auditService->logDeleted($code);
        
        return $code->delete();
    }

    public function importMembershipCodes(Membership $membership, array $data): int
    {
        $file = $data['import_file'];
        $allowedUsage = $data['allowed_usage'];
        
        $filePath = $file->getRealPath();
        $fileContent = file_get_contents($filePath);
        
        $lines = explode("\n", $fileContent);
        $count = 0;

        foreach ($lines as $line) {
            $codeValue = trim($line);
            
            // Skip empty lines
            if (empty($codeValue)) {
                continue;
            }

            // Check if code already exists
            $exists = $membership->codes()->where('code', $codeValue)->exists();
            
            if (!$exists) {
                $membership->codes()->create([
                    'code' => $codeValue,
                    'allowed_usage' => $allowedUsage,
                    'used' => 0,
                    'status' => 'active',
                ]);
                $count++;
            }
        }

        // Log the import
        $this->auditService->logImport($membership, $count);

        return $count;
    }
}
