<div class="bg-white rounded-lg shadow-sm p-6" data-testid="registration-notes-card">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Notes</h3>
            <p class="mt-0.5 text-sm text-gray-500">Internal notes and images visible only to admins.</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700" data-testid="notes-count">
            {{ $registration->registrationNotes->count() }}
        </span>
    </div>

    {{-- Add Note Form --}}
    <form action="{{ route('admin.registrations.notes.store', $registration) }}"
          method="POST"
          enctype="multipart/form-data"
          class="mb-6 rounded-xl border border-dashed border-indigo-200 bg-indigo-50/40 p-4 space-y-3"
          data-testid="add-note-form">
        @csrf

        <div>
            <label for="note-body" class="mb-1 block text-sm font-medium text-slate-700">Note text</label>
            <textarea id="note-body"
                      name="body"
                      rows="3"
                      placeholder="Add an internal note…"
                      class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 @error('body') border-red-400 @enderror"
                      data-testid="note-body-input">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="note-image" class="mb-1 block text-sm font-medium text-slate-700">
                Attach image <span class="font-normal text-slate-500">(optional — JPEG, PNG, GIF · max 5 MB)</span>
            </label>
            <input id="note-image"
                   name="image"
                   type="file"
                   accept="image/jpeg,image/png,image/jpg,image/gif"
                   class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-200 @error('image') border-red-400 @enderror"
                   data-testid="note-image-input">
            @error('image')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    data-testid="add-note-submit">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Note
            </button>
        </div>
    </form>

    {{-- Notes timeline --}}
    @if($registration->registrationNotes->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center" data-testid="notes-empty">
            <svg class="mx-auto mb-2 h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium text-gray-700">No notes yet</p>
            <p class="mt-0.5 text-sm text-gray-500">Use the form above to add the first note.</p>
        </div>
    @else
        <div class="space-y-4" data-testid="notes-list">
            @foreach($registration->registrationNotes as $note)
                <div class="group relative rounded-xl border border-slate-200 bg-white p-4 shadow-sm" data-testid="note-item">
                    {{-- header: author + time --}}
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                                {{ strtoupper(substr($note->author_name ?? 'A', 0, 1)) }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $note->author_name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-500" title="{{ $note->created_at->toDateTimeString() }}">
                                    {{ $note->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Delete button --}}
                        <form action="{{ route('admin.registrations.notes.destroy', [$registration, $note]) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this note?')"
                              data-testid="delete-note-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg border border-transparent px-2 py-1 text-xs font-medium text-red-600 opacity-0 transition hover:border-red-200 hover:bg-red-50 group-hover:opacity-100"
                                    data-testid="delete-note-btn">
                                <svg class="h-3.5 w-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>

                    {{-- Body text --}}
                    @if($note->body)
                        <p class="whitespace-pre-wrap text-sm text-slate-700" data-testid="note-body">{{ $note->body }}</p>
                    @endif

                    {{-- Attached image --}}
                    @if($note->image_path)
                        <div class="mt-3" data-testid="note-image-wrapper">
                            <a href="{{ storage_public_url($note->image_path) }}" target="_blank" rel="noopener">
                                <img src="{{ storage_public_url($note->image_path) }}"
                                     alt="Note attachment"
                                     class="max-h-64 w-auto rounded-lg border border-slate-200 object-cover shadow-sm hover:opacity-90 transition">
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
