<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpmailer.io
 * @since             0.0.1
 * @package           Wpmailer
 *
 * @wordpress-plugin
 * Plugin Name:       WPMailer
 * Plugin URI:        https://wordpress.org/plugins/wpmailer
 * Description:       The best mail builder, No More Core for your emails support Elementor, CF7 forms etc.
 * Version:           0.0.7
 * Author:            giladtakoni
 * Author URI:        https://wpmailer.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpmailer
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( !function_exists( 'wpm_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wpm_fs()
    {
        global  $wpm_fs ;
        
        if ( !isset( $wpm_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/libs/freemius-sdk/start.php';
            $wpm_fs = fs_dynamic_init( array(
                'id'               => '5577',
                'slug'             => 'wpmailer',
                'premium_slug'     => 'wpmailer-pro',
                'type'             => 'plugin',
                'public_key'       => 'pk_bca96d2e7ac162ec04008fae48878',
                'is_premium'       => false,
                'is_premium_only'  => false,
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'menu'             => array(
                'slug' => 'wpmailer',
            ),
                'is_live'          => true,
            ) );
        }
        
        return $wpm_fs;
    }
    
    // Init Freemius.
    wpm_fs();
    // Signal that SDK was initiated.
    do_action( 'wpm_fs_loaded' );
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPMAILER_VERSION', '1.0.0' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpmailer-activator.php
 */
function activate_wpmailer()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmailer-activator.php';
    Wpmailer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpmailer-deactivator.php
 */
function deactivate_wpmailer()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpmailer-deactivator.php';
    Wpmailer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpmailer' );
register_deactivation_hook( __FILE__, 'deactivate_wpmailer' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpmailer.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-wpmailer-api.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpmailer()
{
    add_filter( 'allowed_http_origins', 'add_allowed_origins' );
    function add_allowed_origins( $origins )
    {
        $origins[] = 'http://localhost:3030';
        return $origins;
    }
    
    $plugin = new Wpmailer();
    $plugin->run();
}

run_wpmailer();