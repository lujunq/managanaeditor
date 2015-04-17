<?php
/**
 * Managana server: list community dis folders available.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// community handle common functions
require_once('DIS_communitycommon.php');

// check for user access level
$level = requestLevel();
minimumLevel($level, 'admin');

// does the communities folder exist?
if (!is_dir("community")) @mkdir("community");
chdir ("community");
chdir (".");

// start list output
startOutput();
noError();

// list all .dis folders found
$handler = opendir(".");
while ($folder = readdir($handler)) {
	if ($folder != "." && $folder != "..") {
		if (is_dir($folder)) {
			if (substr($folder, -4, 4) == ".dis") {
				// get the dis name
				$disname = substr($folder, 0, (strlen($folder) - 4));
				// is the dis folder already a community defined on server?
				$dbfind = queryDB("SELECT * FROM dis_community WHERE com_id='$disname'");
				if (mysql_num_rows($dbfind) > 0) {
					// a community to update
					echo('<dis id="' . $folder . '" newcom="false">' . $disname . '</dis>');
				} else {
					// a new community
					echo('<dis id="' . $folder . '" newcom="true">' . $disname . '</dis>');
				}
				mysql_free_result($dbfind);
			}
		}
	}
}
closedir($handler);

// end list output
endOutput();
?>