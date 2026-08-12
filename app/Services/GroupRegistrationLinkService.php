<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Registration;
use Illuminate\Validation\ValidationException;

class GroupRegistrationLinkService
{
    public function link(Group $group, Registration $registration): Registration
    {
        if ((int) $registration->event_id !== (int) $group->event_id) {
            throw ValidationException::withMessages([
                'registration_id' => 'Registration must belong to the same event as the group.',
            ]);
        }

        if (
            $registration->group_id
            && (int) $registration->group_id !== (int) $group->id
        ) {
            throw ValidationException::withMessages([
                'registration_id' => 'Registration is already linked to another group.',
            ]);
        }

        if ((int) $registration->group_id !== (int) $group->id) {
            $memberCount = $group->registrations()->count();
            if ($memberCount >= $group->allowed_attendees) {
                throw ValidationException::withMessages([
                    'registration_id' => "Group has reached its capacity of {$group->allowed_attendees} attendees.",
                ]);
            }
        }

        $registration->update([
            'registration_type' => 'group',
            'group_id' => $group->id,
            'exhibitor_id' => null,
        ]);

        return $registration->fresh(['registrationCategory', 'registrationStatus']);
    }

    public function unlink(Group $group, Registration $registration): Registration
    {
        if ((int) $registration->group_id !== (int) $group->id) {
            abort(404);
        }

        $registration->update([
            'registration_type' => 'individual',
            'group_id' => null,
        ]);

        return $registration->fresh();
    }
}
