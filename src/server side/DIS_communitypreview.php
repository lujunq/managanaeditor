<?php
/**
 * Managana server: community preview information.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$id = postString('community');
$level = communityLevel($id);
minimumLevel($level, 'author');

// is provided ID valid?
$dbresult = queryDB("SELECT * FROM dis_community WHERE com_id='$id'");

if (mysql_num_rows($dbresult) == 0) {
	// no community with provided id
	mysql_free_result($dbresult);
	exitOnError('ERPREVIEW-0');
} else {
	$row = mysql_fetch_assoc($dbresult);
	// is there a published home stream?
	$homecheck = queryDB("SELECT * FROM dis_stream WHERE str_community='" . $id . "' AND str_id='" . $row['com_home'] . "' AND str_state='publish'");
	$hashome = true;
	if (mysql_num_rows($homecheck) == 0) {
		$hashome = false;
	}
	mysql_free_result($homecheck);
	// prepare output
	startOutput();
	noError();
	// home stream
	if ($hashome) outputData('home', $row['com_home']);
		else outputData('home', '');
	// list published streams
	$streamcheck = queryDB("SELECT * FROM dis_stream WHERE str_community='" . $id . "' AND str_state='publish'");
	if (mysql_num_rows($streamcheck) > 0) {
		for ($i=0; $i<mysql_num_rows($streamcheck); $i++) {
			$stream = mysql_fetch_assoc($streamcheck);
			echo('<stream id="' . $stream['str_id'] . '">' . $stream['str_title'] . '</stream>');
		}
	}
	mysql_free_result($streamcheck);
	mysql_free_result($dbresult);
	endOutput();
}
?>