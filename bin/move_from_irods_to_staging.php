<?php
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . "<filename>\n";
		die();	
	}
	
	$fname = $argv[1];
	
	$dirpart = substr($fname, 0, 2);
	
	echo "Dirpart $dirpart\n";
	
	echo "Copying file\n";
	
	$handle = popen("iget -P -f tdr/$dirpart/$fname /tmp", 'r');
	
	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
 	    echo "$read";
	} 
	
	pclose($handle);
