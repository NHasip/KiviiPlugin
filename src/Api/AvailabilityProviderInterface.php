<?php

namespace Kivii\Api;

/**
 * Interface for availability data providers.
 * Adapter pattern – implementations can be swapped.
 */
interface AvailabilityProviderInterface {

    /**
     * Get available days for a given month.
     *
     * @param int $month      Month number (1-12).
     * @param int $year       Year (e.g. 2026).
     * @param int $duration   Total duration in minutes.
     *
     * @return array Array of [ 'date' => 'Y-m-d', 'status' => 'available'|'almost_full'|'full' ]
     */
    public function getAvailableDays( int $month, int $year, int $duration ): array;

    /**
     * Get available time slots for a given date.
     *
     * @param string $date     Date in Y-m-d format.
     * @param int    $duration Total duration in minutes.
     *
     * @return array Array of time strings, e.g. ['09:00', '09:30', '10:00']
     */
    public function getAvailableTimeSlots( string $date, int $duration ): array;
}
