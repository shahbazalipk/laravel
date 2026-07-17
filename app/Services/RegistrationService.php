<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\RegistrationStatus;
use App\Models\Event;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Calculate price with tax based on event settings
     */
    public function calculatePrice(RegistrationCategory $category, Event $event): array
    {
        $vatPercentage = $category->vat_percentage ?? $event->vat_percentage ?? 0;
        
        if ($event->tax_inclusive) {
            // Price already includes tax
            $totalAmount = $category->price;
            $basePrice = $totalAmount / (1 + ($vatPercentage / 100));
            $taxAmount = $totalAmount - $basePrice;
        } else {
            // Tax is added on top
            $basePrice = $category->price;
            $taxAmount = $basePrice * ($vatPercentage / 100);
            $totalAmount = $basePrice + $taxAmount;
        }

        return [
            'base_price' => round($basePrice, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'vat_percentage' => $vatPercentage,
            'tax_inclusive' => $event->tax_inclusive,
            'currency' => $category->currency ?? $event->currency ?? 'AED',
        ];
    }

    /**
     * Recalculate and apply category pricing onto registration attributes.
     */
    public function pricingAttributesForCategory(RegistrationCategory $category, ?Event $event = null): array
    {
        $event ??= Event::getCurrentEvent();
        $pricing = $this->calculatePrice($category, $event);

        return [
            'base_price' => $pricing['base_price'],
            'tax_amount' => $pricing['tax_amount'],
            'total_amount' => $pricing['total_amount'],
            'currency' => $pricing['currency'],
        ];
    }

    /**
     * Validate category requirements
     */
    public function validateCategory(RegistrationCategory $category, array $data): array
    {
        $errors = [];

        // Check password
        if ($category->needs_password && (!isset($data['category_password']) || $data['category_password'] !== $category->password)) {
            $errors['category_password'] = 'Invalid category password.';
        }

        // Check membership
        if ($category->need_membership_id) {
            if (empty($data['membership_id'])) {
                $errors['membership_id'] = 'Membership ID is required for this category.';
            } else {
                $membershipValid = $this->validateMembership($data['membership_id'], $category->membership_id);
                if (!$membershipValid) {
                    $errors['membership_id'] = $category->membership_not_found_message ?? 'Invalid membership ID.';
                }
            }
        }

        // Check professional/student ID
        if ($category->need_professional_student_id && empty($data['professional_student_id'])) {
            $errors['professional_student_id'] = $category->professional_student_id_message ?? 'Professional/Student ID is required.';
        }

        // Check valid dates
        if ($category->valid_from && now()->lt($category->valid_from)) {
            $errors['category'] = 'Registration for this category has not opened yet.';
        }

        if ($category->valid_to && now()->gt($category->valid_to)) {
            $errors['category'] = 'Registration for this category has closed.';
        }

        // Check capacity
        if ($category->capacity) {
            $registeredCount = Registration::where('registration_category_id', $category->id)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();
            
            if ($registeredCount >= $category->capacity) {
                $errors['category'] = 'This category has reached its capacity.';
            }
        }

        return $errors;
    }

    /**
     * Validate membership ID
     */
    public function validateMembership(string $membershipId, ?int $requiredMembershipId = null): bool
    {
        $query = Membership::where('code', $membershipId)
            ->where('is_active', true);

        if ($requiredMembershipId) {
            $query->where('id', $requiredMembershipId);
        }

        return $query->exists();
    }

    /**
     * Create a new registration
     */
    public function createRegistration(array $data): Registration
    {
        return DB::transaction(function () use ($data) {
            // Generate unique registration number
            $data['registration_number'] = $this->generateRegistrationNumber();
            
            // Generate badge number
            $data['badge_number'] = $this->generateBadgeNumber();
            
            // Set terms accepted timestamp
            if ($data['terms_accepted'] ?? false) {
                $data['terms_accepted_at'] = now();
            }

            // Create registration
            $registration = Registration::create($data);

            // Generate QR code
            $registration->qr_code = $this->generateQRCode($registration);
            $registration->save();

            // Generate email verification token if required and not already verified
            $event = Event::getCurrentEvent();
            if ($event && $event->email_verification_required && empty($data['email_verified'])) {
                $registration->email_verification_token = Str::random(64);
                $registration->email_verified = false;
                $registration->save();
            } elseif (!empty($data['email_verified'])) {
                $registration->email_verified = true;
                $registration->email_verified_at = $data['email_verified_at'] ?? now();
                $registration->email_verification_token = null;
                $registration->save();
            }

            // Generate verification code if required
            if ($event && $event->code_verification_required) {
                $registration->verification_code = $this->generateVerificationCode();
                $registration->save();
            }

            return $registration;
        });
    }

    /**
     * Generate unique registration number
     */
    public function generateRegistrationNumber(): string
    {
        do {
            $number = 'REG-' . strtoupper(Str::random(8));
        } while (Registration::where('registration_number', $number)->exists());

        return $number;
    }

    /**
     * Generate unique badge number
     */
    public function generateBadgeNumber(): string
    {
        do {
            $number = 'BADGE-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Registration::where('badge_number', $number)->exists());

        return $number;
    }

    /**
     * Generate QR code for registration
     */
    public function generateQRCode(Registration $registration): string
    {
        $data = json_encode([
            'registration_number' => $registration->registration_number,
            'badge_number' => $registration->badge_number,
            'name' => $registration->full_name,
            'email' => $registration->email,
            'company' => $registration->company_name,
        ]);

        return base64_encode(QrCode::format('png')->size(300)->generate($data));
    }

    /**
     * Generate verification code
     */
    public function generateVerificationCode(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify email token
     */
    public function verifyEmail(string $token): ?Registration
    {
        $registration = Registration::where('email_verification_token', $token)
            ->where('email_verified', false)
            ->first();

        if ($registration) {
            $registration->email_verified = true;
            $registration->email_verified_at = now();
            $registration->email_verification_token = null;
            $registration->save();
        }

        return $registration;
    }

    /**
     * Verify code
     */
    public function verifyCode(string $registrationNumber, string $code): ?Registration
    {
        $registration = Registration::where('registration_number', $registrationNumber)
            ->where('verification_code', $code)
            ->where('code_verified', false)
            ->first();

        if ($registration) {
            $registration->code_verified = true;
            $registration->code_verified_at = now();
            $registration->save();
        }

        return $registration;
    }

    /**
     * Check in registration
     */
    public function checkIn(Registration $registration, ?string $checkedInBy = null): bool
    {
        if (!$registration->can_check_in) {
            return false;
        }

        $registration->checked_in = true;
        $registration->checked_in_at = now();
        $registration->checked_in_by = $checkedInBy;
        $registration->save();

        return true;
    }

    /**
     * Mark badge as printed
     */
    public function markBadgePrinted(Registration $registration): void
    {
        $registration->badge_printed = true;
        $registration->badge_printed_at = now();
        $registration->save();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Registration $registration, string $status, ?array $paymentData = []): void
    {
        $registration->payment_status = $status;
        
        if ($status === 'paid') {
            $registration->payment_date = now();
        }

        if (isset($paymentData['payment_method'])) {
            $registration->payment_method = $paymentData['payment_method'];
        }

        if (isset($paymentData['payment_reference'])) {
            $registration->payment_reference = $paymentData['payment_reference'];
        }

        $registration->save();
    }

    /**
     * Assign a registration status while preserving payment/check-in independence.
     */
    public function updateStatus(Registration $registration, RegistrationStatus $status): Registration
    {
        $oldStatusId = $registration->registration_status_id;
        $oldStatusName = $registration->registrationStatus?->name;

        if ((int) $oldStatusId === (int) $status->id) {
            return $registration;
        }

        $registration->registration_status_id = $status->id;
        $registration->save();

        $this->auditService->log(
            'updated',
            $registration,
            [
                'old_status_id' => $oldStatusId,
                'old_status_name' => $oldStatusName,
                'new_status_id' => $status->id,
                'new_status_name' => $status->name,
            ],
            "Updated registration status for {$registration->registration_number} from ".
            ($oldStatusName ?: 'None')." to {$status->name}"
        );

        return $registration->fresh(['registrationStatus']);
    }

    /**
     * Get all registrations with filters
     */
    public function getAllRegistrations(array $filters = [])
    {
        $query = Registration::with([
            'registrationCategory',
            'registrationStatus',
            'exhibitor',
            'group',
            'industry',
            'businessActivity'
        ]);

        if (isset($filters['category_id'])) {
            $query->where('registration_category_id', $filters['category_id']);
        }

        if (isset($filters['status_id'])) {
            $query->where('registration_status_id', $filters['status_id']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['registration_type'])) {
            $query->where('registration_type', $filters['registration_type']);
        }

        if (isset($filters['checked_in'])) {
            $query->where('checked_in', $filters['checked_in']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('badge_number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Get registration statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => Registration::count(),
            'paid' => Registration::where('payment_status', 'paid')->count(),
            'pending' => Registration::where('payment_status', 'pending')->count(),
            'checked_in' => Registration::where('checked_in', true)->count(),
            'by_category' => Registration::select('registration_category_id', DB::raw('count(*) as count'))
                ->groupBy('registration_category_id')
                ->with('registrationCategory:id,name')
                ->get(),
            'by_type' => Registration::select('registration_type', DB::raw('count(*) as count'))
                ->groupBy('registration_type')
                ->get(),
            'revenue' => Registration::where('payment_status', 'paid')->sum('total_amount'),
        ];
    }
}
