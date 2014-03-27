<?php

require_once "../src/dpn_irods_utils.php";
require_once "../src/dpn_db_utils.php";

new_dpn_irods_transfer("OU812-Dpn-Object-ID");



$obj = get_next_irods_transfer();

echo "Next obj $obj\n";

//new_dpn_file("/home/ubuntu/foobar.txt");

