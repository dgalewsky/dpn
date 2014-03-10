#!/usr/bin/php

<?php
	// Resend replication request - without creating info in the file table or the registry
	// Used if we do not get enough replies
	// Note - Re-uses the correlation id from the original message. TODO is this a good idea?
	
	require_once 'dpn_utils.php';
	require_once 'common.php';
	require_once 'dpn_file_transfer_db_utils.php';
	require_once 'dpn_registry_utils.php';
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . "<dpn-object-id>\n";
		die();	
	}
	
	$obj_id = $argv[1];			
	
	//Setup for DPN - get correlation id
	
	$correlation_id = get_correlation_id_from_objectid($obj_id);
	
	if ($correlation_id == "") {
		echo "Correlation ID for object $obj_id does not exist. Exiting.\n";
		return;
	}

	$path = get_file_path_from_objectid($obj_id);
	
 	if ($path == '') { 
 		echo "Unable to find database record for dpn object: $obj_id";
 		return;
 	} else {
 		echo "\nSending replication_request for file: " . $path . "\n";	
 		send_replication_request($path, $correlation_id, $obj_id);
 	}
