@extends('online.wizard.layout')

@section('title', 'Category')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Choose your category</h2>
        <p class="mt-1 text-sm text-slate-500">Totals include applicable VAT based on category and event settings.</p>
    </div>

    <form method="POST"
          action="{{ $wizardStepRoute('category', 'store') }}"
          class="space-y-5"
          data-testid="wizard-category-form">
        @csrf

        <div class="grid grid-cols-1 gap-4" data-testid="wizard-category-list">
            @forelse($categories as $category)
                @php $pricing = $category->pricing; @endphp
                <label class="block cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/40">
                    <div class="flex items-start gap-3">
                        <input type="radio"
                               name="registration_category_id"
                               value="{{ $category->id }}"
                               class="mt-1 category-radio"
                               required
                               data-needs-password="{{ $category->needs_password ? '1' : '0' }}"
                               data-needs-membership="{{ $category->need_membership_id ? '1' : '0' }}"
                               data-needs-professional="{{ $category->need_professional_student_id ? '1' : '0' }}"
                               {{ (string) old('registration_category_id', $payload['registration_category_id'] ?? '') === (string) $category->id ? 'checked' : '' }}
                               data-testid="wizard-category-{{ $category->id }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $category->name }}</p>
                                    @if($category->description)
                                        <p class="mt-1 text-sm text-slate-500">{{ $category->description }}</p>
                                    @endif
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-lg font-bold text-indigo-700">
                                        {{ number_format($pricing['total_amount'], 2) }} {{ $pricing['currency'] }}
                                    </p>
                                    @if($pricing['tax_amount'] > 0)
                                        <p class="text-xs text-slate-500">
                                            Includes {{ number_format($pricing['tax_amount'], 2) }} {{ $pricing['currency'] }} VAT
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-500">No VAT applied</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    No registration categories are available for this link.
                </div>
            @endforelse
        </div>

        <div id="categoryExtraFields" class="hidden space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div id="passwordField" class="hidden">
                <label class="mb-1 block text-sm font-medium text-slate-700">Category password *</label>
                <input type="password" name="category_password" value="{{ old('category_password') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div id="membershipField" class="hidden">
                <label class="mb-1 block text-sm font-medium text-slate-700">Membership ID *</label>
                <input type="text" name="membership_id" value="{{ old('membership_id', $payload['membership_id'] ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div id="professionalField" class="hidden">
                <label class="mb-1 block text-sm font-medium text-slate-700">Professional / Student ID *</label>
                <input type="text" name="professional_student_id" value="{{ old('professional_student_id', $payload['professional_student_id'] ?? '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
            <a href="{{ $wizardStepRoute('information') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                    data-testid="wizard-category-continue"
                    @disabled($categories->isEmpty())>
                Save & continue
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function syncCategoryExtras() {
        const selected = document.querySelector('.category-radio:checked');
        const wrap = document.getElementById('categoryExtraFields');
        const passwordField = document.getElementById('passwordField');
        const membershipField = document.getElementById('membershipField');
        const professionalField = document.getElementById('professionalField');
        if (!selected) {
            wrap.classList.add('hidden');
            return;
        }
        const needsPassword = selected.dataset.needsPassword === '1';
        const needsMembership = selected.dataset.needsMembership === '1';
        const needsProfessional = selected.dataset.needsProfessional === '1';
        passwordField.classList.toggle('hidden', !needsPassword);
        membershipField.classList.toggle('hidden', !needsMembership);
        professionalField.classList.toggle('hidden', !needsProfessional);
        wrap.classList.toggle('hidden', !(needsPassword || needsMembership || needsProfessional));
    }
    document.querySelectorAll('.category-radio').forEach((input) => {
        input.addEventListener('change', syncCategoryExtras);
    });
    syncCategoryExtras();
</script>
@endpush
