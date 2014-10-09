
<?php
	require_once 'dpn_bag_utils.php';
	
	//
	// Make a dpn bag
	//
	
		
	$indir = "/tmp/bagging_area/tamug_microsocpe_collection";
	$outdir = '/tmp/bagging_area';
		
	$obj_id = make_dpn_bag($indir, $outdir);	
	
	// The file has .tar as a suffix
	
	echo "\nMake_dpn_bag - returning- $obj_id\n";
	
	echo "Rename: " . "$outdir/$obj_id" . ".tar ", "/dpn/outgoing/" . $obj_id ";
	
	// We dont want the package named tar
	
	rename ("$outdir/$obj_id" . ".tar", "/dpn/outgoing/" . $obj_id );
	
	echo "Exec send_replication_request for $obj_id\n";
	
	
	$ret = exec("php /mnt/dpn_test_env/dpn_local/src/send_replication_request.php $obj_id");
	
	echo "\nReturn from exec $ret\n";
