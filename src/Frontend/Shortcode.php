<?php

namespace Kivii\Frontend;

/**
 * Registers the [kivii_online_afspraak] shortcode.
 */
class Shortcode {

    public function init(): void {
        add_shortcode( 'kivii_online_afspraak', [ $this, 'render' ] );
    }

    public function render( $atts = [] ): string {
        ob_start();
        include KIVII_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }
}
