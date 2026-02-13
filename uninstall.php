<?php
/**
 * Uninstall Kivii Online Afspraak.
 * Removes all database tables and options if configured to do so.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$options = get_option( 'kivii_general', [] );
$remove_data = $options['remove_data_on_uninstall'] ?? false;

if ( $remove_data ) {
    global $wpdb;

    // Drop tables
    $tables = [
        $wpdb->prefix . 'kiviiweb_booking_items',
        $wpdb->prefix . 'kiviiweb_bookings',
        $wpdb->prefix . 'kiviiweb_services',
        $wpdb->prefix . 'kiviiweb_service_categories',
        $wpdb->prefix . 'kiviiweb_logs',
    ];

    foreach ( $tables as $table ) {
        $wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
    }

    // Remove options
    $option_keys = [
        'kivii_general',
        'kivii_styling',
        'kivii_booking_rules',
        'kivii_api',
        'kivii_db_version',
        'kivii_texts_nl',
        'kivii_texts_en',
        'kivii_email_templates',
        'kivii_field_mapping',
    ];

    foreach ( $option_keys as $key ) {
        delete_option( $key );
    }
}
