<?php

namespace Kivii;

use Kivii\Database\Schema;

/**
 * Runs on plugin activation.
 */
class Activator {

    public static function activate(): void {
        // Create database tables
        Schema::create_tables();

        // Store DB version
        update_option( 'kivii_db_version', KIVII_DB_VERSION );

        // Set default options if not existing
        self::set_defaults();

        // Seed the default services catalog on fresh installs.
        ( new Database\ServiceRepository() )->ensure_default_catalog();

        // Schedule cron
        if ( ! wp_next_scheduled( 'kivii_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'kivii_daily_cleanup' );
        }

        // Flush rewrite rules for REST
        flush_rewrite_rules();
    }

    private static function set_defaults(): void {
        $defaults = [
            'kivii_general' => [
                'language'       => 'nl',
                'company_name'   => '',
                'privacy_url'    => '',
                'retention_days' => 365,
            ],
            'kivii_styling' => [
                'primary_color'    => '#B0C426',
                'secondary_color'  => '#B0C426',
                'border_radius'    => '8',
                'background_color' => '#F8F9FA',
                'text_color'       => '#1A1A2E',
            ],
            'kivii_booking_rules' => [
                'drop_off_times'     => "09:00\n13:00",
                'min_lead_hours'     => 24,
                'max_advance_days'   => 60,
                'calendar_note_nl'   => 'Wij houden altijd ruimte vrij voor spoedgevallen.',
                'calendar_note_en'   => 'We always keep room for emergencies.',
            ],
            'kivii_api' => [
                'base_url' => '',
                'token'    => '',
                'timeout'  => 30,
                'retries'  => 2,
            ],
        ];

        foreach ( $defaults as $key => $value ) {
            if ( get_option( $key ) === false ) {
                add_option( $key, $value );
            }
        }
    }
}
