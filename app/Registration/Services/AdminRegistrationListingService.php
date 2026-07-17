<?php

namespace App\Registration\Services;

use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Registration\Data\AdminRegistrationListItem;
use App\Registration\Models\RegistrationDraft;
use App\Services\RegistrationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class AdminRegistrationListingService
{
    public function __construct(
        private RegistrationService $registrations
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, AdminRegistrationListItem>
     */
    public function paginate(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $stage = $filters['stage'] ?? 'all';
        $page = max(1, (int) request()->input('page', 1));
        $hasStepFilter = !empty($filters['abandoned_step']);

        if ($stage === 'registered' && !$hasStepFilter) {
            return $this->paginateRegistrations($filters, $perPage);
        }

        if ($stage === 'registered') {
            $items = collect();
        } elseif ($stage === 'draft' || $hasStepFilter) {
            $items = $this->draftItems($filters);
        } else {
            $items = $this->draftItems($filters)
                ->concat($this->registrationItems($filters))
                ->sortByDesc(fn (AdminRegistrationListItem $item) => $item->createdAt->getTimestamp())
                ->values();
        }

        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, int>
     */
    public function statistics(array $filters = []): array
    {
        $base = $this->registrations->getStatistics();
        $base['drafts'] = $this->activeDraftsQuery()->count();

        return $base;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, AdminRegistrationListItem>
     */
    private function paginateRegistrations(array $filters, int $perPage): LengthAwarePaginator
    {
        $paginator = $this->registrations->getAllRegistrations($this->registrationFilters($filters));
        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn (Registration $registration) => AdminRegistrationListItem::fromRegistration($registration)
            )
        );

        return $paginator->appends(request()->query());
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, AdminRegistrationListItem>
     */
    private function registrationItems(array $filters): Collection
    {
        $registrationFilters = $this->registrationFilters($filters);
        $query = Registration::with([
            'registrationCategory',
            'registrationStatus',
            'exhibitor',
            'group',
            'industry',
            'businessActivity',
        ]);

        if (!empty($registrationFilters['category_id'])) {
            $query->where('registration_category_id', $registrationFilters['category_id']);
        }
        if (!empty($registrationFilters['status_id'])) {
            $query->where('registration_status_id', $registrationFilters['status_id']);
        }
        if (!empty($registrationFilters['payment_status'])) {
            $query->where('payment_status', $registrationFilters['payment_status']);
        }
        if (!empty($registrationFilters['registration_type'])) {
            $query->where('registration_type', $registrationFilters['registration_type']);
        }
        if (array_key_exists('checked_in', $registrationFilters) && $registrationFilters['checked_in'] !== '') {
            $query->where('checked_in', $registrationFilters['checked_in']);
        }
        if (!empty($registrationFilters['search'])) {
            $search = $registrationFilters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('badge_number', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->get()->map(
            fn (Registration $registration) => AdminRegistrationListItem::fromRegistration($registration)
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, AdminRegistrationListItem>
     */
    private function draftItems(array $filters): Collection
    {
        if ($this->excludesDraftsDueToFilters($filters)) {
            return collect();
        }

        $drafts = $this->activeDraftsQuery()->orderByDesc('created_at')->get();
        $search = strtolower(trim((string) ($filters['search'] ?? '')));
        $categoryFilter = !empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $stepFilter = trim((string) ($filters['abandoned_step'] ?? ''));

        $categoryIds = $drafts
            ->map(fn (RegistrationDraft $draft) => (int) $draft->payloadValue('registration_category_id'))
            ->filter()
            ->unique()
            ->values();

        $categories = $categoryIds->isEmpty()
            ? collect()
            : RegistrationCategory::query()->whereIn('id', $categoryIds)->get()->keyBy('id');

        return $drafts->map(function (RegistrationDraft $draft) use ($categories, $search, $categoryFilter, $stepFilter) {
            $categoryId = (int) $draft->payloadValue('registration_category_id');
            $category = $categoryId ? $categories->get($categoryId) : null;
            $currentStep = $draft->current_step instanceof \BackedEnum
                ? $draft->current_step->value
                : (string) $draft->current_step;

            if ($categoryFilter && $categoryFilter !== $categoryId) {
                return null;
            }

            if ($stepFilter !== '' && $stepFilter !== $currentStep) {
                return null;
            }

            if ($search !== '' && !$this->draftMatchesSearch($draft, $search)) {
                return null;
            }

            return AdminRegistrationListItem::fromDraft($draft, $category);
        })->filter()->values();
    }

    private function activeDraftsQuery()
    {
        return RegistrationDraft::query()
            ->whereNull('completed_at')
            ->whereNull('registration_id');
    }

    private function draftMatchesSearch(RegistrationDraft $draft, string $search): bool
    {
        $haystacks = [
            strtolower((string) $draft->email),
            strtolower(trim((string) $draft->payloadValue('first_name').' '.(string) $draft->payloadValue('last_name'))),
            strtolower((string) $draft->payloadValue('company_name', '')),
            strtolower((string) $draft->payloadValue('phone', '')),
            strtolower('draft-'.substr((string) $draft->public_id, 0, 8)),
        ];

        foreach ($haystacks as $haystack) {
            if ($haystack !== '' && str_contains($haystack, $search)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function excludesDraftsDueToFilters(array $filters): bool
    {
        return !empty($filters['payment_status'])
            || !empty($filters['registration_type'])
            || !empty($filters['status_id'])
            || (isset($filters['checked_in']) && $filters['checked_in'] !== '' && $filters['checked_in'] !== null);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function registrationFilters(array $filters): array
    {
        return collect($filters)
            ->except('stage', 'abandoned_step')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }
}
