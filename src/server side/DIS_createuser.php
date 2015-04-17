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
$level = requestLevel();
minimumLevel($level, 'super');

// get new user data
$error = "";
$newname = encodeApostrophe(postString("newname"));
$newmail = postString("newmail");
$newpass = postString("newpass");
$newlevel = postString("newlevel");
if (($newname == "") || ($newpass == "") || ($newlevel == "") || ($newmail == "")) {
	// not enough data
	$error = "ERUSER-0";
} else {
	// look for previous users
	$check = queryDB("SELECT * FROM dis_user WHERE usr_email='$newmail'");
	if (mysql_num_rows($check) > 0) {
		$error = "ERUSER-1";
	} else {
		// create the new user
		queryDB("INSERT INTO dis_user (usr_id, usr_email, usr_pass, usr_name, usr_level) VALUES ('" . noSpecial($newmail) . "', '$newmail', '" . md5($newpass) . "', '$newname', '$newlevel')");
		// send an e-mail to the new user
		$mailto = $newmail;
		$subject = MAILNEWSUBJECT;
		if ($newlevel == "subscriber") $message = str_replace("[LINK]", (INSTALLFOLDER . "/editor.php"), MAILNEWBODY);
			else $message = str_replace("[LINK]", (INSTALLFOLDER . "/editor.php"), MAILNEWBODYSUBSCRIBER);
		$message = str_replace("[VIEWLINK]", INSTALLFOLDER, $message);
		$message = str_replace("[NAME]", $newname, $message);
		$message = str_replace("[EMAIL]", $newmail, $message);
		$message = str_replace("[PASS]", $newpass, $message);
		$message = str_replace("\n.", "\n..", $message); // for windows hosts
		$headers = 'From: ' . MAILFROM . "\r\n" . 'Reply-To: ' . MAILFROM . "\r\n" . 'X-Mailer: PHP/' . phpversion();
		if (!@mail($mailto, $subject, $message, $headers)) $error = "ERUSER-2";
	}
	mysql_free_result($check);
}
	
// output
if ($error != "") {
	exitOnError($error);
} else {
	startOutput();
	noError();
	outputData('id', noSpecial($newmail));
	outputData('email', $newmail);
	outputData('name', $newname);
	outputData('level', $newlevel);
	endOutput();
}

?>