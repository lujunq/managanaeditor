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
$new = postString("new");
if (($user == "") || ($new == "")) {
	// no e-mail
	$error = "ERLOGIN-2";
} else {
	// check login information
	$result = queryDB("SELECT * FROM dis_user WHERE usr_email='$user'");
	if (mysql_num_rows($result) == 0) {
		$error = "ERLOGIN-3";
	} else {
		// get user data
		$userData = mysql_fetch_assoc($result);
		// assign recover key
		$key = randKey(10);
		$recoverlink = INSTALLFOLDER . "/recoverpass.php?key=" . urlencode($key) . "&mail=" . urlencode($user) . "&index=" . $userData['usr_index'];
		queryDB("UPDATE dis_user SET usr_status='recover', usr_key='$key', usr_new='" . md5($new) . "' WHERE usr_index='" . $userData['usr_index'] . "'");
		// send recover e-mail
		$mailto = $userData['usr_email'];
		$subject = MAILRECOVERSUBJECT;
		$message = str_replace("[LINK]", $recoverlink, MAILRECOVERBODY);
		$message = str_replace("\n.", "\n..", $message); // for windows hosts
		$headers = 'From: ' . MAILFROM . "\r\n" . 'Reply-To: ' . MAILFROM . "\r\n" . 'X-Mailer: PHP/' . phpversion();
		if (!@mail($mailto, $subject, $message, $headers)) $error = "ERLOGIN-4";
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