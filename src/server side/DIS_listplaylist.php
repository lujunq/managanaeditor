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
//requestLevel('author');

// get community
$community = postString('community');
$from = postString('from');
if ($from == 'active') $archived = '0';
	else $archived = '1';

// check user level
session_start();

$community = "landsport";
$from = "active";
$archived = "0";
$_SESSION['usr_level'] = "admin";

if (($_SESSION['usr_level'] == 'admin') || ($_SESSION['usr_level'] == 'editor')) {
	$query = "SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_archived='$archived' ORDER BY ply_index DESC";
} else if ($_SESSION['usr_level'] == 'author') {
	$query = "SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_archived='$archived' AND ply_authorid='" . $_SESSION['usr_id'] . "' ORDER BY ply_index DESC";
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
		echo('<playlist id="' . $row['ply_id'] . '">' . $row['ply_title'] . '</playlist>');
	}
	mysql_free_result($list);
	endOutput();
}
?>