<?php
// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// check for web player
@session_start();
if (isset($_POST['community']) && isset($_POST['stream']) && isset($_POST['player'])) {
	if (trim($_POST['player']) == "web") {
		$_SESSION['idplayer'] = trim($_POST['player']);
		$_SESSION['idcommunity'] = trim($_POST['community']);
		$_SESSION['idstream'] = trim($_POST['stream']);
	}
}

// manage display
$state = "firstshow";

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

// login action
$ac = postString('ac');
$from = getString('from');
$pkey = getString('pkey');
if ($from == "") $from = "player";
if ($ac == "managana") {
	$managanaLogin = postString("managanaLogin");
	$managanaPass = postString("managanaPass");
	// check login
	require_once('DIS_database.php');
	$check = queryDB("SELECT * FROM dis_user WHERE usr_email='$managanaLogin' AND usr_pass='" . md5($managanaPass) . "'");
	if (mysql_num_rows($check) == 0) {
		// running from remote control? check remote-only users
		if ((postString("managanaFrom") == "remote") && (postString("managanaKey") != "")) {
			$checkRLogin = queryDB("SELECT * FROM dis_remoteusers WHERE rus_login='$managanaLogin' AND rus_password='$managanaPass' AND rus_publickey='" . postString("managanaKey") . "'");
			if (mysql_num_rows($checkRLogin) == 0) {
				$state = "invalidauth";
			} else {
				$row = mysql_fetch_assoc($checkRLogin);
				$usermail = $row['rus_login'];
				$username = $row['rus_login'];
				$state = "authok";
				$key = md5($usermail . time());
				$time = time();
				$remoteKey = "";
				$checkRemote = queryDB("SELECT * FROM dis_openusers WHERE opn_mail='$usermail'");
				if (mysql_num_rows($checkRemote) > 0) {
					$rowRemote = mysql_fetch_assoc($checkRemote);
					$remoteKey = $rowRemote['opn_remote'];
				}
				mysql_free_result($checkRemote);
				queryDB("UPDATE dis_openusers SET opn_key='' WHERE opn_lastdate<'" . ($time - 600) . "'");
				queryDB("DELETE FROM dis_openusers WHERE opn_mail='$usermail'");
				queryDB("INSERT INTO dis_openusers (opn_mail, opn_name, opn_key, opn_lastdate, opn_managana, opn_remote) VALUES ('$usermail', '$username', '$key', '$time', '', '$remoteKey')");
			}
			mysql_free_result($checkRLogin);
		} else {
			$state = "invalidauth";
		}
	} else {
		$row = mysql_fetch_assoc($check);
		$usermail = $row['usr_email'];
		$username = $row['usr_name'];
		$state = "authok";
		$key = md5($usermail . time());
		$time = time();
		$remoteKey = "";
		$checkRemote = queryDB("SELECT * FROM dis_openusers WHERE opn_mail='$usermail'");
		if (mysql_num_rows($checkRemote) > 0) {
			$rowRemote = mysql_fetch_assoc($checkRemote);
			$remoteKey = $rowRemote['opn_remote'];
		}
		mysql_free_result($checkRemote);
		queryDB("UPDATE dis_openusers SET opn_key='' WHERE opn_lastdate<'" . ($time - 600) . "'");
		queryDB("DELETE FROM dis_openusers WHERE opn_mail='$usermail'");
		queryDB("INSERT INTO dis_openusers (opn_mail, opn_name, opn_key, opn_lastdate, opn_managana, opn_remote) VALUES ('$usermail', '$username', '$key', '$time', '" . $row['usr_index'] . "', '$remoteKey')");
	}
	mysql_free_result($check);
}

// openid?
if (ALLOWGUEST == "true") {
	try {
		// use LightOpenID
		require_once('openid.php');	
	} catch(Exception $e) {
		// open id failed to load (local web server?)
		define (ALLOWGUEST, "false");
	}
}

// allow guests?
if (ALLOWGUEST == "true") {
	// create openid object
	$openid = new LightOpenID(INSTALLFOLDER . '/');
	if(!$openid->mode) {
		// mode not defined: show first login form or ask for authentication
		if (isset($_GET['ac'])) {
			switch (trim($_GET['ac'])) {
				case "google":
					$openid->identity = 'https://www.google.com/accounts/o8/id';
					$openid->required = array('namePerson/first', 'namePerson/last', 'contact/email');
					header('Location: ' . $openid->authUrl());
					break;
				case "yahoo":
					$openid->identity = 'https://me.yahoo.com';
					$openid->required = array('namePerson/first', 'namePerson/last', 'contact/email');
					header('Location: ' . $openid->authUrl());
					break;
			}
		}
	} else if ($openid->mode == "cancel") {
		$state = "cancelauth";
	} else {
		if ($openid->validate()) {
			$state = "authok";
			$attributes = $openid->getAttributes();
			$id = $openid->identity;
			$username = trim($attributes['namePerson/first'] . " " . $attributes['namePerson/last']);
			$usermail = $attributes['contact/email'];
			// check user name
			if ($username == "") {
				$temp = explode("@", $usermail);
				$username = $temp[0];
			}
			// write user login on table
			require_once('DIS_database.php');
			$key = md5($usermail . time());
			$time = time();
			queryDB("UPDATE dis_openusers SET opn_key='' WHERE opn_lastdate<'" . ($time - 600) . "'");
			$checkRemote = queryDB("SELECT * FROM dis_openusers WHERE opn_mail='$usermail'");
			if (mysql_num_rows($checkRemote) > 0) {
				$rowRemote = mysql_fetch_assoc($checkRemote);
				$remoteKey = $rowRemote['opn_remote'];
			} else {
				$remoteKey = "";
			}
			mysql_free_result($checkRemote);
			queryDB("DELETE FROM dis_openusers WHERE opn_mail='$usermail'");
			queryDB("INSERT INTO dis_openusers (opn_mail, opn_name, opn_key, opn_lastdate, opn_remote) VALUES ('$usermail', '$username', '$key', '$time', '$remoteKey')");
		} else {
			// was the authentication really a failure? (a bug on LightOpenID?)
			if (isset($_GET['openid_ext1_value_contact_email']) && isset($_GET['openid_ext1_value_namePerson_first'])) {
				if ((trim($_GET['openid_ext1_value_contact_email']) != "") && (trim($_GET['openid_ext1_value_namePerson_first']) != "")) {
					// confirm authentication
					$state = "authok";
					$username = trim($_GET['openid_ext1_value_namePerson_first']);
					if (isset($_GET['openid_ext1_value_namePerson_last'])) {
						if (trim($_GET['openid_ext1_value_namePerson_last']) != "") {
							$username .= " " . trim($_GET['openid_ext1_value_namePerson_last']);
						}
					}
					$usermail = trim($_GET['openid_ext1_value_contact_email']);
					// check user name
					if ($username == "") {
						$temp = explode("@", $usermail);
						$username = $temp[0];
					}
					// write user login on table
					require_once('DIS_database.php');
					$key = md5($usermail . time());
					$time = time();
					queryDB("UPDATE dis_openusers SET opn_key='' WHERE opn_lastdate<'" . ($time - 600) . "'");
					$checkRemote = queryDB("SELECT * FROM dis_openusers WHERE opn_mail='$usermail'");
					if (mysql_num_rows($checkRemote) > 0) {
						$rowRemote = mysql_fetch_assoc($checkRemote);
						$remoteKey = $rowRemote['opn_remote'];
					} else {
						$remoteKey = "";
					}
					mysql_free_result($checkRemote);
					queryDB("DELETE FROM dis_openusers WHERE opn_mail='$usermail'");
					queryDB("INSERT INTO dis_openusers (opn_mail, opn_name, opn_key, opn_lastdate, opn_remote) VALUES ('$usermail', '$username', '$key', '$time', '$remoteKey')");					
				} else {
					// authentication really failed
					$state = "invalidauth";
				}
			} else {
				// authentication really failed
				$state = "invalidauth";
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
<title><?php echo($text['LOGINTITLE']); ?></title>
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
<script language="javascript" type="text/javascript">
function showWaiting() {
	var area = document.getElementById('managanabox');
	area.style.display = 'none';
	area = document.getElementById('waitingarea');
	area.style.display = 'block';
	return(true);
}
</script>
</head>

<body>
<div id="headerspacer">&nbsp;</div>
<div id="managanabox">
	<div id="managanatitle">
    	<?php echo($text['LOGINTITLE']); ?>
    </div>
	<?php 
	// check page state
	if ($state == "firstshow") {
		if (ALLOWGUEST == "true") {
			echo("<p>" . $text['LOGINWITHGUEST'] . "</p>");
			?>
            <form name="managanaID" onsubmit="return showWaiting();" method="post">
                 <table class="managanatable">
                	<tr>
                    	<td width="120"><?php echo($text['LOGINMAILFIELD']); ?></td>
                    	<td><input type="text" class="managanainput" name="managanaLogin" id="managanaLogin"/></td>
                    </tr>
                    <tr>
                    	<td width="120"><?php echo($text['LOGINPASSFIELD']); ?></td>
                    	<td><input type="password" class="managanainput" name="managanaPass" id="managanaPass"/></td>
                    </tr>
                    <tr>
                    	<td><input type="hidden" name="ac" value="managana" /><input type="hidden" name="managanaFrom" value="<?php echo($from); ?>" /><input type="hidden" name="managanaKey" value="<?php echo($pkey); ?>" /></td>
                    	<td><input type="submit" value="<?php echo($text['LOGINCHECKLABEL']); ?>" /></td>
                    </tr>
                    <tr>
                    	<td>&nbsp;</td>
                        <td>
                        	<p><a href="<?php echo(INSTALLFOLDER); ?>/recoverlogin.php"><?php echo($text['LOGINRECOVERPASS']); ?></a></p>
                            <?php
								if (CREATEALLOW == 'true') {
									echo('<p><a href="' . CREATELINK . '">' . $text['CREATETEXT'] . '</a></p>');
								}
							?>
                        </td>
                    </tr>
                </table>
            </form>
            <table class="managanatable">
	            <tr>
                	<td width="20%">&nbsp;</td>
                	<td width="40%" align="center">
                        <form name="googleform" onsubmit="return showWaiting();">
                            <input type="hidden" name="ac" value="google" />
                            <input type="image" src="pics/loginButtonGoogle.jpg" />
                        </form>
            		</td>
                    <td width="40%" align="center">
                        <form name="yahooform" onsubmit="return showWaiting();">
                            <input type="hidden" name="ac" value="yahoo" />
                            <input type="image" src="pics/loginButtonYahoo.jpg" />
                        </form>
            		</td>
				</tr>
            </table>
            <?php
		} else {
			echo("<p>" . $text['LOGINNOGUEST'] . "</p>");
			?>
            <form name="managanaID" onsubmit="return showWaiting();" method="post">
                <table class="managanatable">
                	<tr>
                    	<td width="120"><?php echo($text['LOGINMAILFIELD']); ?></td>
                    	<td><input type="text" class="managanainput" name="managanaLogin" id="managanaLogin"/></td>
                    </tr>
                    <tr>
                    	<td width="120"><?php echo($text['LOGINPASSFIELD']); ?></td>
                    	<td><input type="password" class="managanainput" name="managanaPass" id="managanaPass"/></td>
                    </tr>
                    <tr>
                    	<td><input type="hidden" name="ac" value="managana" /><input type="hidden" name="managanaFrom" value="<?php echo($from); ?>" /><input type="hidden" name="managanaKey" value="<?php echo($pkey); ?>" /></td>
                    	<td><input type="submit" value="<?php echo($text['LOGINCHECKLABEL']); ?>" /></td>
                    </tr>
                    <tr>
                    	<td>&nbsp;</td>
                        <td>
                        	<p><a href="<?php echo(INSTALLFOLDER); ?>/recoverlogin.php"><?php echo($text['LOGINRECOVERPASS']); ?></a></p>
                            <?php
								if (CREATEALLOW == 'true') {
									echo('<p><a href="' . CREATELINK . '">' . $text['CREATETEXT'] . '</a></p>');
								}
							?>
                        </td>
                    </tr>
                </table>
            </form>
            <?php
		}
		?>
		<?php
			if (isset($_SESSION['idplayer']) && isset($_SESSION['idcommunity']) && isset($_SESSION['idstream'])) {
				$returnlink = "index.php?community=" . urlencode($_SESSION['idcommunity']) . "&stream=" . urlencode($_SESSION['idstream']) . "&ui=true&server=" . urlencode(INSTALLFOLDER . '/');
			} else {
				$returnlink = "authenticateerror.php";
			}
		?>
		<p><a href="<?php echo($returnlink); ?>"><?php echo($text['LOGINNORETURN']); ?></a></p>
		<?php
	} else if ($state == "cancelauth") {
		?>
		<p><?php echo($text['LOGINOPENCANCEL']); ?></p>
        	<form name="managanaID" onsubmit="return showWaiting();" method="post">
                <table class="managanatable">
                	<tr>
                    	<td width="120"><?php echo($text['LOGINMAILFIELD']); ?></td>
                    	<td><input type="text" class="managanainput" name="managanaLogin" id="managanaLogin"/></td>
                    </tr>
                    <tr>
                    	<td width="120"><?php echo($text['LOGINPASSFIELD']); ?></td>
                    	<td><input type="password" class="managanainput" name="managanaPass" id="managanaPass"/></td>
                    </tr>
                    <tr>
                    	<td><input type="hidden" name="ac" value="managana" /><input type="hidden" name="managanaFrom" value="<?php echo($from); ?>" /><input type="hidden" name="managanaKey" value="<?php echo($pkey); ?>" /></td>
                    	<td><input type="submit" value="<?php echo($text['LOGINCHECKLABEL']); ?>" /></td>
                    </tr>
                    <tr>
                    	<td>&nbsp;</td>
                        <td>
                        	<p><a href="<?php echo(INSTALLFOLDER); ?>/recoverlogin.php"><?php echo($text['LOGINRECOVERPASS']); ?></a></p>
                            <?php
								if (CREATEALLOW == 'true') {
									echo('<p><a href="' . CREATELINK . '">' . $text['CREATETEXT'] . '</a></p>');
								}
							?>
                        </td>
                    </tr>
                </table>
            </form>
        <?php if (ALLOWGUEST == "true") { ?>
            <table class="managanatable">
	            <tr>
                	<td width="20%">&nbsp;</td>
                	<td width="40%" align="center">
                        <form name="googleform" onsubmit="return showWaiting();">
                            <input type="hidden" name="ac" value="google" />
                            <input type="image" src="pics/loginButtonGoogle.jpg" />
                        </form>
            		</td>
                    <td width="40%" align="center">
                        <form name="yahooform" onsubmit="return showWaiting();">
                            <input type="hidden" name="ac" value="yahoo" />
                            <input type="image" src="pics/loginButtonYahoo.jpg" />
                        </form>
            		</td>
				</tr>
            </table>
        <?php } ?>
		<?php
			if (isset($_SESSION['idplayer']) && isset($_SESSION['idcommunity']) && isset($_SESSION['idstream'])) {
				$returnlink = "index.php?community=" . urlencode($_SESSION['idcommunity']) . "&stream=" . urlencode($_SESSION['idstream']) . "&ui=true&server=" . urlencode(INSTALLFOLDER . '/');
			} else {
				$returnlink = "authenticateerror.php";
			}
		?>
		<p><a href="<?php echo($returnlink); ?>"><?php echo($text['LOGINNORETURN']); ?></a></p>
		<?php
	} else if ($state == "invalidauth") {
		?>
		<p><?php echo($text['LOGINFAIL']); ?></p>
        	<form name="managanaID" onsubmit="return showWaiting();" method="post">
                <table class="managanatable">
                	<tr>
                    	<td width="120"><?php echo($text['LOGINMAILFIELD']); ?></td>
                    	<td><input type="text" class="managanainput" name="managanaLogin" id="managanaLogin"/></td>
                    </tr>
                    <tr>
                    	<td width="120"><?php echo($text['LOGINPASSFIELD']); ?></td>
                    	<td><input type="password" class="managanainput" name="managanaPass" id="managanaPass"/></td>
                    </tr>
                    <tr>
                    	<td><input type="hidden" name="ac" value="managana" /><input type="hidden" name="managanaFrom" value="<?php echo($from); ?>" /><input type="hidden" name="managanaKey" value="<?php echo($pkey); ?>" /></td>
                    	<td><input type="submit" value="<?php echo($text['LOGINCHECKLABEL']); ?>" /></td>
                    </tr>
                    <tr>
                    	<td>&nbsp;</td>
                        <td>
                        	<p><a href="<?php echo(INSTALLFOLDER); ?>/recoverlogin.php"><?php echo($text['LOGINRECOVERPASS']); ?></a></p>
                            <?php
								if (CREATEALLOW == 'true') {
									echo('<p><a href="' . CREATELINK . '">' . $text['CREATETEXT'] . '</a></p>');
								}
							?>
                        </td>
                    </tr>
                </table>
            </form>
        <?php if (ALLOWGUEST == "true") { ?>
            <table class="managanatable">
	            <tr>
                	<td width="20%">&nbsp;</td>
                	<td width="40%" align="center">
                        <form name="googleform" onsubmit="return showWaiting();">
                            <input type="hidden" name="ac" value="google" />
                            <input type="image" src="pics/loginButtonGoogle.jpg" />
                        </form>
            		</td>
                    <td width="40%" align="center">
                        <form name="yahooform" onsubmit="return showWaiting();">
                            <input type="hidden" name="ac" value="yahoo" />
                            <input type="image" src="pics/loginButtonYahoo.jpg" />
                        </form>
            		</td>
				</tr>
            </table>
        <?php } ?>
		<?php
			if (isset($_SESSION['idplayer']) && isset($_SESSION['idcommunity']) && isset($_SESSION['idstream'])) {
				$returnlink = "index.php?community=" . urlencode($_SESSION['idcommunity']) . "&stream=" . urlencode($_SESSION['idstream']) . "&ui=true&server=" . urlencode(INSTALLFOLDER . '/');
			} else {
				$returnlink = "authenticateerror.php";
			}
		?>
		<p><a href="<?php echo($returnlink); ?>"><?php echo($text['LOGINNORETURN']); ?></a></p>
		<?php
	} else if ($state == "authok") {
		?>
		<p><?php echo(str_replace('[NAME]', utf8_encode($username), $text['LOGINOK'])); ?></p>
		<?php
			if (isset($_SESSION['idplayer']) && isset($_SESSION['idcommunity']) && isset($_SESSION['idstream'])) {
				$returnlink = "index.php?community=" . urlencode($_SESSION['idcommunity']) . "&stream=" . urlencode($_SESSION['idstream']) . "&ui=true&server=" . urlencode(INSTALLFOLDER . '/') . "&loginkey=" . $key;
			} else {
				$returnlink = "authenticateok.php?key=" . $key;
			}
		?>
		<p><a href="<?php echo($returnlink); ?>"><?php echo($text['LOGINOKRETURN']); ?></a></p>
		<?php
	}
	?>
</div>
<div id="waitingarea">
	<div id="managanatitle">
    	<?php echo($text['LOGINTITLE']); ?>
    </div>
    <p><?php echo($text['LOGINWAIT']); ?></p>
</div>
</body>
</html>