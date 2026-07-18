<?php

namespace App\Submissions\Services;

use App\Submissions\Jobs\SendSubmissionCommunication;
use App\Submissions\Models\CommunicationTemplate;
use App\Submissions\Models\Submission;
use Illuminate\Validation\ValidationException;

final class CommunicationService
{
    /** @param array<string, scalar|null> $variables */
    public function queue(Submission $submission, string $event, string $recipient, array $variables = []): void
    {
        $template = CommunicationTemplate::query()->where('trigger', $event)->where('is_active', true)->latest()->first();
        if (! $template) {
            return;
        }
        $variables = [
            'applicant_name' => $submission->applicant->name,
            'submission_number' => $submission->reference_number,
            'submission_title' => $submission->title,
            'submission_type' => $submission->type->name,
            'current_stage' => $submission->currentStage?->name,
            'decision' => $submission->final_decision,
            ...$variables,
        ];
        $subject = $this->render($template->subject, $variables);
        $html = $this->render($template->body_html, $variables);
        $text = $this->render($template->body_text ?: strip_tags($html), $variables);

        SendSubmissionCommunication::dispatch(
            $submission->event_id,
            $submission->org_id,
            $recipient,
            $subject,
            $html,
            $text,
            ['submission_id' => $submission->getKey(), 'event_key' => $event],
        );
    }

    /** @param array<string, scalar|null> $variables */
    public function render(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{([a-z0-9_]+)\}\}/i', function (array $match) use ($variables): string {
            if (! array_key_exists($match[1], $variables)) {
                throw ValidationException::withMessages(['template' => "Unknown merge field: {$match[1]}"]);
            }

            return e((string) $variables[$match[1]]);
        }, $template) ?? $template;
    }
}
