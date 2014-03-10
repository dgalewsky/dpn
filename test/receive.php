<?php
/** 
 * Filename: receive.php
 * Purpose: 
 * Receive messages from RabbitMQ server using AMQP extension
 * Exchange Name: exchange1
 * Exchange Type: fanout
 * Queue Name: queue1
 */
$connection = new AMQPConnection();
$connection->connect();
if (!$connection->isConnected()) {
    die('Not connected :('. PHP_EOL);
}
// Open channel
$channel    = new AMQPChannel($connection);
// Open Queue and bind to exchange
$queue      = new AMQPQueue($channel);
$queue->setName('queue1');
$queue->bind('exchange1', 'key1');
$queue->declare();
// Prevent message redelivery with AMQP_AUTOACK param
while ($envelope = $queue->get(AMQP_AUTOACK)) {
    echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New Message';
    echo PHP_EOL;
    echo $envelope->getBody(), PHP_EOL;
}

?>


