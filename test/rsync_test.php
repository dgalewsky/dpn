<?php
// rsync -azv /dpn/outgoing/dpn-bag1.tar /dpn/incoming/
	$handle = popen('rsync -azv /dpn/outgoing/dpn-bag1.tar /dpn/incoming/', 'r');

	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
	    echo "Read: $read";
	} 
	
	pclose($handle);


