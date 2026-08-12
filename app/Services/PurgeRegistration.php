<?php

namespace App\Services;

use App\Models\Registration;
use App\Payments\Models\RegistrationPaymentEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeRegistration
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Permanently remove a registration and records owned by it.
     *
     * Payment entries normally remain immutable. This explicit account-level
     * purge is the only workflow allowed to bypass model events and erase them.
     */
    public function execute(Registration $registration): void
    {
        $noteImages = $registration->registrationNotes()
            ->withTrashed()
            ->pluck('image_path')
            ->filter()
            ->values()
            ->all();

        $files = array_merge(
            array_filter([
                $registration->profile_picture,
                $registration->professional_id_document_path,
            ]),
            $noteImages
        );

        DB::transaction(function () use ($registration): void {
            $registration->refresh();

            $paymentEntries = RegistrationPaymentEntry::withoutGlobalScopes()
                ->where('registration_id', $registration->id);

            $paymentEntryCount = (clone $paymentEntries)->count();

            // Break self-references before deleting the immutable ledger rows.
            (clone $paymentEntries)->update(['reverses_entry_id' => null]);
            $paymentEntries->delete();

            $this->auditService->log(
                'deleted',
                $registration,
                [
                    'permanent' => true,
                    'registration_number' => $registration->registration_number,
                    'email' => $registration->email,
                    'payment_entries_deleted' => $paymentEntryCount,
                ],
                "Permanently deleted registration {$registration->registration_number} and its owned records"
            );

            // Database cascades remove attendee favorites, wall activity,
            // connections, and messages. The force-delete event removes its hash.
            $registration->forceDelete();
        });

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }
    }
}
