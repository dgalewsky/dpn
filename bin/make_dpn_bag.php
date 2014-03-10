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
	
	if (!is_dir($indir)) {
	    echo "$indir is not a directory. It must be a directory.";
	    die();
	}
	
	if (!is_dir($outdir)) {
	    echo "$outdir is not a directory. It must be a directory.";
	    die();
	}
		
	make_dpn_bag($indir, $outdir);	

