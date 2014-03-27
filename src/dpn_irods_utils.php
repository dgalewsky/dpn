<?php

require_once 'KLogger.php';
require_once 'common.php';


$log = new KLogger($_SERVER["DPN_HOME"] . "/log/dpn_log.txt", KLogger::INFO);

function new_dpn_irods_transfer($dpn_object_id) {
	$db = db_connect();
	
	$db->exec("insert into dpn_irods_transfer(dpn_object_id) values ('$dpn_object_id')");
		
}

//
// Get next file for iRODS.
//

function get_next_irods_transfer() {
	
	echo "Get_next_irods\n";
	
        $db = db_connect();
	$res = $db->querySingle("SELECT dpn_object_id FROM dpn_irods_transfer where transfer_timestamp is null order by creation_timestamp ");
		

        $db->close();
        unset($db);

	return $res;		
}

function set_irods_transfer_timestamp($dpn_object_id) {
        $db = db_connect();
        
        $timestr = date('c', time());
	
	$db->exec("update dpn_irods_transfer set transfer_timestamp = '$timestr' where dpn_object_id = '$dpn_object_id'"); 
	
	$db->close();
	unset($db);
}







// include the streamer class, which enable PHP core to recongnize "irods"
// as a valid stream, just like file stream, socket stream, or HTTP stream.

/*
require_once("/home/dg2226/irodsphp/prods/src/Prods.inc.php");

function dpn_puts($filename, $str) {
	
	$account = new RODSAccount("icat.corral.tacc.utexas.edu", 1247, "tdl-utexas", "xsw2#EDC");	
	$fp = new ProdsFile($account, "/corralZ/home/tdl-utexas/" . $filename);	
	$fp->open("w+", "gpfs-tacc");
	$bytes = $fp->write($str);
	$fp->close();
	
}

dpn_puts("dg_test", "2015 Production system at TB scale");

*/

