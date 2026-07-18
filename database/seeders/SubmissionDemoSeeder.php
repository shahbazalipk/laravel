<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Submissions\Models\PortalUser;
use App\Submissions\Models\Reviewer;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            $this->command?->warn('SubmissionDemoSeeder skipped outside local/development/testing.');

            return;
        }

        $event = Event::getCurrentEvent();
        $orgId = $event?->organization_id;
        if (! $event || ! $orgId) {
            $this->command?->warn('SubmissionDemoSeeder requires an event with an organization context.');

            return;
        }

        config(['event.event_id' => $event->getKey(), 'event.org_id' => $orgId]);
        app()->instance('current.event', $event);

        DB::transaction(function (): void {
            $type = SubmissionType::query()->updateOrCreate(
                ['code' => 'DEMO-ABS'],
                [
                    'name' => 'Conference Session Proposal',
                    'slug' => 'conference-session-proposal',
                    'public_title' => 'Share Your Expertise',
                    'description' => 'Submit a practical session proposal for the demo conference.',
                    'instructions' => 'Describe the audience, outcomes, and delivery format for your session.',
                    'category' => 'abstract',
                    'status' => 'active',
                    'timezone' => config('app.timezone', 'UTC'),
                    'allow_drafts' => true,
                    'allow_editing_after_submission' => false,
                    'allow_anonymous_review' => true,
                    'enable_scoring' => true,
                    'enable_revisions' => true,
                    'enable_speaker_onboarding' => true,
                    'number_prefix' => 'DEMO',
                    'number_pattern' => '{PREFIX}-{YEAR}-{NUMBER:4}',
                    'published_form_version' => 1,
                    'published_at' => now(),
                    'decision_rules' => ['minimum_reviews' => 1, 'minimum_score' => 60],
                    'success_message' => 'Your proposal has been received.',
                ],
            );

            $proposal = $type->sections()->updateOrCreate(
                ['slug' => 'proposal'],
                ['title' => 'Session proposal', 'description' => 'Tell the committee what attendees will learn.', 'sort_order' => 1],
            );
            $speaker = $type->sections()->updateOrCreate(
                ['slug' => 'speaker'],
                ['title' => 'Speaker details', 'description' => 'Information used after blind review.', 'sort_order' => 2],
            );

            $title = $proposal->questions()->updateOrCreate(
                ['key' => 'session_title'],
                ['label' => 'Session title', 'type' => 'text', 'is_required' => true, 'sort_order' => 1],
            );
            $abstract = $proposal->questions()->updateOrCreate(
                ['key' => 'abstract'],
                ['label' => 'Abstract', 'type' => 'textarea', 'is_required' => true, 'sort_order' => 2],
            );
            $format = $proposal->questions()->updateOrCreate(
                ['key' => 'format'],
                ['label' => 'Format', 'type' => 'select', 'is_required' => true, 'sort_order' => 3],
            );
            foreach (['talk' => 'Talk', 'workshop' => 'Workshop', 'panel' => 'Panel'] as $value => $label) {
                $format->options()->updateOrCreate(['value' => $value], ['label' => $label, 'sort_order' => array_search($value, ['talk', 'workshop', 'panel'], true) + 1]);
            }
            $speaker->questions()->updateOrCreate(
                ['key' => 'speaker_bio'],
                [
                    'label' => 'Speaker biography',
                    'type' => 'textarea',
                    'is_required' => true,
                    'hidden_during_anonymous_review' => true,
                    'sort_order' => 1,
                ],
            );

            $stages = [];
            foreach ([
                ['draft', 'Draft', 'draft', true, false],
                ['submitted', 'Submitted', 'submitted', false, false],
                ['under-review', 'Under Review', 'review', false, false],
                ['revision', 'Revision Requested', 'revision', false, false],
                ['selected', 'Selected', 'accepted', false, true],
                ['rejected', 'Rejected', 'rejected', false, true],
            ] as $order => [$slug, $name, $category, $initial, $terminal]) {
                $stages[$slug] = $type->workflowStages()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'category' => $category,
                        'sort_order' => $order,
                        'is_initial' => $initial,
                        'is_terminal' => $terminal,
                    ],
                );
            }
            $type->forceFill(['default_stage_id' => $stages['draft']->id])->save();

            foreach ([
                ['draft', 'submitted', []],
                ['submitted', 'under-review', []],
                ['under-review', 'revision', ['note_required' => true]],
                ['revision', 'under-review', []],
                ['under-review', 'selected', ['completed_reviews' => 1]],
                ['under-review', 'rejected', ['completed_reviews' => 1]],
            ] as [$from, $to, $conditions]) {
                $stages[$from]->outgoingTransitions()->updateOrCreate(
                    ['submission_type_id' => $type->id, 'to_stage_id' => $stages[$to]->id],
                    ['name' => "{$stages[$from]->name} to {$stages[$to]->name}", 'conditions' => $conditions],
                );
            }

            $scorecard = $type->scorecards()->updateOrCreate(
                ['name' => 'Program Committee Scorecard'],
                ['description' => 'Balanced content and audience-fit assessment.', 'status' => 'active', 'passing_score' => 60],
            );
            foreach ([
                ['Relevance', 2, 1],
                ['Practical value', 2, 2],
                ['Clarity', 1, 3],
            ] as [$name, $weight, $order]) {
                $scorecard->criteria()->updateOrCreate(
                    ['name' => $name],
                    ['type' => 'rating', 'weight' => $weight, 'min_score' => 1, 'max_score' => 5, 'sort_order' => $order],
                );
            }

            $reviewerUser = PortalUser::query()->updateOrCreate(
                ['email' => 'reviewer.demo@example.test'],
                ['name' => 'Riley Reviewer', 'status' => 'active', 'roles' => ['reviewer']],
            );
            $reviewer = Reviewer::query()->updateOrCreate(
                ['email' => $reviewerUser->email],
                ['portal_user_id' => $reviewerUser->id, 'name' => $reviewerUser->name, 'status' => 'active', 'capacity' => 8],
            );
            $reviewer->expertise()->updateOrCreate(['topic' => 'Event technology'], ['level' => 5]);

            $applicant = PortalUser::query()->updateOrCreate(
                ['email' => 'speaker.demo@example.test'],
                ['name' => 'Alex Speaker', 'status' => 'active', 'roles' => ['applicant']],
            );
            $draft = $this->submission($type, $applicant, 'Demo draft: Accessible Event Experiences', 'draft', true, null, $stages['draft']->id);
            $submitted = $this->submission($type, $applicant, 'Building Reliable Event Platforms', 'under_review', false, 'DEMO-'.now()->format('Y').'-0001', $stages['under-review']->id);

            foreach ([
                [$draft, $title, 'Demo draft: Accessible Event Experiences'],
                [$draft, $abstract, 'A draft proposal exploring inclusive event design.'],
                [$submitted, $title, 'Building Reliable Event Platforms'],
                [$submitted, $abstract, 'Patterns for resilient registration, content, and speaker workflows.'],
                [$submitted, $format, 'talk'],
            ] as [$submission, $question, $value]) {
                $submission->answers()->updateOrCreate(
                    ['question_id' => $question->id],
                    [
                        'question_key' => $question->key,
                        'question_label' => $question->label,
                        'question_type' => $question->type->value,
                        'answer_text' => $value,
                        'normalized_value' => mb_strtolower($value),
                    ],
                );
            }
            $submitted->reviewAssignments()->updateOrCreate(
                ['reviewer_id' => $reviewer->id],
                ['scorecard_id' => $scorecard->id, 'status' => 'assigned', 'due_at' => now()->addWeek()],
            );
        });

        $this->command?->info('Speaker submission demo data seeded.');
    }

    private function submission(
        SubmissionType $type,
        PortalUser $applicant,
        string $title,
        string $status,
        bool $isDraft,
        ?string $reference,
        int $stageId,
    ): Submission {
        return $type->submissions()->updateOrCreate(
            ['title' => $title, 'portal_user_id' => $applicant->id],
            [
                'current_stage_id' => $stageId,
                'reference' => $reference,
                'status' => $status,
                'is_draft' => $isDraft,
                'submitted_at' => $isDraft ? null : now()->subDays(2),
            ],
        );
    }
}
