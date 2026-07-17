<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\StoreInquiryFormRequest;
use App\Sales\Enums\SalesFieldType;
use App\Sales\Models\InquiryForm;
use App\Sales\Models\Pipeline;
use App\Sales\Models\PipelineType;
use App\Sales\Services\InquiryFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryFormController extends Controller
{
    public function __construct(private readonly InquiryFormService $service)
    {
    }

    public function index(Request $request): View
    {
        $forms = $this->service->list($request->only(['status', 'search']));

        return view('admin.sales.inquiry-forms.index', compact('forms'));
    }

    public function create(): View
    {
        return view('admin.sales.inquiry-forms.create', [
            'fieldTypes' => SalesFieldType::cases(),
            'pipelineTypes' => PipelineType::query()->active()->orderBy('name')->get(),
            'pipelines' => Pipeline::query()->with('stages')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreInquiryFormRequest $request): RedirectResponse
    {
        $form = $this->service->create($request->validated());

        return redirect()->route('admin.sales.inquiry-forms.show', $form)
            ->with('success', 'Inquiry form created.');
    }

    public function show(InquiryForm $inquiry_form): View
    {
        $inquiry_form->load(['fields', 'pipelineType', 'pipeline', 'defaultStage']);

        return view('admin.sales.inquiry-forms.show', ['form' => $inquiry_form]);
    }

    public function edit(InquiryForm $inquiry_form): View
    {
        $inquiry_form->load('fields');

        return view('admin.sales.inquiry-forms.edit', [
            'form' => $inquiry_form,
            'fieldTypes' => SalesFieldType::cases(),
            'pipelineTypes' => PipelineType::query()->active()->orderBy('name')->get(),
            'pipelines' => Pipeline::query()->with('stages')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, InquiryForm $inquiry_form): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'heading' => ['nullable', 'string', 'max:255'],
            'intro_text' => ['nullable', 'string', 'max:10000'],
            'submit_button_label' => ['nullable', 'string', 'max:64'],
            'success_message' => ['nullable', 'string', 'max:5000'],
            'sales_pipeline_type_id' => ['nullable', 'exists:sales_pipeline_types,id'],
            'sales_pipeline_id' => ['nullable', 'exists:sales_pipelines,id'],
            'default_stage_id' => ['nullable', 'exists:sales_pipeline_stages,id'],
            'auto_create_deal' => ['nullable', 'boolean'],
            'allowed_domains' => ['nullable', 'string', 'max:2000'],
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.type' => ['required_with:fields', 'string'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.map_to_deal_field' => ['nullable', 'string', 'max:64'],
            'fields.*.map_to_contact_field' => ['nullable', 'string', 'max:64'],
            'fields.*.public_id' => ['nullable', 'string'],
        ]);
        $data['auto_create_deal'] = $request->boolean('auto_create_deal');

        $this->service->update($inquiry_form, $data);

        return redirect()->route('admin.sales.inquiry-forms.show', $inquiry_form)
            ->with('success', 'Inquiry form updated.');
    }

    public function destroy(InquiryForm $inquiry_form): RedirectResponse
    {
        $this->service->delete($inquiry_form);

        return redirect()->route('admin.sales.inquiry-forms.index')
            ->with('success', 'Inquiry form deleted.');
    }

    public function publish(InquiryForm $inquiry_form): RedirectResponse
    {
        $this->service->publish($inquiry_form);

        return back()->with('success', 'Inquiry form published.');
    }

    public function unpublish(InquiryForm $inquiry_form): RedirectResponse
    {
        $this->service->unpublish($inquiry_form);

        return back()->with('success', 'Inquiry form unpublished.');
    }

    public function duplicate(InquiryForm $inquiry_form): RedirectResponse
    {
        $copy = $this->service->duplicate($inquiry_form);

        return redirect()->route('admin.sales.inquiry-forms.edit', $copy)
            ->with('success', 'Inquiry form duplicated.');
    }
}
