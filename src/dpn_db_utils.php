<?php

require_once 'KLogger.php';

$nodelist = array('aptrust', 'hathi', 'sdr', 'chron');
$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	

// (First-Node) Create a record - for each node - that we have sent them a registry_create message

function new_dpn_registry_item_create_message($correlation_id, $message) {
	global $nodelist;
	
	$db = db_connect();	

	$db->exec("INSERT INTO dpn_registry_item_create_message (correlation_id, message) VALUES ('$correlation_id', '$message')");
	
	foreach ($nodelist as $node) {
	    $time = null;	    
	    $db->exec("INSERT INTO dpn_registry_item_create_message_detail (correlation_id, nodename, ack_time) VALUES ('$correlation_id', '$node', '$time')");
	}
	
	$db->close();
}

function ack_dpn_registry_item_create_message($correlation_id, $node) {
	global $log;	
	global $nodelist;
	

	if (registry_item_message_exists($correlation_id)) {
		$log->LogInfo("Updating registry messages - messages exist for correlation_id $correlation_id");
		return;
	}
	
	if (!in_array($node, $nodelist)) {
		$log->LogInfo("Ack from $node not in list of known nodes. Nothing to update.");		
		return;		    
	}	
		
	$db = db_connect();	
	$db->exec("update dpn_registry_item_create_message_detail set ack_time = current_timestamp where nodename = '$node'");
	$db->close();	    
}

// See if we already have records for recording regstry messages.

function registry_item_message_exists($correlation_id) {
	$db = db_connect();
	
	$obj = $db->querySingle("SELECT correlation_id FROM dpn_registry_item_create_message where correlation_id = '$correlation_id'");
	
	return ($obj != "");
	
	$db->close();
		
}

function get_registry_json() {

	$db = db_connect();
	
	$ret = $db->query("Select dpn_object_id from dpn_registry order by id");
	
	$ret_array = array();
	
	while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {
	    $dpn_object_id = $res['dpn_object_id'];
	 
	    $reg = get_registry_info($dpn_object_id);
	    
	    $ret_array[] = $reg;
	}	
	
	return $ret_array;
	
}


// Common function to connect to the db

function db_connect() {
	$db = new SQLite3($_SERVER["DPN_HOME"] . "/db/dpn.db");	
	return $db;
}


