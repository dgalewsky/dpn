<?php

require_once 'common.php';
require_once 'KLogger.php';

// Set up the logger
$log = new KLogger ( $_SERVER['DPN_HOME'] . "/log/dpn_log.txt" , KLogger::INFO );	

// Create a row in the registry - from info in the 'body' 

function save_registry_entry($body) {
	global $log;
	
	$db = db_connect(); 
	
	// Check to see if we already have an row for this registry entry.
	// Delete the row before we re-add it.
	
	$obj_id = $body['dpn_object_id'];
	$id = get_registry_id($obj_id);
	
	if ($id != null) {
		delete_registry_entry($id);
		$log->LogInfo("Deleted registry entry: $id . Updated entry.");
	}
	
	$dpn_object_id = $body['dpn_object_id'];              
	$local_id = $body['local_id'];                   
	$first_node_name = $body['first_node_name'];            
	$replicating_node_names = $body['replicating_node_names'];     
	$version_number = $body['version_number'];             

	if (isset($body['previous_version_object_id']))
	    $previous_version_object_id = $body['previous_version_object_id']; 
	else
	    $previous_version_object_id = "";

	if (isset($body['forward_version_object_id']))
	    $forward_version_object_id = $body['forward_version_object_id'];  
	else
	    $forward_version_object_id = "";

	$first_version_object_id = $body['first_version_object_id'];    
	$fixity_algorithm = $body['fixity_algorithm'];           
	$fixity_value = $body['fixity_value'];               
	$last_fixity_date = $body['last_fixity_date'];           
	$creation_date = $body['creation_date'];              
	$last_modified_date = $body['last_modified_date'];         
	$bag_size = $body['bag_size'];                   
	$brightening_object_id = $body['brightening_object_id'];      
	$rights_object_id = $body['rights_object_id'];           
	$object_type = $body['object_type'];	
		
	// See if this registry entry already exists.
	// Delete if so - before we add it back
	
	$id = get_registry_id($dpn_object_id);
	
	if ($id != null) {
	    delete_registry_entry($id);	
	}
	
	$sql_stmt = <<<END
insert or replace into dpn_registry (
		dpn_object_id,  
		local_id,                   
		first_node_name,            
		version_number,             
		previous_version_object_id, 
		forward_version_object_id,  
		first_version_object_id,    
		fixity_algorithm,           
		fixity_value,               
		last_fixity_date,           
		creation_date,              
		last_modified_date,         
		bag_size,                   
		object_type) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?);              
END;

		$stmt = $db->prepare($sql_stmt);
		
		$stmt->bindValue(1,$dpn_object_id,SQLITE3_TEXT);              
		$stmt->bindValue(2,$local_id,SQLITE3_TEXT);                   
		$stmt->bindValue(3,$first_node_name,SQLITE3_TEXT);            
		$stmt->bindValue(4,$version_number,SQLITE3_INTEGER);             
		$stmt->bindValue(5,$previous_version_object_id,SQLITE3_TEXT); 
		$stmt->bindValue(6,$forward_version_object_id,SQLITE3_TEXT);  
		$stmt->bindValue(7,$first_version_object_id,SQLITE3_TEXT);    
		$stmt->bindValue(8,$fixity_algorithm,SQLITE3_TEXT);           
		$stmt->bindValue(9,$fixity_value,SQLITE3_TEXT);               
		$stmt->bindValue(10,$last_fixity_date,SQLITE3_TEXT);           
		$stmt->bindValue(11,$creation_date,SQLITE3_TEXT);              
		$stmt->bindValue(12,$last_modified_date,SQLITE3_TEXT);         
		$stmt->bindValue(13,$bag_size,SQLITE3_TEXT);                   
		$stmt->bindValue(14,$object_type,SQLITE3_TEXT);
		
		$result = $stmt->execute();
		
		$id = $db->lastInsertRowid();	
		
		$stmt->close();
		
		if ($replicating_node_names != null) {
		
		    foreach($replicating_node_names as $node) {
			$stmt = $db->prepare("insert into dpn_registry_node_name (dpn_registry_id, name) values (?, ?)");
			$stmt->bindValue(1, $id ,SQLITE3_INTEGER);
			$stmt->bindValue(2,$node); 
			$result = $stmt->execute();		    
		    }
		
		}
		
		$stmt->close();
		
		if ($brightening_object_id != null) {
		
		    foreach($brightening_object_id as $obj) {
			$stmt = $db->prepare("insert into dpn_registry_brightening_object_id (dpn_registry_id, object_id) values (?, ?)");
			$stmt->bindValue(1, $id ,SQLITE3_INTEGER);
			$stmt->bindValue(2,$obj); 
			$result = $stmt->execute();		    
		    }		
		}

		$stmt->close();
		
		if ($rights_object_id != null) {
		
		    foreach($rights_object_id as $obj) {
			$stmt = $db->prepare("insert into dpn_registry_rights_object_id (dpn_registry_id, object_id) values (?, ?)");
			$stmt->bindValue(1, $id ,SQLITE3_INTEGER);
			$stmt->bindValue(2,$obj); 
			$result = $stmt->execute();		    
		    }		
		}
				
		$db->close();
	
}

// 
// Save away the JSON for this registry entry. Save in the dpn local repository.
//

function save_registry_json($body) {
	$dpn_object_id = $body['dpn_object_id'];
	$json = json_encode($body);
		
}

// Return a registry_id - given an object_id

function get_registry_id($obj) {
	$db = db_connect();
	$id = $db->querySingle("SELECT id FROM dpn_registry where dpn_object_id = '$obj'");
	return $id;
}

// Add a new node to an existing registry entry

function add_node_to_registry_record($obj, $from) {
        $db = db_connect();	
        
        $id = get_registry_id($obj);
	
	$db->exec("INSERT INTO dpn_registry_node_name (dpn_registry_id, name) VALUES ($id , '$from')");
	
	$db->close();		
}

// Pull the node info from the db and put into an array - ready to be added to a body array

function get_node_array($obj, $db) {
    
    $id = get_registry_id($obj);
    
    $results = $db->query("select name from dpn_registry_node_name where dpn_registry_id = '$id'");
    
    $ret = array();
    
    $i=0;
    while ($row = $results->fetchArray()) {
        $ret[$i] = $row['name'];
        $i = $i+1;
    }
        
    return $ret;
}


// Pull the brightening info - into an array

function get_brightening_array($obj, $db) {
    
    $id = get_registry_id($obj);
    
    $results = $db->query("select object_id from dpn_registry_brightening_object_id where dpn_registry_id = '$id'");
    
    $ret = array();
    
    $i=0;
    while ($row = $results->fetchArray()) {
        $ret[$i] = $row['object_id'];
        $i = $i+1;
    }
       
    return $ret;
}

// Pull the rights info into into an array

function get_rights_array($obj, $db) {
    
    $id = get_registry_id($obj);
    
    $results = $db->query("select object_id from dpn_registry_rights_object_id where dpn_registry_id = '$id'");
    
    $ret = array();
    
    $i=0;
    while ($row = $results->fetchArray()) {
        $ret[$i] = $row['object_id'];
        $i = $i+1;
    }
       
    return $ret;
}


// Return an array containing registry info for a given object. Look the info up in the db
//strftime('%Y-%m-%dT%H:%M:%SZ', last_fixity_date)

function get_registry_info($obj) {
	global $log;

        $db = db_connect();	

	$sql_stmt = <<<END
select
	dpn_object_id,  
	local_id,                   
	first_node_name,            
	version_number,             
	previous_version_object_id, 
	forward_version_object_id,  
	first_version_object_id,    
	fixity_algorithm,           
	fixity_value,               
	strftime('%Y-%m-%dT%H:%M:%SZ',last_fixity_date) as last_fixity_date,           
	strftime('%Y-%m-%dT%H:%M:%SZ',creation_date) as creation_date,              
	strftime('%Y-%m-%dT%H:%M:%SZ',last_modified_date) as last_modified_date,         
	bag_size,                   
	object_type	
from dpn_registry where dpn_object_id = '$obj'	
END;


	$ret = $db->query($sql_stmt);
	$res = $ret->fetchArray(SQLITE3_ASSOC);
	
	if (!$res) {
		$log->LogInfo("$obj - not found in local registry");
		return $res;
	}
	
	// Get arrays of replicating_node_names, brightening_object_id and rights_object_id
		
	$nodes = get_node_array($obj, $db);
	
	$bright = get_brightening_array($obj, $db);

	$rights = get_rights_array($obj, $db);
	
	
	$res['replicating_node_names'] = $nodes;
	
	$res['brightening_object_id'] = $bright;
	
	$res['rights_object_id'] = $rights;
	
	return $res;
}

// Utility function for returning an array of registry items between two dates.

function get_regsitry_entries_by_date_range($start, $end) {
	$db = db_connect();

	$ret = $db->query("Select dpn_object_id from dpn_registry order by id where creation_date >= $start and creation_date <= $end");

}


// Clean up a dpn registry entry
function delete_registry_entry($id) {
    $db = db_connect();	
    
    $db->exec("delete from dpn_registry where id = $id");
    $db->exec("delete from dpn_registry_node_name where dpn_registry_id = $id");
    $db->exec("delete from dpn_registry_brightening_object_id where dpn_registry_id = $id");
    $db->exec("delete from dpn_registry_rights_object_id where dpn_registry_id = $id");   	       
}

// Compare two registry entries (as PHP arrays)

function registry_entries_equal($r1, $r2) {	
	
	global $log;

	// Iterate over each part of the registry entry
	
	foreach($r1 as $key => $val) {
		
	  if (isset($r1[$key])) {

	    if (is_array($r1[$key])) {
	    	
	    	if (!arrays_match($r1[$key], $r2[$key])) {
	    	    return false;
	    	}    	
	    	continue;
	    }
	    	    
	    if ($r1[$key] != $r2[$key]) {
	    	$log ->LogInfo("Comparing reistry entries. $key not the same");	
	    	return false;
	    }
	  }
	}
	
	return true;	
		
    }

	// Return wether or not two arrays have same values
	function arrays_match($a1, $a2) {
		
	    foreach ($a1 as $key => $val) {
	    	if ($a1[$key] != $a2[$key]) {
	    	    return false;
	    	}
	    }
	    
	    return true;
		
	}	
	
	// Check a remote registry entry against our registry.
	// $remote_reg is an array
	// If it does not exist - then add it
	// If it is in our registry and it all matches then do nothing
	// If it is in our registry and it does not match - log this as an error
	
	function process_registry_sync($remote_reg) {
		global $log;	
		
		$remote_obj_id = $remote_reg['dpn_object_id'];
		
		// Get registry info in our local registry as an array.
		// Value is 'false' if not found
		
		$log->LogInfo("Checking registry info $remote_obj_id");
		$local_reg = get_registry_info($remote_obj_id);				
		
		// If not found in our local registry - add it
		
		if (!$local_reg) {
			$log->LogInfo("Object not found $remote_obj_id - adding");
			$rr = json_encode($remote_reg);
			$log->LogInfo("Encoded json $rr");
			
			save_registry_entry($remote_reg);	
			return;
		}
		
		if (!registry_entries_equal($local_reg, $remote_reg)) {
			$log->LogInfo("Regstry entries dont match $remote_obj_id" );
			return;
		}
		
		$log->LogInfo("Registry Entries Match");
	}
	

