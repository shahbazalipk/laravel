<?php

namespace App\Services;

use App\Models\Speaker;
use App\Models\Lecture;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SpeakerService
{
    /**
     * Get all speakers
     */
    public function getAllSpeakers(): Collection
    {
        return Speaker::orderBy('full_name')->get();
    }

    /**
     * Create a new speaker
     */
    public function createSpeaker(array $data): Speaker
    {
        return Speaker::create($data);
    }

    /**
     * Update a speaker
     */
    public function updateSpeaker(Speaker $speaker, array $data): Speaker
    {
        $speaker->update($data);
        return $speaker->fresh();
    }

    /**
     * Delete a speaker
     */
    public function deleteSpeaker(Speaker $speaker): bool
    {
        return $speaker->delete();
    }

    /**
     * Check if speaker is available during a time slot
     */
    public function isAvailable(Speaker $speaker, Carbon $startTime, Carbon $endTime, ?int $excludeLectureId = null): bool
    {
        $query = $speaker->lectures()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeLectureId) {
            $query->where('id', '!=', $excludeLectureId);
        }

        return $query->count() === 0;
    }

    /**
     * Get speaker's schedule
     */
    public function getSchedule(Speaker $speaker): Collection
    {
        return $speaker->lectures()
            ->with(['session', 'location'])
            ->orderBy('start_time')
            ->get();
    }
}
