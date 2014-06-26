<?php

	// 
	// Check the dpn_recovery_file table to see if we need to stage a file.
	// If so - pull the file from TACC and put in the recovery staging area.
	//
	
	require_once 'KLogger.php';
	require_once 'dpn_file_transfer_db_utils.php';
	require_once 'common.php';
	require_once 'dpn_utils.php';

	define('RECOVERY_DIRECTORY', '/dpn/recovery');	
	
	// Set up the logger	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_recovery_staging_log.txt" , KLogger::INFO );	
	
	error_reporting(E_ALL);
	
	// get the object id of the file to stage; get the file from irods and put into the staging area.
	
	$ret = get_next_recovery_file();
	
	$obj_id = $ret['object_id'];
	$reply_key = $ret['reply_key'];
	$correlation_id = $ret['correlation_id'];

        echo "obj $obj_id reply $reply_key corr $correlation_id\n";
	
	if ($obj_id == '') {
	    $log->LogInfo('Nothing to recover.');
	    return;
	}
	
	$log->LogInfo("Object Id: $obj_id");
	
	$dirpart = substr($obj_id, 0, 2);
	
	###
	
	set_recovery_file_status($correlation_id, INITIATED_STATUS);
	
	echo "Dirpart $dirpart\n";

	echo "Copying file\n";

	// This should probably go to /dpn/recovery - once I have root access
	
	//str_replace('\/', '/', json_encode($body)
		
	$handle = popen("iget -P -f tdr/$dirpart/$correlation_id /dpn/outgoing", 'r');

	while(!feof($handle)) {
	    $read = fread($handle, 1024);
 	    echo "$read";
	}

	pclose($handle);
	
	
	send_recovery_transfer_reply("rsync", $reply_key, $correlation_id, "/dpn/outgoing/$fname");			
	
?>

