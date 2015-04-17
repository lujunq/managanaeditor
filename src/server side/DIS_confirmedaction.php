<?php
/**
 * Managana server: actions that need confirmation.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');
require_once('DIS_communitycommon.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
@session_start();
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'author');

// check action
$action = postString('action');
switch ($action) {
	case 'deleteplaylist':
		$playlist = postString('playlist');
		$check = queryDB("SELECT * FROM dis_playlist WHERE ply_community='$community' AND ply_id='$playlist' ORDER BY ply_index DESC LIMIT 1");
		if (mysql_num_rows($check) == 0) {
			// no playlist found
			mysql_free_result($check);
			exitOnError('ERCONFIRM-0');
		} else {
			if ($level == 'author') {
				// user must own the playlist
				$row = mysql_fetch_assoc($check);
				if ($row['ply_authorid'] == $_SESSION['usr_id']) {
					// remove the palylist
					queryDB("DELETE FROM dis_playlist WHERE ply_community='$community' AND ply_id='$playlist'");
					queryDB("DELETE FROM dis_element WHERE elm_community='$community' AND elm_playlist='$playlist'");
					queryDB("DELETE FROM dis_file WHERE fil_community='$community' AND fil_playlist='$playlist'");
					queryDB("DELETE FROM dis_action WHERE act_community='$community' AND act_playlist='$playlist'");
					if (is_file("./community/" . $community . ".dis/playlist/" . $playlist . ".xml")) {
						unlink("./community/" . $community . ".dis/playlist/" . $playlist . ".xml");
					}
					mysql_free_result($check);
				} else {
					// user have no rights
					exitOnError('ERCONFIRM-1');
					mysql_free_result($check);
				}
			} else {
				// remove the playlist
				queryDB("DELETE FROM dis_playlist WHERE ply_community='$community' AND ply_id='$playlist'");
				queryDB("DELETE FROM dis_element WHERE elm_community='$community' AND elm_playlist='$playlist'");
				queryDB("DELETE FROM dis_file WHERE fil_community='$community' AND fil_playlist='$playlist'");
				queryDB("DELETE FROM dis_action WHERE act_community='$community' AND act_playlist='$playlist'");
				if (is_file("./community/" . $community . ".dis/playlist/" . $playlist . ".xml")) {
					unlink("./community/" . $community . ".dis/playlist/" . $playlist . ".xml");
				}
				mysql_free_result($check);
			}
		}
		break;
	case 'deletestream':
		$stream = postString('stream');
		$check = queryDB("SELECT * FROM dis_stream WHERE str_community='$community' AND str_id='$stream' ORDER BY str_index DESC LIMIT 1");
		if (mysql_num_rows($check) == 0) {
			// no stream found
			mysql_free_result($check);
			exitOnError('ERCONFIRM-3');
		} else {
			if ($level == 'author') {
				// user must own the stream
				$row = mysql_fetch_assoc($check);
				if ($row['str_authorid'] == $_SESSION['usr_id']) {
					// remove the stream
					$indexes = queryDB("SELECT * FROM dis_stream WHERE str_community='$community' AND str_id='$stream'");
					for ($i=0; $i<mysql_num_rows($indexes); $i++) {
						$rowintance = mysql_fetch_assoc($indexes);
						queryDB("DELETE FROM dis_instance WHERE ins_community='$community' AND ins_streamindex='" . $rowintance['str_index'] . "'");
					}
					mysql_free_result($indexes);
					queryDB("DELETE FROM dis_stream WHERE str_community='$community' AND str_id='$stream'");
					if (is_file("./community/" . $community . ".dis/stream/" . $stream . ".xml")) {
						unlink("./community/" . $community . ".dis/stream/" . $stream . ".xml");
					}
					mysql_free_result($check);
				} else {
					// user have no rights
					exitOnError('ERCONFIRM-4');
					mysql_free_result($check);
				}
			} else {
				// remove the stream
				$indexes = queryDB("SELECT * FROM dis_stream WHERE str_community='$community' AND str_id='$stream'");
				for ($i=0; $i<mysql_num_rows($indexes); $i++) {
					$rowintance = mysql_fetch_assoc($indexes);
					queryDB("DELETE FROM dis_instance WHERE ins_community='$community' AND ins_streamindex='" . $rowintance['str_index'] . "'");
				}
				mysql_free_result($indexes);
				queryDB("DELETE FROM dis_stream WHERE str_community='$community' AND str_id='$stream'");
				if (is_file("./community/" . $community . ".dis/stream/" . $stream . ".xml")) {
					unlink("./community/" . $community . ".dis/stream/" . $stream . ".xml");
				}
				mysql_free_result($check);
			}
		}
		break;
	case 'deletecommunity':
		$com = postString('com');
		// check for user permission
		if (($_SESSION['usr_level'] == "super") || ($_SESSION['usr_level'] == "admin")) {
			// is the comunity the right one?
			if ($com == $community) {
				// so, the user must know what he is doing... delete the community
				queryDB("DELETE FROM dis_community WHERE com_id='$com' LIMIT 1");
				queryDB("DELETE FROM dis_action WHERE act_community='$com'");
				queryDB("DELETE FROM dis_category WHERE cat_community='$com'");
				queryDB("DELETE FROM dis_element WHERE elm_community='$com'");
				queryDB("DELETE FROM dis_feed WHERE fed_community='$com'");
				queryDB("DELETE FROM dis_file WHERE fil_community='$com'");
				queryDB("DELETE FROM dis_instance WHERE ins_community='$com'");
				queryDB("DELETE FROM dis_playlist WHERE ply_community='$com'");
				queryDB("DELETE FROM dis_stream WHERE str_community='$com'");
				// remove community folder
				$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("./community/" . $community . ".dis"), RecursiveIteratorIterator::CHILD_FIRST);
				foreach ($iterator as $path) {
					if ($path->isDir()) {
						rmdir($path->__toString());
					} else {
						unlink($path->__toString());
					}
				}
				rmdir("./community/" . $community . ".dis");
				if (is_file("./community/" . $community . ".php")) {
					@unlink("./community/" . $community . ".php");
				}
			} else {
				exitOnError('ERCONFIRM-6');
			}
		} else {
			exitOnError('ERCONFIRM-5');
		}
		break;
	case 'publishcommunity':
		$com = postString('com');
		// check for user permission
		if (($_SESSION['usr_level'] == "super") || ($_SESSION['usr_level'] == "admin")) {
			// is the comunity the right one?
			if ($com == $community) {
				// get a list of all published streams
				$pstreams = queryDB("SELECT * FROM dis_stream WHERE str_community='$community' AND str_state='publish'");
				if (mysql_num_rows($pstreams) > 0) {
					for ($i=0; $i<mysql_num_rows($pstreams); $i++) {
						$prow = mysql_fetch_assoc($pstreams);
						publishStream($prow['str_id'], $community);
					}
				}
				mysql_free_result($pstreams);
			} else {
				exitOnError('ERCONFIRM-6');
			}
		} else {
			exitOnError('ERCONFIRM-5');
		}
		break;
	case 'resetrating':
		$com = postString('com');
		// check for user permission
		if (($_SESSION['usr_level'] == "super") || ($_SESSION['usr_level'] == "admin")) {
			// is the comunity the right one?
			if ($com == $community) {
				// so, the user must know what he is doing... remove all rating
				queryDB("DELETE FROM dis_rate WHERE rat_community='$com'");
			} else {
				exitOnError('ERCONFIRM-7');
			}
		} else {
			exitOnError('ERCONFIRM-7');
		}
		break;
	case 'resetstats':
		$com = postString('com');
		// check for user permission
		if (($_SESSION['usr_level'] == "super") || ($_SESSION['usr_level'] == "admin")) {
			// is the comunity the right one?
			if ($com == $community) {
				// so, the user must know what he is doing... remove all stats
				queryDB("DELETE FROM dis_stats WHERE sta_community='$com'");
				queryDB("DELETE FROM dis_statstime WHERE tim_community='$com'");
				// remove community folder
			} else {
				exitOnError('ERCONFIRM-8');
			}
		} else {
			exitOnError('ERCONFIRM-8');
		}
		break;
	case 'resetcomments':
		$com = postString('com');
		// check for user permission
		if (($_SESSION['usr_level'] == "super") || ($_SESSION['usr_level'] == "admin")) {
			// is the comunity the right one?
			if ($com == $community) {
				// so, the user must know what he is doing... remove all comments
				queryDB("DELETE FROM dis_comment WHERE cmt_community='$com'");
				// remove community folder
			} else {
				exitOnError('ERCONFIRM-9');
			}
		} else {
			exitOnError('ERCONFIRM-9');
		}
		break;
	case 'deletemeta':
		if (($level == 'author') || ($level == 'editor')) {
			exitOnError('ERCONFIRM-10');
		} else {
			$metaid = postString('metaid');
			// remove meta data from the streams
			queryDB("DELETE FROM dis_streammeta WHERE smt_community='$community' AND smt_metaindex='$metaid'");
			// remove meta data from the community
			queryDB("DELETE FROM dis_meta WHERE met_community='$community' AND met_index='$metaid'");
		}
		break;
	case 'deletefile':
		$from = postString('from');
		if ($from == 'community') {
			// permission?
			if ($level == 'author') {
				exitOnError('ERCONFIRM-11');
			} else {
				// delete file
				@unlink("./community/" . $community . ".dis/media/community/" . postString('type') . "/" . postString('file'));
			}
		} else if ($from == 'personal') {
			// delete file
			@unlink("./community/" . $community . ".dis/media/" . postString('user') . "/" . postString('type') . "/" . postString('file'));
		}
		break;
	case 'deletelanguage':
		$lang = postString('lang');
		if ($level != 'super') {
			exitOnError('ERCONFIRM-10');
		} else {
			if ($lang != "default") queryDB("DELETE FROM dis_language WHERE lng_language='$lang'");
		}
		break;
	default:
		exitOnError('ERCONFIRM-2');
		break;
}

// output
startOutput();
noError();
endOutput();
?>