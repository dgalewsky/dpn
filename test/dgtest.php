<?php

require_once '../src/common.php';

$filename    = $argv[1];

$timestamp      = time();      // seconds (64-bit integer)
$expiration     = 60 * 60 * 1; // seconds, 1 hour
$correlation_id = uniqid();

$properties = array(
	    'headers'        => array(
	    'from'           => NODE,
	    'exchange'       => EXCHANGE,
	    'reply_key'      => 'dpn.utexas.inbound',
	    'correlation_id' => $correlation_id,
	    'sequence'       => 0,
	    'date'           => date('c', $timestamp),
	    'ttl'            => $expiration,
	),
);

$body = array(
	'message_name'       => 'replication-init-query',
	'replication_size'   => filesize($filename),
	'protocol'           => array('rsync'),
);

$c = new AMQPConnection();
$c->connect();

// channel with automatic transactions and QOS disabled
$ch = new AMQPChannel($c);
$ch->setPrefetchCount(0);
$ch->setPrefetchSize(0);

$ex = new AMQPExchange($ch);
$ex->setName(EXCHANGE);
$ex->setFlags(0);
$ex->setFlags(AMQP_DURABLE);      // AMQP_DURABLE, AMQP_PASSIVE
$ex->setType(AMQP_EX_TYPE_TOPIC); // DIRECT, FANOUT, HEADER or TOPIC
$ex->setArguments(array());       // [rabbit] alternate-exchange, x-dead-letter-exchange
$ex->declare();

$ex->publish(json_encode($body), 'broadcast', AMQP_NOPARAM, $properties);
$c->disconnect();



