<?php

$t = '2013-09-12T23:49:01+00:00';
//$t = '2013-09-12T21:49:01+00:00';

$date1 = new DateTime($t);

var_dump ($date1);

$curr = new DateTime("now", new DateTimeZone('UTC'));

var_dump ($curr);

$val= $date1->diff($curr);

echo "time: " . $val->format("%h %i %s");


echo "----------------\n";

$start = strtotime($t);

$now = time();

$diff = $start - $now;

echo "Diff: " . $diff . "\n";

echo "gmdate: " . gmdate("H:i:s", $diff);


