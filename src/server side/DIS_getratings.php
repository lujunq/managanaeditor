<?php
/**
 * Managana server: check ratings for a community.
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
		// check the number of comments of each published community stream
		$streams = queryDB("SELECT t1.* FROM dis_stream t1 WHERE t1.str_community='$community' AND t1.str_index=(SELECT MAX(t2.str_index) FROM dis_stream t2 WHERE t1.str_id = t2.str_id AND t2.str_community='$community') ORDER BY t1.str_index DESC");
		for ($i=0; $i<mysql_num_rows($streams); $i++) {
			$rowst = mysql_fetch_assoc($streams);
			// check all ratings
			$total = 0;
			$average = 0;
			$ratings = queryDB("SELECT * FROM dis_rate WHERE rat_community='$community' AND rat_stream='" . $rowst['str_id'] . "'");
			for ($j=0; $j<mysql_num_rows($ratings); $j++) {
				$total++;
				$rowrat = mysql_fetch_assoc($ratings);
				$average += (int)$rowrat['rat_rate'];
			}
			mysql_free_result($ratings);
			if ($total > 0) $average /= $total;
			echo('<stream>');
			echo('<name><![CDATA[' . $rowst['str_title'] . ']]></name>');
			echo('<total><![CDATA[' . $total . ']]></total>');
			echo('<average><![CDATA[' . $average . ']]></average>');
			echo('<id><![CDATA[' . $rowst['str_id'] . ']]></id>');
			echo('</stream>');
		}
		mysql_free_result($streams);
		// finish output
		endOutput();
		break;
	case 'streamList':
		// prepare output
		startOutput();
		noError();
		// check stream comments
		$stream = postString('stream');
		$ratings = queryDB("SELECT * FROM dis_rate WHERE rat_community='$community' AND rat_stream='$stream'");
		for ($j=0; $j<mysql_num_rows($ratings); $j++) {
			$rowrat = mysql_fetch_assoc($ratings);
			echo('<rate>');
			echo('<value><![CDATA[' . $rowrat['rat_rate'] . ']]></value>');
			echo('<name><![CDATA[' . $rowrat['rat_username'] . ']]></name>');
			echo('<date><![CDATA[' . date("Y/m/d - H:i", (int)$rowrat['rat_time']) . ']]></date>');
			echo('</rate>');
		}
		mysql_free_result($ratings);
		// finish output
		endOutput();
		break;
	case 'download':
		// create download file
		if (!is_dir('stats')) mkdir('stats');
		$file = fopen('./stats/' . $community . '_ratings.csv', 'wb');
		fputs($file, ('"stream";"rate";"user name";"user ip";"user e-mail";"date";"time";"time zone";"timestamp"' . "\n"));
		$ratings = queryDB("SELECT * FROM dis_rate WHERE rat_community='$community'");
		for ($j=0; $j<mysql_num_rows($ratings); $j++) {
			$rowrat = mysql_fetch_assoc($ratings);
			fputs($file, ('"' . $rowrat['rat_stream'] . '";' . $rowrat['rat_rate'] . ';"' . $rowrat['rat_username'] . '";"' . $rowrat['rat_ip'] . '";"' . $rowrat['rat_user'] . '";' . date("Y/m/d", (int)$rowrat['rat_time']) . ';' . date("H:i", (int)$rowrat['rat_time']) . ';"' . $rowrat['rat_timezone'] . '";' . $rowrat['rat_time'] . "\n"));
		}
		mysql_free_result($ratings);
		fclose($file);
		// output
		startOutput();
		outputData('link', (INSTALLFOLDER . '/stats/' . $community . '_ratings.csv'));
		noError();
		endOutput();
		break;
}
?>