<?php

namespace Kivii\Api;

/**
 * Mock API adapter for development and testing.
 * Generates dummy availability and accepts any booking.
 */
class MockApiAdapter implements AvailabilityProviderInterface, BookingWriterInterface {

    public function getAvailableDays( int $month, int $year, int $duration ): array {
        $days  = [];
        $total = cal_days_in_month( CAL_GREGORIAN, $month, $year );
        $today = gmdate( 'Y-m-d' );

        for ( $d = 1; $d <= $total; $d++ ) {
            $date    = sprintf( '%04d-%02d-%02d', $year, $month, $d );
            $day_num = (int) gmdate( 'N', strtotime( $date ) );

            // Skip weekends
            if ( $day_num >= 6 ) {
                continue;
            }

            // Skip past dates
            if ( $date <= $today ) {
                continue;
            }

            // Random status
            $rand   = wp_rand( 1, 10 );
            $status = 'available';
            if ( $rand <= 2 ) {
                $status = 'full';
            } elseif ( $rand <= 4 ) {
                $status = 'almost_full';
            }

            $days[] = [
                'date'   => $date,
                'status' => $status,
            ];
        }

        return $days;
    }

    public function getAvailableTimeSlots( string $date, int $duration ): array {
        $slots = [];
        $start = 7 * 60 + 30; // 07:30
        $end   = 17 * 60;     // 17:00
        $step  = 15;          // 15 min intervals

        for ( $time = $start; $time < $end; $time += $step ) {
            // Skip lunch hour (12:30-13:30)
            if ( $time >= 12 * 60 + 30 && $time < 13 * 60 + 30 ) {
                continue;
            }

            // Random chance to remove slot
            if ( wp_rand( 1, 10 ) <= 2 ) {
                continue;
            }

            $hours   = intdiv( $time, 60 );
            $minutes = $time % 60;
            $slots[] = sprintf( '%02d:%02d', $hours, $minutes );
        }

        return $slots;
    }

    public function createBooking( array $payload ): array {
        return [
            'success'     => true,
            'external_id' => 'MOCK-' . strtoupper( substr( md5( wp_json_encode( $payload ) ), 0, 8 ) ),
            'message'     => 'Mock booking created successfully.',
        ];
    }
}
