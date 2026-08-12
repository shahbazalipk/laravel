<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRegistrationNoteRequest;
use App\Models\Registration;
use App\Models\RegistrationNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class RegistrationNoteController extends Controller
{
    public function store(
        StoreRegistrationNoteRequest $request,
        Registration $registration
    ): RedirectResponse {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $imagePath = $file->storeAs('registrations/notes', $filename, 'public');
        }

        $adminName = session('admin_name') ?: session('admin_email') ?: 'Admin';

        RegistrationNote::create([
            'event_id' => $registration->event_id,
            'org_id' => $registration->org_id,
            'registration_id' => $registration->id,
            'body' => filled($request->input('body')) ? $request->input('body') : null,
            'image_path' => $imagePath,
            'author_name' => $adminName,
        ]);

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Note added successfully.');
    }

    public function destroy(
        Registration $registration,
        RegistrationNote $note
    ): RedirectResponse {
        abort_unless(
            (int) $note->registration_id === (int) $registration->id
            && (int) $note->event_id === (int) $registration->event_id,
            404
        );

        $imagePath = $note->image_path;
        $note->forceDelete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Note deleted.');
    }
}
