<?php

namespace Kivii\Api;

/**
 * Kivii availability provider implementation.
 */
class KiviiAvailabilityProvider implements AvailabilityProviderInterface {

    private KiviiApiClient $client;

    public function __construct() {
        $this->client = new KiviiApiClient();
    }

    public function getAvailableDays( int $month, int $year, int $duration ): array {
        $result = $this->client->get( 'availability/days', [
            'month'    => $month,
            'year'     => $year,
            'duration' => $duration,
        ] );

        if ( ! $result['success'] || empty( $result['data']['days'] ) ) {
            return [];
        }

        return $result['data']['days'];
    }

    public function getAvailableTimeSlots( string $date, int $duration ): array {
        $result = $this->client->get( 'availability/slots', [
            'date'     => $date,
            'duration' => $duration,
        ] );

        if ( ! $result['success'] || empty( $result['data']['slots'] ) ) {
            return [];
        }

        return $result['data']['slots'];
    }
}
