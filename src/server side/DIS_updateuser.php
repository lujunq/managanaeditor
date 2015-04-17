<?php
/**
 * Managana server: search for community users
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check user level
$level = requestLevel();
minimumLevel($level, 'super');

// get search data
$error = "";
$newname = encodeApostrophe(postString("newname"));
$newmail = postString("newmail");
$newindex = postString("newindex");
$newlevel = postString("newlevel");
$action = postString("action");


if (($newname == "") || ($newmail == "") || ($newindex == "") || ($newlevel == "") || ($action == "")) {
	// not enough data
	$error = "ERUSER-4";
} else {
	// check for previous user information
	$check = queryDB("SELECT * FROM dis_user WHERE usr_index='$newindex'");
	if (mysql_num_rows($check) == 0) {
		$error = "ERUSER-5";
	} else {
		$dochange = true.
		$row = mysql_fetch_assoc($check);
		// was the user a super one?
		if ($row['usr_level'] == "super") {
			// there must be at least another super user in order to change
			if ($newlevel != "super") {
				$checksuper = queryDB("SELECT * FROM dis_user WHERE usr_level='super'");
				if (mysql_num_rows($checksuper) < 2) {
					$error = "ERUSER-6";
					$dochange = false;
				}
				mysql_free_result($checksuper);
			}
		}
		if ($dochange) {
			queryDB("UPDATE dis_user SET usr_name='$newname', usr_email='$newmail', usr_level='$newlevel' WHERE usr_index='$newindex'");
		}
	}
	mysql_free_result($check);
}
	
// output
if ($error != "") {
	exitOnError($error);
} else {
	startOutput();
	noError();
	endOutput();
}
?>