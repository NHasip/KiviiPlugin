<?php

namespace Kivii\Database;

/**
 * Database schema – creates all custom tables.
 */
class Schema {

    public static function create_tables(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $prefix  = $wpdb->prefix;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Service categories
        $sql = "CREATE TABLE {$prefix}kiviiweb_service_categories (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name_nl VARCHAR(255) NOT NULL,
            name_en VARCHAR(255) NOT NULL DEFAULT '',
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";
        dbDelta( $sql );

        // Services
        $sql = "CREATE TABLE {$prefix}kiviiweb_services (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id BIGINT UNSIGNED NOT NULL,
            title_nl VARCHAR(255) NOT NULL,
            title_en VARCHAR(255) NOT NULL DEFAULT '',
            description_nl TEXT,
            description_en TEXT,
            long_desc_nl TEXT,
            long_desc_en TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            duration_minutes INT NOT NULL DEFAULT 30,
            is_addon TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_category (category_id),
            KEY idx_active (is_active)
        ) $charset;";
        dbDelta( $sql );

        // Bookings
        $sql = "CREATE TABLE {$prefix}kiviiweb_bookings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_reference VARCHAR(36) NOT NULL,
            license_plate VARCHAR(20) NOT NULL,
            mileage INT UNSIGNED NOT NULL DEFAULT 0,
            appointment_date DATE NOT NULL,
            appointment_time TIME NULL,
            is_drop_off TINYINT(1) DEFAULT 0,
            drop_off_time VARCHAR(10) NULL,
            total_price DECIMAL(10,2) DEFAULT 0,
            total_duration INT DEFAULT 0,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            street VARCHAR(255) NOT NULL,
            house_number VARCHAR(20) NOT NULL,
            house_addition VARCHAR(20) NULL,
            postal_code VARCHAR(10) NOT NULL,
            city VARCHAR(100) NOT NULL,
            remarks TEXT NULL,
            privacy_accepted TINYINT(1) DEFAULT 0,
            language VARCHAR(5) DEFAULT 'nl',
            status VARCHAR(20) DEFAULT 'pending',
            kivii_synced TINYINT(1) DEFAULT 0,
            kivii_response TEXT NULL,
            source_domain VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_reference (booking_reference),
            KEY idx_date (appointment_date),
            KEY idx_status (status)
        ) $charset;";
        dbDelta( $sql );

        // Booking items
        $sql = "CREATE TABLE {$prefix}kiviiweb_booking_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) DEFAULT 0,
            duration_minutes INT DEFAULT 0,
            is_addon TINYINT(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY idx_booking (booking_id)
        ) $charset;";
        dbDelta( $sql );

        // Logs
        $sql = "CREATE TABLE {$prefix}kiviiweb_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            level VARCHAR(20) DEFAULT 'info',
            message TEXT NOT NULL,
            context LONGTEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_level (level),
            KEY idx_created (created_at)
        ) $charset;";
        dbDelta( $sql );
    }
}
