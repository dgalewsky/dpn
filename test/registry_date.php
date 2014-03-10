<?php


	require_once $_SERVER["DPN_HOME"] . '/src/dpn_db_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_registry_utils.php';
	
	$db = db_connect();
	
	$ret = $db->query("SELECT creation_date FROM dpn_registry where creation_date >= '2013-09-19T15:35:37+00:00'");
	
	
	while ($res = $ret->fetchArray(SQLITE3_ASSOC)) {
		$cd = $res['creation_date'];
		
		echo "Creation Date:";
		echo $cd . "\n";
	}
