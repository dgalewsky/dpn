#!/bin/bash

while :
do
    php $DPN_HOME/src/dpn_message_processor.php
    echo "Restarting DPN listener:"
    sleep 1
done


