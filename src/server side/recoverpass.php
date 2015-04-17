<?php
/**
 * Managana server: recover password.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check requested language
require_once('language/language_default.php');
$lang = getString('lang');
if ($lang != "") {
	if (is_file("language/language_" . $lang . ".php")) {
		require_once('language/language_' . $lang . '.php');
	} else if (is_file("language/language_" . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . ".php")) {
		require_once('language/language_' . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . '.php');
	}
} else {
	if (is_file("language/language_" . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . ".php")) {
		require_once('language/language_' . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . '.php');
	}
}

// check required data
$key = getString('key');
$mail = getString('mail');
$index = getString('index');

// look for the e-mail on database
$message = "";
$check = queryDB("SELECT * FROM dis_user WHERE usr_email='$mail' AND usr_index='$index' AND usr_key='$key' AND usr_status='recover'");
if (mysql_num_rows($check) == 0) {
	// no assigned account found
	$message = $text['RECOVERMESSAGEERROR'];
} else {
	// change the password
	$row = mysql_fetch_assoc($check);
	queryDB("UPDATE dis_user SET usr_status='', usr_key='', usr_new='', usr_pass='" . $row['usr_new'] . "' WHERE usr_index='$index'");
	$message = $text['RECOVERMESSAGEOK'];
}
mysql_free_result($check);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<title><?php echo($text['MAILRECOVERSUBJECT']); ?></title>
<style type="text/css">
body, html {
	width: 100%;
	height: 100%;
}
body,td,th {
	color: #959595;
	font-size: 12px;
}
body {
	background-color:#000000;
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	font-family: Verdana, Geneva, sans-serif;
}
#headerspacer {
	width: 100%;
	height: 40px;
}
#managanabox {
	max-width: 440px;
	margin: 0 auto;
	background-color:#000000;
	font-family: Verdana, Geneva, sans-serif;
	border-bottom:#1f1f1f 1px solid;
	background:url(pics/bgImage.jpg) no-repeat top;
}
#managanabox h1 {
	font-size: 16px;
	color:#555555;
	font-weight:bold;
}
#managanabox a {
	color:#555555;
	font-weight:bold;
	text-decoration:none;
	padding-left:14px;
	background:url(pics/arrow.png) no-repeat;
}
#managanabox a:hover {
	color:#959595;
}
#managanatitle {
	width: 100%;
	text-align: right;
	font-size: 16px;
	color:#555555;
	font-weight:bold;
	border-bottom:#1f1f1f 1px solid;
}
</style>
</head>
<body>
<div id="headerspacer">&nbsp;</div>
<div id="managanabox">
	<div id="managanatitle">
    	<?php echo($text['MAILRECOVERSUBJECT']); ?>
    </div>
    <p><?php echo($message); ?></p>
</div>
</body>
</html>