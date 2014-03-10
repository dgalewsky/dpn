<?php

require_once 'dpn_bag_utils.php';
	
$dir = '/mnt/replicate';

if (ready_to_replicate()) {
	echo "Ok - get to bagging\n";
	
	$bagpath = make_dpn_bag($dir . "/dpn_xfer",  "/tmp");
	
	echo "*Ok bagpath from make_dpn_bag $bagpath\n";
	
	// Ok - now there is a bag in /tmp called <guid>.tar
	
	$basename = basename($bagpath);
	
	echo "Basename $basename\n";
	
	$dirpart = substr($basename, 0, 2);
	
	echo "Dirpart $dirpart\n";
	
	echo "Pushing to iRODS\n";
	
	$handle = popen("iput -P -f $bagpath tdr/$dirpart/$basename", 'r');
	
	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
 	    echo "$read";
	} 
	
	pclose($handle);
	
	# Ok - now the file is in irods - copy to staging area
	
	rename($bagpath, "/dpn/outgoing/$basename");
	
	exit(0);
		
}


echo "Not yet";
exit(0);

function ready_to_replicate() {
	$dir = '/mnt/replicate';
	$repdir = scandir($dir);

	foreach ($repdir as $f) {
	    if ($f == '.' or $f == '..')
	    	    continue;
	    
	    echo "File: $f\n";
	    if ($f == "dpn_xfer") { 
		    echo "Found file: $f\n";
		    return true;
	    }
	}
	
	echo "File not found. Sorry\n";
	return false;
}
