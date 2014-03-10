<?php

require_once 'KLogger.php';
require_once 'common.php';

$db = db_connect();
$ret = $db->query("SELECT destination, status, file_path, file_size, dpn_object_id, dpn_outbound_transfer.creation_timestamp, transfer_succesful_timestamp  FROM dpn_file, dpn_outbound_transfer where dpn_outbound_transfer.correlation_id = dpn_file.correlation_id order by dpn_file.creation_timestamp");



while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {

    $destination = $res['destination'];
    $status = $res['status'];
    $file_path = $res['file_path'];
    $dpn_object_id = $res['dpn_object_id'];
    $creation_timestamp = $res['creation_timestamp'];
    $transfer_succesful_timestamp = $res['transfer_succesful_timestamp'];

    echo "\n====\n-Outgoing file-\nDestination: $destination\nStatus: $status\nFile Path: $file_path\nObject Id: $dpn_object_id\nTime Initiated: $creation_timestamp\nTime Completed: $transfer_succesful_timestamp\n";
}

function db_connect() {
	$db = new SQLite3($_SERVER["DPN_HOME"] . "/db/dpn.db");	
	return $db;
}

