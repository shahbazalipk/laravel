<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventUrl;
use App\Models\Partner;
use App\Models\RegistrationCategory;
use App\Models\Sponsor;
use App\Registration\Enums\RegistrationFormat;
use App\Services\CustomHtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventUrlController extends Controller
{
    public function __construct(
        private CustomHtmlSanitizer $htmlSanitizer
    ) {}

    public function index(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $query = EventUrl::where('event_id', $eventId)
            ->where('organization_id', $organizationId);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $urls = $query->orderBy('type')->orderBy('name')->paginate(20);

        return view('admin.event-urls.index', compact('urls'));
    }

    public function create()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $categories = RegistrationCategory::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->where('is_active', true)
            ->get();

        $types = ['online', 'onsite', 'exhibitors', 'groups', 'badge'];
        [$sponsors, $partners] = $this->availableSponsorships();

        return view('admin.event-urls.create', compact('categories', 'types', 'sponsors', 'partners'));
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $validated = $request->validate($this->rules());
        $validated = $this->normalizeRegistrationFormat($validated);

        $validated['event_id'] = $eventId;
        $validated['organization_id'] = $organizationId;
        $validated['is_active'] = $request->has('is_active');
        $validated['allow_reprint'] = $request->has('allow_reprint');
        $validated['allow_print_from_photo'] = $request->has('allow_print_from_photo');
        $validated['enable_barcode_scanner'] = $request->has('enable_barcode_scanner');
        $validated['enable_manual_input'] = $request->has('enable_manual_input');
        $validated['custom_html'] = $this->htmlSanitizer->sanitize($validated['custom_html'] ?? null);
        $sponsorIds = $validated['sponsor_ids'] ?? [];
        $partnerIds = $validated['partner_ids'] ?? [];
        unset($validated['sponsor_ids'], $validated['partner_ids']);

        DB::transaction(function () use ($validated, $sponsorIds, $partnerIds): void {
            $eventUrl = EventUrl::create($validated);
            $eventUrl->sponsors()->sync($sponsorIds);
            $eventUrl->partners()->sync($partnerIds);
        });

        return redirect()->route('admin.event-urls.index')
            ->with('success', 'URL created successfully');
    }

    public function edit(EventUrl $eventUrl)
    {
        $this->assertCurrentTenant($eventUrl);

        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $categories = RegistrationCategory::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->where('is_active', true)
            ->get();

        $types = ['online', 'onsite', 'exhibitors', 'groups', 'badge'];
        [$sponsors, $partners] = $this->availableSponsorships();
        $eventUrl->load('sponsors:id', 'partners:id');

        return view('admin.event-urls.edit', compact('eventUrl', 'categories', 'types', 'sponsors', 'partners'));
    }

    public function update(Request $request, EventUrl $eventUrl)
    {
        $this->assertCurrentTenant($eventUrl);
        $validated = $request->validate($this->rules($eventUrl));
        $validated = $this->normalizeRegistrationFormat($validated);

        $validated['is_active'] = $request->has('is_active');
        $validated['allow_reprint'] = $request->has('allow_reprint');
        $validated['allow_print_from_photo'] = $request->has('allow_print_from_photo');
        $validated['enable_barcode_scanner'] = $request->has('enable_barcode_scanner');
        $validated['enable_manual_input'] = $request->has('enable_manual_input');
        $validated['custom_html'] = $this->htmlSanitizer->sanitize($validated['custom_html'] ?? null);
        $sponsorIds = $validated['sponsor_ids'] ?? [];
        $partnerIds = $validated['partner_ids'] ?? [];
        unset($validated['sponsor_ids'], $validated['partner_ids']);

        DB::transaction(function () use ($eventUrl, $validated, $sponsorIds, $partnerIds): void {
            $eventUrl->update($validated);
            $eventUrl->sponsors()->sync($sponsorIds);
            $eventUrl->partners()->sync($partnerIds);
        });

        return redirect()->route('admin.event-urls.index')
            ->with('success', 'URL updated successfully');
    }

    public function destroy(EventUrl $eventUrl)
    {
        $this->assertCurrentTenant($eventUrl);
        $eventUrl->delete();

        return redirect()->route('admin.event-urls.index')
            ->with('success', 'URL deleted successfully');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?EventUrl $eventUrl = null): array
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');
        $slugRule = Rule::unique('event_urls', 'slug');
        if ($eventUrl) {
            $slugRule->ignore($eventUrl->id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $slugRule],
            'type' => ['required', Rule::in(['online', 'onsite', 'exhibitors', 'groups', 'badge'])],
            'registration_format' => ['nullable', Rule::enum(RegistrationFormat::class)],
            'is_active' => ['sometimes', 'boolean'],
            'enabled_categories' => ['nullable', 'array'],
            'enabled_categories.*' => [
                Rule::exists('registration_categories', 'id')
                    ->where('event_id', $eventId)
                    ->where('org_id', $organizationId),
            ],
            'allow_reprint' => ['sometimes', 'boolean'],
            'allow_print_from_photo' => ['sometimes', 'boolean'],
            'enable_barcode_scanner' => ['sometimes', 'boolean'],
            'enable_manual_input' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'custom_html' => ['nullable', 'string', 'max:20000'],
            'sponsor_ids' => ['nullable', 'array'],
            'sponsor_ids.*' => [
                'integer',
                Rule::exists('sponsors', 'id')
                    ->where('event_id', $eventId)
                    ->where('org_id', $organizationId)
                    ->where('is_active', true)
                    ->where('visible_online', true)
                    ->whereNull('deleted_at'),
            ],
            'partner_ids' => ['nullable', 'array'],
            'partner_ids.*' => [
                'integer',
                Rule::exists('partners', 'id')
                    ->where('event_id', $eventId)
                    ->where('org_id', $organizationId)
                    ->where('is_active', true)
                    ->where('visible_online', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeRegistrationFormat(array $validated): array
    {
        if (($validated['type'] ?? null) !== 'online') {
            $validated['registration_format'] = RegistrationFormat::MultiStep->value;

            return $validated;
        }

        $validated['registration_format'] = $validated['registration_format']
            ?? RegistrationFormat::MultiStep->value;

        return $validated;
    }

    private function assertCurrentTenant(EventUrl $eventUrl): void
    {
        abort_unless(
            (int) $eventUrl->event_id === (int) config('event.event_id')
                && (int) $eventUrl->organization_id === (int) config('event.org_id'),
            404
        );
    }

    /**
     * @return array{0: \Illuminate\Database\Eloquent\Collection, 1: \Illuminate\Database\Eloquent\Collection}
     */
    private function availableSponsorships(): array
    {
        return [
            Sponsor::query()->active()->visibleOnline()->ordered()->get(),
            Partner::query()->active()->visibleOnline()->ordered()->get(),
        ];
    }
}
