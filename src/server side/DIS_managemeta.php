<?php
/**
 * Managana server: meta data field manipulation.
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
minimumLevel($level, 'admin');
@session_start();

// check action
$action = postString('action');
switch ($action) {
	case 'new':
		$metaname = postString('metaname');
		queryDB("INSERT INTO dis_meta (met_community, met_name) VALUES ('$community', '$metaname')");
		break;
	case 'list':
		// do nothing, just list
		break;
	case 'update':
		$metaname = postString('metaname');
		$metaid = postString('metaid');
		// update community meta field
		queryDB("UPDATE dis_meta SET met_name='$metaname' WHERE met_community='$community' AND met_index='$metaid'");
		// update streams meta field names
		queryDB("UPDATE dis_streammeta set smt_metaname='$metaname' WHERE smt_community='$community' AND smt_metaindex='$metaid'");
		break;
}

// output
startOutput();
noError();
$list = queryDB("SELECT * FROM dis_meta WHERE met_community='$community'");
for ($i=0; $i<mysql_num_rows($list); $i++) {
	$row = mysql_fetch_assoc($list);
	echo('<meta id="' . $row['met_index'] . '"><![CDATA[' . $row['met_name'] . ']]></meta>');
}
mysql_free_result($list);
endOutput();
?>