<?php
/**
 * Managana server: check comments for a community.
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
			// check all comments
			$total = 0;
			$comments = queryDB("SELECT * FROM dis_comment WHERE cmt_community='$community' AND cmt_stream='" . $rowst['str_id'] . "'");
			for ($j=0; $j<mysql_num_rows($comments); $j++) {
				$total++;
				$rowcom = mysql_fetch_assoc($comments);
				if ($rowcom['cmt_status'] == '1') {
					echo('<wait>');
					echo('<stream><![CDATA[' . $rowst['str_title'] . ']]></stream>');
					echo('<name><![CDATA[' . $rowcom['cmt_username'] . ']]></name>');
					echo('<date><![CDATA[' . date("Y/m/d - H:i", (int)$rowcom['cmt_time']) . ']]></date>');
					echo('<index><![CDATA[' . $rowcom['cmt_index'] . ']]></index>');
					echo('<text><![CDATA[' . decodeApostrophe($rowcom['cmt_comment']) . ']]></text>');
					echo('</wait>');
				}
			}
			mysql_free_result($comments);
			echo('<stream>');
			echo('<name><![CDATA[' . $rowst['str_title'] . ']]></name>');
			echo('<comments><![CDATA[' . $total . ']]></comments>');
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
		$comments = queryDB("SELECT * FROM dis_comment WHERE cmt_community='$community' AND cmt_stream='$stream'");
		for ($j=0; $j<mysql_num_rows($comments); $j++) {
			$rowcom = mysql_fetch_assoc($comments);
			echo('<comment>');
			echo('<status><![CDATA[' . $rowcom['cmt_status'] . ']]></status>');
			echo('<name><![CDATA[' . $rowcom['cmt_username'] . ']]></name>');
			echo('<date><![CDATA[' . date("Y/m/d - H:i", (int)$rowcom['cmt_time']) . ']]></date>');
			echo('<index><![CDATA[' . $rowcom['cmt_index'] . ']]></index>');
			echo('<text><![CDATA[' . decodeApostrophe($rowcom['cmt_comment']) . ']]></text>');
			echo('</comment>');
		}
		mysql_free_result($comments);
		// finish output
		endOutput();
		break;
	case 'download':
		// create download file
		if (!is_dir('stats')) mkdir('stats');
		$file = fopen('./stats/' . $community . '_comments.csv', 'wb');
		fputs($file, ('"stream";"comment";"user name";"user ip";"user e-mail";"date";"time";"status";"time zone";"timestamp"' . "\n"));
		$status = array("approved", "waiting", "rejected");
		$comments = queryDB("SELECT * FROM dis_comment WHERE cmt_community='$community'");
		for ($j=0; $j<mysql_num_rows($comments); $j++) {
			$rowcom = mysql_fetch_assoc($comments);
			fputs($file, ('"' . $rowcom['cmt_stream'] . '";"' . str_replace('"', "'", decodeApostrophe($rowcom['cmt_comment'])) . '";"' . $rowcom['cmt_username'] . '";"' . $rowcom['cmt_ip'] . '";"' . $rowcom['cmt_user'] . '";' . date("Y/m/d", (int)$rowcom['cmt_time']) . ';' . date("H:i", (int)$rowcom['cmt_time']) . ';"' . $status[(int)$rowcom['cmt_status']] . '";"' . $rowcom['cmt_timezone'] . '";' . $rowcom['cmt_time'] . "\n"));
		}
		mysql_free_result($comments);
		fclose($file);
		// output
		startOutput();
		outputData('link', (INSTALLFOLDER . '/stats/' . $community . '_comments.csv'));
		noError();
		endOutput();
		break;
}
?>