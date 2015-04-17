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
$id = postString('id');
$from = postString('from');
if ($from == 'active') $set = '1';
	else $set = '0';

// check user level
@session_start();
if (($level == 'super') || ($level == 'admin') || ($level == 'editor')) {
	$query = "UPDATE dis_playlist SET ply_archived='$set' WHERE ply_community='$community' AND ply_id='$id'";
} else {
	$query = "UPDATE dis_playlist SET ply_archived='$set' WHERE ply_community='$community' AND ply_id='$id AND ply_authorid='" . $_SESSION['usr_id'] . "'";
}

// update database
queryDB($query);

// output
startOutput();
noError();
outputData('from', $from);
endOutput();
?>