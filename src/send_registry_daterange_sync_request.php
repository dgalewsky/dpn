#!/usr/bin/php

<?php
	
	//
	// Send a registry-daterange-sync-request.
	//
	
	require_once 'dpn_utils.php';
	require_once 'common.php';
	
	echo "\nSending registry-daterange-sync-request\n";
	
	if ($argc != 3) {
		echo "Usage: " . $argv[0] . "<start date> <end date> (format 2013-09-22T18:06:55Z\n";
		die();	
	}
	
	$start = $argv[1];	
	$end = $argv[2];	
		
	$correlation_id = uniqid();
	
	send_registry_daterange_sync_request($start, $end, $correlation_id);
	




