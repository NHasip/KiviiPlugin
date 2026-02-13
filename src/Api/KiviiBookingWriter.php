<?php

namespace Kivii\Api;

/**
 * Kivii booking writer implementation.
 */
class KiviiBookingWriter implements BookingWriterInterface {

    private KiviiApiClient $client;

    public function __construct() {
        $this->client = new KiviiApiClient();
    }

    public function createBooking( array $payload ): array {
        // Apply field mapping from admin settings
        $mapped = $this->apply_mapping( $payload );

        $result = $this->client->post( 'bookings', $mapped );

        if ( $result['success'] ) {
            return [
                'success'     => true,
                'external_id' => $result['data']['id'] ?? null,
                'message'     => 'Booking succesvol aangemaakt in Kivii.',
            ];
        }

        return [
            'success'     => false,
            'external_id' => null,
            'message'     => $result['error'] ?? 'Onbekende fout bij aanmaken booking.',
        ];
    }

    /**
     * Apply field mapping from admin settings.
     */
    private function apply_mapping( array $payload ): array {
        $mapping = get_option( 'kivii_field_mapping', [] );

        if ( empty( $mapping ) ) {
            return $payload;
        }

        $mapped = [];
        foreach ( $payload as $key => $value ) {
            $target_key    = $mapping[ $key ] ?? $key;
            $mapped[ $target_key ] = $value;
        }

        return $mapped;
    }
}
