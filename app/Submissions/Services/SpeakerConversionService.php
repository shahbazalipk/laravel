<?php

namespace App\Submissions\Services;

use App\Models\Speaker;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SpeakerConversionService
{
    /** @return array<int, Speaker> */
    public function convert(Submission $submission): array
    {
        if (! $submission->is_selected) {
            throw ValidationException::withMessages(['submission' => 'Only selected submissions can be converted to speakers.']);
        }

        return DB::transaction(function () use ($submission): array {
            $people = $submission->people()->whereIn('role', ['primary_speaker', 'co_speaker', 'moderator', 'panelist', 'presenting_author'])->get();
            if ($people->isEmpty()) {
                $people = collect([[
                    'name' => $submission->applicant->name,
                    'email' => $submission->applicant->email,
                    'role' => 'primary_speaker',
                ]]);
            }
            $speakers = [];
            foreach ($people as $person) {
                $email = is_array($person) ? $person['email'] : $person->email;
                $name = is_array($person) ? $person['name'] : $person->name;
                $speaker = Speaker::query()->where('email', $email)->first();
                if (! $speaker) {
                    $speaker = Speaker::query()->create([
                        'full_name' => $name,
                        'email' => $email,
                        'phone' => is_array($person) ? null : ($person->profile['phone'] ?? null),
                        'company' => is_array($person) ? null : $person->organization,
                        'job_title' => is_array($person) ? null : ($person->profile['job_title'] ?? null),
                        'bio' => is_array($person) ? null : ($person->profile['biography'] ?? null),
                    ]);
                }
                $link = $submission->speakerLinks()->firstOrCreate(
                    ['speaker_id' => $speaker->getKey()],
                    [
                        'status' => 'linked',
                        'linked_by' => session('admin_id'),
                        'linked_at' => now(),
                        'settings' => ['role' => is_array($person) ? $person['role'] : $person->role],
                    ],
                );
                $onboarding = $link->onboarding()->firstOrCreate([], ['status' => 'not_started', 'completion_percent' => 0]);
                foreach ($submission->type->speakerChecklists()->where('is_active', true)->get() as $checklist) {
                    foreach ($checklist->items as $item) {
                        $key = is_array($item) ? ($item['key'] ?? str($item['label'] ?? 'item')->slug('_')) : str($item)->slug('_');
                        $onboarding->checklistProgress()->firstOrCreate(
                            ['speaker_checklist_id' => $checklist->getKey(), 'item_key' => $key],
                            ['status' => 'pending'],
                        );
                    }
                }
                $speakers[] = $speaker;
            }
            SubmissionActivity::record($submission, 'submission.converted_to_speaker', ['speaker_ids' => collect($speakers)->pluck('id')]);

            return $speakers;
        });
    }
}
