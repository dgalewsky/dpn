<?php

$bag_program = '/mnt/java_bagit/bagit-4.8/bin/bag';
#$bag_program = '/usr/local/bagit/bin/bag';


function make_dpn_bag($indir, $outdir) {
	//
	// Make a dpn bag
	//
	
	global $bag_program;
	
	#= '/mnt/java_bagit/bagit-4.8/bin/bag';
	#$bag_program = '/usr/local/bagit/bin/bag';
		
	$guid = guidv4();

	//
	// NOTE - This program is responsible for assigning/minting the DPN Object ID.
	// The object ID is the name of the file. The object ID is also in the DPN Info file.
	
	echo "Guid: " . $guid . "\n";
	
	// Create the unserialized bag from the content
	
	$bag_args = "create --tagmanifestalgorithm  sha256 --payloadmanifestalgorithm sha256";
	
	$handle = popen($bag_program . " " . $bag_args . " " . $outdir . "/" . $guid . " " . $indir, 'r');
	
	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
 	    echo "$read";
	} 
	
	pclose($handle);
	
	// Add in the dpn-tags/dpn-info.txt
	
	$tagdir = $outdir . "/" . $guid . "/dpn-tags";
	
	mkdir($tagdir);
	
	echo "Created directory $tagdir\n";
	
	$dpn_info = <<<EOD
DPN-Object-ID: $guid 
Local-ID:  $indir
First-Node-Name:  TDR
First-Node-Address:
First-Node-Contact-Name:
First-Node-Contact-Email:
Version-Number: 1
Previous-Version-Object-ID: 
First-Version-Object-ID: $guid
Brightening-Object-ID: 
Rights-Object-ID: 
Profile-Object-ID: 
Object-Type: data 
EOD;

	file_put_contents($outdir . "/" . $guid . "/dpn-tags" . "/dpn-info.txt", $dpn_info);
	
	// Tar the bag
	
	echo "Creating tar file of bag\n";
	
	echo "Changing to dir: $outdir\n";
	
	chdir($outdir);
	
	$handle = popen("tar cvf " . $outdir . "/" .  $guid . ".tar" . " " . $guid, 'r');
	
	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
 	    echo "$read";
	} 
	
	pclose($handle);	
	
	echo "\nBag path: " . $outdir . "/" .  $guid . ".tar" . " " . $outdir . "/" . $guid . "\n";
	
	echo "*** Returning " . $guid;
	
	return $guid;
}


function guidv4()
{
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}	


function validate_dpn_bag($bag_path) {
	
	global $bag_program;
	
	$bag_args = "verifyvalid";
	
	$handle = popen($bag_program . " " . $bag_args . " " . $bag_path, 'r');
	
	while(!feof($handle)) { 
	    $read = fread($handle, 1024); 
 	    echo "$read";
 	    if ($read == "Result is true.") 
 	    	    echo "\nApparently the bag is valid\n";
 	    	    break;
	} 
	
	pclose($handle);

}

