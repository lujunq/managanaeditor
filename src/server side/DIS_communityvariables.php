<?php
/**
 * Managana server: list of a community variables.
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
$action = postString('action');
minimumLevel($level, 'admin');

// check action
switch ($action) {
	case "list":
		// list current community variables
		$list = queryDB("SELECT * FROM dis_communityvalues WHERE cvl_community='$community' ORDER BY cvl_name");
		startOutput();
		noError();
		for ($i=0; $i<mysql_num_rows($list); $i++) {
			$row = mysql_fetch_assoc($list);
			echo('<variable><name><![CDATA[' . decodeApostrophe($row['cvl_name']) . ']]></name><value><![CDATA[' . decodeApostrophe($row['cvl_value']) . ']]></value></variable>');
		}
		mysql_free_result($list);
		endOutput();
		break;
	case "save":
		// save current variables and values
		$count = postInt('count');
		// remove previous variables
		queryDB("DELETE FROM dis_communityvalues WHERE cvl_community='$community'");
		// retrieve each value and save it
		for ($i=0; $i<$count; $i++) {
			queryDB("INSERT INTO dis_communityvalues (cvl_community, cvl_name, cvl_value, cvl_time) VALUES ('$community', '" . postString('name' . $i) . "', '" . postString('value' . $i) . "', '" . time() . "')");
		}
		// return
		startOutput();
		noError();
		endOutput();
		break;
	default:
		exitOnError("ERCVAR-0");
		break;
}
?>