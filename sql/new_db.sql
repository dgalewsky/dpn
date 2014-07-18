PRAGMA foreign_keys=OFF;


BEGIN TRANSACTION;

CREATE TABLE dpn_file (
	id integer primary key autoincrement,
	creation_timestamp DATETIME DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),	
	file_path varchar(256),
	file_size int,
	checksum varchar(256),
	dpn_object_id varchar(256),
	correlation_id varchar(32));
	
CREATE TABLE dpn_outbound_transfer (
	id integer primary key autoincrement,
	correlation_id varchar(32),
	creation_timestamp DATETIME DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
	destination varchar(256),
	status varchar(256),
	transfer_succesful_timestamp DATETIME);
	
CREATE TABLE dpn_inbound_transfer (
	id integer primary key autoincrement,
	correlation_id varchar(32),
	creation_timestamp DATETIME DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
	status varchar(256),
	protocol varchar(256),
	location varchar(256),
	source varchar(256),
	reply_key varchar(256),
	file_name varchar(256),
	checksum varchar(256));

--
-- dpn_irods_transfer - a request to transfer a dpn bag to iRODS
--

CREATE TABLE dpn_irods_transfer (
	id integer primary key autoincrement,
	dpn_object_id varchar(32),
	creation_timestamp DATETIME DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
	transfer_timestamp DATETIME
	);
	
create table dpn_registry_item_create_message (
	id integer primary key autoincrement,
	create_time datetime default (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')), 
	correlation_id varchar(256),
	message varchar(1024)
);	

create table dpn_registry_item_create_message_detail (
	id integer primary key autoincrement,
	nodename varchar(8),
	correlation_id varchar(256),
	ack_time datetime DEFAULT 0
);

  create table dpn_registry (
	  id integer primary key autoincrement,
	  dpn_object_id varchar(256),              
	  local_id varchar(256),                   
	  first_node_name varchar(256),               
	  version_number integer,            
	  previous_version_object_id varchar(256),
	  forward_version_object_id  varchar(256),
	  first_version_object_id varchar(256),
	  fixity_algorithm varchar(256),          
	  fixity_value  varchar(256),             
	  last_fixity_date datetime,           
	  creation_date datetime,            
	  last_modified_date datetime,                                                                  
	  bag_size integer,  
	  object_type varchar(256)
 
  );          
  
  create table dpn_registry_node_name (
  	dpn_registry_id integer,
  	name varchar(256)
  );

  create table dpn_registry_brightening_object_id (
        dpn_registry_id integer,
  	object_id varchar(256)  
  );
  
  create table dpn_registry_rights_object_id (
        dpn_registry_id integer,
  	object_id varchar(256)  
  );
  
--
-- DPN recovery record. Used by the node requesting the recovery (i.e. the node 
-- that needs the bag).
--

CREATE TABLE dpn_recovery_request (
	id integer primary key autoincrement,
	correlation_id varchar(32),
	object_id varchar(256),
	creation_timestamp DATETIME DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
	recovery_source varchar(256),
	status varchar(256),
	location varchar(256),
	protocol varchar(256),
	reply_key varchar(256),
	recovery_succesful_timestamp DATETIME);  
  
--
-- DPN Recovery - file (Replicating node). Track information needed by the node
-- when supplying a DPN bag for a recovery operation.
--

CREATE TABLE dpn_recovery_file (
	id integer primary key autoincrement,
	correlation_id varchar(32),
	object_id varchar(256),
	creation_timestamp DATETIME DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
	recovery_destination varchar(256),
	status varchar(256),
	path varchar(256),
	reply_key varchar(256),
	recovery_succesful_timestamp DATETIME);  


	
DELETE FROM sqlite_sequence;
INSERT INTO "sqlite_sequence" VALUES('dpn_file',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_outbound_transfer',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_inbound_transfer',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_registry_item_create_message',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_registry_item_create_message_detail',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_registry',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_registry_node_names',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_registry_brightening_object_id',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_registry_rights_object_id',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_recovery_request',1);


CREATE UNIQUE INDEX idx_object_id ON dpn_registry (dpn_object_id);


COMMIT;
