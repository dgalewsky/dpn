#!/usr/local/bin/php
<?php

require_once 'common.php';

$routing_pattern = '#';

$connection = new AMQPConnection();

try {
	// connection with default credentials
	$connection->connect();

	// channel with automatic transactions and QOS disabled
	$ch = new AMQPChannel($connection);
	$ch->setPrefetchCount(0);
	$ch->setPrefetchSize(0);

	// durable exchange of type 'topic'
	$ex = new AMQPExchange($ch);
	$ex->setName(EXCHANGE);
	$ex->setFlags(0);
	$ex->setFlags(AMQP_DURABLE);      // AMQP_DURABLE, AMQP_PASSIVE
	$ex->setType(AMQP_EX_TYPE_TOPIC); // DIRECT, FANOUT, HEADER or TOPIC
	$ex->setArguments(array());       // [rabbit] alternate-exchange, x-dead-letter-exchange
	$ex->declare();

	// durable queue with a well-known name
	$q = new AMQPQueue($ch);
	$q->setName(QUEUE);
	$q->setFlags(0);
	$q->setFlags(AMQP_DURABLE);       // AMQP_DURABLE, PASSIVE, EXCLUSIVE, AUTODELETE
	$q->setArguments(array());        // [rabbit] x-message-ttl, x-expires
	$q->declare();

	// messages sent with a particular routing key are 
	// delivered to queues with a matching routing pattern
	$q->bind(EXCHANGE, $routing_pattern);

	// send topic exchange a message with routing key, R   
	// - consumer binds to topic exchange with routing pattern, P
	// - consumer receives message if R matches P

	echo "Preparing to consume";

//	$q->consume('process_message', AMQP_AUTOACK);  // blocks
	
	$msg = $q->get(AMQP_AUTOACK);
	
	if ($msg != false)
	    echo "\n" . $msg->getBody() . "\n";
	else
	    echo "\nNothing read\n";
	    
	$connection->disconnect();

} catch ( Exception $e ) {
	printf("%s\n", $e->getMessage() );
}

function process_message($envelope, $queue) {
	$from           = $envelope->getHeader('from');
	$correlation_id = $envelope->getHeader('correlation_id');
	$sequence       = $envelope->getHeader('sequence');
	$reply_key      = $envelope->getHeader('reply_key');
	$body           = json_decode($envelope->getBody(), TRUE);
	$message_name   = $body['message_name'];

	printf("\n**** %s %s %-21s from '%s'.\n\n", $correlation_id, $sequence, $message_name, $from);

	if ( DEBUG ) print_envelope($envelope);

	if ( $message_name == 'replication-init-query' ) {
		if ( $from == 'utexas' ) {
			if ( DEBUG ) print_envelope($envelope);
			printf("Ignoring self broadcast.\n");
		} else {
			printf("ERROR\n");
		}
	} elseif ( $message_name == 'replication-available-reply' ) {
		
	} else {
		printf("Unknown message (%s) from: %s\n", $message_name, $from);
	}
}

function send_direct($correlation_id, $routing_key, $message_att) {
	global $connection; // ugly

	if ( DEBUG ) print_envelope($envelope);

	$timestamp      = time();      // seconds (64-bit integer)
	$expiration     = 60 * 60 * 1; // seconds, 1 hour

	$properties = array(
		'headers'            => array(
		    'src_node'       => NODE,
		    'exchange'       => EXCHANGE,
		    'routing_key'    => $routing_key,
		    'correlation_id' => $correlation_id,
		    'sequence'       => 1,
		    'date'           => date('c', $timestamp),
		    'ttl'            => $expiration,
		),
	);

	$body = array(
		'src_node'      => NODE,
		'message_type'  => array('direct' => 'reply'),
		'message'       => 'is_available_replication',
		'message_att'   => $message_att,
		'date'          => date(DATE_RFC2822, $timestamp),
	);

	$ch = new AMQPChannel($connection);

	$ex = new AMQPExchange($ch);
	$ex->setName(EXCHANGE);
	$ex->setFlags(0);
	$ex->setFlags(AMQP_DURABLE);
	$ex->setType(AMQP_EX_TYPE_TOPIC);
	$ex->setArguments(array());
	$ex->declare();
//	$ex->publish(json_encode($body), $routing_key, AMQP_NOPARAM, $properties);

	$src_node     = $body['src_node'];
	$message      = $body['message'];
	$message_type = json_encode($body['message_type']);
	$sequence     = $properties['headers']['sequence'];

	printf("%s %s P %-21s where key=%s: %s\n", 
		$correlation_id, $sequence, $message_type, $routing_key, $message_att);
}

