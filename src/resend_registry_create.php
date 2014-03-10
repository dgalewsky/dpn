#!/usr/bin/php

<?php
	
	//
	// Send a registry_create message.
	//
	
	require_once 'dpn_utils.php';
	require_once 'common.php';
		
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/common.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_file_transfer_db_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_registry_utils.php';
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . "<dpn-object-id>\n";
		die();	
	}
	
	$obj_id = $argv[1];	
	
	$body = get_registry_info($obj_id);				
	
	$correlation_id = get_correlation_id_from_objectid($obj_id);
	
	send_registry_item_create($correlation_id, $body);
	




