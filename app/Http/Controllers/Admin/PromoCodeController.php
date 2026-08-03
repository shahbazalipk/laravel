<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromoDiscountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromoCodeRequest;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\PromoCodeEmail;
use App\Services\PromoCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function __construct(
        private PromoCodeService $promoCodes
    ) {}

    public function index(): View
    {
        return view('admin.promo-codes.index', [
            'promoCodes' => $this->promoCodes->getAll(),
        ]);
    }

    public function create(): View
    {
        return view('admin.promo-codes.create', $this->formData());
    }

    public function store(StorePromoCodeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $promo = $this->promoCodes->create($data, $request->file('email_list_file'));

        return redirect()
            ->route('admin.promo-codes.show', $promo)
            ->with('success', 'Promo code created successfully.');
    }

    public function show(Request $request, PromoCode $promoCode): View
    {
        $promoCode->loadCount('emails');

        $search = trim((string) $request->query('q', ''));
        $usageStatus = trim((string) $request->query('status', ''));
        $emails = $this->promoCodes->getEmails(
            $promoCode,
            $search !== '' ? $search : null,
            $usageStatus !== '' ? $usageStatus : null,
        );
        $usageSummary = $this->promoCodes->usageSummary($promoCode);
        $redemptions = $this->promoCodes->getRedemptions($promoCode);

        return view('admin.promo-codes.show', [
            'promoCode' => $promoCode,
            'emails' => $emails,
            'search' => $search,
            'usageStatus' => in_array($usageStatus, ['used', 'unused'], true) ? $usageStatus : '',
            'usageSummary' => $usageSummary,
            'redemptions' => $redemptions,
        ]);
    }

    public function edit(PromoCode $promoCode): View
    {
        $promoCode->loadCount('emails');

        return view('admin.promo-codes.edit', array_merge($this->formData(), [
            'promoCode' => $promoCode,
        ]));
    }

    public function update(StorePromoCodeRequest $request, PromoCode $promoCode): RedirectResponse
    {
        $data = $request->validated();
        $promo = $this->promoCodes->update($promoCode, $data, $request->file('email_list_file'));

        return redirect()
            ->route('admin.promo-codes.show', $promo)
            ->with('success', 'Promo code updated successfully.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $this->promoCodes->delete($promoCode);

        return redirect()
            ->route('admin.promo-codes.index')
            ->with('success', 'Promo code deleted successfully.');
    }

    public function toggleActive(PromoCode $promoCode): RedirectResponse
    {
        $this->promoCodes->toggleActive($promoCode);

        return redirect()
            ->back()
            ->with('success', 'Promo code status updated.');
    }

    public function clearEmails(PromoCode $promoCode): RedirectResponse
    {
        $this->promoCodes->clearEmailAllowlist($promoCode);

        if ($promoCode->restrict_to_email_list) {
            $promoCode->update(['restrict_to_email_list' => false]);
        }

        return redirect()
            ->route('admin.promo-codes.show', $promoCode)
            ->with('success', 'Email allowlist cleared.');
    }

    public function storeEmail(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $this->promoCodes->addEmail($promoCode, $validated['email']);

        return redirect()
            ->route('admin.promo-codes.show', $promoCode)
            ->with('success', 'Email added to the allowlist.');
    }

    public function updateEmail(Request $request, PromoCode $promoCode, PromoCodeEmail $promo_code_email): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $this->promoCodes->updateEmail($promoCode, $promo_code_email, $validated['email']);

        return redirect()
            ->route('admin.promo-codes.show', $promoCode)
            ->with('success', 'Email updated.');
    }

    public function destroyEmail(PromoCode $promoCode, PromoCodeEmail $promo_code_email): RedirectResponse
    {
        $this->promoCodes->deleteEmail($promoCode, $promo_code_email);

        return redirect()
            ->route('admin.promo-codes.show', $promoCode)
            ->with('success', 'Email removed from the allowlist.');
    }

    /**
     * @return array{discountTypes: array<int, PromoDiscountType>, defaultCurrency: string}
     */
    private function formData(): array
    {
        return [
            'discountTypes' => PromoDiscountType::cases(),
            'defaultCurrency' => Event::getCurrentEvent()?->currency ?: 'AED',
        ];
    }
}
