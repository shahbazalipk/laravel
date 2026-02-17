<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;

class LocationService
{
    /**
     * Get all locations
     */
    public function getAllLocations(): Collection
    {
        return Location::orderBy('name')->get();
    }

    /**
     * Create a new location
     */
    public function createLocation(array $data): Location
    {
        return Location::create($data);
    }

    /**
     * Update a location
     */
    public function updateLocation(Location $location, array $data): Location
    {
        $location->update($data);
        return $location->fresh();
    }

    /**
     * Delete a location
     */
    public function deleteLocation(Location $location): bool
    {
        return $location->delete();
    }

    /**
     * Check if location has capacity for attendees
     */
    public function hasCapacity(Location $location, int $attendees): bool
    {
        if ($location->capacity === null) {
            return true; // No capacity limit
        }

        return $location->capacity >= $attendees;
    }
}
