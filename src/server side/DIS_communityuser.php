<?php
/**
 * Managana server: create a new user
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check user level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'admin');

// get search data
$error = "";
$action = postString("action");
$addlevel = postString("addlevel");
$user = postString("user");

if ($action == "") {
	// not enough data
	$error = "ERCOMMUNITY-5";
} else {
	// add users?
	if ($action == "add") {
		// any previous user permissions?
		$checkPrevious = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$community' AND usc_user='$user'");
		if (mysql_num_rows($checkPrevious) > 0) {
			$error = "ERCOMMUNITY-7";
		} else {
			queryDB("INSERT INTO dis_usercommunity (usc_community, usc_user, usc_level) VALUES ('$community', '$user', '$addlevel')");
		}
		mysql_free_result($checkPrevious);
	}
	// remove users
	if ($action == "remove") {
		if ($addlevel == "admin") {
			$checkAdmin = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$community' AND usc_level='admin'");
			if (mysql_num_rows($checkAdmin) > 1) {
				queryDB("DELETE FROM dis_usercommunity WHERE usc_community='$community' AND usc_user='$user'");
			} else {
				$error = "ERCOMMUNITY-6";
			}
			mysql_free_result($checkAdmin);
		} else {
			queryDB("DELETE FROM dis_usercommunity WHERE usc_community='$community' AND usc_user='$user'");
		}
	}
}
	
// output
if ($error != "") {
	exitOnError($error);
} else {
	// list all user permissions for current community
	startOutput();
	noError();
	$check = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$community'");
	for ($i=0; $i<mysql_num_rows($check); $i++) {
		$row = mysql_fetch_assoc($check);
		$usrcheck = queryDB("SELECT * FROM dis_user WHERE usr_id='" . $row['usc_user'] . "'");
		if (mysql_num_rows($usrcheck) > 0) {
			$usrrow = mysql_fetch_assoc($usrcheck);
			echo('<user>');
			echo('<level>' . $row['usc_level'] . '</level>');
			echo('<name><![CDATA[' . $usrrow['usr_name'] . " / " . $usrrow['usr_email'] . ']]></name>');
			echo('<data><![CDATA[' . $row['usc_user'] . ']]></data>');
			echo('</user>');
		}
		mysql_free_result($usrcheck);
	}
	mysql_free_result($check);
	endOutput();
}
?>