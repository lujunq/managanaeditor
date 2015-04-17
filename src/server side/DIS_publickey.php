<?php
/**
 * Managana server: manage reserved public keys
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

// get data
$action = postString("action");

// check action
if ($action == "") {
	// not enough data
	exitOnError("ERPKEY-0");
} else {
	switch($action) {
		case 'list':
			// list available users
			$userlist = queryDB("SELECT * FROM dis_publickey");
			startOutput();
			noError();
			if (mysql_num_rows($userlist) > 0) {
				for ($i=0; $i<mysql_num_rows($userlist); $i++) {
					$row = mysql_fetch_assoc($userlist);
					echo('<pkey>');
						echo('<publickey><![CDATA[' . $row['pky_key'] . ']]></publickey>');
						echo('<password><![CDATA[' . $row['pky_pass'] . ']]></password>');
					echo('</pkey>');
				}
			}
			mysql_free_result($userlist);
			endOutput();
			break;
		case 'save':
			// save an user list
			$total = postInt("total");
			// remove previous list
			queryDB("TRUNCATE TABLE dis_publickey");
			// add the received table
			for ($i=0; $i<$total; $i++) {
				queryDB("INSERT INTO dis_publickey (pky_pass, pky_key) VALUES ('" . postString('password' . $i) . "', '" . postString('publickey' . $i) . "')");
			}
			// return
			startOutput();
			noError();
			endOutput();
			break;
		default:
			// action not recognized
			exitOnError("ERPKEY-0");
			break;
	}
}
?>