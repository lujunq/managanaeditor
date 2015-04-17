<?php
/**
 * Managana server: playlist list;
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

// get community
$from = postString('from');
if ($from == 'active') $archived = '0';
	else $archived = '1';

// check user level
@session_start();
if (($level == 'super') || ($level == 'admin') || ($level == 'editor')) {
	$query = "SELECT t1.* FROM dis_playlist t1 WHERE t1.ply_community='$community' AND t1.ply_index=(SELECT MAX(t2.ply_index) FROM dis_playlist t2 WHERE t1.ply_id = t2.ply_id AND t2.ply_community='$community' AND t2.ply_archived='$archived') ORDER BY t1.ply_index DESC";
} else {
	$query = "SELECT t1.* FROM dis_playlist t1 WHERE t1.ply_community='$community' AND t1.ply_index=(SELECT MAX(t2.ply_index) FROM dis_playlist t2 WHERE t1.ply_id = t2.ply_id AND t2.ply_community='$community' AND t2.ply_archived='$archived') AND t1.ply_authorid='" . $_SESSION['usr_id'] . "' ORDER BY t1.ply_index DESC";
}

$list = queryDB($query);

if (mysql_num_rows($list) == 0) {
	mysql_free_result($list);
	exitOnError('ERPLAYLIST-0');
} else {
	startOutput();
	noError();
	outputData('from', $from);
	for ($i=0; $i<mysql_num_rows($list); $i++) {
		$row = mysql_fetch_assoc($list);
		// is playlist in use by someone else?
		$checkuse = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='playlist' AND cur_id='". $row['ply_id'] . "' AND cur_user NOT LIKE '" . $_SESSION['usr_id'] . "'");
		if (mysql_num_rows($checkuse) == 0) {
			$locked = "";
		} else {
			$locked = "x";
		}
		mysql_free_result($checkuse);
		echo('<playlist id="' . $row['ply_id'] . '" locked="' . $locked . '"><title><![CDATA[' . decodeApostrophe($row['ply_title']) . ']]></title><date><![CDATA[' . $row['ply_date'] . ']]></date></playlist>');
	}
	mysql_free_result($list);
	endOutput();
}
?>