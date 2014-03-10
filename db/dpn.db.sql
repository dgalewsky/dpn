PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE dpn_file (
id integer primary key autoincrement,
creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
file_path varchar(256),
file_size int,
correlation_id varchar(32));
CREATE TABLE dpn_outbound_transfer (
id integer primary key autoincrement,
correlation_id varchar(32),
creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
destination varchar(256),
transfer_succesful_timestamp DATETIME);
CREATE TABLE dpn_inbound_transfer (
id integer primary key autoincrement,
correlation_id varchar(32),
creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
status varchar(256),
protocol varchar(256),
location varchar(256));
DELETE FROM sqlite_sequence;
INSERT INTO "sqlite_sequence" VALUES('dpn_file',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_outbound_transfer',1);
INSERT INTO "sqlite_sequence" VALUES('dpn_inbound_transfer',1);
COMMIT;
