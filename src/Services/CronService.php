<?php

namespace Kivii\Services;

use Kivii\Database\BookingRepository;
use Kivii\Database\LogRepository;

/**
 * Cron service for daily cleanup.
 */
class CronService {

    public function init(): void {
        add_action( 'kivii_daily_cleanup', [ $this, 'run_cleanup' ] );
    }

    public function run_cleanup(): void {
        $options        = get_option( 'kivii_general', [] );
        $retention_days = (int) ( $options['retention_days'] ?? 365 );

        if ( $retention_days <= 0 ) {
            return;
        }

        $booking_repo = new BookingRepository();
        $deleted      = $booking_repo->delete_old( $retention_days );

        $log_repo = new LogRepository();
        $log_repo->delete_old( 90 ); // Keep logs for 90 days

        if ( $deleted > 0 ) {
            $log_repo->info( "Daily cleanup: {$deleted} old bookings removed." );
        }
    }
}
