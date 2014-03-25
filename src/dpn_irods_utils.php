<?php

require_once 'KLogger.php';
require_once 'common.php';


$log = new KLogger($_SERVER["DPN_HOME"] . "/log/dpn_log.txt", KLogger::INFO);

function new_dpn_irods_transfer($dpn_object_id) {
	$db = db_connect();
	
	$db->exec("insert into dpn_irods_transfer(dpn_object_id) values ($dpn_object_id)");
		
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

function 
