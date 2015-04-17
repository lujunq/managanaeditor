<?php
/**
 * Managana server: manage remote control users
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
	exitOnError("ERUSER-7");
} else {
	switch($action) {
		case 'list':
			// list available users
			$userlist = queryDB("SELECT * FROM dis_remoteusers");
			startOutput();
			noError();
			if (mysql_num_rows($userlist) > 0) {
				for ($i=0; $i<mysql_num_rows($userlist); $i++) {
					$row = mysql_fetch_assoc($userlist);
					echo('<user>');
						echo('<publickey><![CDATA[' . $row['rus_publickey'] . ']]></publickey>');
						echo('<login><![CDATA[' . $row['rus_login'] . ']]></login>');
						echo('<password><![CDATA[' . $row['rus_password'] . ']]></password>');
					echo('</user>');
				}
			}
			mysql_free_result($userlist);
			endOutput();
			break;
		case 'save':
			// save an user list
			$total = postInt("total");
			// remove previous list
			queryDB("TRUNCATE TABLE dis_remoteusers");
			// add the received table
			for ($i=0; $i<$total; $i++) {
				queryDB("INSERT INTO dis_remoteusers (rus_login, rus_password, rus_publickey) VALUES ('" . postString('login' . $i) . "', '" . postString('password' . $i) . "', '" . postString('publickey' . $i) . "')");
			}
			// return
			startOutput();
			noError();
			endOutput();
			break;
		default:
			// action not recognized
			exitOnError("ERUSER-7");
			break;
	}
}
?>