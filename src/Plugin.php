<?php

namespace Kivii;

use Kivii\Admin\AdminMenu;
use Kivii\Frontend\Shortcode;
use Kivii\Frontend\GutenbergBlock;
use Kivii\Rest\RestController;
use Kivii\Services\CronService;
use Kivii\I18n\Translator;

/**
 * Main plugin class – singleton bootstrap.
 */
class Plugin {

    private static ?Plugin $instance = null;

    public static function instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Initialize all plugin components.
     */
    public function init(): void {
        // Load text domain
        load_plugin_textdomain(
            'kivii-online-afspraak',
            false,
            dirname( KIVII_PLUGIN_BASENAME ) . '/languages'
        );

        // Translator
        Translator::instance()->init();

        // Admin
        if ( is_admin() ) {
            ( new AdminMenu() )->init();
        }

        // Frontend
        ( new Shortcode() )->init();
        ( new GutenbergBlock() )->init();

        // REST API
        add_action( 'rest_api_init', function () {
            ( new RestController() )->register_routes();
        } );

        // Cron
        ( new CronService() )->init();

        // Enqueue frontend assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );

        // Enqueue admin assets
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        // Output CSS variables
        add_action( 'wp_head', [ $this, 'output_css_variables' ] );
    }

    /**
     * Enqueue frontend CSS and JS.
     */
    public function enqueue_frontend_assets(): void {
        global $post;

        // Only load on pages with our shortcode or block
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        $has_shortcode = has_shortcode( $post->post_content, 'kivii_online_afspraak' );
        $has_block     = has_block( 'kiviiweb/booking-form', $post );

        if ( ! $has_shortcode && ! $has_block ) {
            return;
        }

        wp_enqueue_style(
            'kivii-frontend',
            KIVII_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            KIVII_VERSION
        );

        wp_enqueue_script(
            'kivii-frontend',
            KIVII_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            KIVII_VERSION,
            true
        );

        wp_localize_script( 'kivii-frontend', 'kiviiData', [
            'restUrl'   => esc_url_raw( rest_url( 'kiviiweb/v1/' ) ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'lang'      => $this->get_current_language(),
            'texts'     => Translator::instance()->get_all_texts(),
            'services'  => $this->get_services_data(),
            'dropOffTimes' => $this->get_drop_off_times(),
            'styling'   => $this->get_styling_options(),
        ] );
    }

    /**
     * Enqueue admin CSS and JS on plugin pages.
     */
    public function enqueue_admin_assets( string $hook ): void {
        if ( strpos( $hook, 'kivii' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'kivii-admin',
            KIVII_PLUGIN_URL . 'assets/css/admin.css',
            [],
            KIVII_VERSION
        );

        wp_enqueue_script(
            'kivii-admin',
            KIVII_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery', 'wp-color-picker' ],
            KIVII_VERSION,
            true
        );

        wp_enqueue_style( 'wp-color-picker' );

        wp_localize_script( 'kivii-admin', 'kiviiAdmin', [
            'restUrl' => esc_url_raw( rest_url( 'kiviiweb/v1/' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        ] );
    }

    /**
     * Output CSS custom properties from admin styling settings.
     */
    public function output_css_variables(): void {
        $options = get_option( 'kivii_styling', [] );

        $primary   = $options['primary_color'] ?? '#0B3D91';
        $secondary = $options['secondary_color'] ?? '#E5A100';
        $radius    = $options['border_radius'] ?? '8';
        $bg        = $options['background_color'] ?? '#F8F9FA';
        $text      = $options['text_color'] ?? '#1A1A2E';

        echo '<style id="kivii-css-vars">
        :root {
            --kivii-primary: ' . esc_attr( $primary ) . ';
            --kivii-secondary: ' . esc_attr( $secondary ) . ';
            --kivii-radius: ' . intval( $radius ) . 'px;
            --kivii-bg: ' . esc_attr( $bg ) . ';
            --kivii-text: ' . esc_attr( $text ) . ';
            --kivii-success: #16A34A;
            --kivii-error: #DC2626;
            --kivii-warning: #F59E0B;
            --kivii-border: #E2E8F0;
            --kivii-muted: #64748B;
        }
        </style>' . "\n";
    }

    /**
     * Get current language (nl or en).
     */
    private function get_current_language(): string {
        $lang = get_option( 'kivii_general', [] )['language'] ?? 'nl';
        return in_array( $lang, [ 'nl', 'en' ], true ) ? $lang : 'nl';
    }

    /**
     * Get services data for frontend.
     */
    private function get_services_data(): array {
        $repo = new Database\ServiceRepository();
        return $repo->get_all_active_grouped();
    }

    /**
     * Get drop-off times from settings.
     */
    private function get_drop_off_times(): array {
        $options = get_option( 'kivii_booking_rules', [] );
        $times   = $options['drop_off_times'] ?? "09:00\n13:00";
        return array_filter( array_map( 'trim', explode( "\n", $times ) ) );
    }

    /**
     * Get styling options.
     */
    private function get_styling_options(): array {
        return get_option( 'kivii_styling', [
            'primary_color'    => '#0B3D91',
            'secondary_color'  => '#E5A100',
            'border_radius'    => '8',
            'background_color' => '#F8F9FA',
            'text_color'       => '#1A1A2E',
        ] );
    }
}
