<?php
// Managana installation script

// current version
$currentversion = 6;
$versionabout = '1.6.0 (August 2013)';
// install step
$step = 1;
// initial message
$message = "Welcome to Managana $versionabout. In order to install the software on your webserver you must first provide some information about your database. You must have a database already available to proceed - prefer clean, brand new databases to avoid problems. If you already have Managana running on this server you may be looking for an update. Run the <a href='update.php'>update.php</a> script instead.";
// any errors found?
$error = false;		// any error found?

// check user input
if (isset($_POST['ac'])) {
	$ac = trim($_POST['ac']);
	switch ($ac) {
		case 'step1':
			$dbHost = trim($_POST['dbHost']);
			$dbName = trim($_POST['dbName']);
			$dbUser = trim($_POST['dbUser']);
			$dbPass = trim($_POST['dbPass']);
			// check connection
			$dbLink = @mysql_connect($dbHost, $dbUser, $dbPass);
			if ($dbLink === false) {
				$error = true;
			} else {
				$dbBank = @mysql_select_db($dbName, $dbLink);
				if ($dbBank === false) $error = true;
			}
			// connection ok?
			if ($error) {
				$message = "The provided information could not be checked. Is the database server ok? Is the database name provided available? Is the user information correct? Does the user have the correct permissions to access the requested database? Please try again.";
			} else {
				$step = 2;
				$message = "The database is checked! To proceed, please provide the information below. On install url, provide the full url you'll use to access your new Managana installation.";
			}
			break;
		case 'step2':
			$dbHost = trim($_POST['dbHost']);
			$dbName = trim($_POST['dbName']);
			$dbUser = trim($_POST['dbUser']);
			$dbPass = trim($_POST['dbPass']);
			// check connection
			$dbLink = @mysql_connect($dbHost, $dbUser, $dbPass);
			if ($dbLink === false) {
				$error = true;
			} else {
				$dbBank = @mysql_select_db($dbName, $dbLink);
				if ($dbBank === false) $error = true;
			}
			if ($error) {
				// database error
				$step = 1;
				$message = "The provided information could not be checked. Is the database server ok? Is the database name provided available? Is the user information correct? Does the user have the correct permissions to access the requested database? Please try again.";
			} else {
				// move to step 2
				$step = 2;
				// check the provided data
				$installURL = trim($_POST['installURL']);
				$admName = trim($_POST['admName']);
				$admMail = trim($_POST['admMail']);
				$admMail2 = trim($_POST['admMail2']);
				$admPass = trim($_POST['admPass']);
				$admPass2 = trim($_POST['admPass2']);
				// install url
				if ($installURL == "") {
					$error = true;
					$message = "You must provide the URL of the address you want to install Managana.";
				} else {
					if (substr($installURL, 0, 7) != 'http://') $installURL = 'http://' . $installURL;
					if (substr($installURL, -1, 1) != "/") $installURL = $installURL . "/";
				}
				// admin name
				if (!$error) {
					if ($admName == "") {
						$error = true;
						$message = "You must provide the administrator full name.";
					}
				}
				// admin email
				if (!$error) {
					if (($admMail == "") || ($admMail2 == "")) {
						$error = true;
						$message = "You must provide the administrator e-mail and its confirmation.";
					} else if ($admMail != $admMail2) {
						$error = true;
						$message = "The administrator e-mail and its confirmation do not match.";
					} else if (!filter_var($admMail, FILTER_VALIDATE_EMAIL)) {
						$error = true;
						$message = "Plase check the provided administrator e-mail address. It contains errors.";
					}
				}
				// admin password
				if (!$error) {
					if (($admPass == "") || ($admPass2 == "")) {
						$error = true;
						$message = "You must provide the administrator password and its confirmation.";
					} else if ($admPass != $admPass2) {
						$error = true;
						$message = "The administrator password and its confirmation do not match.";
					} else if (strlen($admPass) < 6) {
						$error = true;
						$message = "Plase create an administrator password of at least 6 characters";
					}
				}
			}
			// move to step 3?
			if (!$error) $step = 3;
			break;
		case 'step3':
			// get data
			$dbHost = trim($_POST['dbHost']);
			$dbName = trim($_POST['dbName']);
			$dbUser = trim($_POST['dbUser']);
			$dbPass = trim($_POST['dbPass']);
			$installURL = trim($_POST['installURL']);
			$admName = trim($_POST['admName']);
			$admMail = trim($_POST['admMail']);
			$admPass = trim($_POST['admPass']);
			// start installation
			// check connection
			$dbLink = @mysql_connect($dbHost, $dbUser, $dbPass);
			if ($dbLink === false) {
				$error = true;
			} else {
				$dbBank = @mysql_select_db($dbName, $dbLink);
				if ($dbBank === false) $error = true;
			}
			if ($error) {
				// database error
				$step = 1;
				$message = "The provided information could not be checked. Is the database server ok? Is the database name provided available? Is the user information correct? Does the user have the correct permissions to access the requested database? Please try again.";
			} else {
				// open the database sql file
				if (is_file('managana.sql')) {
					$sqlfile = file_get_contents('managana.sql');
					if ($sqlfile === false) {
						$message = "Some of the required files for the installation were not found. Did you copy all the Managana files correctly to your server?";
						$step = 1;
					} else {
						// process sql found on file
						$sqlstatements = explode("\n", $sqlfile);
						for ($i=0; $i<sizeof($sqlstatements); $i++) {
							if (!$error) {
								$result = mysql_query($sqlstatements[$i]);
								if ($result === false) {
									$error = true;
									$message = "We found errors while creating the Managana database. Please check the provided database, clean it and try again.";
									$step = 1;
								}
							}
						}
						// create database values
						if (!$error) {
							// update configuration
							@mysql_query("UPDATE dis_options set opt_value='$dbName' WHERE opt_name='DB_NAME'");
							@mysql_query("UPDATE dis_options set opt_value='$dbUser' WHERE opt_name='DB_USER'");
							@mysql_query("UPDATE dis_options set opt_value='$dbPass' WHERE opt_name='DB_PASSWORD'");
							@mysql_query("UPDATE dis_options set opt_value='$dbHost' WHERE opt_name='DB_HOST'");
							@mysql_query("UPDATE dis_options set opt_value='$admMail' WHERE opt_name='MAILSENDER'");
							@mysql_query("UPDATE dis_options set opt_value='$installURL' WHERE opt_name='INSTALLFOLDER'");
							// add admin user
							$id = "";
							for ($i=0; $i<strlen($admMail); $i++) {
								$char = substr($admMail, $i, 1);
								if ($char != urlencode($char)) $char = "_";
								$id .= $char;
							}
							@mysql_query("INSERT INTO dis_user (usr_id, usr_email, usr_pass, usr_name, usr_level) VALUES ('$id', '$admMail', '" . md5($admPass) . "', '$admName', 'super')");
							// write configuration files
							$check = @mysql_query("SELECT * FROM dis_options");
							$output = "<?php\n";
							$output .= "// Managana configuration file\n";
							$outputreader = '<?xml version="1.0" encoding="utf-8"?><data>';
							for ($i=0; $i<mysql_num_rows($check); $i++) {
								$row = mysql_fetch_assoc($check);
								$output .= 'define("' . $row['opt_name'] . '", "' . $row['opt_value'] . '");' . "\n";
								if (strrpos($row['opt_file'], "managana") === false) {
									// do not write on xml config file
								} else {
									$outputreader .= '<config><name>' . $row['opt_xmlname'] . '</name><value>' . $row['opt_value'] . '</value></config>';
								}
							}
							$output .= "?>";
							$outputreader .= '</data>';
							mysql_free_result($check);
							$file = fopen("DIS_config.php", 'wb');
							fputs($file, $output);
							fclose($file);
							$file = fopen("managanaconfig.xml", 'wb');
							fputs($file, $outputreader);
							fclose($file);
							
							
							// step 4: finish
							$step = 4;
							$subject = "Managana installation results";
							$message = "Welcome to Managana!\n";
							$message .= "Your Managana installation was successfully completed. Below you'll find some useful information about it.\n";
							$message .= "reader access url: $installURL \n";
							$message .= "editor access url: " . $installURL . "editor.php \n";
							$message .= "admin full name: $admName \n";
							$message .= "admin e-mail: $admMail \n";
							$message .= "admin password: $admPass";
							$headers = 'From: ' . $admMail . "\r\n" . 'Reply-To: ' . $admMail . "\r\n" . 'X-Mailer: PHP/' . phpversion();
							if (@mail($admMail, $subject, $message, $headers)) {
								$message = "Welcome to Managana! Your installation was completed successfully. Below you'll find some useful information about it. This data was also sent to the administrator e-mail address.";
							} else {
								$message = "Welcome to Managana! Your installation was completed successfully. Below you'll find some useful information about it.";
							}
							// try to remove the installation files
							@unlink('managana.sql');
							@unlink('install.php');
							@unlink('update.php');
							for ($idb=0; $idb<=$currentversion; $idb++) {
								@unlink('./dbupdate/' . $idb . '.sql');
							}
							@unlink('dbupdate');
						}
					}
				} else {
					$message = "Some of the required files for the installation were not found. Did you copy all the Managana files correctly to your server?";
					$step = 1;
				}
			}
			break;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Managana install</title>
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
#titlearea {
	width: 440px;
	text-align: right;
	font-size: 16px;
	color:#444444;
	font-weight:bold;
	border-bottom:#1f1f1f 1px solid;
}
#textarea {
	position: absolute;
	top: 50px;
	left: 50%;
	width: 440px;
	margin-left: -220px;
	background-color:#000000;
	font-family: Verdana, Geneva, sans-serif;
	display: block;
	border-bottom:#1f1f1f 1px solid;
	background:url(pics/bgImage.jpg) no-repeat top;
}
#textarea h1 {
	font-size: 16px;
	color:#444444;
	font-weight:bold;
}
#textarea a{
	color:#444444;
	font-weight:bold;
	text-decoration:none;
	padding-left:14px;
	background:url(pics/arrow.png) no-repeat;
}
#textarea a:hover{
	color:#959595;
}
#textarea input{
	background-color:#1f1f1f;
	border:#959595 1px solid;
	color:#CCCCCC;
	margin:2px;
}
</style>
</head>
<body>
<?php if ($step == 1) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana installation, step 1 of 3
    </div>
    <p><?php echo($message); ?></p>
    <form method="post" name="step1">
    	<table width="100%">
        	<tr>
            	<td width="160">the database server</td>
                <td><input type="text" style="width:100%;" name="dbHost" id="dbHost" value="<?php if (isset($dbHost)) echo($dbHost); ?>"/></td>
            </tr>
            <tr>
            	<td>the database name</td>
                <td><input type="text" style="width:100%;" name="dbName" id="dbName" value="<?php if (isset($dbName)) echo($dbName); ?>"/></td>
            </tr>
            <tr>
            	<td>the database user</td>
                <td><input type="text" style="width:100%;" name="dbUser" id="dbUser" value="<?php if (isset($dbUser)) echo($dbUser); ?>"/></td>
            </tr>
            <tr>
            	<td>the database password</td>
                <td><input type="text" style="width:100%;" name="dbPass" id="dbPass" value="<?php if (isset($dbPass)) echo($dbPass); ?>"/></td>
            </tr>
            <tr>
            	<td><input type="hidden" name="ac" value="step1" /></td>
                <td><input type="submit" value="check database information" /></td>
            </tr>
        </table>
    </form>
</div>
<?php } else if ($step == 2) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana installation, step 2 of 3
    </div>
    <p><?php echo($message); ?></p>
    <form method="post" name="step2">
    	<table width="100%">
        	<tr>
            	<td width="160">install url</td>
                <td><input type="text" style="width:100%;" name="installURL" id="installURL" value="<?php if (isset($installURL)) { echo($installURL); } else { echo("http://"); } ?>"/></td>
            </tr>
            <tr>
            	<td>admin user full name</td>
                <td><input type="text" style="width:100%;" name="admName" id="admName" value="<?php if (isset($admName)) echo($admName); ?>"/></td>
            </tr>
            <tr>
            	<td>admin user e-mail</td>
                <td><input type="text" style="width:100%;" name="admMail" id="admMail" value="<?php if (isset($admMail)) echo($admMail); ?>"/></td>
            </tr>
            <tr>
            	<td>admin user e-mail (again)</td>
                <td><input type="text" style="width:100%;" name="admMail2" id="admMail2"/></td>
            </tr>
            <tr>
            	<td>admin user password</td>
                <td><input type="password" style="width:100%;" name="admPass" id="admPass"/></td>
            </tr>
            <tr>
            	<td>admin user password (again)</td>
                <td><input type="password" style="width:100%;" name="admPass2" id="admPass2"/></td>
            </tr>
            <tr>
            	<td><input type="hidden" name="ac" value="step2" /><input type="hidden" name="dbHost" value="<?php echo($dbHost); ?>" /><input type="hidden" name="dbUser" value="<?php echo($dbUser); ?>" /><input type="hidden" name="dbName" value="<?php echo($dbName); ?>" /><input type="hidden" name="dbPass" value="<?php echo($dbPass); ?>" /></td>
                <td><input type="submit" value="install Managana" /></td>
            </tr>
        </table>
    </form>
</div>
<?php } else if ($step == 3) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana installation, step 3 of 3
    </div>
    <p>Plase review the information below. If everything is ok, you may complete the Maganana installation. If not, please start again.</p>
    <form method="post" name="step2">
    	<table width="100%">
        	<tr>
            	<td width="160">the database server</td>
                <td><b><?php echo($dbHost); ?></b></td>
            </tr>
            <tr>
            	<td>the database name</td>
                <td><b><?php echo($dbName); ?></b></td>
            </tr>
            <tr>
            	<td>the database user</td>
                <td><b><?php echo($dbUser); ?></b></td>
            </tr>
            <tr>
            	<td>the database password</td>
                <td><b><?php echo($dbPass); ?></b></td>
            </tr>
            <tr>
            	<td width="160">install url</td>
                <td><b><?php echo($installURL); ?></b></td>
            </tr>
            <tr>
            	<td>admin user full name</td>
                <td><b><?php echo($admName); ?></b></td>
            </tr>
            <tr>
            	<td>admin user e-mail</td>
                <td><b><?php echo($admMail); ?></b></td>
            </tr>
            <tr>
            	<td>admin user password</td>
                <td><b><?php echo($admPass); ?></b></td>
            </tr>
            <tr>
            	<td><input type="hidden" name="ac" value="step3" /><input type="hidden" name="dbHost" value="<?php echo($dbHost); ?>" /><input type="hidden" name="dbUser" value="<?php echo($dbUser); ?>" /><input type="hidden" name="dbName" value="<?php echo($dbName); ?>" /><input type="hidden" name="dbPass" value="<?php echo($dbPass); ?>" /><input type="hidden" name="installURL" value="<?php echo($installURL); ?>" /><input type="hidden" name="admName" value="<?php echo($admName); ?>" /><input type="hidden" name="admMail" value="<?php echo($admMail); ?>" /><input type="hidden" name="admPass" value="<?php echo($admPass); ?>" /></td>
                <td><input type="submit" value="complete Managana installation" /></td>
            </tr>
        </table>
    </form>
    <br /><a href="install.php">if the data is not correct, click here to restart installation</a>
</div>
<?php } else if ($step == 4) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana installation finished
    </div>
    <p><?php echo($message); ?></p>
    <table width="100%">
		<tr><td width="160"><b>reader access url</b></td><td><a href="<?php echo($installURL); ?>" target="_blank"><?php echo($installURL); ?></a></td></tr>
		<tr><td width="160"><b>editor interface access url</b></td><td><a href="<?php echo($installURL); ?>editor.php" target="_blank"><?php echo($installURL); ?>editor.php</a></td></tr>
		<tr><td width="160"><b>admin name</b></td><td><?php echo($admName); ?></td></tr>
		<tr><td width="160"><b>admin e-mail</b></td><td><?php echo($admMail); ?></td></tr>
		<tr><td width="160"><b>admin password</b></td><td><?php echo($admPass); ?></td></tr>
    </table>
    <p>If you want your Managana installation to handle Facebook external feeds, you must download the <a href="https://developers.facebook.com/docs/reference/php/" target="_blank">Facebook PHP SDK</a> and copy its files to the "feed" folder. You must also <a href="https://developers.facebook.com/apps" target="_blank">create an application on Facebook</a> and provide the app id and secret on the "Facebook application" editor configuration.</p>
    <?php if (is_file('install.php') || is_file('managana.sql')) { ?>
		<p>Also, please notice that you'll need to manually remove the <b>install.php</b> and the <b>managana.sql</b> files in order to finish the installation process.</p>
	<?php } ?>
</div>
<?php } ?>
</body>
</html>