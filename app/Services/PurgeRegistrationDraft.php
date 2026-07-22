<?php

namespace App\Services;

use App\Registration\Models\RegistrationDraft;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PurgeRegistrationDraft
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function execute(RegistrationDraft $draft): void
    {
        $profilePicture = $draft->payloadValue('profile_picture');
        $reference = $draft->displayReference();

        DB::transaction(function () use ($draft, $reference): void {
            $draft->refresh();

            if (Schema::hasTable('custom_form_responses')) {
                $draft->customFormResponses()->get()->each->forceDelete();
            }

            $this->auditService->log(
                'deleted',
                $draft,
                [
                    'permanent' => true,
                    'draft_reference' => $reference,
                    'email' => $draft->email,
                    'expired' => $draft->isExpired(),
                ],
                "Permanently deleted registration draft {$reference}"
            );

            $draft->delete();
        });

        if (is_string($profilePicture) && $profilePicture !== '') {
            Storage::disk('public')->delete($profilePicture);
        }
    }
}
