<?php

namespace Kivii;

/**
 * Runs on plugin deactivation.
 */
class Deactivator {

    public static function deactivate(): void {
        // Remove scheduled cron
        $timestamp = wp_next_scheduled( 'kivii_daily_cleanup' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'kivii_daily_cleanup' );
        }

        flush_rewrite_rules();
    }
}
