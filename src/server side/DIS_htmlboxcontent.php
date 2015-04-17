<?php
/**
 * Managana server: list of content available for html box
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'author');
@session_start();

// output
startOutput();
noError();
// navigate to media folder
chdir ("community");
chdir (".");
if (!is_dir($community . ".dis")) @mkdir($community . ".dis");
chdir ($community . ".dis");
chdir (".");
if (!is_dir("media")) @mkdir("media");
chdir ("media");
chdir (".");
// community content
if (!is_dir("community")) @mkdir("community");
chdir ("community");
chdir (".");
if (!is_dir("html")) @mkdir("html");
chdir ("html");
chdir (".");
$handler = opendir(".");
while ($file = readdir($handler)) {
	if ($file != "." && $file != "..") {
		if (is_dir($file)) echo('<community><![CDATA[' . $file . ']]></community>');
	}
}
closedir($handler);
// user content
chdir(".."); // return to community
chdir(".."); // return to media
if (!is_dir($_SESSION['usr_id'])) @mkdir($_SESSION['usr_id']);
chdir ($_SESSION['usr_id']);
chdir (".");
if (!is_dir("html")) @mkdir("html");
chdir ("html");
chdir (".");
$handler = opendir(".");
while ($file = readdir($handler)) {
	if ($file != "." && $file != "..") {
		if (is_dir($file)) echo('<user><![CDATA[' . $file . ']]></user>');
	}
}
closedir($handler);
// end output
endOutput();
?>