#!/usr/bin/php

<?php
	
	//
	// Receive and process message from DPN. Main processing loop.
	//	
	
	require_once '../src/dpn_utils.php';
	require_once '../src/KLogger.php';
	require_once '../src/dpn_registry_utils.php';
	require_once '../src/dpn_file_transfer_db_utils.php';
	
	// Set up the logger
	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	
	
	$connection = new AMQPConnection();

	try {
		
		$ch = setup_channel();
		$ex = setup_exchange($ch);
		
		$q = setup_queue($ch);
		
		$home = $_SERVER['DPN_HOME'];
		echo "DPN Message Processor - processing messages.\nLog file is: $home/log/dpn_log.txt\n";
		
		// Process messages.
		
		$q->consume('process_message', AMQP_AUTOACK);  // blocks
		
		
	} catch ( Exception $e ) {
		$log->LogInfo($e->getMessage() );
	}
	
	function process_message($envelope) {
		global $log;
			
		if ($envelope != false) {	
			
		    $from = $envelope->getHeader('from');
		    
		    // Messages from us - are really - really - boring.
		    		    
		    if ($from == NODE) {
			    $log->LogInfo("Skipping message for " . $from);
		    	    return;
		    }
		    
		    $log->LogInfo("RECEIVED from: " . $envelope->getHeader('from') . " " . $envelope->getBody());
		} else {
		    $log->LogInfo("Nothing read");
		    return;
		}
		
		// Extract info from the header. From is the id of the node.
		
		$from = $envelope->getHeader('from');
		$ttl = $envelope->getHeader('ttl');
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
		$log->LogInfo("TTL: " . $ttl);
		
	
		// If this message was not sent to us - then just ignore it.
		
		if ($routing_key != 'broadcast') {
			if ($routing_key != 'dpn.utexas.inbound') {
				$log->LogInfo("Message not sent to UT - not procesing");
				return;
			}
		}
		
		// Check ttl to see if this message has already expired.
		
		$ttldiff = strtotime($ttl) - time();
		
		$log->LogInfo("TTL-Diff: $ttldiff - TTL Time Remaining: " . gmdate("H:i:s",$ttldiff));
			
		if ($ttldiff <= 0) {
			$log->LogInfo("TTL $ttl - for message has expired - ignoring");	
			return;
		}
		
		

	}
	

