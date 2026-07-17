<?php

namespace App\Sales\Services;

use App\Sales\Enums\InquiryFormStatus;
use App\Sales\Enums\SalesFieldType;
use App\Sales\Enums\SubmissionStatus;
use App\Sales\Models\InquiryForm;
use App\Sales\Models\InquirySubmission;
use App\Sales\Models\Pipeline;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InquirySubmissionService
{
    public function __construct(
        private SalesActivityLogger $activities,
        private DealService $deals,
    ) {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = InquirySubmission::query()
            ->with('form')
            ->orderByDesc('created_at');

        if (! empty($filters['form_id'])) {
            $query->where('sales_inquiry_form_id', $filters['form_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('submitter_name', 'like', "%{$search}%")
                    ->orWhere('submitter_email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 20)->withQueryString();
    }

    public function submit(InquiryForm $form, array $data, ?Request $request = null): InquirySubmission
    {
        if ($form->status !== InquiryFormStatus::Published) {
            throw ValidationException::withMessages([
                'form' => 'This form is not accepting submissions.',
            ]);
        }

        $form->load('fields');
        $this->validateAnswers($form, $data);

        return DB::transaction(function () use ($form, $data, $request) {
            $answers = $data['answers'] ?? [];
            $submission = InquirySubmission::query()->create([
                'sales_inquiry_form_id' => $form->id,
                'reference' => $this->nextReference(),
                'status' => SubmissionStatus::New,
                'submitter_name' => $data['submitter_name'] ?? $answers['name'] ?? null,
                'submitter_email' => $data['submitter_email'] ?? $answers['email'] ?? null,
                'submitter_phone' => $data['submitter_phone'] ?? $answers['phone'] ?? null,
                'company_name' => $data['company_name'] ?? $answers['company_name'] ?? null,
                'answers' => $answers,
                'source_url' => $data['source_url'] ?? $request?->headers->get('referer'),
                'embed_domain' => $data['embed_domain'] ?? null,
                'utm' => $data['utm'] ?? null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);

            $this->storeFiles($form, $submission, $data['files'] ?? []);

            $this->activities->log($submission, 'inquiry_submission.received', 'New submission '.$submission->reference);

            if ($form->auto_create_deal && $form->sales_pipeline_id) {
                $this->convertToDeal($submission->fresh(['form.fields', 'files']));
            }

            return $submission->fresh(['form', 'files']);
        });
    }

    public function updateStatus(InquirySubmission $submission, SubmissionStatus $status, ?string $notes = null): InquirySubmission
    {
        $submission->update([
            'status' => $status,
            'internal_notes' => $notes ?? $submission->internal_notes,
        ]);

        $this->activities->log($submission, 'inquiry_submission.status_updated', 'Status changed to '.$status->label());

        return $submission;
    }

    public function assign(InquirySubmission $submission, ?int $adminId): InquirySubmission
    {
        $submission->update(['assigned_admin_id' => $adminId]);
        $this->activities->log($submission, 'inquiry_submission.assigned', 'Submission assigned');

        return $submission;
    }

    public function convertToDeal(InquirySubmission $submission): InquirySubmission
    {
        if ($submission->sales_deal_id) {
            throw ValidationException::withMessages([
                'submission' => 'This submission has already been converted to a deal.',
            ]);
        }

        $form = $submission->form()->with('fields')->firstOrFail();

        if (! $form->sales_pipeline_id) {
            throw ValidationException::withMessages([
                'pipeline' => 'Configure a target pipeline on the inquiry form before converting.',
            ]);
        }

        $pipeline = Pipeline::query()->with('stages')->findOrFail($form->sales_pipeline_id);
        $dealData = $this->mapSubmissionToDeal($form, $submission, $pipeline);

        return DB::transaction(function () use ($submission, $pipeline, $dealData) {
            $deal = $this->deals->create($pipeline, $dealData);

            $submission->update([
                'sales_deal_id' => $deal->id,
                'status' => SubmissionStatus::Converted,
                'converted_at' => now(),
            ]);

            $this->activities->log($submission, 'inquiry_submission.converted', 'Converted to deal '.$deal->reference);

            return $submission->fresh(['deal', 'form']);
        });
    }

    public function markSpam(InquirySubmission $submission): InquirySubmission
    {
        return $this->updateStatus($submission, SubmissionStatus::Spam);
    }

    public function archive(InquirySubmission $submission): InquirySubmission
    {
        return $this->updateStatus($submission, SubmissionStatus::Archived);
    }

    public function delete(InquirySubmission $submission): void
    {
        foreach ($submission->files as $file) {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        }

        $submission->delete();
        $this->activities->log($submission, 'inquiry_submission.deleted', 'Submission deleted');
    }

    public function nextReference(): string
    {
        $year = now()->year;
        $prefix = "SUB-{$year}-";

        $latest = InquirySubmission::withTrashed()
            ->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function validateAnswers(InquiryForm $form, array $data): void
    {
        $answers = $data['answers'] ?? [];
        $errors = [];

        foreach ($form->fields()->where('is_active', true)->get() as $field) {
            if ($field->type === SalesFieldType::Upload) {
                continue;
            }

            if ($field->is_required) {
                $value = $answers[$field->key] ?? null;
                if ($value === null || $value === '' || $value === []) {
                    $errors["answers.{$field->key}"] = "{$field->label} is required.";
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, UploadedFile|list<UploadedFile>>  $files
     */
    private function storeFiles(InquiryForm $form, InquirySubmission $submission, array $files): void
    {
        $uploadFields = $form->fields->filter(fn ($f) => $f->type === SalesFieldType::Upload);

        foreach ($uploadFields as $field) {
            $file = $files[$field->key] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('sales-inquiry-submissions/'.$submission->public_id, 'local');

            $submission->files()->create([
                'field_key' => $field->key,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
            ]);
        }
    }

    private function mapSubmissionToDeal(InquiryForm $form, InquirySubmission $submission, Pipeline $pipeline): array
    {
        $answers = $submission->answers ?? [];
        $customFields = [];
        $contact = [
            'name' => $submission->submitter_name ?? 'Unknown',
            'email' => $submission->submitter_email,
            'phone' => $submission->submitter_phone,
            'company_name' => $submission->company_name,
        ];

        $dealTitle = $submission->company_name ?: ($submission->submitter_name ?: 'Inquiry '.$submission->reference);
        $dealData = [
            'title' => $dealTitle,
            'lead_source' => 'inquiry_form:'.$form->slug,
            'custom_field_answers' => [],
            'contact' => $contact,
        ];

        if ($form->default_stage_id) {
            $dealData['sales_pipeline_stage_id'] = $form->default_stage_id;
        }

        foreach ($form->fields as $field) {
            $value = $answers[$field->key] ?? null;
            if ($value === null) {
                continue;
            }

            if ($field->map_to_contact_field) {
                $contact[$field->map_to_contact_field] = is_array($value) ? implode(', ', $value) : $value;
            }

            if ($field->map_to_deal_field) {
                if ($field->map_to_deal_field === 'title') {
                    $dealData['title'] = is_array($value) ? implode(', ', $value) : (string) $value;
                } elseif ($field->map_to_deal_field === 'value') {
                    $dealData['value'] = $value;
                } elseif ($field->map_to_deal_field === 'description') {
                    $dealData['description'] = is_array($value) ? implode(', ', $value) : (string) $value;
                } else {
                    $customFields[$field->map_to_deal_field] = $value;
                }
            }
        }

        $mappings = $form->field_mappings ?? [];
        foreach ($mappings as $formKey => $dealKey) {
            if (isset($answers[$formKey])) {
                $customFields[$dealKey] = $answers[$formKey];
            }
        }

        $dealData['custom_field_answers'] = $customFields;
        $dealData['contact'] = $contact;

        return $dealData;
    }
}
