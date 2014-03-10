<?php
/** 
 * Filename: send.php
 * Purpose: 
 * Send messages to RabbitMQ server using AMQP extension
 * Exchange Name: exchange1
 * Exchange Type: fanout
 * Queue Name: queue1
 */
$connection = new AMQPConnection();
$connection->connect();
if (!$connection->isConnected()) {
    die('Not connected :(' . PHP_EOL);
}
// Open Channel
$channel    = new AMQPChannel($connection);
// Declare exchange
$exchange   = new AMQPExchange($channel);
$exchange->setName('exchange1');
$exchange->setType('fanout');
$exchange->declare();
// Create Queue
$queue      = new AMQPQueue($channel);
$queue->setName('queue1');
$queue->declare();

$message    = $exchange->publish('Custom Message (ts): '.time(), 'key1');
if (!$message) {
    echo 'Message not sent', PHP_EOL;
} else {
    echo 'Message sent!', PHP_EOL;
}
?>


