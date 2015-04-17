<?php
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

// get login recover data
$show = "start";
$ac = postString("ac");
if ($ac == "recover") {
	$user = postString("user");
	$new = postString("new");
	// check user on database
	$check = queryDB("SELECT * FROM dis_user WHERE usr_email='$user'");
	if (mysql_num_rows($check) == 0) {
		// no user found
		$show = "nouser";
	} else {
		// ask for password change
		$show = "recover";
		$userData = mysql_fetch_assoc($check);
		$key = randKey(10);
		$recoverlink = INSTALLFOLDER . "/recoverpass.php?key=" . urlencode($key) . "&mail=" . urlencode($user) . "&index=" . $userData['usr_index'];
		queryDB("UPDATE dis_user SET usr_status='recover', usr_key='$key', usr_new='" . md5($new) . "' WHERE usr_index='" . $userData['usr_index'] . "'");
		// send recover e-mail
		$mailto = $userData['usr_email'];
		$subject = $text['MAILRECOVERSUBJECT'];
		$message = str_replace("[LINK]", $recoverlink, $text['MAILRECOVERBODY']);
		$message = str_replace("\n.", "\n..", $message); // for windows hosts
		$headers = 'From: ' . MAILSENDER . "\r\n" . 'Reply-To: ' . MAILSENDER . "\r\n" . 'X-Mailer: PHP/' . phpversion();
		if (!@mail($mailto, $subject, $message, $headers)) $show = "nouser";
	}
	mysql_free_result($check);
}
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
.managanatable {
	max-width: 440px;
}
.managanainput {
	width: 100%;
}
#waitingarea {
	max-width: 440px;
	margin: 0 auto;
	background-color:#000000;
	font-family: Verdana, Geneva, sans-serif;
	border-bottom:#1f1f1f 1px solid;
	background:url(pics/bgImage.jpg) no-repeat top;
	display: none;
}
#waitingarea h1 {
	font-size: 16px;
}
#waitingarea a{
	color:#555555;
	font-weight:bold;
	text-decoration:none;
	padding-left:14px;
	background:url(pics/arrow.png) no-repeat;
}
#waitingarea a:hover{
	color:#959595;
}
</style>
</head>

<body>
<div id="headerspacer">&nbsp;</div>
<div id="managanabox">
	<div id="managanatitle">
    	<?php echo($text['MAILRECOVERSUBJECT']); ?>
    </div>
<?php
switch ($show) {
	case "start":
		?>
        <p><?php echo($text['LOGINRECOVERABOUT']); ?></p>
        <form name="recover" method="post">
        <table class="managanatable">
           	<tr>
               	<td width="140"><?php echo($text['LOGINRECOVERMAILFIELD']); ?></td>
               	<td><input type="text" class="managanainput" name="user" id="user"/></td>
            </tr>
            <tr>
              	<td width="140"><?php echo($text['LOGINRECOVERPASSFIELD']); ?></td>
              	<td><input type="password" class="managanainput" name="new" id="new"/></td>
            </tr>
            <tr>
               	<td><input type="hidden" name="ac" value="recover" /></td>
              	<td><input type="submit" value="<?php echo($text['LOGINRECOVERBUTTON']); ?>" /></td>
            </tr>
        </table>
        </form>
		<?php
		break;
	case "nouser":
		?>
        <p><?php echo($text['LOGINRECOVERNOTFOUND']); ?></p>
        <form name="recover" method="post">
        <table class="managanatable">
           	<tr>
               	<td width="140"><?php echo($text['LOGINRECOVERMAILFIELD']); ?></td>
               	<td><input type="text" class="managanainput" name="user" id="user"/></td>
            </tr>
            <tr>
              	<td width="140"><?php echo($text['LOGINRECOVERPASSFIELD']); ?></td>
              	<td><input type="password" class="managanainput" name="new" id="new"/></td>
            </tr>
            <tr>
               	<td><input type="hidden" name="ac" value="recover" /></td>
              	<td><input type="submit" value="<?php echo($text['LOGINRECOVERBUTTON']); ?>" /></td>
            </tr>
        </table>
        </form>
		<?php
		break;
	case "recover":
		?>
        <p><?php echo($text['LOGINRECOVEROK']); ?></p>
		<?php
		break;
}
?>
</div>
</body>
</html>