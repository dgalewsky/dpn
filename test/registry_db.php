<?php
require_once  $_SERVER['DPN_HOME'] . '/src/dpn_registry_utils.php';
require_once  $_SERVER['DPN_HOME'] . "/src/dpn_db_utils.php";

$body = array(
		'message_name'               => 'registry-item-create',
		'dpn_object_id'              => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
		'local_id'                   => 'TDR-282dcbdd-c16b-42f1-8c21-0dd7875fb94e',
		'first_node_name'            => 'tdr',
		'replicating_node_names'     => array('hathi', 'chron', 'sdr'),
		'version_number'             => 1,
		'previous_version_object_id' => 'null',
		'forward_version_object_id'  => 'null',
		'first_version_object_id'    => 'f47ac10b-58cc-4372-a567-0e02b2c3d479', 
		'fixity_algorithm'           => 'sha256',
		'fixity_value'               => '2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824',
		'last_fixity_date'            => '2013-01-18T09:49:28-0800',
		'creation_date'              => '2013-01-05T09:49:28-0800',
		'last_modified_date'         => '2013-01-05T09:49:28-0800',  
		'bag_size'                   => 65536,
		'brightening_object_id'      => array('bright-ou812'),
		'rights_object_id'           => array('rights-ou812'),
		'object_type'                => 'data'  		
		);

print_r ($body);

save_registry_entry($body) ;

$body = get_registry_info('f47ac10b-58cc-4372-a567-0e02b2c3d479');

print_r($body);


