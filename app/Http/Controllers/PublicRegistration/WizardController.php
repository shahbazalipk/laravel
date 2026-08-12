<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventUrl;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

abstract class WizardController extends Controller
{
    public function __construct(
        protected OnlineRegistrationContext $context,
        protected RegistrationDraftService $drafts
    ) {}

    protected function bootContext(string $slug): array
    {
        $event = $this->context->resolveEvent();
        $eventUrl = $this->context->resolveEventUrl($slug, $event);

        return [$event, $eventUrl];
    }

    protected function draftKeyFromRequest(Request $request): ?string
    {
        $reg = $request->route('reg');

        return is_string($reg) && $reg !== '' ? $reg : null;
    }

    protected function draftFromRequest(Request $request, Event $event, ?string $urlToken = null): ?RegistrationDraft
    {
        return $this->drafts->resolveFromRequest(
            $event,
            $request->cookie(RegistrationDraftService::COOKIE_NAME),
            $urlToken,
            $this->draftKeyFromRequest($request)
        );
    }

    protected function requireDraft(Request $request, Event $event, ?string $urlToken = null): RegistrationDraft
    {
        $draft = $this->draftFromRequest($request, $event, $urlToken);

        if (!$draft) {
            throw new InvalidArgumentException('We could not find your saved progress. Please start again with your email.');
        }

        $this->drafts->assertAccessible($draft, $event);

        return $draft;
    }

    protected function redirectToStep(
        string $slug,
        RegistrationWizardStep $step,
        ?RegistrationDraft $draft = null
    ): RedirectResponse {
        if ($draft) {
            return redirect()->route('online.registration.reg.step.'.$step->value, [
                'slug' => $slug,
                'reg' => $this->drafts->encodeUrlKey($draft),
            ]);
        }

        return redirect()->route('online.registration.step.'.$step->value, [
            'slug' => $slug,
        ]);
    }

    protected function ensureDraftUrl(Request $request, string $slug, RegistrationDraft $draft, RegistrationWizardStep $step): ?RedirectResponse
    {
        if ($this->draftKeyFromRequest($request)) {
            return null;
        }

        return $this->redirectToStep($slug, $step, $draft);
    }

    protected function wizardView(
        string $stepView,
        Event $event,
        EventUrl $eventUrl,
        string $slug,
        ?RegistrationDraft $draft,
        RegistrationWizardStep $activeStep,
        array $extra = []
    ): View {
        $reg = $draft ? $this->drafts->encodeUrlKey($draft) : null;
        $eventUrlSponsors = $eventUrl->sponsors()
            ->active()
            ->visibleOnline()
            ->ordered()
            ->get();
        $eventUrlPartners = $eventUrl->partners()
            ->active()
            ->visibleOnline()
            ->ordered()
            ->get();

        return view($stepView, array_merge([
            'event' => $event,
            'eventUrl' => $eventUrl,
            'slug' => $slug,
            'draft' => $draft,
            'reg' => $reg,
            'activeStep' => $activeStep,
            'steps' => RegistrationWizardStep::ordered(),
            'payload' => $draft?->payload ?? [],
            'eventUrlSponsors' => $eventUrlSponsors,
            'eventUrlPartners' => $eventUrlPartners,
            'wizardStepRoute' => function (string $step, ?string $action = null) use ($slug, $reg): string {
                $name = $reg
                    ? 'online.registration.reg.step.'.$step.($action ? '.'.$action : '')
                    : 'online.registration.step.'.$step.($action ? '.'.$action : '');

                $params = ['slug' => $slug];
                if ($reg) {
                    $params['reg'] = $reg;
                }

                return route($name, $params);
            },
        ], $extra));
    }
}
