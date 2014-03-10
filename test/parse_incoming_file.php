<?php

//tdr@dpn-demo.stanford.edu:63c947b0-ce05-11e2-8b8b-0800200c9a66.tar
//ubuntu@ec2-50-16-41-0.compute-1.amazonaws.com:/dpn/outgoing/dpn-bag1.tar

$f = "ubuntu@ec2-50-16-41-0.compute-1.amazonaws.com:/dpn/outgoing/dpn-bag1.tar";

$parts = preg_split('/:/', $f, -1, PREG_SPLIT_NO_EMPTY);

print_r($parts);

echo "Your file: $parts[1]\n";

echo "Basename " . basename($parts[1]) . "\n";

	
