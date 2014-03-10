<?php
	// 
	// Copy a file from the local file-system into 
	// it's appropriate iRODS directory
	//
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . "<filename>\n";
		die();	
	}
	
	$fname = $argv[1];
	
	$basename = basename($fname);
	
	$dirpart = substr($basename, 0, 2);
	
	echo "Dirpart $dirpart\n";
	
	echo "Pushing to iRODS\n";
	
	$handle = popen("iput -P -f $fname tdr/$dirpart/$basename", 'r');
	
	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
 	    echo "$read";
	} 
	
	pclose($handle);
