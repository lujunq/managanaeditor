<?php
/**
 * Managana server: list of published or saved streams.
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

// list streams
$ac = postString('ac');
switch ($ac) {
	case 'all': // a list of all saved streams, published or not
		if (($level == "super") || ($level == "admin") || ($level == "editor")) {
			$query = "SELECT t1.* FROM dis_stream t1 WHERE t1.str_community='$community' AND t1.str_index=(SELECT MAX(t2.str_index) FROM dis_stream t2 WHERE t1.str_id = t2.str_id AND t2.str_community='$community') ORDER BY t1.str_index DESC";
		} else {
			$query = "SELECT t1.* FROM dis_stream t1 WHERE t1.str_community='$community' AND t1.str_index=(SELECT MAX(t2.str_index) FROM dis_stream t2 WHERE t1.str_id = t2.str_id AND t2.str_community='$community') AND t1.str_authorid='" . $_SESSION['usr_id'] . "' ORDER BY t1.str_index DESC";
		}
		$list = queryDB($query);
		break;
	default: // published streams only
		$list = queryDB("SELECT * FROM dis_stream WHERE str_state='publish' AND str_community='$community' ORDER BY str_title ASC");
		break;
}

startOutput();
noError();
for ($i=0; $i<mysql_num_rows($list); $i++) {
	$row = mysql_fetch_assoc($list);
	echo('<stream file="' . $row['str_id'] . '" author="' . $row['str_author'] . '" update="' . $row['str_update'] . '" index="' . $row['str_index'] . '"><![CDATA[' . decodeApostrophe($row['str_title']) . ']]></stream>');
}
mysql_free_result($list);
endOutput();
?>