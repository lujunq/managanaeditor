<?php
/**
 * Managana server: current content editions.
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

// get information
$stream = postString('stream');
$streamname = postString('streamname');
$numplaylist = postInt('numplaylist');

// get all playlists
$playlists = array();


// release old data
$now = time();
$old = $now - (5 * 60);	// 5 minutes before
queryDB("DELETE FROM dis_current WHERE cur_time<'$old'");
queryDB("DELETE FROM dis_current WHERE cur_user='" . $_SESSION['usr_id'] . "'");

// add new stream data
if ($stream != "") {
	$check = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='stream' AND cur_id='$stream'");
	if (mysql_num_rows($check) == 0) {
		queryDB("INSERT INTO dis_current (cur_community, cur_ref, cur_user, cur_username, cur_id, cur_idname, cur_time) VALUES ('$community', 'stream', '" . $_SESSION['usr_id'] . "', '" . $_SESSION['usr_name'] . "', '$stream', '$streamname', '$now')");
	}
	mysql_free_result($check);
}

// add new playlist data
for ($i=0; $i<$numplaylist; $i++) {
	$check = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='playlist' AND cur_id='" . postString('pl' . $i) . "'");
	if (mysql_num_rows($check) == 0) {
		queryDB("INSERT INTO dis_current (cur_community, cur_ref, cur_user, cur_username, cur_id, cur_idname, cur_time) VALUES ('$community', 'playlist', '" . $_SESSION['usr_id'] . "', '" . $_SESSION['usr_name'] . "', '" . postString('pl' . $i) . "', '" . postString('plname' . $i) . "', '$now')");
	}
	mysql_free_result($check);
}

// return usage date
$result = queryDB("SELECT * FROM dis_current WHERE cur_community='$community'");

// start output
startOutput();
noError();

// output all data available about current community
for ($i=0; $i<mysql_num_rows($result); $i++) {
	$row = mysql_fetch_assoc($result);
	echo('<' . $row['cur_ref'] . '>');
	echo('<id><![CDATA[' . $row['cur_id'] . ']]></id>');
	echo('<idname><![CDATA[' . $row['cur_idname'] . ']]></idname>');
	echo('<user><![CDATA[' . $row['cur_user'] . ']]></user>');
	echo('<username><![CDATA[' . $row['cur_username'] . ']]></username>');
	echo('<time>' . $row['cur_time'] . '</time>');
	echo('</' . $row['cur_ref'] . '>');
}
mysql_free_result($result);

// end output
endOutput();
?>