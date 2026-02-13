<?php

namespace Kivii\Admin;

/**
 * Admin menu registration and page routing.
 */
class AdminMenu {

    public function init(): void {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );

        // AJAX handlers
        add_action( 'wp_ajax_kivii_save_service', [ $this, 'ajax_save_service' ] );
        add_action( 'wp_ajax_kivii_delete_service', [ $this, 'ajax_delete_service' ] );
        add_action( 'wp_ajax_kivii_save_category', [ $this, 'ajax_save_category' ] );
        add_action( 'wp_ajax_kivii_delete_category', [ $this, 'ajax_delete_category' ] );
        add_action( 'wp_ajax_kivii_export_services', [ $this, 'ajax_export_services' ] );
        add_action( 'wp_ajax_kivii_import_services', [ $this, 'ajax_import_services' ] );
        add_action( 'wp_ajax_kivii_export_csv', [ $this, 'ajax_export_csv' ] );
        add_action( 'wp_ajax_kivii_resend_booking', [ $this, 'ajax_resend_booking' ] );
        add_action( 'wp_ajax_kivii_resend_email', [ $this, 'ajax_resend_email' ] );
        add_action( 'wp_ajax_kivii_clear_logs', [ $this, 'ajax_clear_logs' ] );
    }

    public function register_menus(): void {
        // Main menu
        add_menu_page(
            'Kivii Online Afspraak',
            'Kivii Afspraak',
            'manage_options',
            'kivii-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-calendar-alt',
            30
        );

        // Submenu items
        add_submenu_page( 'kivii-settings', 'Algemeen', 'Algemeen', 'manage_options', 'kivii-settings', [ $this, 'render_settings_page' ] );
        add_submenu_page( 'kivii-settings', 'Taalinstellingen', 'Taalinstellingen', 'manage_options', 'kivii-language', [ $this, 'render_language_page' ] );
        add_submenu_page( 'kivii-settings', 'Styling', 'Styling', 'manage_options', 'kivii-styling', [ $this, 'render_styling_page' ] );
        add_submenu_page( 'kivii-settings', 'Boekingsregels', 'Boekingsregels', 'manage_options', 'kivii-booking-rules', [ $this, 'render_booking_rules_page' ] );
        add_submenu_page( 'kivii-settings', 'Werkzaamheden', 'Werkzaamheden', 'manage_options', 'kivii-services', [ $this, 'render_services_page' ] );
        add_submenu_page( 'kivii-settings', 'Integratie Kivii', 'Integratie Kivii', 'manage_options', 'kivii-integration', [ $this, 'render_integration_page' ] );
        add_submenu_page( 'kivii-settings', 'E-mail templates', 'E-mail templates', 'manage_options', 'kivii-emails', [ $this, 'render_email_page' ] );
        add_submenu_page( 'kivii-settings', 'Boekingen', 'Boekingen', 'manage_options', 'kivii-bookings', [ $this, 'render_bookings_page' ] );
        add_submenu_page( 'kivii-settings', 'Logs', 'Logs', 'manage_options', 'kivii-logs', [ $this, 'render_logs_page' ] );
    }

    public function register_settings(): void {
        register_setting( 'kivii_general_group', 'kivii_general' );
        register_setting( 'kivii_styling_group', 'kivii_styling' );
        register_setting( 'kivii_booking_rules_group', 'kivii_booking_rules' );
        register_setting( 'kivii_api_group', 'kivii_api' );
        register_setting( 'kivii_email_templates_group', 'kivii_email_templates' );
        register_setting( 'kivii_texts_nl_group', 'kivii_texts_nl' );
        register_setting( 'kivii_texts_en_group', 'kivii_texts_en' );
        register_setting( 'kivii_field_mapping_group', 'kivii_field_mapping' );
    }

    // ── Page renders ────────────────────────────────────

    public function render_settings_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/settings.php';
    }

    public function render_language_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/language.php';
    }

    public function render_styling_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/styling.php';
    }

    public function render_booking_rules_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/booking-rules.php';
    }

    public function render_services_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/services.php';
    }

    public function render_integration_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/integration.php';
    }

    public function render_email_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/email-templates.php';
    }

    public function render_bookings_page(): void {
        if ( isset( $_GET['booking_id'] ) ) {
            include KIVII_PLUGIN_DIR . 'src/Admin/views/booking-detail.php';
        } else {
            include KIVII_PLUGIN_DIR . 'src/Admin/views/bookings.php';
        }
    }

    public function render_logs_page(): void {
        include KIVII_PLUGIN_DIR . 'src/Admin/views/logs.php';
    }

    // ── AJAX handlers ────────────────────────────────────

    public function ajax_save_service(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $repo = new \Kivii\Database\ServiceRepository();
        $data = $_POST['service'] ?? [];
        $id   = absint( $data['id'] ?? 0 );

        if ( $id > 0 ) {
            $repo->update_service( $id, $data );
            wp_send_json_success( [ 'id' => $id, 'message' => 'Bijgewerkt' ] );
        } else {
            $new_id = $repo->create_service( $data );
            wp_send_json_success( [ 'id' => $new_id, 'message' => 'Aangemaakt' ] );
        }
    }

    public function ajax_delete_service(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $id   = absint( $_POST['id'] ?? 0 );
        $repo = new \Kivii\Database\ServiceRepository();
        $repo->delete_service( $id );
        wp_send_json_success();
    }

    public function ajax_save_category(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $repo = new \Kivii\Database\ServiceRepository();
        $data = $_POST['category'] ?? [];
        $id   = absint( $data['id'] ?? 0 );

        if ( $id > 0 ) {
            $repo->update_category( $id, $data );
            wp_send_json_success( [ 'id' => $id ] );
        } else {
            $new_id = $repo->create_category( $data );
            wp_send_json_success( [ 'id' => $new_id ] );
        }
    }

    public function ajax_delete_category(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $id   = absint( $_POST['id'] ?? 0 );
        $repo = new \Kivii\Database\ServiceRepository();
        $repo->delete_category( $id );
        wp_send_json_success();
    }

    public function ajax_export_services(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $repo = new \Kivii\Database\ServiceRepository();
        $data = $repo->export_all();
        wp_send_json_success( $data );
    }

    public function ajax_import_services(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );

        $json = $_POST['json_data'] ?? '';
        $data = json_decode( stripslashes( $json ), true );

        if ( ! is_array( $data ) ) {
            wp_send_json_error( 'Ongeldig JSON formaat.' );
        }

        $repo = new \Kivii\Database\ServiceRepository();
        $repo->import_all( $data );
        wp_send_json_success( 'Import succesvol.' );
    }

    public function ajax_export_csv(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $repo = new \Kivii\Database\BookingRepository();
        $data = $repo->get_for_export( $_POST['filters'] ?? [] );
        wp_send_json_success( $data );
    }

    public function ajax_resend_booking(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $id      = absint( $_POST['booking_id'] ?? 0 );
        $service = new \Kivii\Services\BookingService();
        $result  = $service->resend_to_kivii( $id );
        wp_send_json( $result );
    }

    public function ajax_resend_email(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $id      = absint( $_POST['booking_id'] ?? 0 );
        $repo    = new \Kivii\Database\BookingRepository();
        $booking = $repo->get_by_id( $id );

        if ( ! $booking ) {
            wp_send_json_error( 'Booking niet gevonden.' );
        }

        $email = new \Kivii\Services\EmailService();
        $email->send_customer_confirmation( $booking );
        wp_send_json_success( 'E-mail opnieuw verzonden.' );
    }

    public function ajax_clear_logs(): void {
        check_ajax_referer( 'kivii_admin_nonce', 'nonce' );
        $repo = new \Kivii\Database\LogRepository();
        $repo->clear();
        wp_send_json_success( 'Logs gewist.' );
    }
}
