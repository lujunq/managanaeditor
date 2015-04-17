<?php
// Managana update script

// install step
$step = 1;

// initial message
$message = "Welcome to Managana. The update script found the <b>[OLDVERSION]</b> version installed on your server. You may update to version <b>[NEWVERSION]</b> by clicking the button below. Make sure you have a full backup of your files and the database before proceeding.";

// version information
$installedversion = 0;	// version currently installed on server
$currentversion = 6;	// current version number
$versiontable = array();
$versiontable[0] = '1.0.5';
$versiontable[1] = '1.1.0';
$versiontable[2] = '1.2.0';
$versiontable[3] = '1.3.0';
$versiontable[4] = '1.4.0';
$versiontable[5] = '1.5.0 (April 2013)';
$versiontable[6] = '1.6.0 (August 2013)';

// check user input
if (isset($_POST['ac'])) {
	$ac = trim($_POST['ac']);
	switch ($ac) {
		case 'update':
			if (is_file('DIS_config.php')) {
				require_once('DIS_config.php');
				// check database connection
				$dbLink = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
				if ($dbLink === false) {
					$message = "An error was found while updating you Managana installation. Please try again - you may also need to copy the installation files over the previous ones again.";
					$step = 0;
				} else {
					$dbBank = @mysql_select_db(DB_NAME, $dbLink);
					if ($dbBank === false) {
						$message = "An error was found while updating you Managana installation. Please try again - you may also need to copy the installation files over the previous ones again.";
						$step = 0;
					} else {
						// check installed version
						$checkVersion = mysql_query("SELECT * FROM dis_options WHERE opt_name='MANAGANAVERSION'");
						if (mysql_num_rows($checkVersion) > 0) {
							$row = mysql_fetch_assoc($checkVersion);
							$installedversion = (int)$row['opt_value'];
						}
						mysql_free_result($checkVersion);
						if ($currentversion <= $installedversion) {
							$message = "An error was found while updating you Managana installation. Please try again - you may also need to copy the installation files over the previous ones again.";
							$step = 0;
						} else {
							// run database updates
							for ($i=($installedversion + 1); $i<=$currentversion; $i++) {
								if (is_file('./dbupdate/' . $i . '.sql')) {
									$sqlfile = file_get_contents('./dbupdate/' . $i . '.sql');
									$sqlstatements = explode("\n", $sqlfile);
									for ($j=0; $j<sizeof($sqlstatements); $j++) {
										@mysql_query($sqlstatements[$j]);
									}
								}
							}
							$step = 2;
							@mysql_query("UPDATE dis_options SET opn_value='$currentversion' WHERE opn_name='MANAGANAVERSION'");
							$message = "Your Managana installation was updated to version " . $versiontable[$currentversion] . ".";
							// remove update files
							@unlink('managana.sql');
							@unlink('install.php');
							@unlink('update.php');
							@unlink('manage.php');
							for ($idb=0; $idb<=$currentversion; $idb++) {
								@unlink('./dbupdate/' . $idb . '.sql');
							}
							@unlink('dbupdate');
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
							
						}
					}
				}
			} else {
				$message = "An error was found while updating you Managana installation. Please try again - you may also need to copy the installation files over the previous ones again.";
				$step = 0;
			}
			break;
	}
} else {
	// check version
	if (is_file('DIS_config.php')) {
		require_once('DIS_config.php');
		// check database connection
		$dbLink = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		if ($dbLink === false) {
			$message = "Managana installation could not be detected. Are you sure you copied the updated version over the installed one? If you wish to start a new installation, run the <a href='install.php'>install.php</a> script.";
			$step = 0;
		} else {
			$dbBank = @mysql_select_db(DB_NAME, $dbLink);
			if ($dbBank === false) {
				$message = "Managana installation could not be detected. Are you sure you copied the updated version over the installed one? If you wish to start a new installation, run the <a href='install.php'>install.php</a> script.";
				$step = 0;
			} else {
				$checkVersion = mysql_query("SELECT * FROM dis_options WHERE opt_name='MANAGANAVERSION'");
				if (mysql_num_rows($checkVersion) > 0) {
					$row = mysql_fetch_assoc($checkVersion);
					$installedversion = (int)$row['opt_value'];
				}
				mysql_free_result($checkVersion);
				// check version to update
				if ($currentversion == $installedversion) {
					$message = "You are currently running the updated version of Managana. There is no need to update.";
					$step = 0;
				} else if ($currentversion < $installedversion) {
					$message = "The installed version of Managana is more recent than the one you are trying to install. Are you sure this is really the newer install/update package of Managana?";
					$step = 0;
				} else {
					$message = str_replace("[OLDVERSION]", $versiontable[$installedversion], $message);
					$message = str_replace("[NEWVERSION]", $versiontable[$currentversion], $message);
				}
			}
		}
	} else {
		// no installation found
		$message = "Managana installation could not be detected. Are you sure you copied the updated version over the installed one? If you wish to start a new installation, run the <a href='install.php'>install.php</a> script.";
		$step = 0;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Managana update</title>
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
<?php if ($step == 0) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana update error
    </div>
    <p><?php echo($message); ?></p>
</div>
<?php } else if ($step == 1) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana update
    </div>
    <p><?php echo($message); ?></p>
    <form method="post" name="update">
    	<table width="100%">
            <tr>
            	<td><input type="hidden" name="ac" value="update" /></td>
                <td><input type="submit" value="update to version <?php echo($versiontable[$currentversion]); ?>" /></td>
            </tr>
        </table>
    </form>
</div>
<?php } else if ($step == 2) { ?>
<div id="textarea">
	<div id="titlearea">
    	Managana updated!
    </div>
    <p><?php echo($message); ?></p>
</div>
<?php } ?>
</body>
</html>