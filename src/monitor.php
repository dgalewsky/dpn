#!/usr/bin/php

<?php
	
	//
	// Receive messages from DPN. No processing - just monitor/empty the queue.
	//	
	
	require_once 'dpn_utils.php';
	require_once 'KLogger.php';
	
	echo "Consuming - not processing - all messages\n";
	
	// Set up the logger
	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_monitor_log.txt" , KLogger::INFO );	
	
	// Non blocking read on DPN queue
	
	while (true) {    
	    $envelope = get_dpn_message();		
	    process_message($envelope);
	    sleep(1);
	}
	
	exit();
	
	
	function process_message($envelope) {
		global $log;
		
		if ($envelope != false)
		    $log->LogInfo("Message from: " . $envelope->getHeader('from') . ": " . $envelope->getBody());
		else {
		    $log->LogInfo("Nothing read");
		    return;
		}
		
		// Extract info from the header
		
		$from = $envelope->getHeader('from');
		$correlation_id = $envelope->getHeader('correlation_id');
		$sequence       = $envelope->getHeader('sequence');
		$reply_key      = $envelope->getHeader('reply_key');
		$body = json_decode($envelope->getBody(), TRUE);
		
		$routing_key = $envelope->getRoutingKey();
		
		// Get message_name from JSON in the body
		
		$message_name   = $body['message_name'];
		
		$log->LogInfo("Message Name: " . $message_name);		
		$log->LogInfo("Routing Key: " . $routing_key);
		$log->LogInfo("From: " . $from);
		$log->LogInfo("Correlation Id: " . $correlation_id);
		$log->LogInfo("sequence: " . $sequence);
		$log->LogInfo("Reply Key " . $reply_key);	
		
		if ($from == 'tdr-local') return;
		
		print "\n";
		print "message-name $message_name\n";
		print "routing-key $routing_key\n";
		print "correlation-id $correlation_id\n";
		print "reply-key $reply_key\n";

		print "from $from\n\n";
		
		
		
	}
	

