<?php
/**
 * Managana server: list of a community categories.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'author');

// get data
$query = "SELECT * FROM dis_category WHERE cat_community='$community' ORDER BY cat_name";
$list = queryDB($query);

startOutput();
noError();
for ($i=0; $i<mysql_num_rows($list); $i++) {
	$row = mysql_fetch_assoc($list);
	echo('<category><![CDATA[' . decodeApostrophe($row['cat_name']) . ']]></category>');
}
mysql_free_result($list);
endOutput();
?>