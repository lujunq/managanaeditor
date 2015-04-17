<?php
/**
 * Managana server: list media files.
 */

// get configuration
require_once('DIS_config.php');

// get database connection
require_once('DIS_database.php');

// get common functions
require_once('DIS_common.php');

// common functions for file handling
require_once('DIS_filecommon.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'author');

// get list options
$type = postString('type');
$cfiles = postBool('cfiles');

// does the requested folder exist?
chdir ("community");
chdir (".");

// community folder
if (!is_dir($community . ".dis")) @mkdir($community . ".dis");
chdir ($community . ".dis");
chdir (".");

// media folder
if (!is_dir("media")) @mkdir("media");
chdir ("media");
chdir (".");

// community or personal folder
if ($cfiles) {
	if (!is_dir("community")) @mkdir("community");
	chdir ("community");
	chdir (".");
} else {
	@session_start();
	if (!is_dir($_SESSION['usr_id'])) @mkdir($_SESSION['usr_id']);
	chdir ($_SESSION['usr_id']);
	chdir (".");
}

// file type folder
if (!is_dir($type)) @mkdir($type);
chdir ($type);
chdir (".");

// list files
startOutput();
noError();
if ($cfiles) outputData('cfiles', '1');
	else outputData('cfiles', '0');
$handler = opendir(".");
while ($file = readdir($handler)) {
	if ($file != "." && $file != "..") {
		if (checkType($file, $type)) {
			echo('<file subtype="' . subType($file) . '" size="' . filesize($file) . '" date="' . filectime($file) . '">' . $file . '</file>');
		}
	}
}
closedir($handler);
endOutput();
?>