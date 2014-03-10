#!/usr/bin/php

<?php	
	require_once 'dpn_file_transfer_db_utils.php';
	
	$path = get_file_path_from_correlation_id('51c47870ca473');	
	echo "Path $path\n";
	
#	new_outbound_transfer('51ba377071e59', 'sdr');
#	echo "New outbound xfer created\n";
	
#	set_outbound_transfer_success_timestamp('51ba377071e59', 'sdr');
#	echo "Updated timestamp";
