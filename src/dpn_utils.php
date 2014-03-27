<?php

require_once 'common.php';
require_once 'KLogger.php';
require_once "dpn_db_utils.php";

// Set up the logger
$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	

// (First-Node) Send a replication-init-query - indicating that we have content to send to DPN
// This is step one in replicating a file in DPN.
// It is assumed that the file is in /dpn/outgoing and there is a record of the file
// in the dpn_file table.

function send_replication_request($filename, $correlation_id, $object_id) {
	global $log;
	
	$log->LogInfo( "Sending replication-init-query to broadcast");
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 0,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	$body = array(
		'message_name'       => 'replication-init-query',
		'replication_size'   => filesize($filename),
		'protocol'           => array('rsync'),
		'dpn_object_id'	     => $object_id,
	);
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);
	
	$log->LogInfo("SENT: " . json_encode($body));
	
	$ex->publish(json_encode($body), 'broadcast', AMQP_NOPARAM, $properties);
}

// (First-Node) replication_location_reply - initiate a transfer.
// This message tells a remote node the location of a DPN bag.
// Upon receipt of this message - the remote node will initiate the pull of the content

function send_replication_location_reply($protocol, $location, $reply_key, $correlation_id) {
	global $log;	
	$log->LogInfo("Sending replication-location-reply to $reply_key");

	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 2,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	$body = array(
		'message_name'       => 'replication-location-reply',
		'protocol'           => $protocol,
		'location'	     => $location );
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);
	
	$log->LogInfo("SENT: " . str_replace('\/', '/', json_encode($body)));
	$log->LogInfo("Routing Key: " . $reply_key);


	// Fix up the json because it messes with file paths. We may need to do this elsewhere
	
	$ex->publish(str_replace('\/', '/', json_encode($body)), $reply_key, AMQP_NOPARAM, $properties);
}


// (First Node) Send replication_verification_reply 
// Ack indicated that checksum verified

function send_replication_verify_reply($message_att, $reply_key, $correlation_id) {
	global $log;	
	
	$log->LogInfo( "Sending replication-verify-reply to $reply_key" );
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 5,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	$body = array(
		'message_name'       => 'replication-verify-reply',
		'message_att'        => $message_att);
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);
	
	$log->LogInfo("SENT: " . json_encode($body));

	$ex->publish(json_encode($body), $reply_key, AMQP_NOPARAM, $properties);
}


// (Replicating Node) Send replication_available_reply - indicating that we are able to come get a package

function send_replication_available_reply($protocol, $reply_key, $correlation_id) {
	global $log;
	
	$log->LogInfo( "Sending replication-available-reply to $reply_key");
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 1,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	$body = array(
		'message_name'       => 'replication-available-reply',
		'message_att'        => 'ack',
		'protocol'	     => $protocol);
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);

	$log->LogInfo("SENT: " . json_encode($body));
	
	$ex->publish(json_encode($body), $reply_key, AMQP_NOPARAM, $properties);
}

// (Replicating Node) Send replication_transfer_reply 
// Indicate that we got the package successfully

function send_replication_transfer_reply($message_att, $reply_key, $correlation_id, $checksum) {
	global $log;
	
	$log->LogInfo( "Sending replication-transfer-reply to $reply_key");
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 4,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	$body = array(
		'message_name'       => 'replication-transfer-reply',
		'message_att'        => $message_att,
		'fixity_algorithm'   => "sha256",
		'fixity_value'       => $checksum);
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);

	$log->LogInfo("SENT: " . json_encode($body));	
	$ex->publish(json_encode($body), $reply_key, AMQP_NOPARAM, $properties);
}

// (Replicating-Node) Send registry_entry_created  
// Indicate that we created the registry entry successfully

function send_registry_entry_created($message_att, $reply_key, $correlation_id) {
	global $log;
	
	$log->LogInfo( "Sending registry_entry_created to $reply_key");
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 1,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	$body = array(
		'message_name'       => 'registry-entry-created',
		'message_att'        => $message_att);
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);

	$log->LogInfo("SENT: " . json_encode($body));	
	$ex->publish(json_encode($body), $reply_key, AMQP_NOPARAM, $properties);

}


// (First Node) Broadcast registry_item_create  
// Tell other nodes to create a registry entry


function send_registry_item_create($correlation_id, $body) {
	global $log;
	
	$log->LogInfo( "Broadcasting registry_item_create");
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 0,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);
	
	$body['message_name'] = 'registry-item-create';
	
	$encoded_body = str_replace('\/', '/', json_encode($body));

	$log->LogInfo("SENT: " . $encoded_body);	
	
	// Broadcast the info in $body - as a registry update
	
	$ex->publish($encoded_body, 'broadcast', AMQP_NOPARAM, $properties);
	
	// Create a db entry for keeping track of the registry update message
	
	new_dpn_registry_item_create_message($correlation_id, $encoded_body);

}

// (First Node) Broadcast registry_daterange_sync_request 
// Get other nodes to reveal their registries

function send_registry_daterange_sync_request($start, $end, $correlation_id) {
	global $log;
	
	$log->LogInfo( "Broadcasting registry_daterange_sync_request");
	
	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 0,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);
	
	$body['message_name'] = 'registry-daterange-sync-request';
	
	$daterange = array($start, $end);
	
	$body['date_range'] = $daterange;
	
	

	$log->LogInfo("SENT: " . json_encode($body));	
	
	$ex->publish(json_encode($body), 'broadcast', AMQP_NOPARAM, $properties);
}


// (Replicating Node) Send  registry-daterange-sync-list-reply
// Send lines from our registry

function send_registry_daterange_sync_list_reply($start, $end, $correlation_id, $reply_key) {
	global $log;
	

	$properties = array(
		    'headers'        => array(
		    'from'           => NODE,
		    'reply_key'      => 'dpn.utexas.inbound',
		    'correlation_id' => $correlation_id,
		    'sequence'       => 0,
		    'date'           => date('c', time()),
		    'ttl'            => get_ttl(),
		),
	);
	
	
	$ch = setup_channel();
	$ex = setup_exchange($ch);
	
	$body['message_name'] = 'registry_daterange_sync_list_reply';
	
	$daterange = array($start, $end);
	
	$body['date_range'] = $daterange;	
	
	$body['reg_sync_list'] = get_registry_json();

	$log->LogInfo("SENT: " . json_encode($body));	
		
	$ex->publish(str_replace('\/', '/', json_encode($body)), $reply_key, AMQP_NOPARAM, $properties);
}




//
// Get a message from the DPN queue - or return false - if there is no message available
//

function get_dpn_message() {
	global $log;
	
	$connection = new AMQPConnection();

	try {
		
		$ch = setup_channel();
		$ex = setup_exchange($ch);
		
		$q = setup_queue($ch);
		
	//	$q->consume('process_message', AMQP_AUTOACK);  // blocks
		
		$msg = $q->get(AMQP_AUTOACK);
		    
		//$connection->disconnect();
		return $msg;
		
	} catch ( Exception $e ) {
		$log->LogInfo($e->getMessage() );
	}
}

// 
// Get a standard value for time to live. Now plus 5 hours.
//

function get_ttl() {
	return date('c', time() + 60* 5);
}

// 
// Set up standard queue
//

function setup_queue($ch) {
	
	$routing_pattern = '#';
		
	// durable queue with a well-known name
	$q = new AMQPQueue($ch);
	$q->setName(QUEUE);
	$q->setFlags(0);
	$q->setFlags(AMQP_DURABLE);       // AMQP_DURABLE, PASSIVE, EXCLUSIVE, AUTODELETE
	$q->setArguments(array());        // [rabbit] x-message-ttl, x-expires
	$q->declare();
	$q->bind(EXCHANGE, $routing_pattern);
	
	return $q;
}

//
// Configure a standard channel
//

function setup_channel() {
	$c = new AMQPConnection();
	$c->connect();
	
	// channel with automatic transactions and QOS disabled
	$ch = new AMQPChannel($c);
	$ch->setPrefetchCount(0);
	$ch->setPrefetchSize(0);
	return $ch;	
}


//
// Configure a standard exchange
//

function setup_exchange($ch) {
	
	$ex = new AMQPExchange($ch);
	$ex->setName(EXCHANGE);
	$ex->setFlags(0);
	$ex->setFlags(AMQP_DURABLE);      // AMQP_DURABLE, AMQP_PASSIVE
	$ex->setType(AMQP_EX_TYPE_TOPIC); // DIRECT, FANOUT, HEADER or TOPIC
	$ex->setArguments(array());       // [rabbit] alternate-exchange, x-dead-letter-exchange
	$ex->declare();
	return $ex;	
}


