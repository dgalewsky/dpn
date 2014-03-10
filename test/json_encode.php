<?php

$protocol = 'rsync';
$location = "ubuntu@ec2-50-16-41-0.compute-1.amazonaws.com:/dpn/outgoing/dpn-bag1.tar";

	$body = array(
		'message_name'       => 'replication-location-reply',
		'protocol'           => $protocol,
		'location'	     => $location );
		
		
echo "serialized " . serialize($body) . "\n";

echo "json " . str_replace('\/', '/', json_encode($body)) . "\n";

