@php
    $canEditActiveView = $activeView && $activeView->isOwnedBy((int) session('admin_id'));
    $activeShareIds = $activeView?->shares->pluck('organization_admin_user_id')->map(fn ($id) => (int) $id)->all() ?? [];
    $selectedKeys = collect($visibleColumns)->pluck('key')->all();
@endphp

<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" data-testid="saved-views-bar">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Table view</p>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                <h2 class="text-base font-semibold text-slate-900" data-testid="active-view-name">
                    {{ $activeView?->name ?? 'Default columns' }}
                </h2>
                @if($activeView)
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                        {{ $activeView->visibility->label() }}
                    </span>
                    @if($activeView->is_default)
                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">Default</span>
                    @endif
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('admin.registrations.index') }}" class="flex items-center gap-2">
                @foreach(collect($filters)->except('stage') as $key => $value)
                    @if(filled($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input type="hidden" name="stage" value="{{ $filters['stage'] ?? 'all' }}">
                <label for="saved-view-select" class="sr-only">Saved views</label>
                <select id="saved-view-select"
                        name="view"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        data-testid="saved-view-select">
                    <option value="">Default columns</option>
                    @foreach($savedViewList as $view)
                        <option value="{{ $view->public_id }}" {{ ($activeView?->public_id === $view->public_id) ? 'selected' : '' }}>
                            {{ $view->name }}
                            @if($view->isOwnedBy((int) session('admin_id')))
                                (mine)
                            @elseif($view->visibility->value === 'public')
                                (public)
                            @else
                                (shared)
                            @endif
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.registrations.views.export', array_filter(array_merge($filters, ['view' => $activeView?->public_id]), fn ($value) => $value !== null && $value !== '')) }}"
               class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
               data-testid="export-saved-view">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"></path>
                </svg>
                Export CSV
            </a>

            <button type="button"
                    onclick="document.getElementById('customizeColumnsPanel').classList.toggle('hidden')"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    data-testid="customize-columns-toggle">
                Customize columns
            </button>
        </div>
    </div>

    <div id="customizeColumnsPanel" class="{{ $errors->has('name') || $errors->has('columns') || $errors->has('share_user_ids') ? '' : 'hidden' }} mt-5 border-t border-slate-100 pt-5" data-testid="customize-columns-panel">
        <form id="saved-view-form"
              method="POST"
              action="{{ $canEditActiveView ? route('admin.registrations.views.update', $activeView) : route('admin.registrations.views.store') }}"
              class="space-y-5">
            @csrf
            @if($canEditActiveView)
                @method('PUT')
            @endif

            @foreach(collect($filters) as $key => $value)
                @if(filled($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <div>
                <p class="mb-3 text-sm font-semibold text-slate-800">Columns to show</p>
                <p class="mb-4 text-xs text-slate-500">Choose standard fields and custom/conditional questions. Drag is not required — order follows the list below.</p>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($columnGroups as $group => $columns)
                        <fieldset class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $group }}</legend>
                            <div class="mt-2 space-y-2">
                                @foreach($columns as $column)
                                    <label class="flex items-start gap-2 text-sm text-slate-700">
                                        <input type="checkbox"
                                               name="columns[]"
                                               value="{{ $column['key'] }}"
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                               {{ in_array($column['key'], $selectedKeys, true) ? 'checked' : '' }}
                                               data-testid="column-{{ $column['key'] }}">
                                        <span>
                                            {{ $column['label'] }}
                                            @if(!empty($column['description']))
                                                <span class="block text-xs text-indigo-600">{{ $column['description'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
                @error('columns')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="view-name" class="mb-1.5 block text-sm font-medium text-slate-700">View name</label>
                    <input id="view-name"
                           type="text"
                           name="name"
                           value="{{ old('name', $canEditActiveView ? $activeView->name : '') }}"
                           maxlength="80"
                           placeholder="e.g. Check-in desk"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                           data-testid="saved-view-name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="mb-1.5 text-sm font-medium text-slate-700">Sharing</p>
                    <div class="space-y-2 text-sm text-slate-700">
                        @foreach(\App\Registration\Enums\RegistrationSavedViewVisibility::cases() as $visibility)
                            <label class="flex items-center gap-2">
                                <input type="radio"
                                       name="visibility"
                                       value="{{ $visibility->value }}"
                                       class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ old('visibility', $activeView?->visibility->value ?? 'private') === $visibility->value ? 'checked' : '' }}
                                       onchange="document.getElementById('share-people-wrap').classList.toggle('hidden', this.value !== 'users')"
                                       data-testid="view-visibility-{{ $visibility->value }}">
                                {{ $visibility->label() }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="share-people-wrap" class="{{ old('visibility', $activeView?->visibility->value ?? 'private') === 'users' ? '' : 'hidden' }}">
                <label for="share_user_ids" class="mb-1.5 block text-sm font-medium text-slate-700">Share with event users</label>
                <select id="share_user_ids"
                        name="share_user_ids[]"
                        multiple
                        size="6"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        data-testid="view-share-users">
                    @forelse($eventAdministrators as $admin)
                        <option value="{{ $admin->id }}" {{ in_array((int) $admin->id, old('share_user_ids', $activeShareIds), true) ? 'selected' : '' }}>
                            {{ $admin->name }} ({{ $admin->email }})
                        </option>
                    @empty
                        <option disabled>No other event users available</option>
                    @endforelse
                </select>
                <p class="mt-1 text-xs text-slate-500">Hold Cmd/Ctrl to select multiple people.</p>
                @error('share_user_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox"
                       name="is_default"
                       value="1"
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('is_default', $canEditActiveView && $activeView->is_default) ? 'checked' : '' }}>
                Set as my default view
            </label>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    @if($canEditActiveView)
                        <button type="submit"
                                formaction="{{ route('admin.registrations.views.store') }}"
                                formmethod="POST"
                                class="text-sm font-medium text-indigo-700 hover:text-indigo-900"
                                onclick="this.form.querySelector('input[name=_method]')?.remove()">
                            Save as a new view
                        </button>
                    @endif
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    @if($canEditActiveView)
                        <button type="submit"
                                form="delete-view-form"
                                class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                                onclick="return confirm('Delete this view?')">
                            Delete
                        </button>
                    @endif
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            data-testid="save-view-submit">
                        {{ $canEditActiveView ? 'Update view' : 'Save view' }}
                    </button>
                </div>
            </div>
        </form>

        @if($canEditActiveView)
            <form id="delete-view-form" method="POST" action="{{ route('admin.registrations.views.destroy', $activeView) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
