@php($editing = isset($form))
@php($audienceLocked = $audienceLocked ?? false)
<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <label for="name" class="block text-sm font-semibold text-gray-800">Form name</label>
        <input id="name" name="name" type="text" required maxlength="255"
               value="{{ old('name', $form->name ?? '') }}"
               class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
               data-testid="custom-form-name">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="audience" class="block text-sm font-semibold text-gray-800">Audience</label>
        @if($audienceLocked)
            <input type="hidden" name="audience" value="{{ $form->audience->value }}">
            <div class="mt-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-800"
                 data-testid="custom-form-audience-locked">
                {{ $form->audience->label() }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Audience cannot be changed after the form is created.</p>
        @else
            <select id="audience" name="audience" required
                    class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                    data-testid="custom-form-audience">
                @foreach($audiences as $audience)
                    <option value="{{ $audience->value }}" @selected(old('audience', $audiences[0]->value ?? 'registration') === $audience->value)>
                        {{ $audience->label() }}
                    </option>
                @endforeach
            </select>
        @endif
        @error('audience') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="lg:col-span-3">
        <label for="slug" class="block text-sm font-semibold text-gray-800">Slug <span class="font-normal text-gray-500">(optional)</span></label>
        <input id="slug" name="slug" type="text" maxlength="255"
               value="{{ old('slug', $form->slug ?? '') }}" placeholder="generated-from-name"
               class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 font-mono text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
               data-testid="custom-form-slug">
        <p class="mt-1 text-xs text-gray-500">Leave blank to generate it automatically. Use lowercase letters, numbers, and hyphens.</p>
        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="lg:col-span-3">
        <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
        <textarea id="description" name="description" rows="4"
                  class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                  data-testid="custom-form-description">{{ old('description', $form->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="lg:col-span-3">
        <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
            <input name="is_active" type="checkbox" value="1"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   @checked(old('is_active', $form->is_active ?? true))
                   data-testid="custom-form-active">
            <span>
                <span class="block text-sm font-semibold text-gray-800">Active</span>
                <span class="block text-xs text-gray-500">Shown on matching Registration, Exhibitor, or Group forms.</span>
            </span>
        </label>
    </div>
</div>
