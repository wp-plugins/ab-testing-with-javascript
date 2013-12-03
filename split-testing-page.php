<div class="row content-wrapper">

	<div class="row">
		<div class="small-12 columns">
			<button id="new_test" class="tiny">Create New Test</button>
		</div>
	</div>

	<div id="input_wrapper" class="row test-data">
		<div id="test_data" class="small-12 columns">
			<div class="row">
				<div class="small-4 columns">
					<label for="test_name"><strong>Enter Test Name</strong></label>
					<input id="test_name" type="text"></label>
					<p id="test_name_error" class="error">This Name Is In Use. Enter Unique Name</p>
				</div>
				<div class="small-8 columns text-right">
					<button id="close_new" class="tiny secondary">Close</button>
				</div>
			</div>

			<div class="new_data">
				<div class="row version_data">
					<div class="small-4 columns">
						<p>Version <span class="ver_num">1</span></p>
						<label>Version Name</label>
						<input class="version_name" type="text"></label>
						<button class="tiny secondary delete_ver">Delete Version</button>
					</div>
					<div class="small-8 columns">				
						<label>Javascript Code (do not enter script tags)</label>
						<textarea class="code_input"></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="row table-header">
		<div class="small-12 columns">
			<div class="row">
				<div class="small-6 columns">
					<p>Test Name</p>
				</div>
				<div class="small-1 columns">
					<p>Started</p>
				</div>
				<div class="small-1 columns">
					<p>Stopped</p>
				</div>
				<div class="small-4 columns"></div>
			</div>
		</div>
	</div>

	<div id="test_table" class="row">
		<div class="small-12 columns cell_wrapper">
			<div class="row cell-header">
				<div class="small-4 columns">
					<p class="test_name"></p>
				</div>
				<div class="small-1 columns pad-0">					
					<label><input class="test_active" type="checkbox"> <strong>Active</strong></label>
				</div>
				<div class="small-1 columns pad-0">
					<label><input class="test_stop" type="checkbox"> <strong>Stop</strong></label>
				</div>
				<div class="small-1 columns">
					<p class="time_start"></p>
				</div>
				<div class="small-1 columns">
					<p class="time_stop"></p>
				</div>
				<div class="small-1 columns">
					<button class="tiny secondary trash_this">Trash</button>
				</div>
				<div class="small-3 columns"></div>
			</div>
			<div class="row version_wrap">
				<div class="small-4 columns">
					<p class="vers_name"></p>
				</div>
				<div class="small-1 columns pad-0">
					<label class="set_winner"><input class="set_winner" type="checkbox"> Winner</label>
				</div>
				<div class="small-7 columns">
					<textarea class="version_code"></textarea>
				</div>
			</div>
		</div>
	</div>

</div>

<script>
	var pageLocalData = {
		ajaxurl: '<?php echo site_url() . "/wp-admin/admin-ajax.php" ?>', 
		nonce: '<?php echo wp_create_nonce( ABST_SPLIT_TEST_NONCE ); ?>'
	};
</script>