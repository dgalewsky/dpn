<?php

require_once 'KLogger.php';
require_once 'common.php';
require_once 'dpn_registry_utils.php';

$db = db_connect();

$ret = $db->query("Select dpn_object_id, local_id, first_node_name, creation_date from dpn_registry order by id");


while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {
    $dpn_object_id = $res['dpn_object_id'];
    $local_id = $res['local_id'];
    $first_node_name = $res['first_node_name'];
    $creation_date = $res['creation_date'];

    
    $nodes = get_node_array($dpn_object_id, $db);
    
    $nlist = "";
    
    foreach($nodes as $n) {
        $nlist = $nlist . " " . $n;    	    
    }
    
    echo "\n-Registry Entry-\nObject-ID: $dpn_object_id\nLocal Id: $local_id\nFirst-Node: $first_node_name\nCreation-Date: $creation_date\n";
    echo "Replicants: " . $nlist . "\n";

}


function db_connect() {
	$db = new SQLite3($_SERVER["DPN_HOME"] . "/db/dpn.db");	
	return $db;
}

