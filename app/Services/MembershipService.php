<?php

namespace App\Services;

use App\Enums\MembershipIdentifierType;
use App\Models\Membership;
use App\Models\MembershipCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MembershipService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllMemberships()
    {
        return Membership::query()
            ->withCount('codes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
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
        $importFile = null;

        if (isset($data['membership_file']) && $data['membership_file'] instanceof UploadedFile) {
            $importFile = $data['membership_file'];
            $data['file_path'] = $importFile->store('memberships', 'public');
            unset($data['membership_file']);
        }

        $data = $this->normalizeApiFields($data);

        $membership = Membership::create($data);

        if ($importFile && $membership->isUploadFile()) {
            $this->importMembershipCodes($membership, [
                'import_file' => $importFile,
                'allowed_usage' => 1,
            ]);
        }

        $this->auditService->logCreated($membership, $data);

        return $membership;
    }

    public function updateMembership(Membership $membership, array $data): Membership
    {
        $oldData = $membership->toArray();
        $importFile = null;

        if (isset($data['membership_file']) && $data['membership_file'] instanceof UploadedFile) {
            $importFile = $data['membership_file'];
            if ($membership->file_path) {
                Storage::disk('public')->delete($membership->file_path);
            }
            $data['file_path'] = $importFile->store('memberships', 'public');
            unset($data['membership_file']);
        }

        $data = $this->normalizeApiFields($data);
        $membership->update($data);

        if ($importFile && $membership->fresh()->isUploadFile()) {
            $this->importMembershipCodes($membership->fresh(), [
                'import_file' => $importFile,
                'allowed_usage' => 1,
            ]);
        }

        $this->auditService->logUpdated($membership, $oldData, $membership->fresh()->toArray());

        return $membership->fresh();
    }

    public function deleteMembership(Membership $membership): bool
    {
        if ($membership->file_path) {
            Storage::disk('public')->delete($membership->file_path);
        }

        $membership->codes()->each(function (MembershipCode $code): void {
            $code->delete();
        });

        $this->auditService->logDeleted($membership);

        return (bool) $membership->delete();
    }

    public function toggleActive(Membership $membership): Membership
    {
        $membership->is_active = ! $membership->is_active;
        $membership->save();

        $this->auditService->logToggled($membership, $membership->is_active);

        return $membership;
    }

    /**
     * Future registration hook: validate an identifier against this list/API.
     */
    public function verifyMembershipNumber(Membership $membership, string $membershipNumber): bool
    {
        $identifier = $this->normalizeIdentifier($membership, $membershipNumber);

        if ($identifier === '') {
            return false;
        }

        if ($membership->isUploadFile()) {
            return $this->verifyAgainstCodes($membership, $identifier)
                || $this->verifyAgainstFile($membership, $identifier);
        }

        if ($membership->isThirdPartyApi()) {
            return $this->verifyAgainstApi($membership, $identifier);
        }

        return false;
    }

    public function getMembershipCodes(Membership $membership, ?string $search = null)
    {
        $query = $membership->codes()->orderByDesc('created_at');

        if ($search) {
            $query->where('code', 'like', '%'.$search.'%');
        }

        return $query->paginate(25);
    }

    public function addMembershipCode(Membership $membership, array $data)
    {
        $data['code'] = $this->normalizeIdentifier($membership, (string) $data['code']);
        $code = $membership->codes()->create($data);
        $this->auditService->logCreated($code, $data);

        return $code;
    }

    public function deleteMembershipCode(int $codeId): bool
    {
        $code = MembershipCode::findOrFail($codeId);
        $this->auditService->logDeleted($code);

        return (bool) $code->delete();
    }

    public function importMembershipCodes(Membership $membership, array $data): int
    {
        /** @var UploadedFile $file */
        $file = $data['import_file'];
        $allowedUsage = (int) ($data['allowed_usage'] ?? 1);
        $content = file_get_contents($file->getRealPath()) ?: '';
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $count = 0;
        $isFirst = true;

        foreach ($lines as $line) {
            $raw = trim((string) $line);
            if ($raw === '') {
                continue;
            }

            // Support simple CSV: take first column; skip header rows.
            $parts = str_getcsv($raw);
            $codeValue = trim((string) ($parts[0] ?? ''));

            if ($isFirst && $this->looksLikeHeader($codeValue)) {
                $isFirst = false;
                continue;
            }
            $isFirst = false;

            $codeValue = $this->normalizeIdentifier($membership, $codeValue);
            if ($codeValue === '') {
                continue;
            }

            $exists = $membership->codes()->where('code', $codeValue)->exists();
            if ($exists) {
                continue;
            }

            $membership->codes()->create([
                'code' => $codeValue,
                'allowed_usage' => $allowedUsage,
                'used' => 0,
                'status' => 'active',
            ]);
            $count++;
        }

        $this->auditService->logImport($membership, $count);

        return $count;
    }

    public function defaultSampleRequest(MembershipIdentifierType $type): string
    {
        return json_encode([
            'identifier' => '{{identifier}}',
            'identifier_type' => $type->value,
            'example' => $type->placeholder(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function defaultSampleResponse(): string
    {
        return json_encode([
            'valid' => true,
            'message' => 'Identifier verified successfully',
            'member' => [
                'id' => '12345',
                'status' => 'active',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function verifyAgainstCodes(Membership $membership, string $identifier): bool
    {
        $code = $membership->codes()
            ->where('code', $identifier)
            ->first();

        return $code?->isAvailable() ?? false;
    }

    private function verifyAgainstFile(Membership $membership, string $membershipNumber): bool
    {
        if (! $membership->file_path || ! Storage::disk('public')->exists($membership->file_path)) {
            return false;
        }

        $fileContent = Storage::disk('public')->get($membership->file_path) ?: '';
        $lines = preg_split('/\r\n|\r|\n/', $fileContent) ?: [];

        foreach ($lines as $line) {
            $parts = str_getcsv(trim((string) $line));
            $value = $this->normalizeIdentifier($membership, (string) ($parts[0] ?? ''));
            if ($value !== '' && $value === $membershipNumber) {
                return true;
            }
        }

        return false;
    }

    private function verifyAgainstApi(Membership $membership, string $membershipNumber): bool
    {
        if (! $membership->api_endpoint) {
            return false;
        }

        try {
            $headers = $membership->getApiHeadersArray();
            if ($membership->api_key) {
                $headers['Authorization'] = 'Bearer '.$membership->api_key;
            }

            $method = strtoupper((string) ($membership->api_method ?: 'GET'));
            $payload = $this->buildApiPayload($membership, $membershipNumber);

            $request = Http::withHeaders($headers)->timeout(15);
            $response = $method === 'POST'
                ? $request->post($membership->api_endpoint, $payload)
                : $request->get($membership->api_endpoint, $payload);

            if (! $response->successful()) {
                return false;
            }

            $json = $response->json();
            if (is_array($json) && array_key_exists('valid', $json)) {
                return (bool) $json['valid'];
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Membership API verification failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildApiPayload(Membership $membership, string $identifier): array
    {
        $sample = trim((string) $membership->api_sample_request);
        if ($sample !== '') {
            $replaced = str_replace(
                ['{{identifier}}', '{{identifier_type}}'],
                [$identifier, $membership->identifierType()->value],
                $sample
            );
            $decoded = json_decode($replaced, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            'identifier' => $identifier,
            'identifier_type' => $membership->identifierType()->value,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeApiFields(array $data): array
    {
        if (isset($data['api_headers']) && is_array($data['api_headers'])) {
            $data['api_headers'] = json_encode($data['api_headers']);
        }

        if (($data['verification_type'] ?? null) === 'third_party_api') {
            $type = MembershipIdentifierType::tryFrom((string) ($data['identifier_type'] ?? ''))
                ?? MembershipIdentifierType::MembershipId;

            $data['api_method'] = strtoupper((string) ($data['api_method'] ?? 'GET'));
            if (blank($data['api_sample_request'] ?? null)) {
                $data['api_sample_request'] = $this->defaultSampleRequest($type);
            }
            if (blank($data['api_sample_response'] ?? null)) {
                $data['api_sample_response'] = $this->defaultSampleResponse();
            }
        }

        return $data;
    }

    private function normalizeIdentifier(Membership $membership, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return $membership->identifierType() === MembershipIdentifierType::Email
            ? Str::lower($value)
            : $value;
    }

    private function looksLikeHeader(string $value): bool
    {
        $normalized = Str::lower(trim($value));

        return in_array($normalized, [
            'code',
            'codes',
            'id',
            'identifier',
            'membership_id',
            'membership id',
            'email',
            'email_id',
            'email id',
            'student_id',
            'student id',
        ], true);
    }
}
