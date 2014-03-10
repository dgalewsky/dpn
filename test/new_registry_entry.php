<?php

// This is a lot like send_replication_request
// Create a throwaway registry entry

	$outgoing_path = '/dpn/outgoing/';
	
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/common.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_file_transfer_db_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_registry_utils.php';
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . "<filename>\n";
		die();	
	}
	
	$file = $argv[1];	
	
	$file = $outgoing_path . $file;
	
	// Make sure the file is actually there
	
	if (file_exists($file)) {
	    echo "The file $file exists";
	} else {
	    echo "The file $file does not exist";
	}
	
	echo "\nCreating registry entry for file: " . $file . "\n";
	
	//Setup for DPN - mint a new correlation id
	
	$correlation_id = uniqid();
	
	$checksum = hash_file('sha256', $file);
	
	$filesize = filesize($file);  
	
	// Create the 'ingestion' record for the file. After this - 
	// the file is officially scheduled for replication
	
	$object_id = new_dpn_file($file, $checksum, $filesize, $correlation_id);
	
	// Create the registry record for this guy
	
	$body = array();
	
	$body['dpn_object_id'] = $object_id;          
	$body['local_id'] = $file;

	$node = NODE;

	$body['first_node_name'] = $node;           
	$body['replicating_node_names'] = array($node);    
	$body['version_number'] = '1';            
	$body['previous_version_object_id'] = null;
	$body['forward_version_object_id']  = null;
	$body['first_version_object_id'] = $object_id;   
	$body['fixity_algorithm']  = 'sha256';         
	$body['fixity_value'] = $checksum;              
	$body['last_fixity_date'] = date('c');  
	$body['creation_date'] = date('c');             
	$body['last_modified_date'] = date('c');        
	$body['bag_size'] = $filesize;                   
	$body['brightening_object_id'] = null;      
	$body['rights_object_id'] = null;          
	$body['object_type'] = 'data';
	
	save_registry_entry($body);
	
	echo "\n\nCreated new registry entry\n";
	print_r($body);
