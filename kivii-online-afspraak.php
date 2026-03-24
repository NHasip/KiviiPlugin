<?php
/**
 * Plugin Name:       Kivii Online Afspraak
 * Plugin URI:        https://kivii.nl
 * Description:       Online afsprakenplanner voor garages. Synchroniseert met Kivii agenda via API.
 * Version:           1.1.5
 * Requires at least: 6.0
 * Requires PHP:      8.4
 * Author:            Kivii
 * Author URI:        https://kivii.nl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kivii-online-afspraak
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'KIVII_VERSION', '1.1.5' );
define( 'KIVII_PLUGIN_FILE', __FILE__ );
define( 'KIVII_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KIVII_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KIVII_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'KIVII_DB_VERSION', '1.1.1' );

// Autoloader
require_once KIVII_PLUGIN_DIR . 'src/Autoloader.php';
\Kivii\Autoloader::register();

// Activation / Deactivation
register_activation_hook( __FILE__, [ \Kivii\Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \Kivii\Deactivator::class, 'deactivate' ] );

// Boot plugin
add_action( 'plugins_loaded', function () {
    \Kivii\Plugin::instance()->init();
} );
