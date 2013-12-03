<?php

class AF_split_testing_menu {

	function __construct() {
		add_action( 'admin_menu', array( &$this, 'af_split_testing_menu_page' ), 9 );
	}

	// create default menu item and page to host various custom posts
	function af_split_testing_menu_page() {	
	   add_menu_page( 'AB Testing', 'AB Testing', 'edit_posts', 'split-testing/split-testing-page.php', '', '', 20 );
	}
}

new AF_split_testing_menu();

