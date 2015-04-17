<?php
/**
 * Managana server: get a playlist information.
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

// get data
$id = postString('id');

// get playlist data
if (($level == "super") || ($level == "super") || ($level == "super")) {
	$query = "SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_id='$id' ORDER BY ply_index DESC LIMIT 1";
} else {
	$query = "SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_id='$id' AND ply_authorid='" . $_SESSION['usr_id'] . "' ORDER BY ply_index DESC LIMIT 1";
}
$playlist = queryDB($query);

// output data
if (mysql_num_rows($playlist) == 0) {
	exitOnError('ERPLAYLIST-1');
} else {
	$info = mysql_fetch_assoc($playlist);
	startOutput();
	noError();
	echo('<id>' . $info['ply_id'] . '</id>');
	echo('<meta>');
	echo('<title><![CDATA[' . decodeApostrophe($info['ply_title']) . ']]></title>');
	echo('<author id="' . $info['ply_authorid'] . '">' . $info['ply_author'] . '</author>');
	echo('<about>' . $info['ply_about'] . '</about>');
	echo('</meta>');
	echo('<elements>');
	$elements = queryDB("SELECT * FROM dis_element WHERE elm_plindex='" . $info['ply_index'] . "' AND elm_community='$community' ORDER BY elm_order ASC");
	if (mysql_num_rows($elements) > 0) {
		for ($j=0; $j<mysql_num_rows($elements); $j++) {
			$element = mysql_fetch_assoc($elements);
			echo('<element id="' . $element['elm_id'] . '" time="' . $element['elm_time'] . '" type="' . $element['elm_type'] . '" end="' . $element['elm_end'] . '">');
			$files = queryDB("SELECT * FROM dis_file WHERE fil_plindex='" . $info['ply_index'] . "' AND fil_community='$community' AND fil_element='" . $element['elm_id'] . "'");
			if (mysql_num_rows($files) > 0) {
				for ($k=0; $k<mysql_num_rows($files); $k++) {
					$file = mysql_fetch_assoc($files);
					echo('<file format="' . $file['fil_format'] . '" lang="' . $file['fil_lang'] . '" absolute="' . $file['fil_absolute'] . '" feed="' . $file['fil_feed'] . '" feedType="' . $file['fil_feedtype'] . '" field="' . $file['fil_field'] . '"><![CDATA[' . decodeApostrophe($file['fil_url']) . ']]></file>');
					}
				}
			mysql_free_result($files);
			$actions = queryDB("SELECT * FROM dis_action WHERE act_plindex='" . $info['ply_index'] . "' AND act_community='$community' AND act_element='" . $element['elm_id'] . "'");
			if (mysql_num_rows($actions) > 0) {
				for ($k=0; $k<mysql_num_rows($actions); $k++) {
					$action = mysql_fetch_assoc($actions);
					echo('<action time="' . $action['act_time'] . '" type="' . $action['act_type'] . '"><![CDATA[' . $action['act_action'] . ']]></action>');
				}
			} else {
				echo('<action />');
			}
			mysql_free_result($actions);
			echo('</element>');
		}
	}
	mysql_free_result($elements);
	echo('</elements>');
	endOutput();
}
mysql_free_result($playlist);
?>