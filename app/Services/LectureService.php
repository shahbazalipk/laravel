<?php

namespace App\Services;

use App\Models\Lecture;
use App\Models\Session;
use App\Models\Speaker;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LectureService
{
    protected SpeakerService $speakerService;

    public function __construct(SpeakerService $speakerService)
    {
        $this->speakerService = $speakerService;
    }

    /**
     * Get all lectures
     */
    public function getAllLectures(): Collection
    {
        return Lecture::with(['session', 'speaker', 'location'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get lectures for a specific session
     */
    public function getLecturesBySession(Session $session): Collection
    {
        return $session->lectures()
            ->with(['speaker', 'location'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Create a new lecture
     */
    public function createLecture(array $data): Lecture
    {
        $this->validateLectureData($data);

        return Lecture::create($data);
    }

    /**
     * Update a lecture
     */
    public function updateLecture(Lecture $lecture, array $data): Lecture
    {
        $this->validateLectureData($data, $lecture->id);

        $lecture->update($data);
        return $lecture->fresh();
    }

    /**
     * Delete a lecture
     */
    public function deleteLecture(Lecture $lecture): bool
    {
        return $lecture->delete();
    }

    /**
     * Validate lecture data
     */
    protected function validateLectureData(array $data, ?int $excludeLectureId = null): void
    {
        // Get session
        $session = Session::find($data['session_id']);
        if (!$session) {
            throw ValidationException::withMessages([
                'session_id' => 'Invalid session.'
            ]);
        }

        // Check if session allows lectures
        if (!$session->allowsLectures()) {
            throw ValidationException::withMessages([
                'session_id' => 'Cannot create lectures for break sessions.'
            ]);
        }

        // Validate time range
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        if ($endTime->lte($startTime)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time.'
            ]);
        }

        // Validate lecture is within session time
        if ($startTime->lt($session->start_time) || $endTime->gt($session->end_time)) {
            throw ValidationException::withMessages([
                'start_time' => 'Lecture must be within session time range.',
                'end_time' => 'Lecture must be within session time range.'
            ]);
        }

        // Check speaker availability
        if (isset($data['speaker_id'])) {
            $speaker = Speaker::find($data['speaker_id']);
            if ($speaker && !$this->speakerService->isAvailable($speaker, $startTime, $endTime, $excludeLectureId)) {
                throw ValidationException::withMessages([
                    'speaker_id' => 'Speaker is not available during this time slot.'
                ]);
            }
        }
    }
}
