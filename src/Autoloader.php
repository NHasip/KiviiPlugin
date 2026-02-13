<?php

namespace Kivii;

/**
 * PSR-4 style autoloader for the Kivii plugin.
 */
class Autoloader {

    /**
     * Register the autoloader.
     */
    public static function register(): void {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }

    /**
     * Autoload a class by namespace.
     */
    public static function autoload( string $class ): void {
        $prefix = 'Kivii\\';

        if ( strpos( $class, $prefix ) !== 0 ) {
            return;
        }

        $relative_class = substr( $class, strlen( $prefix ) );
        $file = KIVII_PLUGIN_DIR . 'src/' . str_replace( '\\', '/', $relative_class ) . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}
