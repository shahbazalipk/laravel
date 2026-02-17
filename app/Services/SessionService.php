<?php

namespace App\Services;

use App\Models\Session;
use App\Models\Agenda;
use App\Models\Speaker;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class SessionService
{
    /**
     * Get all sessions
     */
    public function getAllSessions(): Collection
    {
        return Session::with(['agenda', 'track', 'location', 'speakers'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get sessions for a specific agenda
     */
    public function getSessionsByAgenda(Agenda $agenda): Collection
    {
        return $agenda->sessions()
            ->with(['track', 'location', 'speakers'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Create a new session
     */
    public function createSession(array $data): Session
    {
        $this->validateSessionData($data);

        // Auto-set requires_speakers based on type
        if ($data['type'] === 'break') {
            $data['requires_speakers'] = false;
            $data['track_id'] = null; // Breaks don't require tracks
        }

        $session = Session::create($data);

        return $session->fresh();
    }

    /**
     * Update a session
     */
    public function updateSession(Session $session, array $data): Session
    {
        $this->validateSessionData($data, $session->id);

        // Auto-set requires_speakers based on type
        if (isset($data['type']) && $data['type'] === 'break') {
            $data['requires_speakers'] = false;
            $data['track_id'] = null;
        }

        $session->update($data);
        return $session->fresh();
    }

    /**
     * Delete a session
     */
    public function deleteSession(Session $session): bool
    {
        return $session->delete();
    }

    /**
     * Attach a speaker to a session
     */
    public function attachSpeaker(Session $session, Speaker $speaker, ?string $role = null): void
    {
        if (!$session->allowsSpeakers()) {
            throw ValidationException::withMessages([
                'type' => 'Cannot attach speakers to break sessions.'
            ]);
        }

        $session->speakers()->attach($speaker->id, ['role' => $role]);
    }

    /**
     * Detach a speaker from a session
     */
    public function detachSpeaker(Session $session, Speaker $speaker): void
    {
        $session->speakers()->detach($speaker->id);
    }

    /**
     * Validate session data
     */
    protected function validateSessionData(array $data, ?int $excludeSessionId = null): void
    {
        // Validate time range
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        if ($endTime->lte($startTime)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time.'
            ]);
        }

        // Validate within agenda date range
        if (isset($data['agenda_id'])) {
            $agenda = Agenda::find($data['agenda_id']);
            if ($agenda) {
                $sessionDate = $startTime->toDateString();
                if ($sessionDate < $agenda->start_date->toDateString() || 
                    $sessionDate > $agenda->end_date->toDateString()) {
                    throw ValidationException::withMessages([
                        'start_time' => 'Session must fall within agenda date range.'
                    ]);
                }
            }
        }

        // Validate track requirement (except for breaks)
        if (isset($data['type']) && $data['type'] !== 'break' && empty($data['track_id'])) {
            throw ValidationException::withMessages([
                'track_id' => 'Track is required for non-break sessions.'
            ]);
        }

        // Check location conflicts
        if (isset($data['location_id'])) {
            $this->checkLocationConflict(
                $data['location_id'],
                $startTime,
                $endTime,
                $excludeSessionId
            );
        }
    }

    /**
     * Check for location conflicts
     */
    protected function checkLocationConflict(int $locationId, Carbon $startTime, Carbon $endTime, ?int $excludeSessionId = null): void
    {
        $query = Session::where('location_id', $locationId)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'location_id' => 'This location is already booked during this time slot.'
            ]);
        }
    }

    /**
     * Get session statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_sessions' => Session::count(),
            'by_type' => Session::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }
}
