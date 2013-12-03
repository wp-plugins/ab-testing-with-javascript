<?php
/*
Plugin Name: A/B Split Testing
Plugin URI: 
Description: A/B testing plugin for wordpress
Version: 1.0
Author: Nate Martin
License: GPL-2.0+
License URI: LICENSE.txt
*/

if ( ! defined( 'ABST_SPLIT_TEST_NONCE' ) )
	define( 'ABST_SPLIT_TEST_NONCE', 'AddYourUniqueNonceHere' );

// create admin_menu item
include( 'functions/split-testing-menu.php' );

// load js and css for admin
include( 'functions/load-resources.php' );

// create ab.js file and directory. Both file and directory must be set to
// permission 0775 or 0755 for PHP writting
include( 'functions/create-app-js-file.php' );

// ajax calls for admin events
include( 'functions/ajax-requests.php' );
