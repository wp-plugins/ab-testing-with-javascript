<?php

class af_split_test_ajax_requests {

	private $wpdb;

	// js file parameters
	private $directory_plugin = 'split-testing';
	private $directory_js = 'js';
	private $js_base = 'ab-testing-base.js';
	private $js_file = 'ab.js';

	// table wp_options parameters
	private $option_prefix = '_ABTEST_';
	private $tests_active_ids = 'tests_active_ids';
	private $tests_stop_ids = 'tests_stop_ids';

	public function __construct() {

		global $wpdb;

		$this->wpdb = $wpdb;

		$this->add_action_wp_ajax();
	}

	public function add_action_wp_ajax() {

		add_action( 'wp_ajax_exsiting_name_check', array( $this, 'exsiting_name_check' ) );
		add_action( 'wp_ajax_upload_test_data', array( $this, 'upload_test_data' ) );
		add_action( 'wp_ajax_get_all_tests', array( $this, 'get_all_tests' ) );
		add_action( 'wp_ajax_set_version_winner', array( $this, 'set_version_winner' ) );
		add_action( 'wp_ajax_set_trash_test', array( $this, 'set_trash_test' ) );
		add_action( 'wp_ajax_set_test_active', array( $this, 'set_test_active' ) );
		add_action( 'wp_ajax_set_test_stop', array( $this, 'set_test_stop' ) );
	}

	/*
		Test names must be unique, so check if test name is already stored in wp_options as option_name
		@param post string test name
		@return json boolean
	*/
	public function exsiting_name_check() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$name = ( isset( $_POST['name'] ) && $_POST['name'] != '' ) ? $_POST['name'] : FALSE;

		if ( $name === FALSE ) {
			$new_name = FALSE;

		} else {
			$new_name = $this->model_exsiting_name_check( $name );
		}

		$new_name = json_encode( array( $new_name ) );
		header('Content-type: application/json');
		echo $new_name;

		// Always use die() on ajax actions
        die('');
	}

	/*
		upload all test data to wp_options
		@param post string test data in json form
		@return null
	*/
	public function upload_test_data() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$tests = ( isset( $_POST['tests'] ) && $_POST['tests'] != '' ) ? $_POST['tests'] : FALSE;

		// strip slashes before running through json_decode, this was causing some bug.
		$tests = stripslashes( $tests );
		$tests = json_decode( $tests, TRUE );

		if ( ! isset( $tests['tstName'] ) || $tests['tstName'] == '' )
			die('');

		if ( ! isset( $tests['verName'] ) || $tests['verName'] == '' || ! is_array( $tests['verName'] ) )
			die('');

		if ( ! isset( $tests['tstCode'] ) || $tests['tstCode'] == '' || ! is_array( $tests['tstCode'] ) )
			die('');

		if ( $tests !== FALSE ) {

			$tests = $this->model_save_test_data( $tests );
			$tests = json_encode( $tests );
			header('Content-type: application/json');
			echo $tests;
		}

		// Always use die() on ajax actions
        die('');

	}

	/*
		get all active and stopped tests
		@param null
		@return json string with all test data
	*/
	public function get_all_tests() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$tests = $this->get_active_tests();
		$tests_stopped = $this->get_stopped_tests();
		$tests = array_merge( $tests, $tests_stopped );
		$tests = json_encode( $tests );
		header('Content-type: application/json');
		echo $tests;

		// Always use die() on ajax actions
        die('');
	}

	/*
		set test version winner
		@param post string/integer used to determin if setting or unsetting winner
		@param post string test name
		@param post string/integer version index
		@return null
	*/
	public function set_version_winner() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$winner = ( isset( $_POST['winner'] ) && ctype_digit( $_POST['winner'] ) ) ? (int) $_POST['winner'] : FALSE;
		$winner = ( $winner === 1 || $winner === 0 ) ? $winner : FALSE;

		$test_name = isset( $_POST['test_name'] ) ? $_POST['test_name'] : FALSE;

		$vers_id = ( isset( $_POST['vers_id'] ) && ctype_digit( $_POST['vers_id'] ) ) ? (int) $_POST['vers_id'] : FALSE;

		if ( $winner === FALSE || 
			 $test_name === FALSE || 
			 $vers_id === FALSE ) {
			return;
		}

		$tests = $this->model_set_version_winner( $winner, $test_name, $vers_id );

		$this->create_production_ab_js_file();

		$tests = json_encode( $tests );
		header('Content-type: application/json');
		echo $tests;

		// Always use die() on ajax actions
        die('');
	}

	/*
		set test to active or paused state
		@param post string/integer used to determin if setting or unsetting active
		@param post string/integer start time
		@param post string test name
		@return null
	*/
	public function set_test_active() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$active = ( isset( $_POST['test_active'] ) && ctype_digit( $_POST['test_active'] ) ) ? (int) $_POST['test_active'] : FALSE;
		$active = ( $active === 1 || $active === 0 ) ? $active : FALSE;

		$start = ( isset( $_POST['time_start'] ) && ctype_digit( $_POST['time_start'] ) ) ? (int) $_POST['time_start'] : FALSE;

		$test_name = isset( $_POST['test_name'] ) ? $_POST['test_name'] : FALSE;

		if ( $active === FALSE ||
			 $test_name === FALSE ||
			 $start === FALSE ) {
			return;
		}

		$update = array();
		$update['active'] = $active;

		if ( $start > 0 )
			$update['time_start'] = $start;

		$this->update_test_option( $test_name, $update );

		$this->create_production_ab_js_file();

		$tests = json_encode( $tests );
		header('Content-type: application/json');
		echo $tests;

		// Always use die() on ajax actions
        die('');
	}

	/*
		set test to trash which ends test and no longer show test in admin
		@param post string test name
		@param post string/integer stop time
		@return null
	*/
	public function set_trash_test() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$test_name = isset( $_POST['test_name'] ) ? $_POST['test_name'] : FALSE;

		$stop = ( isset( $_POST['time_stop'] ) && ctype_digit( $_POST['time_stop'] ) ) ? (int) $_POST['time_stop'] : FALSE;

		if ( $test_name === FALSE || $stop === FALSE ) {
			return;
		}

		$update = array();
		$update['trash'] = 1;

		if ( $stop > 0 )
			$update['time_stop'] = $stop;

		$test_id = $this->update_test_option( $test_name, $update );
		$test_id = $test_id['id'];

		$this->update_option_test_id_status( 'remove', $this->tests_active_ids, $test_id );
		$this->update_option_test_id_status( 'remove', $this->tests_stop_ids, $test_id );

		$this->create_production_ab_js_file();

		$tests = json_encode( $tests );
		header('Content-type: application/json');
		echo $tests;

		// Always use die() on ajax actions
        die('');
	}


	/*
		set test to stop which ends test, test will still show in admin
		@param post string test name
		@param post string/integer stop time
		@return null
	*/
	public function set_test_stop() {

		if ( ! check_ajax_referer( ABST_SPLIT_TEST_NONCE, 'nonce' ) )
			die('');

		$test_name = isset( $_POST['test_name'] ) ? $_POST['test_name'] : FALSE;

		$stop = ( isset( $_POST['time_stop'] ) && ctype_digit( $_POST['time_stop'] ) ) ? (int) $_POST['time_stop'] : FALSE;

		if ( $test_name === FALSE || $stop === FALSE ) {
			return;
		}

		$update = array();
		$update['stop'] = 1;

		if ( $stop > 0 )
			$update['time_stop'] = $stop;

		$test_id = $this->update_test_option( $test_name, $update );
		$test_id = $test_id['id'];

		$this->update_option_test_id_status( 'add', $this->tests_stop_ids, $test_id );
		$this->update_option_test_id_status( 'remove', $this->tests_active_ids, $test_id );

		$this->create_production_ab_js_file();

		$tests = json_encode( array() );
		header('Content-type: application/json');
		echo $tests;

		// Always use die() on ajax actions
        die('');
	}

	/*
		upon update of set_test_active(), set_version_winner(), set_trash_test(), or set_test_stop()
		this function is run, which gets test data from wp_options, prepares if for output, and prints
		the output to js/ab.js.
		Make sure your server `/js/` directory and file `/js/ab.js` have permissions 0755 or 0775
		so PHP can write output to the `ab.js` file.
		@param
		@return
	*/
	private function create_production_ab_js_file() {

		$tests = $this->get_active_tests();
		$tests = $this->remove_meta_items( $tests );
		$tests = $this->set_active_stop_boolean( $tests );
		$tests = $this->tests_json_output( $tests );
		$this->create_production_js_file( $tests );
	}

	/*
		remove meta items that are not needed for production JS
		@param array test data
		@return array test data
	*/
	private function remove_meta_items( $tests ) {

		for ( $i = 0; $i < count( $tests ); $i++ ) {

			if ( isset( $tests[$i]['time_created'] ) )
				unset( $tests[$i]['time_created'] );

			if ( isset( $tests[$i]['time_start'] ) )
				unset( $tests[$i]['time_start'] );

			if ( isset( $tests[$i]['time_stop'] ) )
				unset( $tests[$i]['time_stop'] );

			if ( isset( $tests[$i]['trash'] ) )
				unset( $tests[$i]['trash'] );
		}

		return $tests;
	}

	/*
		convert 1 and 0's to boolean
		@param array test data
		@return array test data
	*/
	private function set_active_stop_boolean( $tests ) {

		for ( $i = 0; $i < count( $tests ); $i++ ) {

			if ( (int) $tests[$i]['active'] === 1 ) {
				$tests[$i]['active'] = true;
			} else {
				$tests[$i]['active'] = false;
			}

			if ( (int) $tests[$i]['stop'] === 1 ) {
				$tests[$i]['stop'] = true;
			} else {
				$tests[$i]['stop'] = false;
			}
		}

		return $tests;
	}

	/*
		#1 itereate over tests
		#2 remove \r \n and \t from javascript
		#3 store only version code itself, and not version meta data in output array
		#4 store only version name and not meta data in output
		#5 json_encode test data
		#6 escape single quotes which may be in javascript
		@param array test data
		@return string json test data
	*/
	private function tests_json_output( $tests ) {

		// convert double quotes to single quotes before printing JSON
		for ( $i = 0; $i < count( $tests ); $i++ ) {

			$versions = array();
			$names = array();
			
			for ( $j = 0; $j < count( $tests[$i]['versions'] ); $j++ ) {
				$versions[$j] = preg_replace( '/\"/', "'", $tests[$i]['versions'][$j] );

				$pattern = array(
					'/\\r/',
					'/\\n/',
					'/\\t/'
				);

				$replacement = array(
					'',
					'',
					''
				);

				$versions[$j] = preg_replace( $pattern, $replacement, $versions[$j] );
			}
			
			for ( $j = 0; $j < count( $tests[$i]['versions'] ); $j++ ) {
				$names[$j] = preg_replace( '/\"/', "'", $tests[$i]['versNames'][$j] );
			}

			$tests[$i]['versions'] = $versions;
			$tests[$i]['versNames'] = $names;
		}

		$tests = json_encode( $tests );

		// escape single quotes
		$tests = preg_replace( "/\'/", "\\'", $tests );

		return $tests;
	}

	/*
		#1 get and check if js/ab-testing-base.js file exsists
		#2 get and check if js/ab.js file exsists
		#3 combine both files for output
		@param string json test data
		@return null
	*/
	private function create_production_js_file( $tests ) {

		$js_dir = $this->get_plugin_js_directory( $this->directory_js );

		$js_base_file = $js_dir . '/' . $this->js_base;

		if ( ! is_file( $js_base_file ) )
			return;

		$js_print_file = $js_dir . '/' . $this->js_file;

		if ( ! is_file( $js_print_file ) )
			return;

		$this->merge_base_print_file( $js_base_file, $js_print_file, $tests );
	}

	private function get_plugin_js_directory( $js_dir ) {

		return plugin_dir_path( __DIR__ ) . $js_dir;
	}

	/*
		#1 enclose json test data in JS function af_split_testing.init()
		#2 open js/ab-testing-base.js and concatinate #1 to string
		#3 output #2 string to js/ab.js
		Make sure your server `/js/` directory and file `/js/ab.js` have permissions 0755 or 0775
		so PHP can write output to the `ab.js` file.
		@param string js/ab-testing-base.js
		@param string js/ab.js
		@param string json test data
		@return null
	*/
	private function merge_base_print_file( $js_base_file, $js_print_file, $tests ) {

		$js = "try { ab.init('";
		$js .= $tests;
		$js .= "') } catch (e) {} });";

		$js_base_file = file( $js_base_file );
		$js_base_file = implode( '', $js_base_file );
		$js_base_file .= $js;

		$js_print_file = fopen( $js_print_file, 'w' );
		fwrite( $js_print_file, $js_base_file );
		fclose( $js_print_file );
	}

	/*
		#1 get the test version names and test code from get_version_name_code
		#2 add version code and version name to $tests object
		#3 add test data to wp_options table
		#4 get option_id from #3
		#5 update test with just id from #4
		#6 add test/option id to active tests array
		@param array test data
		@return null
	*/
	private function model_save_test_data( $tests ) {

		$test_versions = $this->get_version_name_code( $tests );

		$tests['versions'] = $test_versions['code'];
		$tests['versNames'] = $test_versions['names'];

		$this->add_option_init_test_data( $tests );

		$option_id = $this->query_select_option_id( $tests['tstName'] );

		$this->update_test_option( $tests['tstName'], array( 'id' => $option_id ) );

		$this->update_option_test_id_status( 'add', $this->tests_active_ids, $option_id );
	}

	/*
		get the test version names and test code from test data
		@param array test data
		@return array test version names and version code
	*/
	private function get_version_name_code( $tests ) {

		$names = array();
		$code = array();

		for ( $i = 0; $i < count( $tests['verName'] ); $i++ ) {			

			$names[$i] = $tests['verName'][$i];
			$code[$i] = $tests['tstCode'][$i];
		}

		return array(
			'names' => $names,
			'code' => $code
		);
	}

	/*
		add test data to wp_option table
		@param array test data
		@return null
	*/
	private function add_option_init_test_data( $tests ) {

		$test_data = array(
			'id' => -1,
			'test_name' => $tests['tstName'],
			'active' => 0,
			'stop' => 0,
			'time_created' => time(),
			'time_start' => 0,
			'time_stop' => 0,
			'winner' => 0,
			'trash' => 0,
			'versions' => $tests['versions'],
			'versNames' => $tests['versNames']
		);

		$test_data = json_encode( $test_data );

		add_option( $this->option_prefix . $tests['tstName'], $test_data );
	}

	/*
		after add the test to the wp_option table, we must get the new
		option id.  the option id serves as the test id
		@param string test name. do not include option_prefix in test name
		@return int option id
	*/
	private function query_select_option_id( $test_name ) {

		$test_id = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"
					SELECT option_id
					FROM wp_options
					WHERE option_name = %s
				",
				$this->option_prefix . $test_name
			),
			ARRAY_A
		);

		if ( count( $test_id ) === 1 )
			return (int) $test_id[0]['option_id'];

		return 0;
	}

	/*
		get option, json_decode test data, then update test_data, re-json encode, then update table option
		@param mixed name of test. do not include test name prefix, ie _ABTEST_
		@param array test data to be udated
		@return array full test obj
	*/
	private function update_test_option( $test_name, $data ) {

		$test_data = get_option( $this->option_prefix . $test_name );
		$test_data = json_decode( $test_data, TRUE );

		foreach ( $data as $key => $value ) {
			$test_data[$key] = $value;
		}

		$test_array = $test_data;
		$test_data = json_encode( $test_data );
		$test_data = update_option( $this->option_prefix . $test_name, $test_data );

		return $test_array;
	}

	/*
		tests_active_ids is index of currently running tests
		@param string action options are add and delete
		@param string id array is the name of option_name index, tests_active_ids or tests_stop_ids
		@param string option id serves as both option_id and test id
		@return
	*/
	private function update_option_test_id_status( $action, $id_array, $test_id ) {

		$option_name = $this->option_prefix . $id_array;

		$ids = get_option( $option_name, FALSE );

		if ( $action == 'add' ) {

			if ( $ids !== FALSE ) {
				$ids = json_decode( $ids, TRUE );
				array_push( $ids, $test_id );
				$ids = json_encode( $ids );
				update_option( $option_name, $ids );
			} else {
				$ids = json_encode( array( $test_id ) );
				add_option( $option_name, $ids );
			}

		} else if ( $action == 'remove' ) {

			if ( $ids !== FALSE ) {				
				$ids = json_decode( $ids, TRUE );
				
				foreach ($ids as $key => $value) {
					if ( $value == $test_id )
						// delete array element
						unset( $ids[$key] );
				}

				// must re dim array
				$ids = array_values( $ids );

				$ids = json_encode( $ids );
				update_option( $option_name, $ids );
			}
		}
	}

	/*
		#1 get active test ids from tests_active_ids option
		#2 query tests based on test/option ids
		#3 convert queried data from string to object/array
		@param
		@return
	*/
	private function get_active_tests() {

		$test_ids = $this->get_option_tests_ids( $this->tests_active_ids );
		$tests = $this->query_tests_by_ids( $test_ids );
		$tests = $this->parse_test_data_to_array( $tests );
		return $tests;
	}

	/*
		get active test ids from wp_option
		@param null
		@return array test ids
	*/
	private function get_option_tests_ids( $ids ) {

		$test_ids = get_option( $this->option_prefix .  $ids, FALSE );

		if ( $test_ids === FALSE ) {
			return array();
		}

		return json_decode( $test_ids, TRUE );
	}

	/*
		query active tests from wp_options
		@param array test ids
		@return array test data
	*/
	private function query_tests_by_ids( $test_ids ) {

		// created placeholders for prepared statement
		$place_holders = '';

		for ( $i = 0; $i < count( $test_ids ); $i++ ) {
			$place_holders .= '%d,';
		}

		$place_holders = trim( $place_holders, ',' );

		$tests = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"
					SELECT option_value
					FROM wp_options
					WHERE `option_id` IN ($place_holders)
				",
				$test_ids
			),
			ARRAY_A
		);

		return $tests;
	}

	/*
		#1 get stopped test ids from tests_active_ids option
		#2 query tests based on test/option ids
		#3 convert queried data from string to object/array
		@param
		@return
	*/
	private function get_stopped_tests() {

		$test_ids = $this->get_option_tests_ids( $this->tests_stop_ids );
		$tests = $this->query_tests_by_ids( $test_ids );
		$tests = $this->parse_test_data_to_array( $tests );
		usort( $tests, array( $this, 'sort_by_time_created' ) );
		return $tests;
	}

	/*
		take test data from wp_option and convert to array
		@param array test data in json form
		@return array test data in associative array
	*/
	private function parse_test_data_to_array( $tests ) {

		for ( $i = 0; $i < count( $tests ); $i++ ) {
			$tests[$i] = json_decode( $tests[$i]['option_value'], TRUE );
		}

		return $tests;
	}

	private function sort_by_time_created( $a, $b ) {
		return $a['time_created'] - $b['time_created'];
	}

	private function model_set_version_winner( $winner, $test_name, $vers_id ) {

		if ( $winner === 1 ) {

			$this->update_test_option( $test_name, array( 'winner' => $vers_id ) );

		} else if ( $winner === 0 ) {

			$this->update_test_option( $test_name, array( 'winner' => 0 ) );
		}
	}

	/*
		check if test name already exists in wp_option table
		@param string
		@return boolean false if name exists in table
	*/
	private function model_exsiting_name_check( $name ) {

		$existing_name = get_option( $this->option_prefix . $name, FALSE );

		if ( $existing_name === FALSE ) {
			return TRUE;
		}

		return FALSE;
	}
}

new af_split_test_ajax_requests();
