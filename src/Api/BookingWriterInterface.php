<?php

namespace Kivii\Api;

/**
 * Interface for booking writers.
 * Adapter pattern – implementations can be swapped.
 */
interface BookingWriterInterface {

    /**
     * Create a booking in the external system.
     *
     * @param array $payload Complete booking payload.
     *
     * @return array [ 'success' => bool, 'external_id' => string|null, 'message' => string ]
     */
    public function createBooking( array $payload ): array;
}
