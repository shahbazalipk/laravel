<?php

namespace App\Services;

use App\Models\Track;
use App\Models\Agenda;
use Illuminate\Support\Collection;

class TrackService
{
    /**
     * Get all tracks
     */
    public function getAllTracks(): Collection
    {
        return Track::with('agenda')->orderBy('sort_order')->get();
    }

    /**
     * Get tracks for a specific agenda
     */
    public function getTracksByAgenda(Agenda $agenda): Collection
    {
        return $agenda->tracks()->orderBy('sort_order')->get();
    }

    /**
     * Create a new track
     */
    public function createTrack(array $data): Track
    {
        return Track::create($data);
    }

    /**
     * Update a track
     */
    public function updateTrack(Track $track, array $data): Track
    {
        $track->update($data);
        return $track->fresh();
    }

    /**
     * Delete a track
     */
    public function deleteTrack(Track $track): bool
    {
        return $track->delete();
    }

    /**
     * Reorder tracks
     */
    public function reorderTracks(array $trackIds): void
    {
        foreach ($trackIds as $index => $trackId) {
            Track::where('id', $trackId)->update(['sort_order' => $index]);
        }
    }
}
