<?php
/**
 * Managana server: check database connection.
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
$level = requestLevel();
switch ($level) {
	case 'super':
		$query = "SELECT * FROM dis_community ORDER BY com_title ASC";
		break;
	default:
		$query = "SELECT dis_community.* FROM dis_community, dis_usercommunity WHERE dis_community.com_id=dis_usercommunity.usc_community AND dis_usercommunity.usc_user='" . $_SESSION['usr_id'] . "' ORDER BY dis_community.com_title ASC";
		break;
}

// is provided ID valid?
$dbresult = queryDB($query);

if (mysql_num_rows($dbresult) == 0) {
	// the community id is taken
	mysql_free_result($dbresult);
	exitOnError('ERCOMMUNITY-1');
} else {
	// get communities information
	startOutput();
	noError();
	for ($i=0; $i<mysql_num_rows($dbresult); $i++) {
		$row = mysql_fetch_assoc($dbresult);
		echo('<community id="' . $row['com_id'] . '">' . $row['com_title'] . '</community>');
	}
	mysql_free_result($dbresult);
	endOutput();
}
?>