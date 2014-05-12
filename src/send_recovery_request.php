#!/usr/bin/php

<?php
	
	//
	// Send a registry-daterange-sync-request.
	//
	
	require_once 'dpn_utils.php';
	require_once 'common.php';
	
	echo "Sending recovery-init-query \n";
	
	if ($argc != 2) {
		echo "Usage: " . $argv[0] . " <dpn_object_id>\n";
		die();	
	}
	
	$objid = $argv[1];	
			
	$correlation_id = uniqid();
	
	send_recovery_request($correlation_id, $objid);
	




