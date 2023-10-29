<?php

namespace WPSP;

/**
 * Main plugin class
 */
class Plugin extends Plugin_Base {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		parent::__construct();

	}

	public function front_enqueue_assets() {
		//wp_enqueue_script( WPSP_PLUGIN_PREFIX . '_js', WPSP_PLUGIN_URL . '/assets/script.js', [ 'jquery' ], WPSP_PLUGIN_VERSION, true );
	}

	public function admin_enqueue_assets() {
	}

	/**
	 * Admin code
	 *
	 * @throws \Exception Page class not found.
	 */
	public function admin_code() {
		$this->register_page( 'Page_Socials' );
	}

	public function global_code() {
	}


}
