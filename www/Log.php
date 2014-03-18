<?php
/**
 * Require the library
 */
require 'phptail.php';
/**
 * Initilize a new instance of PHPTail
 * @var PHPTail
 */

$tail = new PHPTail( array(
	"DPN_Log" => "/home/dg2226/dpn/dpn_local/log/dpn_log.txt",
));

/**
 * We're getting an AJAX call
 */
if(isset($_GET['ajax']))  {
	echo $tail->getNewLines($_GET['tab'], $_GET['lastsize'], $_GET['grep'], $_GET['invert']);
	die();
}
/**
 * Regular GET/POST call, print out the GUI
 */
$tail->generateGUI();

