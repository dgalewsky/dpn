<?php
	require_once("head.php");
	require_once("/home/dg2226/dpn_webapp/common.php");

?>
    <div class="container">

      <h1>DPN Browser</h1>

<?php
function get_repl_node_list($db, $reg_id) {
    $ret = $db->query("select name from dpn_registry_node_name where dpn_registry_id = '$reg_id'");
    $lst = "";
    while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
        $lst = $lst . " " . $res['name'];
    }
 
    return $lst;
}

$db = new SQLite3("/home/dg2226/dpn/dpn_local/db/dpn.db");	
$ret = $db->query("SELECT file_path, file_size, correlation_id, dpn_object_id, creation_timestamp FROM dpn_file");

?>

<table class="table table-bordered table-striped table-hover">
  <caption><h3>DPN File</h3></caption>
  <thead>
    <tr>
      <th>File Path</th>
      <th>File Size</th>
      <th>Correlation Id</th>
      <th>DPN ObjectId</th>
      <th>Creation Timestamp</th>
    </tr>
  </thead>
  <tbody>

<?php

while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
      echo "<tr>";
      echo "<td>" . $res['file_path'] . "</td>";
      echo "<td>" . $res['file_size'] . "</td>";
      echo "<td>" . $res['correlation_id'] . "</td>";
      echo "<td>" . $res['dpn_object_id'] . "</td>";
      echo "<td>" . $res['creation_timestamp'] . "</td>";
      echo "</tr>";
}

?>
</tbody>
</table>

<!-- Outbound Transfer -->

<?php


$ret = $db->query("SELECT correlation_id, creation_timestamp, destination, status, transfer_succesful_timestamp FROM dpn_outbound_transfer");

?>

<table class="table table-bordered table-striped table-hover">
  <caption><h3>DPN Outbound File Transfers</h3></caption>
  <thead>
    <tr>
      <th>Correlation ID</th>
      <th>Creation Timestamp</th>
      <th>Destination</th>
      <th>Status</th>
      <th>Transfer Timestamp</th>      
    </tr>
  </thead>
  <tbody>

<?php

while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
      echo "<tr>";
      echo "<td>" . $res['correlation_id'] . "</td>";
      echo "<td>" . $res['creation_timestamp'] . "</td>";
      echo "<td>" . $res['destination'] . "</td>";
      echo "<td>" . $res['status'] . "</td>";
      echo "<td>" . $res['transfer_succesful_timestamp'] . "</td>";      
      echo "</tr>";
}
?>

</tbody>
</table>


<!-- Inbound Transfer -->

<?php


$ret = $db->query("SELECT correlation_id, creation_timestamp, status, protocol, location, source, reply_key FROM dpn_inbound_transfer");

?>

<table class="table table-bordered table-striped table-hover">
  <caption><h3>DPN Inbound File Transfers</h3></caption>
  <thead>
    <tr>
      <th>Correlation ID</th>
      <th>Creation Timestamp</th>
      <th>Status</th>
      <th>Protocol</th>
      <th>Location</th>
      <th>Source</th>
      <th>Reply Key</th>
      <th></th>      
    </tr>
  </thead>
  <tbody>

<?php

while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
      echo "<tr>";
      echo "<td>" . $res['correlation_id'] . "</td>";
      echo "<td>" . $res['creation_timestamp'] . "</td>";
      echo "<td>" . $res['status'] . "</td>";
      echo "<td>" . $res['protocol'] . "</td>";   
      echo "<td>" . $res['location'] . "</td>";      
      echo "<td>" . $res['source'] . "</td>";      
      echo "<td>" . $res['reply_key'] . "</td>";      
      echo "</tr>";
}
?>

</tbody>
</table>

<!-- Inbound Transfer -->

<?php
$ret = $db->query("SELECT id, dpn_object_id, first_node_name FROM dpn_registry");
?>

<table class="table table-bordered table-striped table-hover">
  <caption><h3>DPN Registry</h3></caption>
  <thead>
    <tr>
      <th>DPN Object Id</th>
      <th>First Node</th>
      <th>Node List</th>
      <th></th>
    </tr>
  </thead>
  <tbody>

<?php

while($res = $ret->fetchArray(SQLITE3_ASSOC)) {
      echo "<tr>";
      echo "<td>" . $res['dpn_object_id'] . "</td>";
      echo "<td>" . $res['first_node_name'] . "</td>";
      echo "<td>" . get_repl_node_list($db, $res['id']) . "</td>";
      echo "</tr>";
}
?>

</tbody>
</table>

</div> <!-- /container -->

<?php
require_once("foot.php");
?>
