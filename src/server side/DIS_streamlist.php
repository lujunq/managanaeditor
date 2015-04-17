<?php
/**
 * Managana server: save community data.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// get community
$community = postString('community');

// check for user access level
$level = communityLevel($community);
minimumLevel($level, 'author');

// check user level
@session_start();
if (($level == "super") || ($level == "admin") || ($level == "editor")) {
	$query = "SELECT t1.* FROM dis_stream t1 WHERE t1.str_community='$community' AND t1.str_index=(SELECT MAX(t2.str_index) FROM dis_stream t2 WHERE t1.str_id = t2.str_id AND t2.str_community='$community') ORDER BY t1.str_index DESC";
} else {
	$query = "SELECT t1.* FROM dis_stream t1 WHERE t1.str_community='$community' AND t1.str_index=(SELECT MAX(t2.str_index) FROM dis_stream t2 WHERE t1.str_id = t2.str_id AND t2.str_community='$community') AND t1.str_authorid='" . $_SESSION['usr_id'] . "' ORDER BY t1.str_index DESC";
}
$list = queryDB($query);

if (mysql_num_rows($list) == 0) {
	mysql_free_result($list);
	exitOnError('ERSTREAM-2');
} else {
	startOutput();
	noError();
	for ($i=0; $i<mysql_num_rows($list); $i++) {
		$row = mysql_fetch_assoc($list);
		// is the stream in use by someone else?
		$check = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='stream' AND cur_id='" . $row['str_id'] . "' AND cur_user NOT LIKE '" . $_SESSION['usr_id'] . "'");
		if (mysql_num_rows($check) == 0) $locked = "";
			else $locked = "x";
		mysql_free_result($check);
		echo('<stream file="' . $row['str_id'] . '" author="' . $row['str_author'] . '" update="' . $row['str_update'] . '" locked="' . $locked . '"><![CDATA[' . decodeApostrophe($row['str_title']) . ']]></stream>');
	}
	mysql_free_result($list);
	endOutput();
}
?>