<?php
/**
 * Managana server: get configuration values.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check user level
$level = requestLevel();
minimumLevel($level, 'super');

// write all configuration values
$values = queryDB("SELECT * FROM dis_options");
startOutput();
noError();
for ($i=0; $i<mysql_num_rows($values); $i++) {
	$row = mysql_fetch_assoc($values);
	echo("<config>");
	echo("<name><![CDATA[" . $row['opt_name'] . "]]></name>");
	echo("<value><![CDATA[" . decodeApostrophe(str_replace("\\n", "\r", $row['opt_value'])) . "]]></value>");
	echo("</config>");
}
endOutput();
mysql_free_result($values);
?>