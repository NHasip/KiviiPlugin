<?php

namespace Kivii\Frontend;

/**
 * Gutenberg block registration.
 */
class GutenbergBlock {

    public function init(): void {
        add_action( 'init', [ $this, 'register_block' ] );
    }

    public function register_block(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( 'kiviiweb/booking-form', [
            'editor_script'   => 'kivii-block-editor',
            'render_callback' => [ $this, 'render' ],
        ] );

        wp_register_script(
            'kivii-block-editor',
            KIVII_PLUGIN_URL . 'assets/js/block-editor.js',
            [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ],
            KIVII_VERSION,
            true
        );
    }

    public function render(): string {
        ob_start();
        include KIVII_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }
}
