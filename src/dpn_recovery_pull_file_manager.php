<?php
	require_once 'KLogger.php';
	require_once 'dpn_file_transfer_db_utils.php';
	require_once 'common.php';
	require_once 'dpn_utils.php';

	
	//
	// Pull a recovery file from a remote node who has staged the content for us
	//
	
	// Set up the logger	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	

	//rsync -azv /dpn/outgoing/ /dpn/incoming/

	error_reporting(E_ALL);
	
	// Get the path and correlation id for this recovery file
	
	$ret = get_next_recovery_file();
	
	$location = $ret['location'];
	$correlation_id = $ret['correlation_id'];
	$reply_key = $ret['reply_key'];
	
	echo ("Loc: $location Corr: $correlation_id\n");
	
	if ($location == '') {
	    echo "Nothing to transfer\n";
	    $log->LogInfo('Nothing to transfer');
	    return;
	}
	
	$log->LogInfo("Location: $location");
	
	// Tell the world that the file is being transferred
	
	set_recovery_request_file_status($correlation_id, TRANSFERRING_STATUS);
	
	// Rsync the file
	
	$handle = popen("rsync -avL $location /dpn/recovery_incoming/", 'r');

	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
	    $log->LogInfo($read);
	    echo "Read: $read";
	} 
	
	pclose($handle);
	
	//$reply_key = get_recovery_reply_key_from_correlation_id($correlation_id);

	echo "\n**** Reply Key $reply_key ***\n";
	
	// Figure out the path to the file we just got.
	
	echo ("\n^^^Location: $location");
		
	// $parts = preg_split('/:/', $location, -1, PREG_SPLIT_NO_EMPTY);
	$incoming_path = "/dpn/recovery_incoming/" . basename($location);
	
	// Calculate and save checksum
	
	$checksum = hash_file('sha256', "$incoming_path");
	$log->LogInfo("Recovered file $incoming_path Checksum $checksum");
	
	$log->LogInfo("Sending transfer status ACK to $reply_key");
	
	send_recovery_transfer_status("rsync", $reply_key, 'ack', $checksum, $correlation_id);				

	
	// Update the database record to reflect the checksum we computed
	
	// set_inbound_file_checksum($correlation_id, $checksum);
	
	// send_replication_transfer_reply('ack', $reply_key, $correlation_id, $checksum);
