<?php
/**
 * Managana server: playlist revisions.
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
$id = postString('id');

// check user level
@session_start();

// check playlists
if (($level == "super") || ($level == "super") || ($level == "super")) {
	$query = "SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_id='$id' ORDER BY ply_index DESC";
} else {
	$query = "SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_id='$id' AND ply_authorid='" . $_SESSION['usr_id'] . "' ORDER BY ply_index DESC";
}
$list = queryDB($query);

if (mysql_num_rows($list) == 0) {
	mysql_free_result($list);
	exitOnError('ERPLAYLIST-0');
} else {
	startOutput();
	noError();
	for ($i=0; $i<mysql_num_rows($list); $i++) {
		$row = mysql_fetch_assoc($list);
		$element = queryDB("SELECT * FROM dis_element WHERE elm_plindex='" . $row['ply_index'] . "'");
		if (mysql_num_rows($element) > 0) echo('<playlist id="' . $row['ply_id'] . '" name="' . $row['ply_title'] . '" index="' . $row['ply_index'] . '" date="' . $row['ply_date'] . '" elements="' . mysql_num_rows($element) . '" />');
		mysql_free_result($element);
	}
	mysql_free_result($list);
	endOutput();
}
?>