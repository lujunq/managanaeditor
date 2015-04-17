<?php
/**
 * Managana server: open stream data.
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
minimumLevel($level, 'author');

// get stream data
$id = postString('id');
$byindex = false;
$index = "";
if (isset($_POST['byindex'])) {
	$byindex = true;
	$index = postString('byindex');
}

// get user data
@session_start();
$authorid = $_SESSION['usr_id'];

// does the stream id exist?
$doopen = false;
if ($byindex) $checkexist = queryDB("SELECT * FROM dis_stream WHERE str_index='$index' AND str_community='$community'");
	else $checkexist = queryDB("SELECT * FROM dis_stream WHERE str_id='$id' AND str_community='$community'");
if (mysql_num_rows($checkexist) == 0) {
	// stream does not exist
	mysql_free_result($checkexist);
	exitOnError('ERSTREAM-3');
} else {
	// check permission
	$row = mysql_fetch_assoc($checkexist);
	if ($level == 'author') {
		if ($row['str_authorid'] != $authorid) {
			// user can't open the stream
			mysql_free_result($checkexist);
			exitOnError('ERSTREAM-4');
		} else {
			$doopen = true;
		}
	} else {
		$doopen = true;
	}
	// is stream in use by someone else?
	$checkuse = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='stream' AND cur_id='$id' AND cur_user NOT LIKE '" . $_SESSION['usr_id'] . "'");
	if (mysql_num_rows($checkuse) != 0) {
		// the stream is already in use
		$doopen = false;
		mysql_free_result($checkuse);
		exitOnError('ERSTREAM-6');
	} else {
		mysql_free_result($checkuse);
	}
}
mysql_free_result($checkexist);

// open sream?
if (!$doopen) {
	exitOnError('ERSTREAM-5');
} else {
	startOutput();
	noError();
	outputStream($id, $community, $byindex, $index);
	endOutput();	
}
?>