<?php

namespace App\Services;

use App\Models\Event;

class EventService
{
    public function getCurrentEvent()
    {
        return Event::getCurrentEvent();
    }
}
