<?php
/**
 * Managana server: bulk actions to apply on several streams at once.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// get community common functions
require_once('DIS_communitycommon.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'editor');

// list streams
$ac = postString('ac');
switch ($ac) {
	case 'setguide': // set stream guides
		$stream = postString('stream');
		$lower = explode("|st|", postString('lower'));
		$upper = explode("|st|", postString('upper'));
		if (sizeof($lower) > 0) {
			for ($i=0; $i<sizeof($lower); $i++) {
				queryDB("UPDATE dis_stream SET str_guidedown='$stream' WHERE str_id='" . $lower[$i] . "' AND str_community='$community'");
				publishStream($lower[$i], $community);
			}
		}
		if (sizeof($upper) > 0) {
			for ($i=0; $i<sizeof($upper); $i++) {
				queryDB("UPDATE dis_stream SET str_guideup='$stream' WHERE str_id='" . $upper[$i] . "' AND str_community='$community'");
				publishStream($upper[$i], $community);
			}
		}
		startOutput();
		noError();
		endOutput();
		break;
	case 'navsequence':
		$sequence = explode("|", postString('sequence'));
		$axis = postString('axis');
		$loop = postBool('loop');
		for ($i=0; $i<sizeof($sequence); $i++) {
			if ($i == 0) {
				if ($loop) {
					queryDB("UPDATE dis_stream SET str_" . $axis . "next='" . $sequence[$i + 1] . "', str_" . $axis . "prev='" . $sequence[sizeof($sequence) - 1] . "' WHERE str_id='" . $sequence[$i] . "'");
				} else {
					queryDB("UPDATE dis_stream SET str_" . $axis . "next='" . $sequence[$i + 1] . "' WHERE str_id='" . $sequence[$i] . "'");
				}
			} else if ($i == (sizeof($sequence) - 1)) {
				if ($loop) {
					queryDB("UPDATE dis_stream SET str_" . $axis . "next='" . $sequence[0] . "', str_" . $axis . "prev='" . $sequence[$i - 1] . "' WHERE str_id='" . $sequence[$i] . "'");
				} else {
					queryDB("UPDATE dis_stream SET str_" . $axis . "prev='" . $sequence[$i - 1] . "' WHERE str_id='" . $sequence[$i] . "'");
				}
			} else {
				queryDB("UPDATE dis_stream SET str_" . $axis . "next='" . $sequence[$i + 1] . "', str_" . $axis . "prev='" . $sequence[$i - 1] . "' WHERE str_id='" . $sequence[$i] . "'");
			}
			publishStream($sequence[$i], $community);
		}
		startOutput();
		noError();
		endOutput();
		break;
	default: // unrecognized action
		exitOnError('ERGUIDE-0');
		break;
}
?>