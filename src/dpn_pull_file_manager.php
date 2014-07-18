<?php
	require_once 'KLogger.php';
	require_once 'dpn_file_transfer_db_utils.php';
	require_once 'common.php';
	require_once 'dpn_utils.php';
	
	
	// Set up the logger	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	

	//rsync -azv /dpn/outgoing/ /dpn/incoming/

	error_reporting(E_ALL);
	
	$ret = get_next_inbound_file();
	
	$location = $ret['location'];
	$correlation_id = $ret['correlation_id'];
	
	if ($location == '') {
	    echo "Nothing to transfer\n";
	    $log->LogInfo('Nothing to transfer');
	    return;
	}
	
	$log->LogInfo("Location: $location");
	
	set_inbound_file_status($correlation_id, TRANSFERRING_STATUS);
	
	$incoming_directory = INCOMING_DIRECTORY;
	
	$handle = popen("rsync -avL $location $incoming_directory", 'r');

	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
	    $log->LogInfo($read);
	    echo "Read: $read";
	} 
	
	pclose($handle);
	
	$reply_key = get_inbound_reply_key_from_correlation_id($correlation_id);
	
	// Figure out the path to the file we just got.
	
	$parts = preg_split('/:/', $location, -1, PREG_SPLIT_NO_EMPTY);
	
	$fname = basename($parts[1]);
		
	$incoming_path = $incoming_directory . "/" . $fname;
	
	// Calculate and save checksum
	
	$checksum = hash_file('sha256', "$incoming_path");
	$log->LogInfo("File $incoming_path Checksum $checksum");
	
	// Update the database record to reflect the checksum we computed
	
	set_inbound_file_checksum($correlation_id, $checksum);
	
	// Keep the filename (aka object_id) from this rsynch
	
	set_inbound_file_name($correlation_id, $fname);
	
	send_replication_transfer_reply('ack', $reply_key, $correlation_id, $checksum);
