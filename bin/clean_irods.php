<?php

$handle = fopen("xx", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $line = trim($line);

        $nm = substr($line,0,2);


        echo  "irm tdr/$nm/$line\n";
    }
} else {
    // error opening the file.
}
fclose($handle);
