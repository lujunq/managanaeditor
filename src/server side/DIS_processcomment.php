<?php
/**
 * Managana server: process a comment.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// community handle common functions
require_once('DIS_communitycommon.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'editor');

// process the comment
$action = postString('action');
$index = postString('index');
$status = -1;
switch ($action) {
	case 'approve':
		echo('<!-- ' . "UPDATE dis_comment SET cmt_status='0' WHERE cmt_index='$index'" . ' -->');
		queryDB("UPDATE dis_comment SET cmt_status='0' WHERE cmt_index='$index'");
		$status = 0;
		break;
	case 'reject':
		queryDB("UPDATE dis_comment SET cmt_status='2' WHERE cmt_index='$index'");
		$status = 2;
		break;
	case 'wait':
		queryDB("UPDATE dis_comment SET cmt_status='1' WHERE cmt_index='$index'");
		$status = 1;
		break;
	case 'delete':
		queryDB("DELETE FROM dis_comment WHERE cmt_index='$index' LIMIT 1");
		break;
}

// output
startOutput();
outputData('action', $action);
outputData('index', $index);
outputData('status', $status);
noError();
endOutput();
?>