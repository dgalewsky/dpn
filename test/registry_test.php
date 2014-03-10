<?php
require_once $_SERVER["DPN_HOME"] . '/src/dpn_db_utils.php';

print "Here goes insert\n";

new_dpn_registry_item_create_message("ou812", "all work and no play makes jack a dull boy");

ack_dpn_registry_item_create_message('5195462d03086', 'aptrust');
