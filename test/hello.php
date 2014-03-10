#!/usr/bin/php
<?php
echo ("hello\n");

// Create a connection
$cnn = new AMQPConnection();
$cnn->connect();

// Create a channel
$ch = new AMQPChannel($cnn);

// Declare a new exchange
$ex = new AMQPExchange($ch);
$ex->declare('exchange1', AMQP_EX_TYPE_FANOUT);

// Create a new queue
$q = new AMQPQueue($ch);
$q->declare('queue1');

// Bind it on the exchange to routing.key
$ex->bind('queue1', 'routing.key');

// Publish a message to the exchange with a routing key
$ex->publish('message', 'routing.key');

// Read from the queue
$msg = $q->consume();

?>
