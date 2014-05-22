<?php

require_once 'KLogger.php';
require_once 'common.php';


$log = new KLogger($_SERVER["DPN_HOME"] . "/log/dpn_log.txt", KLogger::INFO);

//
// Create a new dpn_file record
//

function new_dpn_file($filename, $checksum, $filesize, $correlation_id) {

	$db = db_connect();
	
	$dpn_object_id = uuid();

	
	$db->exec("INSERT INTO dpn_file (file_path, checksum, file_size, correlation_id, dpn_object_id) VALUES ('$filename', '$checksum', '$filesize', '$correlation_id', '$dpn_object_id')");
	
	$db->close();
	unset($db);
	return $dpn_object_id;
}

//
// Return a file path from a dpn_file record - given a correlation id
//

function get_file_path_from_correlation_id($correlation_id) {
        $db = db_connect();
	$path = $db->querySingle("SELECT file_path FROM dpn_file where correlation_id = '$correlation_id'");

        $db->close();
        unset($db);

	return $path;		
}

// Return the saved checksum for a dpn_file

function get_dpn_file_checksum($correlation_id) {
	$db = db_connect();	
		
	$checksum = $db->querySingle("SELECT checksum FROM dpn_file where correlation_id = '$correlation_id'");

        $db->close();
        unset($db);

	return $checksum;
}


// 
// Create a new record of an outbound transfer
//

function new_outbound_transfer($correlation_id, $destination) {

        $db = db_connect();
        $status = INITIATED_STATUS;
	
	$db->exec("INSERT INTO dpn_outbound_transfer (correlation_id, destination, status) VALUES ('$correlation_id', '$destination', '$status')");
	

        $db->close();
        unset($db);

}

// Update the outbound transfer record to show success

function set_outbound_transfer_success($correlation_id, $destination) {
	
        $db = db_connect();
        
        $status = COMPLETE_STATUS;
	
        // Get ZULU time for timestamp
        $timestr = date('c', time());
	
	$db->exec("update dpn_outbound_transfer set transfer_succesful_timestamp = '$timestr', status = '$status' where correlation_id = '$correlation_id' and destination = '$destination'");
	

        $db->close();
        unset($db);

}

// Update the outbound transfer record to show hashes didn't match - retry

function set_outbound_transfer_retry($correlation_id, $destination) {
	
        $db = db_connect();
        
        $status = RETRY_STATUS;
	
	$db->exec("update dpn_outbound_transfer set status = '$status' where correlation_id = '$correlation_id' and destination = '$destination'");
	

        $db->close();
        unset($db);

}


// 
// Create a new record of an inbound transfer
// correlation_id - the id of the message sequence initiating the transfer
// protocol - one of rsync or https
// location - the file-path to the asset
// reply_key - the AMQP route back to the node
// source - the id of the node
//

function new_inbound_transfer($correlation_id, $protocol, $location, $reply_key, $source) {

        $db = db_connect();

	$status = QUEUED_STATUS;
	
	$db->exec("INSERT INTO dpn_inbound_transfer (correlation_id, protocol, location, status, reply_key, source) VALUES ('$correlation_id', '$protocol', '$location', '$status', '$reply_key', '$source')");
	

        $db->close();
        unset($db);

}

//
// Get next inbound file. This needs to be tightened up - a bit.
//

function get_next_inbound_file() {
	
        $db = db_connect();
	$ret = $db->query("SELECT correlation_id, location, status FROM dpn_inbound_transfer where status = 'queued' order by creation_timestamp");
		
	$res = $ret->fetchArray(SQLITE3_ASSOC);
	

        $db->close();
        unset($db);

	return $res;		
}

//
// Set the status field for an inbound_file record
//

function set_inbound_file_status($correlation_id, $status) {
	
        $db = db_connect();

	
	$db->exec("update dpn_inbound_transfer set status = '$status' where correlation_id = '$correlation_id'");
	

        $db->close();
        unset($db);

}

// Map correlation_id to reply_key

function get_inbound_reply_key_from_correlation_id($correlation_id) {
        $db = db_connect();
	$path = $db->querySingle("SELECT reply_key FROM dpn_inbound_transfer where correlation_id = '$correlation_id'");

        $db->close();
        unset($db);

	return $path;		
}

//
// Set the checksum field for an inbound_file record
//

function set_inbound_file_checksum($correlation_id, $checksum) {
	
        $db = db_connect();
	
	$db->exec("update dpn_inbound_transfer set checksum = '$checksum' where correlation_id = '$correlation_id'");
	

        $db->close();
        unset($db);

}

// Map correlation_id to object_id. 

function get_object_id_from_correlation($correlation_id) {
        $db = db_connect();
	$obj = $db->querySingle("SELECT dpn_object_id FROM dpn_file where correlation_id = '$correlation_id'");


        $db->close();
        unset($db);

	return $obj;		
}

function get_file_path_from_objectid($object_id) {
	$db = db_connect();
	$path = $db->querySingle("select file_path from dpn_file where dpn_object_id = '$object_id'");


        $db->close();
        unset($db);

	
	return $path;
	
}

//
// On a re-send operation - look up and re-use the correlation_id associated with the object
//
function get_correlation_id_from_objectid($object_id) {
	$db = db_connect();
	$path = $db->querySingle("select correlation_id from dpn_file where dpn_object_id = '$object_id'");
	

        $db->close();
        unset($db);

	return $path;
	
}

// 
// Create a new record of a recovery request
//

function new_recovery_request($correlation_id, $object_id) {

        $db = db_connect();
        $status = INITIATED_STATUS;
	
	$db->exec("INSERT INTO dpn_recovery_request (correlation_id, object_id, status) VALUES ('$correlation_id', '$object_id', '$status')");	

        $db->close();
        unset($db);
}


// 
// Create a new record of a recovery file - a file to be staged from our repo - for recovery. 
// Called by a repilcating node - when it has been asked to provide a recovery file.
//

function new_recovery_file($correlation_id, $object_id) {

        $db = db_connect();
        $status = INITIATED_STATUS;
	
	$db->exec("INSERT INTO dpn_recovery_file (correlation_id, object_id, status) VALUES ('$correlation_id', '$object_id', '$status')");	

        $db->close();
        unset($db);
}

//
// Set recovery_source for a Recovery Request
//

function set_recovery_request_recovery_source($correlation_id, $recovery_source) {
	
        $db = db_connect();
	
	$db->exec("update dpn_recovery_request set recovery_source = '$recovery_source' where correlation_id = '$correlation_id'");
	

        $db->close();
        unset($db);

}


//
// Get recovery_source for a Recovery Request
//

function get_recovery_request_recovery_source($correlation_id) {
	
        $db = db_connect();

       	$src = $db->querySingle("SELECT recovery_source FROM dpn_recovery_request where correlation_id = '$correlation_id'");	

        $db->close();
        unset($db);

        return $src;
}


//
// Set recovery_source for a Recovery Request
//

function set_recovery_request_status($correlation_id, $status) {
	
        $db = db_connect();
        
        // Get ZULU time for timestamp
        
        $timestr = date('c', time());
	
	$db->exec("update dpn_recovery_request set status = '$status' where correlation_id = '$correlation_id'");
	
	// Should this actually get set here?
	
	$db->exec("update dpn_recovery_request set recovery_succesful_timestamp = '$timestr'");

        $db->close();
        unset($db);

}

//
// Set status for a Recovery File
//

function set_recovery_file_status($correlation_id, $status) {
	
        $db = db_connect();
        
        // Get ZULU time for timestamp
        
        $timestr = date('c', time());
	
	$db->exec("update dpn_recovery_file set status = '$status' where correlation_id = '$correlation_id'");

        $db->close();
        unset($db);

}


?>

