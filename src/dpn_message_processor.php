#!/usr/bin/php

<?php
	
	//
	// Receive and process message from DPN. Main processing loop.
	//	
	
	require_once 'dpn_utils.php';
	require_once 'KLogger.php';
	require_once 'dpn_registry_utils.php';
	require_once 'dpn_file_transfer_db_utils.php';
	
	// Set up the logger
	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	
	
	while(1) {

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
		
		$log->LogInfo("Exception with AMQPConnection");
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
		
		// 
		// (Replicating Node) Add an entry to the registry
		//
		
		if ($message_name == 'registry-item-create' ) {
			$log->LogInfo( "registry-item-create from: " . $from);
			
			// Write info into the registry
			
			save_registry_entry($body);
			
			send_registry_entry_created("ack", $reply_key, $correlation_id);					
			return;
		}
		
		
		// 
		// (First Node) Process registry-item-created messages from other nodes.
		// Receipt of this message means that a remote - replicating node has 
		// updated their registry.
		//
		
		if ($message_name == 'registry-entry-created' ) {
			$log->LogInfo( "registry-item-created from: " . $from);
			
			// Update db to show that this node has saved the messge
			
			ack_dpn_registry_item_create_message($correlation_id, $from);
			
			return;
		}		
		
		
		//
		// (Replicating-node) replication-init-query - a remote node has content for us to pick up.
		// Send a replication-available-reply saying that we are available to come get the package.
		// 
		// TODO -- what should we do if we already have the package.
		//
		
		if ($message_name == 'replication-init-query' ) {
			
			$log->LogInfo( "replication_init_query from: " . $from . " Object id: " . $body['dpn_object_id'] );
			
			$object_id = $body['dpn_object_id'];

			send_replication_available_reply("rsync", $reply_key, $correlation_id);				
	
			return;
		}
		
		//
		// (First node) replication-available-reply is sent as a reply to a replication-init-query
		//
		// It indicates if the remote node is available to replicate our content.
		// Remote node may send a message_att of nack - indicating that they can not retrieve our content.
		// If we did not recieve a nack - then send them the message indicating where they can retrieve from.
		// After that message is sent - the content will be pulled from the remote system
		//
		
		if ($message_name == 'replication-available-reply') {
			
			$log->LogInfo( "replication-available-reply " . $from);
			
			$message_att = $body['message_att'];	
		    
			if ($message_att != 'nak') {
			    
			    // Figure out which bag we are transferring
			    
			    $path = get_file_path_from_correlation_id($correlation_id);
			    
			    // Register the initiation of the transfer
			    
			    new_outbound_transfer($correlation_id, $from); 
			    			    
			    // send_replication_location_reply( 'rsync', 'dpn@dpn.lib.utexas.edu:' . $path, $reply_key, $correlation_id);
			    send_replication_location_reply( 'rsync', RSYNC_HOST_STRING . $path, $reply_key, $correlation_id);

			} else {	    	    
				$log->LogInfo("Recieved replication-available-reply nak: " . $message_att . " for replication-available-reply - from " . $from);	    	
			}
		    
			return;  
		}
		
		//
		// (First node) replication-transfer-reply - received when a remote node has transferred our content.
		// This is the end of the transaction chain for a first-node transferring content.
		// Finish up the accounting for this transfer.
		//
		
		if ($message_name == 'replication-transfer-reply') {
			
		    $log->LogInfo("Recieved : replication-transfer-reply\n");
		    
		    $message_att = $body['message_att'];	
	
		    
		    if ($message_att != 'nak') {
			    $fixity_algorithm = $body['fixity_algorithm'];
			    $fixity_value = $body['fixity_value'];	    	   
			    
			    // Check received fixity - against our calculated value
			    
			    $my_checksum = get_dpn_file_checksum($correlation_id);
			    
			    $log->LogInfo("Received checksum $fixity_value - my checkesum : $my_checksum");
			    
			    // Ok - checksum checks out. All is good.
			    
			    if ($my_checksum == $fixity_value) {
			    	
			    	// Record that the transfer completed succesfuly
			    	
			    	set_outbound_transfer_success($correlation_id, $from);   
			    	
			    	// Then send verify reply
			    	
			        send_replication_verify_reply('ack', $reply_key, $correlation_id);			       			        
			        
			        // Now send a registry update
				
				// Which object is this message about
				
				$obj = get_object_id_from_correlation($correlation_id);
				
				// Update the registry to reflect the new copy
				
				add_node_to_registry_record($obj, $from);
				
				// Get the new registry info
				
				$body = get_registry_info($obj);
				
				// FIXME - Is it ok to re-use correlation_id ??
				// Now update the network with the new information
				
				send_registry_item_create($correlation_id, $body);			        			        			      			        
			        
			    } else {
			    	set_outbound_transfer_retry($correlation_id, $from);
			    	send_replication_verify_reply('retry', $reply_key, $correlation_id);
			    	$log->LogInfo("replication_transfer_reply - checksums did not match. ERROR");
			    }
			    
		    } else {
	
			$message_error = $body['message_error'];    
			$log->LogInfo("Received replication-available-reply nak: " . $message_att . " error: " . $message_error . " for replication-transfer-reply - from " . $from);	    	
		    }
		    
		    return;			
			
		}
		
		//
		// (Replicating Node) replicaton-location-reply - Recieved from a first node. 
		// It supplies information about how we should pick up the package.
		// Create a file object and then an inbound transfer queue entry.
		//
		
		if ($message_name == 'replication-location-reply') {
			
			$log->LogInfo("process_replication_location_reply " . $from);
			
			$protocol = $body['protocol'];
			$location = $body['location'];
			$log ->LogInfo("Received replication-location-reply " . $protocol . " " . $location);
			
			//
			// Put an entry into the inbound transfer table.
			// The inbound_transfer_daemon will query this table an pull the file.
			// After the pull is successful - the inbound_transfer_daemon will update
			// the state of the inbound_transfer record and send the replication_transfer_reply
			//
			
			new_inbound_transfer($correlation_id, $protocol, $location, $reply_key, $from);
			
			
			$log ->LogInfo("Execute fetch operation (php dpn_pull_file_manager.php)");
				
			//After we have picked up the package - we send an ack (this may be done by the transfer processor).
				
			//send_replication_transfer_reply('ack', $reply_key, $correlation_id);
									
			return;
		}
		

		// 
		// (Replicating Node) replication-verify-reply - this is the last step in the process of receiving a file 
		// from DPN.
		// Recipt of this message indicates that the first node validated that we received the dpn object
		// If we never receive ACK - then delete the file at some point.
		//
		
		if ($message_name == 'replication-verify-reply') {
			
			$log->LogInfo("process_replication_verify_reply " . $from);
			
			$message_att = $body['message_att'];

			$log ->LogInfo("Received replication-verify-reply " . $message_att);
			
			if ($message_att == 'ack') {		
				set_inbound_file_status($correlation_id, COMPLETE_STATUS);	
				
				// File xfer was successful - move file to iRODS and delete from staging area
				
			}
			
			if ($message_att == 'retry') {
			    set_inbound_file_status($correlation_id, RETRY_STATUS);
			    // We may want to reset the status to QUEUED_STATUS 
			    // so the file just gets picked up again
			}
			
			if ($message_att == 'nak') {
			    set_inbound_file_status($correlation_id, ABORTED_STATUS);
			}
									
			return;
		}
		
		
		// (First Node) registry_daterange_sync_list_reply - receive a registry list and process it.
		
		if ($message_name == 'registry-list-daterange-reply') {
			$log->LogInfo("registry-list-daterange-reply " . $from);
			
			// Get the array of registry entries
			
			$regarray = $body["reg_sync_list"];
			
			foreach ($regarray as $remote_reg) {
				$log->LogInfo("Registry Sync List Element\n" . json_encode($remote_reg));	
				$log->LogInfo("DPN Object ID " . $remote_reg['dpn_object_id']);
								
				process_registry_sync($remote_reg);
    			}
					
		}
		
		// (Replicating Node) - 
		if ($message_name == 'registry-daterange-sync-request') {
			$log->LogInfo("registry-daterange-sync-request");
			
			$date_array = $body['date_range'];
			
			$start = $date_array[0];
			$end = $date_array[1];
			
			send_registry_daterange_sync_list_reply($start, $end, $correlation_id, $reply_key);
			
		}
		
		//
		// Recovery Messages
		//
		
		
		//
		// (Replicating-node) recovery-init-query - Sent by a first node that needs to recover content.
		//
		
		if ($message_name == 'recovery-init-query' ) {
			
			$log->LogInfo( "recovery_init_query from: " . $from . " Object id: " . $body['dpn_object_id'] );
			
			$object_id = $body['dpn_object_id'];

			send_recovery_available_reply("rsync", $reply_key, $correlation_id);				
	
			return;
		}	
		
		
		//
		// (First-node) recovery-available-reply - Received from a replicating node indicating that they have the bag that we wanted
		//
		
		if ($message_name == 'recovery-available-reply' ) {
			
			$log->LogInfo( "recovery-available-reply: " . $from);
			
			$available_at = $body['available_at'];
			$message_att = $body['message_att'];
			$protocol = $body['protocol'];
			$cost = $body['cost'];

			// Need to check that we have not already told someone else we would pull from them
			
			send_recovery_transfer_request("rsync", $reply_key, $correlation_id, $from);				
	
			return;
		}
		
		//
		// (Replicating-node) recovery-transfer-request - Received from a first-node indicating that we should stage the content. We have been chosen.
		//
		
		if ($message_name == 'recovery-transfer-request' ) {
			
			$log->LogInfo( "recovery-transfer-request: " . $from );
			
			$message_att = $body['message_att'];
			$protocol = $body['protocol'];

			// At this point we go and stage the content and then send 
			send_recovery_transfer_reply("rsync", $reply_key, $correlation_id, "/path-to/stage/file");				
	
			return;
		}
		

		//
		// (First-node) recovery-transfer-reply - Received from replicating node - indicating that the bag has been staged and is ready to pull
		//
		
		if ($message_name == 'recovery-transfer-reply' ) {
			
			$log->LogInfo( "recovery-transfer-reply: " . $from );
			
			$protocol = $body['protocol'];
			$location = $body['location'];
			
			// At this point we pull the content and then send
			send_recovery_transfer_status("rsync", $reply_key, 'ack', 'ou812-fixity', $correlation_id);				
	
			return;
		}		

		//
		// (Replicating-node) recovery-transfer-status - Received from first node after they have pulled the content
		//
		
		if ($message_name == 'recovery-transfer-status' ) {
			
			$log->LogInfo( "recovery-transfer-status: " . $from );
			
			$message_att = $body['message_att'];			
			$fixity_algorithm = $body['fixity_algorithm'];	
			$fixity_value = $body['fixity_value'];			

			// Do something with the fact that we have acknowledged the transfer of the file. Clean up staging areas.
	
			return;
		}		

	}
	

