<?php



	$outgoing_path = '/dpn/outgoing/';
	
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/common.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_file_transfer_db_utils.php';
	require_once $_SERVER["DPN_HOME"] . '/src/dpn_registry_utils.php';
	
// Open two files each containing JSON for a registry entry - and compare

	$mystr = file_get_contents("test/registry_entry.json");
	$mystr2 = file_get_contents("test/registry_entry2.json");

	
	echo "Read: " . $mystr . "\n";
	
	$myjson = json_decode($mystr, true);
	$myjson2 = json_decode($mystr2, true);

	
	echo "\n***\n";
	
	print_r($myjson);
	
	echo "Here we go";
	process_registry_sync($remote_reg);
	process_registry_sync($mystr);
	

	foreach($myjson as $key => $val) {
		
	  if (isset($myjson[$key])) {

	    if (is_array($myjson[$key])) {
	    	echo "$key is an array" . PHP_EOL;
	    	print_r($myjson[$key]);
	    	
	    	if (!arrays_match($myjson[$key], $myjson2[$key])) 
	    		return false;
	    	continue;
	    }
	    
	    echo "key " . $key . " " . $myjson[$key],' is ',$myjson2[$key];
	    echo PHP_EOL;
	    
	    if ($myjson[$key] != $myjson2[$key]) {
	    	echo "$key not the same" . PHP_EOL;	
	    	return false;
	    }
	  }
	}
	
	return true;	
	
	
	// Return wether or not two arrays have same values
	function arrays_match($a1, $a2) {
		
	    foreach ($a1 as $key => $val) {
	    	echo "Array Comparison key $key $a1[$key] $a2[$key] \n";
	    	if ($a1[$key] != $a2[$key]) {
	    	    echo "Match failed $key\n";
	    	    return false;
	    	}
	    }
	    
	    return true;
		
	}
	
	
	
	function process_registry_sync($remote_reg) {
		 
		$remote_obj_id = $remote_reg['dpn_object_id'];
		
		$local_reg = get_registry_info($remote_object_id);
		
		// If not found in our local registry - add it
		
		if (!$local_reg) {
			$log->LogInfo("Object not found $remote_obj_id - adding");
			save_registry_entry(json_encode($remote_reg));	
			return;
		}
		
		if (!registry_entries_equal($local_reg, $$remote_reg)) {
			$log->LogInfo("Regstry entries dont match $remote_obj_id" );
		}
		
	}
