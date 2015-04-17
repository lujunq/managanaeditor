<?php
/**
 * Managana server: list of all stream revisions.
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
$stream = postString('stream');

// check user level
if (($level == 'super') || ($level == 'admin') || ($level == 'editor')) {
	$query = "SELECT * FROM dis_stream WHERE str_id='$stream' AND str_community='$community' ORDER BY str_update DESC";
} else {
	$query = "SELECT * FROM dis_stream WHERE str_id='$stream' AND str_community='$community' AND str_authorid='" . $_SESSION['usr_id'] . "' ORDER BY str_update DESC";
}
$list = queryDB($query);

startOutput();
noError();
for ($i=0; $i<mysql_num_rows($list); $i++) {
	$row = mysql_fetch_assoc($list);
	echo('<stream state="' . $row['str_state'] . '" index="' . $row['str_index'] . '" file="' . $row['str_id'] . '" author="' . $row['str_author'] . '" update="' . $row['str_update'] . '"><![CDATA[' . decodeApostrophe($row['str_title']) . ']]></stream>');
}
mysql_free_result($list);
endOutput();
?>