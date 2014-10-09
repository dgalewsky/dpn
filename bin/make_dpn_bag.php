<?php
	require_once 'dpn_bag_utils.php';
	
	//
	// Make a dpn bag
	//
	

	
	if ($argc != 3) {
		echo "Usage: " . $argv[0] . " <input_directory> <output_path>\n";
		die();	
	}
	
	$indir = $argv[1];
	$outdir = $argv[2];
	
	// Input can be a file. Output is a dir.
	
	if (!is_dir($outdir)) {
	    echo "$outdir is not a directory. It must be a directory.";
	    die();
	}
		
	$obj_id = make_dpn_bag($indir, $outdir);	
	
	echo "\nMake_dpn_bag - returning- $obj_id\n";
	
	return $obj_id;
