<?php
	require_once 'KLogger.php';
	require_once 'dpn_file_transfer_db_utils.php';
	require_once 'common.php';
	require_once 'dpn_utils.php';
	
	
	// Set up the logger	
	$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	

	error_reporting(E_ALL);
	
	while(1) {
		//
		// Go see if there is a new file waiting to be 'rsync'ed. 
		// Get the path to rsync from, and the correlation id of the 
		// message chain.
		//
				
		$ret = get_next_inbound_file();
		
		
		$location = $ret['location'];
		$correlation_id = $ret['correlation_id'];
		
		if ($location == '') {
		    sleep(1);
		    continue;
		}
		
		$log->LogInfo("Location: $location");
		
		//
		// Ok - we have a file to pull - set it's status to TRANSFERRING
		//
		
		set_inbound_file_status($correlation_id, TRANSFERRING_STATUS);
		
		$incoming_directory = INCOMING_DIRECTORY;
		
		//
		// Do the actual rsync of the asset.
		//	
		
		echo "spawning process...\n";
		
		// $handle = popen("php /mnt/dpn_test_env/dpn_remote/src/dpn_pull_file_process.php $location $correlation_id &", 'r');
		$ret = shell_exec("php $_SERVER['DPN_HOME']/src/dpn_pull_file_process.php $location $correlation_id > /dev/null 2>&1 &");
	
		//while(!feof($handle)) { 
		 //   $read = fread($handle, 1024); 
		  //  $log->LogInfo($read);
		   // echo "Read: $read";
		//} 
		
		//pclose($handle);
		echo "\nReturned from exec $ret \n";
		
		echo "End of exec process";

	}
