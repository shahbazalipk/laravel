<?php

namespace App\Services;

use App\Enums\PromoDiscountType;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\PromoCodeEmail;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PromoCodeService
{
    /**
     * @return Collection<int, PromoCode>
     */
    public function getAll(): Collection
    {
        return PromoCode::query()
            ->withCount('emails')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\App\Models\PromoCodeEmail>
     */
    public function getEmails(PromoCode $promoCode, ?string $search = null, ?string $usageStatus = null)
    {
        $usageCounts = $this->usageCountsByEmail($promoCode);
        $status = in_array($usageStatus, ['used', 'unused'], true) ? $usageStatus : null;

        $query = $promoCode->emails()->orderBy('email');

        if ($search) {
            $query->where('email', 'like', '%'.strtolower(trim($search)).'%');
        }

        if ($status === 'used') {
            $usedEmails = $usageCounts->filter(fn (int $count) => $count > 0)->keys()->all();
            if ($usedEmails === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('email', $usedEmails);
            }
        } elseif ($status === 'unused') {
            $usedEmails = $usageCounts->filter(fn (int $count) => $count > 0)->keys()->all();
            if ($usedEmails !== []) {
                $query->whereNotIn('email', $usedEmails);
            }
        }

        $emails = $query->paginate(50)->withQueryString();

        $emails->getCollection()->transform(function (PromoCodeEmail $row) use ($usageCounts) {
            $row->setAttribute('times_used', (int) ($usageCounts[$row->email] ?? 0));

            return $row;
        });

        return $emails;
    }

    /**
     * @return \Illuminate\Support\Collection<string, int>
     */
    public function usageCountsByEmail(PromoCode $promoCode)
    {
        return Registration::query()
            ->where('event_id', $promoCode->event_id)
            ->where('promo_code_id', $promoCode->id)
            ->selectRaw('email, COUNT(*) as times_used')
            ->groupBy('email')
            ->pluck('times_used', 'email')
            ->map(fn ($count) => (int) $count);
    }

    /**
     * @return array{used_emails: int, unused_emails: int, total_emails: int, total_redemptions: int}
     */
    public function usageSummary(PromoCode $promoCode): array
    {
        $totalEmails = (int) $promoCode->emails()->count();
        $usageCounts = $this->usageCountsByEmail($promoCode);
        $usedEmails = $usageCounts->filter(fn (int $count) => $count > 0)->count();

        return [
            'used_emails' => $usedEmails,
            'unused_emails' => max(0, $totalEmails - $usedEmails),
            'total_emails' => $totalEmails,
            'total_redemptions' => (int) Registration::query()
                ->where('event_id', $promoCode->event_id)
                ->where('promo_code_id', $promoCode->id)
                ->count(),
        ];
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\App\Models\Registration>
     */
    public function getRedemptions(PromoCode $promoCode)
    {
        return Registration::query()
            ->where('event_id', $promoCode->event_id)
            ->where('promo_code_id', $promoCode->id)
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'redemptions_page')
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $emailList = null): PromoCode
    {
        $promo = PromoCode::query()->create($this->normalize($data));

        if ($emailList) {
            $this->replaceEmailAllowlist($promo, $emailList);
        }

        return $promo->fresh('emails');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PromoCode $promoCode, array $data, ?UploadedFile $emailList = null): PromoCode
    {
        $promoCode->update($this->normalize($data, $promoCode));

        if ($emailList) {
            $this->replaceEmailAllowlist($promoCode, $emailList);
        } elseif (! empty($data['clear_email_list'])) {
            $this->clearEmailAllowlist($promoCode);
        }

        return $promoCode->fresh('emails');
    }

    public function delete(PromoCode $promoCode): void
    {
        $promoCode->emails()->delete();
        $promoCode->delete();
    }

    public function toggleActive(PromoCode $promoCode): PromoCode
    {
        $promoCode->update(['is_active' => ! $promoCode->is_active]);

        return $promoCode->fresh();
    }

    public function clearEmailAllowlist(PromoCode $promoCode): void
    {
        $promoCode->emails()->delete();
    }

    public function addEmail(PromoCode $promoCode, string $email): PromoCodeEmail
    {
        $normalized = strtolower(trim($email));

        if ($promoCode->emails()->where('email', $normalized)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email is already on the allowlist.',
            ]);
        }

        $event = Event::getCurrentEvent();

        return $promoCode->emails()->create([
            'event_id' => $event?->id ?? $promoCode->event_id,
            'org_id' => $event?->organization_id ?? $event?->org_id ?? $promoCode->org_id,
            'email' => $normalized,
        ]);
    }

    public function updateEmail(PromoCode $promoCode, PromoCodeEmail $promoCodeEmail, string $email): PromoCodeEmail
    {
        $this->assertEmailBelongsToPromo($promoCode, $promoCodeEmail);

        $normalized = strtolower(trim($email));

        $duplicate = $promoCode->emails()
            ->where('email', $normalized)
            ->whereKeyNot($promoCodeEmail->id)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'email' => 'This email is already on the allowlist.',
            ]);
        }

        $promoCodeEmail->update(['email' => $normalized]);

        return $promoCodeEmail->fresh();
    }

    public function deleteEmail(PromoCode $promoCode, PromoCodeEmail $promoCodeEmail): void
    {
        $this->assertEmailBelongsToPromo($promoCode, $promoCodeEmail);
        $promoCodeEmail->delete();
    }

    private function assertEmailBelongsToPromo(PromoCode $promoCode, PromoCodeEmail $promoCodeEmail): void
    {
        if ((int) $promoCodeEmail->promo_code_id !== (int) $promoCode->id) {
            abort(404);
        }
    }

    public function replaceEmailAllowlist(PromoCode $promoCode, UploadedFile $file): int
    {
        $emails = $this->parseEmailCsv($file);
        $event = Event::getCurrentEvent();
        $eventId = $event?->id ?? $promoCode->event_id;
        $orgId = $event?->organization_id ?? $event?->org_id ?? $promoCode->org_id;

        $promoCode->emails()->delete();

        $rows = [];
        $now = now();
        foreach ($emails as $email) {
            $rows[] = [
                'promo_code_id' => $promoCode->id,
                'event_id' => $eventId,
                'org_id' => $orgId,
                'email' => $email,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            PromoCodeEmail::query()->insert($chunk);
        }

        return count($rows);
    }

    /**
     * @return list<string>
     */
    public function parseEmailCsv(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath()) ?: '';
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $emails = [];
        $isFirst = true;

        foreach ($lines as $line) {
            $raw = trim((string) $line);
            if ($raw === '') {
                continue;
            }

            $parts = str_getcsv($raw);
            $value = trim((string) ($parts[0] ?? ''));

            if ($isFirst && $this->looksLikeEmailHeader($value)) {
                $isFirst = false;
                continue;
            }
            $isFirst = false;

            $email = strtolower(trim($value));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emails[$email] = $email;
        }

        return array_values($emails);
    }

    public function findUsable(
        string $code,
        Event $event,
        string $email,
        ?int $exceptRegistrationId = null,
    ): PromoCode {
        $normalizedCode = strtoupper(trim($code));
        $normalizedEmail = strtolower(trim($email));

        $promo = PromoCode::query()
            ->where('event_id', $event->id)
            ->where('code', $normalizedCode)
            ->first();

        if (! $promo) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is not valid.',
            ]);
        }

        if (! $promo->is_active) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is not active.',
            ]);
        }

        if (! $promo->hasStarted()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is not available yet.',
            ]);
        }

        if ($promo->isExpired()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code has expired.',
            ]);
        }

        if (! $promo->hasRemainingUses()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code has reached its usage limit.',
            ]);
        }

        if ($promo->restrict_to_email_list && ! $promo->isEmailAllowed($normalizedEmail)) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is not available for your email address.',
            ]);
        }

        if ($promo->max_uses_per_email !== null) {
            $usedByEmailQuery = Registration::query()
                ->where('event_id', $event->id)
                ->where('promo_code_id', $promo->id)
                ->where('email', $normalizedEmail);

            if ($exceptRegistrationId !== null) {
                $usedByEmailQuery->whereKeyNot($exceptRegistrationId);
            }

            if ($usedByEmailQuery->count() >= $promo->max_uses_per_email) {
                throw ValidationException::withMessages([
                    'promo_code' => 'You have already used this promo code the maximum number of times.',
                ]);
            }
        }

        return $promo;
    }

    /**
     * @param  array{base_price: float|int|string, tax_amount: float|int|string, total_amount: float|int|string, vat_percentage?: float|int|string, tax_inclusive?: bool, currency: string}  $pricing
     * @return array{base_price: float, tax_amount: float, total_amount: float, vat_percentage: float|int|string, tax_inclusive: bool, currency: string, discount_amount: float, promo_code_id: int, promo_code: string}
     */
    public function applyToPricing(array $pricing, PromoCode $promo): array
    {
        $vatPercentage = (float) ($pricing['vat_percentage'] ?? 0);
        $taxInclusive = (bool) ($pricing['tax_inclusive'] ?? false);
        $currency = (string) $pricing['currency'];

        // Recover pre-discount base (category base before VAT) from current pricing.
        if ($taxInclusive) {
            $gross = (float) $pricing['total_amount'];
            $baseBeforeDiscount = $vatPercentage > 0
                ? $gross / (1 + ($vatPercentage / 100))
                : $gross;
        } else {
            $baseBeforeDiscount = (float) $pricing['base_price'];
        }

        $discountAmount = $this->discountAmountForBase($promo, $baseBeforeDiscount);
        $discountedBase = max(0, $baseBeforeDiscount - $discountAmount);

        if ($taxInclusive) {
            $totalAmount = $discountedBase * (1 + ($vatPercentage / 100));
            $taxAmount = $totalAmount - $discountedBase;
            $basePrice = $discountedBase;
        } else {
            $basePrice = $discountedBase;
            $taxAmount = $basePrice * ($vatPercentage / 100);
            $totalAmount = $basePrice + $taxAmount;
        }

        return [
            'base_price' => round($basePrice, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'vat_percentage' => $pricing['vat_percentage'] ?? $vatPercentage,
            'tax_inclusive' => $taxInclusive,
            'currency' => $currency,
            'discount_amount' => round($discountAmount, 2),
            'promo_code_id' => $promo->id,
            'promo_code' => $promo->code,
        ];
    }

    /**
     * Shared pricing resolver for online registration and admin redemption.
     *
     * @return array{base_price: float, tax_amount: float, total_amount: float, vat_percentage: float|int|string, tax_inclusive: bool, currency: string, discount_amount: float, promo_code_id?: int, promo_code?: string}
     */
    public function priceWithOptionalPromo(
        RegistrationCategory $category,
        Event $event,
        ?string $promoCode,
        string $email,
        ?int $exceptRegistrationId = null,
    ): array {
        $pricing = app(RegistrationService::class)->calculatePrice($category, $event);
        $pricing['discount_amount'] = 0.0;

        $code = strtoupper(trim((string) $promoCode));
        if ($code === '') {
            return $pricing;
        }

        $promo = $this->findUsable($code, $event, $email, $exceptRegistrationId);

        return $this->applyToPricing($pricing, $promo);
    }

    /**
     * @param  array{base_price: float|int|string, tax_amount: float|int|string, total_amount: float|int|string, currency: string, discount_amount?: float|int|string, promo_code_id?: int|null, promo_code?: string|null}  $pricing
     * @return array{base_price: float|int|string, tax_amount: float|int|string, total_amount: float|int|string, currency: string, discount_amount: float|int|string, promo_code_id: int|null, promo_code: string|null}
     */
    public function registrationAttributesFromPricing(array $pricing): array
    {
        return [
            'base_price' => $pricing['base_price'],
            'tax_amount' => $pricing['tax_amount'],
            'total_amount' => $pricing['total_amount'],
            'currency' => $pricing['currency'],
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'promo_code_id' => $pricing['promo_code_id'] ?? null,
            'promo_code' => $pricing['promo_code'] ?? null,
        ];
    }

    /**
     * Apply a promo code to an existing registration (admin + shared redemption path).
     */
    public function redeemOnRegistration(
        Registration $registration,
        string $code,
        bool $replaceExisting = false,
    ): Registration {
        $normalizedCode = strtoupper(trim($code));
        if ($normalizedCode === '') {
            throw ValidationException::withMessages([
                'promo_code' => 'Enter a promo code to redeem.',
            ]);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $normalizedCode, $replaceExisting) {
            /** @var Registration $locked */
            $locked = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->with(['registrationCategory', 'event'])
                ->firstOrFail();

            $event = $locked->event ?? Event::getCurrentEvent();
            if (! $event) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Unable to resolve the event for this registration.',
                ]);
            }

            $category = $locked->registrationCategory;
            if (! $category) {
                throw ValidationException::withMessages([
                    'promo_code' => 'This registration has no category to price against.',
                ]);
            }

            if ($locked->promo_code_id) {
                if (strtoupper((string) $locked->promo_code) === $normalizedCode) {
                    throw ValidationException::withMessages([
                        'promo_code' => 'This promo code is already applied to the registration.',
                    ]);
                }

                if (! $replaceExisting) {
                    throw ValidationException::withMessages([
                        'promo_code' => 'A promo code is already applied. Enable replace to swap it.',
                    ]);
                }

                $this->releaseRedemptionById((int) $locked->promo_code_id);
            }

            $pricing = $this->priceWithOptionalPromo(
                $category,
                $event,
                $normalizedCode,
                (string) $locked->email,
                $locked->id,
            );

            $locked->fill($this->registrationAttributesFromPricing($pricing));
            $locked->save();

            if (! empty($pricing['promo_code_id'])) {
                $promo = PromoCode::query()->find($pricing['promo_code_id']);
                if ($promo) {
                    $this->recordRedemption($promo);
                }
            }

            return $locked->fresh(['registrationCategory', 'event']);
        });
    }

    /**
     * Remove an applied promo and restore category pricing.
     */
    public function removeFromRegistration(Registration $registration): Registration
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($registration) {
            /** @var Registration $locked */
            $locked = Registration::query()
                ->whereKey($registration->id)
                ->lockForUpdate()
                ->with(['registrationCategory', 'event'])
                ->firstOrFail();

            if (! $locked->promo_code_id) {
                throw ValidationException::withMessages([
                    'promo_code' => 'No promo code is applied to this registration.',
                ]);
            }

            $event = $locked->event ?? Event::getCurrentEvent();
            $category = $locked->registrationCategory;

            if (! $event || ! $category) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Unable to restore pricing for this registration.',
                ]);
            }

            $this->releaseRedemptionById((int) $locked->promo_code_id);

            $pricing = app(RegistrationService::class)->calculatePrice($category, $event);
            $locked->fill([
                'base_price' => $pricing['base_price'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'currency' => $pricing['currency'],
                'discount_amount' => 0,
                'promo_code_id' => null,
                'promo_code' => null,
            ]);
            $locked->save();

            return $locked->fresh(['registrationCategory', 'event']);
        });
    }

    public function recordRedemption(PromoCode $promo): void
    {
        $locked = PromoCode::query()->whereKey($promo->id)->lockForUpdate()->first();
        if (! $locked) {
            throw new InvalidArgumentException('Promo code not found.');
        }

        if (! $locked->hasRemainingUses()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code has reached its usage limit.',
            ]);
        }

        $locked->increment('used_count');
    }

    public function releaseRedemptionById(int $promoCodeId): void
    {
        $locked = PromoCode::query()->whereKey($promoCodeId)->lockForUpdate()->first();
        if (! $locked) {
            return;
        }

        if ($locked->used_count > 0) {
            $locked->decrement('used_count');
        }
    }

    private function discountAmountForBase(PromoCode $promo, float $base): float
    {
        if ($promo->discount_type === PromoDiscountType::Percentage) {
            return min($base, $base * ((float) $promo->discount_value / 100));
        }

        return min($base, (float) $promo->discount_value);
    }

    private function looksLikeEmailHeader(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['email', 'email_address', 'email address', 'e-mail'], true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?PromoCode $existing = null): array
    {
        $type = $data['discount_type'] instanceof PromoDiscountType
            ? $data['discount_type']
            : PromoDiscountType::from((string) $data['discount_type']);

        $event = Event::getCurrentEvent();
        $currency = $type === PromoDiscountType::Fixed
            ? ($data['currency'] ?? $existing?->currency ?? $event?->currency ?? 'AED')
            : null;

        return [
            'code' => strtoupper(trim((string) $data['code'])),
            'name' => Arr::get($data, 'name'),
            'description' => Arr::get($data, 'description'),
            'discount_type' => $type->value,
            'discount_value' => $data['discount_value'],
            'currency' => $currency,
            'starts_at' => Arr::get($data, 'starts_at') ?: null,
            'expires_at' => Arr::get($data, 'expires_at') ?: null,
            'max_total_uses' => Arr::get($data, 'max_total_uses') !== null && Arr::get($data, 'max_total_uses') !== ''
                ? (int) $data['max_total_uses']
                : null,
            'max_uses_per_email' => Arr::get($data, 'max_uses_per_email') !== null && Arr::get($data, 'max_uses_per_email') !== ''
                ? (int) $data['max_uses_per_email']
                : null,
            'is_active' => (bool) Arr::get($data, 'is_active', true),
            'restrict_to_email_list' => (bool) Arr::get($data, 'restrict_to_email_list', false),
        ];
    }
}
