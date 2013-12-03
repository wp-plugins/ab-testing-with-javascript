<?php

class AF_split_testing_load_resources {

	public function __construct() {

		$this->hooks();
	}

	public function hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'load_resources' ) );
	}

	public function load_resources( $hook ) {

		if ( $hook != 'split-testing/split-testing-page.php' )
			return FALSE;

		// css
		wp_register_style( 'foundation', plugins_url( 'split-testing/css/foundation.min.css' ) );
		wp_register_style( 'plugin_style', plugins_url( 'split-testing/css/plugin-style.css' ), array( 'foundation' ) );

		wp_enqueue_style( 'foundation' );
		wp_enqueue_style( 'plugin_style' );

		// js
		wp_register_script( 'plugin_js', plugins_url( 'split-testing/js/ab-testing-admin.js' ) );
		wp_enqueue_script( 'plugin_js' );
	}
}

new AF_split_testing_load_resources();