<?php
/**
 * Managana server database connection.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// attempt connection
$dbLink = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('<?xml version="1.0" encoding="utf-8"?><data><agent>' . AGENT . '</agent><error id="ERDBASE-0" /></data>');
$dbBank = @mysql_select_db(DB_NAME, $dbLink) or die('<?xml version="1.0" encoding="utf-8"?><data><agent>' . AGENT . '</agent><error id="ERDBASE-1" /></data>');

/**
 * Request database information.
 */
function queryDB($query) {
	$result = mysql_query($query) or die('<?xml version="1.0" encoding="utf-8"?><data><agent>' . AGENT . '</agent><error id="ERDBASE-2" /></data>');
	return($result);
}
?>