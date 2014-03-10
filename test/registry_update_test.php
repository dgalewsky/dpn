<?php

	require_once $_SERVER["DPN_HOME"] . '/src/dpn_db_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_registry_utils.php';
	
	$obj = '52a87b8a-15cd-4469-8151-3768ffd493af';
	
	$id = get_registry_id($obj);
	
	if ($id == null) {
		echo "$obj Not found\n";
		//exit;
	} else
		echo "Found Id: " . $id . "\n";
		
	$body = get_registry_info($obj);
	
	print_r($body);
	
	echo "deleting $id";
	
	delete_registry_entry($id);

