#
# Setup script for Rabbit on Solaris
#

rabbitmqctl stop_app
rabbitmqctl reset
echo 'reset done'

rabbitmq-plugins enable rabbitmq_federation
echo 'enable federation done'

rabbitmq-plugins enable rabbitmq_federation_management
echo 'fed management done'

rabbitmq-plugins enable rabbitmq_management
echo 'rabbitmq management done'

rabbitmq-plugins enable rabbitmq_management_visualiser
echo 'vis  done'

rabbitmq-plugins enable rabbitmq_tracing
echo 'tracing  done'

rabbitmqctl stop

/etc/init.d/rabbitmq start
#service rabbitmq-server restart


# Set up the federation upstreams

rabbitmqctl set_parameter federation-upstream apt-upstream '{"uri":"amqp://dpn-dev.aptrust.org"}'
rabbitmqctl set_parameter federation-upstream chron-upstream '{"uri":"amqp://adapt-mq.umiacs.umd.edu"}'
rabbitmqctl set_parameter federation-upstream ht-upstream '{"uri":"amqp://dpn.hathitrust.org"}'
rabbitmqctl set_parameter federation-upstream sdr-upstream '{"uri":"amqp://dpn-demo.stanford.edu"}'


echo "done set_parameter federation-upstream"

rabbitmqctl set_parameter federation-upstream-set dpn-upstreams '[ {"upstream":"sdr-upstream","exchange":"dpn-control-exchange"}, {"upstream":"chron-upstream","exchange":"dpn-control-exchange"}, {"upstream":"apt-upstream","exchange":"dpn-control-exchange"}, {"upstream":"ht-upstream","exchange":"dpn-control-exchange"}  ]'

rabbitmqctl set_cluster_name 'utdr-node'

rabbitmqctl set_policy federate-me "dpn-control-exchange" '{"federation-upstream-set":"dpn-upstreams"}'

# Restart server for good measure

#rabbitmqctl stop
#service rabbitmq-server restart


