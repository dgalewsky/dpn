<?php
	require_once 'dpn_bag_utils.php';
	
	//
	// Verify a dpn bag
	//
	

	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . " <bag_file_path>\n";
		die();	
	}
	
	$bag_file = $argv[1];
	
	if (!file_exists($bag_file)) {
		echo "$bag_file does not exist";
		die();		
	}
		
	validate_dpn_bag($bag_file);	

