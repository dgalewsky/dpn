<?php

require_once 'KLogger.php';
require_once 'common.php';

$db = db_connect();
$ret = $db->query("SELECT nodename, correlation_id, ack_time from dpn_registry_item_create_message_detail order by correlation_id");

while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {
    $nodename = $res['nodename'];
    $correlation_id = $res['correlation_id'];
    $ack_time = $res['ack_time'];    

    if ($ack_time == "")
    	    $ack_time = "<No Ack>";

    echo "\n-Registry Message-\nNode: $nodename\nCorrelation ID: $correlation_id\nACK Time: $ack_time\n";
}
function db_connect() {
	$db = new SQLite3($_SERVER["DPN_HOME"] . "/db/dpn.db");	
	return $db;
}

