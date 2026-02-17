<?php

namespace App\Services;

use App\Models\Event;

class EventSettingsService
{
    public function getCurrentEvent(): ?Event
    {
        return Event::getCurrentEvent();
    }

    public function updateEventSettings(Event $event, array $data): Event
    {
        $event->update($data);
        return $event->fresh();
    }
}
