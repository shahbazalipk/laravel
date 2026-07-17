@php
    $definition = config("parameter_imports.{$parameter}");
    $modalId = 'bulk-import-' . $parameter;
    $defaultColor = old('color', '#6366f1');
@endphp

<button type="button"
        onclick="document.getElementById('{{ $modalId }}').classList.remove('hidden')"
        class="inline-flex items-center justify-center rounded-lg border border-indigo-200 bg-white px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        data-testid="bulk-import-button">
    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
    </svg>
    Bulk Import
</button>

<div id="{{ $modalId }}"
     class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="{{ $modalId }}-title"
     data-testid="bulk-import-modal">
    <div class="flex min-h-full items-start justify-center pt-4 sm:items-center sm:pt-0">
        <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white px-5 py-5 sm:px-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="{{ $modalId }}-title" class="text-xl font-semibold text-gray-900">
                            Import {{ $definition['plural'] }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Paste one {{ strtolower($definition['label']) }} per line or upload a TXT/CSV file.
                        </p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                            class="rounded-lg p-2 text-gray-400 transition hover:bg-white hover:text-gray-600"
                            aria-label="Close bulk import">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.parameters.bulk-import', $parameter) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="px-5 py-5 sm:px-6"
                  data-testid="bulk-import-form">
                @csrf

                @if($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-5">
                    <div>
                        <label for="{{ $modalId }}-names" class="mb-2 block text-sm font-medium text-gray-700">
                            Paste {{ strtolower($definition['plural']) }}
                        </label>
                        <textarea id="{{ $modalId }}-names"
                                  name="names"
                                  rows="7"
                                  class="w-full rounded-xl border border-gray-300 px-4 py-3 font-mono text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                  placeholder="{{ $definition['label'] }} One&#10;{{ $definition['label'] }} Two&#10;{{ $definition['label'] }} Three"
                                  data-testid="bulk-import-names">{{ old('names') }}</textarea>
                        <p class="mt-1.5 text-xs text-gray-500">Blank lines and duplicate names are automatically skipped.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="h-px flex-1 bg-gray-200"></div>
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400">or upload</span>
                        <div class="h-px flex-1 bg-gray-200"></div>
                    </div>

                    <div>
                        <label for="{{ $modalId }}-file" class="mb-2 block text-sm font-medium text-gray-700">
                            TXT or CSV file
                        </label>
                        <input id="{{ $modalId }}-file"
                               type="file"
                               name="import_file"
                               accept=".txt,.csv,text/plain,text/csv"
                               class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-200"
                               data-testid="bulk-import-file">
                        <a href="{{ asset('samples/parameter-names-sample.txt') }}"
                           download
                           class="mt-2 inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-800">
                            Download sample file
                        </a>
                    </div>

                    @if($definition['requires_sponsorship_fields'])
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="{{ $modalId }}-type" class="mb-2 block text-sm font-medium text-gray-700">
                                    Type <span class="text-red-500">*</span>
                                </label>
                                <input id="{{ $modalId }}-type"
                                       type="text"
                                       name="type"
                                       value="{{ old('type', $definition['default_type']) }}"
                                       required
                                       class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div>
                                <label for="{{ $modalId }}-label" class="mb-2 block text-sm font-medium text-gray-700">
                                    {{ $definition['sponsorship_label'] }} <span class="text-red-500">*</span>
                                </label>
                                <input id="{{ $modalId }}-label"
                                       type="text"
                                       name="sponsorship_label"
                                       value="{{ old('sponsorship_label') }}"
                                       required
                                       placeholder="e.g. Gold"
                                       class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if($definition['supports_color'])
                            <div>
                                <label for="{{ $modalId }}-color" class="mb-2 block text-sm font-medium text-gray-700">
                                    Default color
                                </label>
                                <div class="flex h-11 items-center gap-3 rounded-xl border border-gray-300 px-3">
                                    <input id="{{ $modalId }}-color"
                                           type="color"
                                           name="color"
                                           value="{{ $defaultColor }}"
                                           oninput="this.nextElementSibling.textContent = this.value"
                                           class="h-7 w-10 cursor-pointer rounded border-0 bg-transparent p-0">
                                    <span class="font-mono text-sm text-gray-600">{{ $defaultColor }}</span>
                                </div>
                            </div>
                        @endif
                        <div>
                            <label for="{{ $modalId }}-sort-order" class="mb-2 block text-sm font-medium text-gray-700">
                                Starting sort order
                            </label>
                            <input id="{{ $modalId }}-sort-order"
                                   type="number"
                                   name="sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-3 rounded-xl bg-gray-50 px-4 py-3">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Active
                        </label>
                        @if($definition['requires_sponsorship_fields'])
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox"
                                       name="visible_online"
                                       value="1"
                                       {{ old('visible_online', true) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Visible online
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox"
                                       name="visible_onsite"
                                       value="1"
                                       {{ old('visible_onsite', true) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Visible onsite
                            </label>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                            class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            data-testid="bulk-import-submit">
                        Import {{ $definition['plural'] }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any() || session('bulk_import_open'))
    <script>
        document.getElementById(@json($modalId))?.classList.remove('hidden');
    </script>
@endif
