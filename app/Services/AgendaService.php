<?php

namespace App\Services;

use App\Models\Agenda;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AgendaService
{
    /**
     * Get all agendas
     */
    public function getAllAgendas(): Collection
    {
        return Agenda::withCount(['tracks', 'sessions'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Create a new agenda
     */
    public function createAgenda(array $data): Agenda
    {
        $this->validateDateRange($data['start_date'], $data['end_date']);

        return Agenda::create($data);
    }

    /**
     * Update an agenda
     */
    public function updateAgenda(Agenda $agenda, array $data): Agenda
    {
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $this->validateDateRange($data['start_date'], $data['end_date']);
        }

        $agenda->update($data);
        return $agenda->fresh();
    }

    /**
     * Delete an agenda
     */
    public function deleteAgenda(Agenda $agenda): bool
    {
        return $agenda->delete();
    }

    /**
     * Validate date range
     */
    protected function validateDateRange(string $startDate, string $endDate): void
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date must be on or after start date.'
            ]);
        }
    }

    /**
     * Get agenda statistics
     */
    public function getStatistics(Agenda $agenda): array
    {
        return [
            'total_tracks' => $agenda->tracks()->count(),
            'total_sessions' => $agenda->sessions()->count(),
            'total_speakers' => $agenda->sessions()
                ->with('speakers')
                ->get()
                ->pluck('speakers')
                ->flatten()
                ->unique('id')
                ->count(),
            'duration_days' => $agenda->start_date->diffInDays($agenda->end_date) + 1,
        ];
    }
}
