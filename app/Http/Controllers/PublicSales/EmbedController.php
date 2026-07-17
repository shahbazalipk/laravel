<?php

namespace App\Http\Controllers\PublicSales;

use App\Http\Controllers\Controller;
use App\Sales\Models\InquiryForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmbedController extends Controller
{
    public function show(Request $request, string $embed_token): View
    {
        $form = InquiryForm::query()
            ->where('embed_token', $embed_token)
            ->with(['fields' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->firstOrFail();

        abort_unless($form->status->isPubliclyAvailable(), 404);
        abort_unless($this->domainAllowed($form, $request), 403);

        return view('sales.public.embed', [
            'form' => $form,
            'embedMode' => true,
        ]);
    }

    private function domainAllowed(InquiryForm $form, Request $request): bool
    {
        $allowed = collect($form->allowed_domains ?? [])
            ->map(fn ($domain) => strtolower(trim((string) $domain)))
            ->filter()
            ->values();

        if ($allowed->isEmpty()) {
            return true;
        }

        $candidates = collect([
            $request->query('parent'),
            $request->headers->get('referer'),
            $request->headers->get('origin'),
        ])->filter()->map(function (string $value) {
            $host = parse_url($value, PHP_URL_HOST);

            return strtolower((string) ($host ?: $value));
        });

        foreach ($candidates as $host) {
            foreach ($allowed as $domain) {
                if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                    return true;
                }
            }
        }

        return false;
    }
}
