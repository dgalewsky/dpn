#!/bin/bash
for i in {2..1000}
do
    echo "output: $i"
    bin/send.sh
    sleep 1s
done

