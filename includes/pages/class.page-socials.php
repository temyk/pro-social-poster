<?php

namespace WPSP;

class Page_Socials extends PageBase {

	/**
	 * Settings constructor.
	 *
	 * @param $plugin Plugin Plugin class
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );

		$this->id                 = 'socials';
		$this->page_menu_dashicon = 'dashicons-share-alt2';
		$this->page_menu_position = 20;
		$this->page_title         = __( 'Poster Socials', 'pro-social-poster' );
		$this->page_menu_title    = __( 'Poster Socials', 'pro-social-poster' );
	}

	public function page_action() {
	}
}
