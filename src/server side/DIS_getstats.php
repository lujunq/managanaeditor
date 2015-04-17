<?php
/**
 * Managana server: check statistics for a community.
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
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'editor');
$action = postString('action');

switch ($action) {
	case 'fullList':
		// prepare output
		startOutput();
		noError();
		// check the number of stats of each published community stream
		$streams = queryDB("SELECT t1.* FROM dis_stream t1 WHERE t1.str_community='$community' AND t1.str_index=(SELECT MAX(t2.str_index) FROM dis_stream t2 WHERE t1.str_id = t2.str_id AND t2.str_community='$community') ORDER BY t1.str_index DESC");
		for ($i=0; $i<mysql_num_rows($streams); $i++) {
			$rowst = mysql_fetch_assoc($streams);
			// check all stats
			$total = 0;
			$last = 0;
			$stats = queryDB("SELECT * FROM dis_stats WHERE sta_community='$community' AND sta_stream='" . $rowst['str_id'] . "' ORDER BY sta_time DESC");
			if (mysql_num_rows($stats) > 0) {
				$total = mysql_num_rows($stats);
				$rowsta = mysql_fetch_assoc($stats);
				$last = (int)$rowsta['sta_time'];
			}
			mysql_free_result($stats);
			$stattime = queryDB("SELECT AVG(tim_time) FROM dis_statstime WHERE tim_community='$community' AND tim_stream='" . $rowst['str_id'] . "'");
			$rowtime = mysql_fetch_array($stattime);
			mysql_free_result($stattime);
			echo('<stream>');
			echo('<name><![CDATA[' . $rowst['str_title'] . ']]></name>');
			echo('<total><![CDATA[' . $total . ']]></total>');
			if ($last == 0) echo('<last><![CDATA[]]></last>');
				else echo('<last><![CDATA[' . date("Y/m/d - H:i", $last) . ']]></last>');
			echo('<id><![CDATA[' . $rowst['str_id'] . ']]></id>');
			echo('<time>' . round($rowtime[0]) . '</time>');
			echo('</stream>');
		}
		mysql_free_result($streams);
		// finish output
		endOutput();
		break;
	case 'download':
		// create download file
		if (!is_dir('stats')) mkdir('stats');
		$file = fopen('./stats/' . $community . '_statistics.csv', 'wb');
		fputs($file, ('"stream";"user name";"user ip";"user e-mail";"date";"time";"time zone";"timestamp"' . "\n"));
		$stats = queryDB("SELECT * FROM dis_stats WHERE sta_community='$community'");
		for ($j=0; $j<mysql_num_rows($stats); $j++) {
			$rowsta = mysql_fetch_assoc($stats);
			fputs($file, ('"' . $rowsta['sta_stream'] . '";"' . $rowsta['sta_username'] . '";"' . $rowsta['sta_ip'] . '";"' . $rowsta['sta_user'] . '";' . date("Y/m/d", (int)$rowsta['sta_time']) . ';' . date("H:i", (int)$rowsta['sta_time']) . ';"' . $rowsta['sta_timezone'] . '";' . $rowsta['sta_time'] . "\n"));
		}
		mysql_free_result($stats);
		fclose($file);
		// output
		startOutput();
		outputData('link', (INSTALLFOLDER . '/stats/' . $community . '_statistics.csv'));
		noError();
		endOutput();
		break;
	case 'downloadnav':
		// create download file
		if (!is_dir('stats')) mkdir('stats');
		$file = fopen('./stats/' . $community . '_navigation.csv', 'wb');
		fputs($file, ('"stream";"user name";"user ip";"user e-mail";"time spent (s)";"next stream"' . "\n"));
		$stats = queryDB("SELECT * FROM dis_statstime WHERE tim_community='$community'");
		for ($j=0; $j<mysql_num_rows($stats); $j++) {
			$rowsta = mysql_fetch_assoc($stats);
			fputs($file, ('"' . $rowsta['tim_stream'] . '";"' . $rowsta['tim_username'] . '";"' . $rowsta['tim_ip'] . '";"' . $rowsta['tim_user'] . '";' . (int)$rowsta['tim_time'] . ';"' . $rowsta['tim_next'] . '"' . "\n"));
		}
		mysql_free_result($stats);
		fclose($file);
		// output
		startOutput();
		outputData('link', (INSTALLFOLDER . '/stats/' . $community . '_navigation.csv'));
		noError();
		endOutput();
		break;
}
?>