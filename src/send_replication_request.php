#!/usr/bin/php

<?php
	$outgoing_path = '/dpn/outgoing/';
	
	//
	// Send a replication request message for a file.
	// The file is expected to be in /dpn/outgoing.
	// This also creates the registry record for the outgoing file.
	// NOTE - It is assumed that the name of the file is the DPN object id
	// and that the file is a tar file that matches the DPN spec.
	//
	
	require_once 'dpn_utils.php';
	require_once 'common.php';
	require_once 'dpn_file_transfer_db_utils.php';
	require_once 'dpn_registry_utils.php';
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . "<filename>\n";
		echo "Filename will be used as the DPN object ID\n";
		die();	
	}
	
	$file = $argv[1];
	
	// The filename is expected to be a dpn object id.
	$object_id = $argv[1];	
	
	$file = $outgoing_path . $file;
	
	// Make sure the file is actually there
	
	if (file_exists($file)) {
	    echo "The file $file exists\n";
	} else {
	    exit( "The file $file does not exist\n");
	}
	
	echo "\nSending replication_request for file: " . $file . "\n";
	
	//Setup for DPN - mint a new correlation id
	
	$correlation_id = uniqid();
	
	$checksum = hash_file('sha256', $file);
	
	$filesize = filesize($file);  
	
	// Create the 'ingestion' record for the file. After this - 
	// the file is officially scheduled for replication.
	// The dpn_file table is a place to keep track of the status of the messages being exchanged.
	
	// Mint a new object_id
	
	// $object_id = uuid();
	
	// Make an appropriately named copy of the file
	
	$dpn_staged_file = $outgoing_path . $object_id . ".tar";
	
	echo "File being staged = $dpn_staged_file\n";
	
	if (!copy($file, $dpn_staged_file)) {
		echo "failed to copy $file...\n";
	}	
	

	
	new_dpn_file($dpn_staged_file, $checksum, $filesize, $correlation_id, $object_id);
	

	
	//Now create the registry record.
	
	$body = array();
	$node = NODE;
	
	$body['dpn_object_id'] = $object_id;          
	$body['local_id'] = $file;                  
	$body['first_node_name'] = "$node";           
	$body['replicating_node_names'] = array("$node");    
	$body['version_number'] = '1';            
	$body['previous_version_object_id'] = '';
	$body['forward_version_object_id']  = '';
	$body['first_version_object_id'] = $object_id;   
	$body['fixity_algorithm']  = 'sha256';         
	$body['fixity_value'] = $checksum;              
	$body['last_fixity_date'] = date('c');  
	$body['creation_date'] = date('c');             
	$body['last_modified_date'] = date('c');        
	$body['bag_size'] = $filesize;                   
	$body['brightening_object_id'] = '';      
	$body['rights_object_id'] = '';          
	$body['object_type'] = 'data';
	
	save_registry_entry($body);
	
	// All of the 'paperwork' has been filed - initiate the transfer
	
	send_replication_request($file, $correlation_id, $object_id);
