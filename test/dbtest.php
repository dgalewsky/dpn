<?php

require_once "../src/dpn_irods_utils.php";
require_once "../src/dpn_db_utils.php";

$obj_id = "OU812-Dpn-Object-ID";

new_dpn_irods_transfer($obj_id);

$obj = get_next_irods_transfer();

echo "Next obj $obj\n";
                                                   
set_irods_transfer_timestamp($obj_id);

//new_dpn_file("/home/ubuntu/foobar.txt");

