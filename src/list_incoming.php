<?php

require_once 'KLogger.php';
require_once 'common.php';

$db = db_connect();
$ret = $db->query("SELECT correlation_id, location, status, source, creation_timestamp FROM dpn_inbound_transfer order by creation_timestamp");

while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {
    $source = $res['source'];
    $location = $res['location'];
    $status = $res['status'];
    $creation_timestamp = $res['creation_timestamp'];

    echo "\n-Incoming File-\nFrom: $source\nLocation: $location\nStatus: $status\nCreation Time: $creation_timestamp\n";
}
function db_connect() {
	$db = new SQLite3($_SERVER["DPN_HOME"] . "/db/dpn.db");	
	return $db;
}

