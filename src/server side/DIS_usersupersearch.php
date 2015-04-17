<?php
/**
 * Managana server: search for community users
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

// get search data
$error = "";
$terms = postString("terms");

if ($terms == "") {
	// not enough data
	$error = "ERSEARCH-0";
} else {
	// look for users
	$check = queryDB("SELECT * FROM dis_user WHERE usr_email LIKE '%$terms%' OR usr_name LIKE '%$terms%'");
	if (mysql_num_rows($check) == 0) {
		$error = "ERSEARCH-1";
	} else {
		// write search results
		startOutput();
		noError();
		for ($i=0; $i<mysql_num_rows($check); $i++) {
			$row = mysql_fetch_assoc($check);
			$label = $row['usr_name'] . " / " . $row['usr_email'];
			echo("<result>");
				echo("<label><![CDATA[$label]]></label>");
				echo("<index><![CDATA[" . $row['usr_index'] . "]]></index>");
				echo("<id><![CDATA[" . $row['usr_id'] . "]]></id>");
				echo("<name><![CDATA[" . $row['usr_name'] . "]]></name>");
				echo("<email><![CDATA[" . $row['usr_email'] . "]]></email>");
				echo("<level><![CDATA[" . $row['usr_level'] . "]]></level>");
			echo("</result>");
		}
		endOutput();
	}
	mysql_free_result($check);
}
	
// output
if ($error != "") {
	exitOnError($error);
}
?>