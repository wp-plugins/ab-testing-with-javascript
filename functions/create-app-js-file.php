<?php

class AF_create_app_js_file {

	private $directory_plugin = 'split-testing';
	private $directory_js = 'js';
	private $directory_js_permissions = 0755;
	private $js_file = 'ab.js';
	private $js_file_permissions = 0755;

	public function __construct() {

		$this->hooks();
	}

	public function hooks() {

		add_action( 'admin_init', array( $this, 'create_js_file' ) );
	}

	public function create_js_file() {

		$this->directory_plugin = $this->set_plugin_directory( $this->directory_plugin );

		$new_directory = $this->directory_plugin . $this->directory_js;

		$this->check_create_js_dir( $new_directory );
	}

	private function set_plugin_directory( $plugin_dir ) {
		return plugin_dir_path( __DIR__ );
	}

	private function check_create_js_dir( $dir ) {

		if ( ! is_dir( $dir ) )
			mkdir( $dir, $this->directory_js_permissions );
	}
}

new AF_create_app_js_file();