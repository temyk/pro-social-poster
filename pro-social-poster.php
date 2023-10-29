<?php
/**
 * Plugin Name: PRO Social Poster
 * Description: Автопостинг в соцсети (ТГ, ВК, ОК)
 * Version:     1.0.0
 * Author:      Webtemyk <webtemyk@yandex.ru>
 * Author URI:  https://temyk.ru
 * Text Domain: pro-social-poster
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_file_data( __FILE__, [ 'ver' => 'Version' ] );

define( 'WPSP_PLUGIN_DIR', __DIR__ );
define( 'WPSP_PLUGIN_SLUG', 'pro-social-poster' );
define( 'WPSP_PLUGIN_VERSION', $data['ver'] );
define( 'WPSP_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'WPSP_PLUGIN_URL', plugins_url( null, __FILE__ ) );
define( 'WPSP_PLUGIN_PREFIX', 'wpsp' );

define( 'WPSP_PLUGIN_SOCIALS_URL', WPSP_PLUGIN_URL . '/socials' );
define( 'WPSP_PLUGIN_SOCIALS_DIR', WPSP_PLUGIN_DIR . '/socials' );

load_plugin_textdomain( WPSP_PLUGIN_SLUG, false, dirname( WPSP_PLUGIN_BASE ) );

require_once WPSP_PLUGIN_DIR . '/includes/boot.php';

try {
	require_once WPSP_PLUGIN_DIR . '/socials/load.php';
	new WPSP\Plugin();
} catch ( Exception $e ) {
	$wpsp_plugin_error_func = function () use ( $e ) {
		$error = sprintf( __( 'The %1$s plugin has stopped. <b>Error:</b> %2$s Code: %3$s', 'pro-social-poster' ),
			'PRO Social Poster', $e->getMessage(), $e->getCode() );
		echo wp_kses_post( '<div class="notice notice-error"><p>' . $error . '</p></div>' );
	};

	add_action( 'admin_notices', $wpsp_plugin_error_func );
	add_action( 'network_admin_notices', $wpsp_plugin_error_func );
}
