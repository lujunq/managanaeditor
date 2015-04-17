<?php
/**
 * Managana server: check database connection.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// get login data
$error = "";
$user = postString("user");
$pass = postString("pass");
if (($user == "") || ($pass == "")) {
	// no login data
	$error = "ERLOGIN-0";
} else {
	// check login information
	$pass = md5($pass);
	$result = queryDB("SELECT * FROM dis_user WHERE usr_email='$user' AND usr_pass='$pass'");
	if (mysql_num_rows($result) == 0) {
		$error = "ERLOGIN-1";
	} else {
		// get user data
		$userData = mysql_fetch_assoc($result);
		// write session values
		if (($userData['usr_level'] == "super") || ($userData['usr_level'] == "admin") || ($userData['usr_level'] == "user")) {
			@session_start();
			$_SESSION['usr_index'] = $userData['usr_index'];
			$_SESSION['usr_email'] = $userData['usr_email'];
			$_SESSION['usr_id'] = $userData['usr_id'];
			$_SESSION['usr_name'] = $userData['usr_name'];
			$_SESSION['usr_level'] = $userData['usr_level'];
		} else {
			$error = 'ERACCESS-0';
		}
	}
	mysql_free_result($result);
}
	
// output
if ($error != "") {
	exitOnError($error);
} else {
	startOutput();
	noError();
	outputData('index', $userData['usr_index']);
	outputData('id', $userData['usr_id']);
	outputData('email', $userData['usr_email']);
	outputData('name', $userData['usr_name']);
	outputData('level', $userData['usr_level']);
	endOutput();
}

?>