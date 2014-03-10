<?php

for ($x=0x00; $x<=0xFF; $x++){
	
    $val = dechex($x);
    if (strlen($val) == 1) $val = "0" . $val;
    echo "mkdir " . $val . "\n";
}
