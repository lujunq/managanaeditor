<?php
/**
 * Managana server: importing a dis folder.
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
minimumLevel($level, 'admin');

// get data
$ac = postString('ac');
$dis = postString('dis');

// check actions
switch($ac) {
	case 'check':	// check dis folder and list its content
		// check update permissions
		if ($level != "super") {
			// check a dis folder
			$dbalready = queryDB("SELECT * FROM dis_community WHERE com_id='$disname'");
			if (mysql_num_rows($dbalready) > 0) {
				// the community exists. does the user have permission to update it?
				$levelcheck = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$dis' AND usc_user='" . $_SESSION['usr_id'] . "'");
				if (mysql_num_rows($levelcheck) == 0) {
					// user is not assigned to community
					exitOnError('ERACCESS-0');
				} else {
					// is the user an admin?
					$rowlevel = mysql_fetch_assoc($levelcheck);
					if ($rowlevel['usc_level'] != 'admin') exitOnError('ERACCESS-0');
				}
				mysql_free_result($levelcheck);
			}
			mysql_free_result($dbalready);
		}
		// check dis folder
		chdir ("community");
		chdir (".");
		if (!is_dir($dis . ".dis")) {
			exitOnError('ERIMPORT-1');
		}
		chdir ($dis . ".dis");
		if (!is_dir("playlist")) {
			exitOnError('ERIMPORT-1');
		}
		if (!is_dir("stream")) {
			exitOnError('ERIMPORT-1');
		}
		if (!is_dir("media")) {
			exitOnError('ERIMPORT-1');
		}
		if (!is_file("dis.xml")) {
			exitOnError('ERIMPORT-1');
		}
		// start list output
		startOutput();
		noError();
		// list playlists
		chdir("playlist");
		chdir(".");
		$handler = opendir(".");
		while ($file = readdir($handler)) {
			if ($file != "." && $folder != "..") {
				if (is_file($file)) {
					if (substr($file, -4, 4) == ".xml") {
						// get the playlist id
						$id = substr($file, 0, (strlen($file) - 4));
						// add to the list
						echo('<playlist id="' . $id . '">' . $file . '</playlist>');
					}
				}
			}
		}
		closedir($handler);
		// list streams
		chdir("..");
		chdir("stream");
		chdir(".");
		$handler = opendir(".");
		while ($file = readdir($handler)) {
			if ($file != "." && $folder != "..") {
				if (is_file($file)) {
					if (substr($file, -4, 4) == ".xml") {
						// get the stream id
						$id = substr($file, 0, (strlen($file) - 4));
						// add to the list
						echo('<stream id="' . $id . '">' . $file . '</stream>');
					}
				}
			}
		}
		closedir($handler);
		// end list output
		endOutput();
		break;
	case 'playlist':	// save a playlist
		// check update permissions
		if ($level != "super") {
			// check a dis folder
			$dbalready = queryDB("SELECT * FROM dis_community WHERE com_id='$disname'");
			if (mysql_num_rows($dbalready) > 0) {
				// the community exists. does the user have permission to update it?
				$levelcheck = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$dis' AND usc_user='" . $_SESSION['usr_id'] . "'");
				if (mysql_num_rows($levelcheck) == 0) {
					// user is not assigned to community
					exitOnError('ERACCESS-0');
				} else {
					// is the user an admin?
					$rowlevel = mysql_fetch_assoc($levelcheck);
					if ($rowlevel['usc_level'] != 'admin') exitOnError('ERACCESS-0');
				}
				mysql_free_result($levelcheck);
			}
			mysql_free_result($dbalready);
		}
		// check dis folder
		if (!is_dir("./community/" . $dis . ".dis")) {
			exitOnError('ERIMPORT-1');
		}
		if (!is_dir("./community/" . $dis . ".dis/playlist")) {
			exitOnError('ERIMPORT-1');
		}
		// save playlist data
		processPlaylists(postString('playlist'), $dis);
		// output
		startOutput();
		noError();
		endOutput();
		break;
	case 'stream':	// save a stream
		// check update permissions
		if ($level != "super") {
			// check a dis folder
			$dbalready = queryDB("SELECT * FROM dis_community WHERE com_id='$disname'");
			if (mysql_num_rows($dbalready) > 0) {
				// the community exists. does the user have permission to update it?
				$levelcheck = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$dis' AND usc_user='" . $_SESSION['usr_id'] . "'");
				if (mysql_num_rows($levelcheck) == 0) {
					// user is not assigned to community
					exitOnError('ERACCESS-0');
				} else {
					// is the user an admin?
					$rowlevel = mysql_fetch_assoc($levelcheck);
					if ($rowlevel['usc_level'] != 'admin') exitOnError('ERACCESS-0');
				}
				mysql_free_result($levelcheck);
			}
			mysql_free_result($dbalready);
		}
		// check dis folder
		if (!is_dir("./community/" . $dis . ".dis")) {
			exitOnError('ERIMPORT-1');
		}
		if (!is_dir("./community/" . $dis . ".dis/stream")) {
			exitOnError('ERIMPORT-1');
		}
		// get stream data
		$title = encodeApostrophe(postString('title'));
		$id = postString('id');
		$tags = postString('tags');
		$about = encodeApostrophe(postString('about'));
		$author = postString('author');
		$authorid = postString('authorid');
		$update = postString('update');
		$speed = postInt('speed');
		$entropy = postInt('entropy');
		$distortion = postInt('distortion');
		$tweening = postString('tweening');
		$fade = postString('fade');
		$target = postString('target');
		$keyframes = postString('keyframes');
		$guideup = postString('guideup');
		$guidedown = postString('guidedown');
		$landscape = postString('landscape');
		$portrait = postString('portrait');
		$category = encodeApostrophe(postString('category'));
		$votetype = postString('votetype');
		$votereference = postString('votereference');
		$xnext = postString('xnext');
		$xprev = postString('xprev');
		$ynext = postString('ynext');
		$yprev = postString('yprev');
		$znext = postString('znext');
		$zprev = postString('zprev');
		$totalmeta = postInt('totalmeta');
		$pcode = postString("pcode");
		$fa = postString("fa");
		$fb = postString("fb");
		$fc = postString("fc");
		$fd = postString("fd");
		$geouse = postString("geouse");
		$geotarget = postString("geotarget");
		$geomap = postString("geomap");
		$geolattop = postString("geolattop");
		$geolongtop = postString("geolongtop");
		$geolatbottom = postString("geolatbottom");
		$geolongbottom = postString("geolongbottom");
		$totalgeopoints = postInt("totalgeopoints");
		$mwup = postString('mwup');
		$mwdown = postString('mwdown');
		// get vote information
		$votedefault = postString('votedefault');
		$votepx = array();
		$votepy = array();
		$voteshow = array();
		$voteaction = array();
		for ($i=0; $i<9; $i++) {
			$votepx[$i] = postInt('votepx' . (string)($i + 1));
			$votepy[$i] = postInt('votepy' . (string)($i + 1));
			$voteshow[$i] = postInt('voteshow' . (string)($i + 1));
			$voteaction[$i] = postString('voteaction' . (string)($i + 1));
		}
		// if there are previous versions of the stream on database, set them as revisions, not publications
		queryDB("UPDATE dis_stream SET str_state='' WHERE str_id='$id' AND str_community='$dis'");
		// save stream on database
		queryDB("INSERT INTO dis_stream (str_id, str_community, str_state, str_title, str_author, str_authorid, str_excerpt, str_tag, str_update, str_speed, str_tweening, str_fade, str_entropy, str_distortion, str_target, str_guideup, str_guidedown, str_landscape, str_portrait, str_category, str_votetype, str_votereference, str_xnext, str_xprev, str_ynext, str_yprev, str_znext, str_zprev, str_vote1, str_vote1px, str_vote1py, str_vote1show, str_vote2, str_vote2px, str_vote2py, str_vote2show, str_vote3, str_vote3px, str_vote3py, str_vote3show, str_vote4, str_vote4px, str_vote4py, str_vote4show, str_vote5, str_vote5px, str_vote5py, str_vote5show, str_vote6, str_vote6px, str_vote6py, str_vote6show, str_vote7, str_vote7px, str_vote7py, str_vote7show, str_vote8, str_vote8px, str_vote8py, str_vote8show, str_vote9, str_vote9px, str_vote9py, str_vote9show, str_pcode, str_functiona, str_functionb, str_functionc, str_functiond, str_mousewup, str_mousewdown, str_votedefault) VALUES ('$id', '$dis', 'publish', '$title', '$author', '$authorid', '$about', '$tags', '$update', '$speed', '$tweening', '$fade', '$entropy', '$distortion', '$target', '$guideup', '$guidedown', '$landscape', '$portrait', '$category', '$votetype', '$votereference', '$xnext', '$xprev', '$ynext', '$yprev', '$znext', '$zprev', '" . $voteaction[0] . "', '" . $votepx[0] . "', '" . $votepy[0] . "', '" . $voteshow[0] . "', '" . $voteaction[1] . "', '" . $votepx[1] . "', '" . $votepy[1] . "', '" . $voteshow[1] . "', '" . $voteaction[2] . "', '" . $votepx[2] . "', '" . $votepy[2] . "', '" . $voteshow[2] . "', '" . $voteaction[3] . "', '" . $votepx[3] . "', '" . $votepy[3] . "', '" . $voteshow[3] . "', '" . $voteaction[4] . "', '" . $votepx[4] . "', '" . $votepy[4] . "', '" . $voteshow[4] . "', '" . $voteaction[5] . "', '" . $votepx[5] . "', '" . $votepy[5] . "', '" . $voteshow[5] . "', '" . $voteaction[6] . "', '" . $votepx[6] . "', '" . $votepy[6] . "', '" . $voteshow[6] . "', '" . $voteaction[7] . "', '" . $votepx[7] . "', '" . $votepy[7] . "', '" . $voteshow[7] . "', '" . $voteaction[8] . "', '" . $votepx[8] . "', '" . $votepy[8] . "', '" . $voteshow[8] . "', '$pcode', '$fa', '$fb', '$fc', '$fd', '$mwup', '$mwdown', '$votedefault')");
		$index = mysql_insert_id();
		// geolocation
		queryDB("DELETE FROM dis_streamgeodata WHERE sgd_community='$dis' AND sgd_stream='$id'");
		if ($geouse == "1") queryDB("INSERT INTO dis_streamgeodata (sgd_community, sgd_stream, sgd_use, sgd_target, sgd_map, sgd_latitudetop, sgd_longitudetop, sgd_latitudebottom, sgd_longitudebottom) VALUES ('$dis', '$id', '$geouse', '$geotarget', '$geomap', '$geolattop', '$geolongtop', '$geolatbottom', '$geolongbottom')");
		// geolocation points
		queryDB("DELETE FROM dis_streamgeopoint WHERE sgp_community='$dis' AND sgp_stream='$id'");
		for ($geo=0; $geo < $totalgeopoints; $geo++) {
			queryDB("INSERT INTO dis_streamgeopoint (sgp_community, sgp_stream, sgp_latitude, sgp_longitude, sgp_code, sgp_name) VALUES ('$dis', '$id', '" . postString("geoplat" . $geo) . "', '" . postString("geoplong" . $geo) . "', '" . postString("geopcode" . $geo) . "', '" . postString("geopname" . $geo) . "')");
		}
		// meta data
		queryDB("DELETE FROM dis_streammeta WHERE smt_community='$dis' AND smt_streamid='$id'");
		if ($totalmeta > 0) {
			for ($j=0; $j<$totalmeta; $j++) {
				// add meta data field to community
				$hasMeta = queryDB("SELECT * FROM dis_meta WHERE met_community='$dis' AND met_name='" . postString('metaname' . $j) . "'");
				if (mysql_num_rows($hasMeta) == 0) {
					queryDB("INSERT INTO dis_meta (met_community, met_name) VALUES ('$dis', '" . postString('metaname' . $j) . "')");
					$metaindex = mysql_insert_id();
				} else {
					$rowMeta = mysql_fetch_assoc($hasMeta);
					$metaindex = $rowMeta['met_index'];
				}
				mysql_free_result($hasMeta);
				// add meta data to stream
				queryDB("INSERT INTO dis_streammeta (smt_community, smt_streamid, smt_metaindex, smt_metaname, smt_metavalue) VALUES ('$dis', '$id', '$metaindex', '" . postString('metaname' . $j) . "', '" . postString('metavalue' . $j) . "')");
			}
		}
		// get keyframes
		processKeyframes($keyframes, $dis, $index);
		// output
		startOutput();
		noError();
		endOutput();
		break;
	case 'community':
		// get data
		$title = encodeApostrophe(postString('title'));
		$width = postString('width');
		$height = postString('height');
		$pwidth = postString('pwidth');
		$pheight = postString('pheight');
		$copyleft = encodeApostrophe(postString('copyleft'));
		$copyright = encodeApostrophe(postString('copyright'));
		$about = encodeApostrophe(postString('about'));
		$background = postString('background');
		$alpha = postString('alpha');
		$highlight = postString('highlight');
		$highlightcolor = postString('highlightcolor');
		$language = postString('language');
		$edition = postString('edition');
		$home = postString('home');
		$feedcount = postInt('feedcount');
		$categories = postInt('categories');
		$icon = postString('icon');
		$target = postString('target');
		$vote0 = postString('vote0');
		$vote10 = postString('vote10');
		$vote20 = postString('vote20');
		$vote30 = postString('vote30');
		$vote40 = postString('vote40');
		$vote50 = postString('vote50');
		$vote60 = postString('vote60');
		$vote70 = postString('vote70');
		$vote80 = postString('vote80');
		$vote90 = postString('vote90');
		$vote100 = postString('vote100');
		$defaultvote = postInt('defaultvote');
		$voterecord = postInt('voterecord');
		$css = encodeApostrophe(postString('css'));
		$navxnext = postString('navxnext');
		$navxprev = postString('navxprev');
		$navynext = postString('navynext');
		$navyprev = postString('navyprev');
		$navznext = postString('navznext');
		$navzprev = postString('navzprev');
		$navhome = postString('navhome');
		$navlist = postString('navlist');
		// remove previous community info
		queryDB("DELETE FROM dis_community WHERE com_id='$dis'");
		// save new community information
		queryDB("INSERT INTO dis_community (com_id, com_title, com_copyleft, com_copyright, com_about, com_icon, com_lang, com_update, com_width, com_height, com_pwidth, com_pheight, com_highlight, com_highlightcolor, com_background, com_alpha, com_home, com_target, com_vote0, com_vote10, com_vote20, com_vote30, com_vote40, com_vote50, com_vote60, com_vote70, com_vote80, com_vote90, com_vote100, com_votedefault, com_voterecord, com_css, com_navxnext, com_navxprev, com_navynext, com_navyprev, com_navznext, com_navzprev, com_navhome, com_navlist) VALUES ('$dis', '$title', '$copyleft', '$copyright', '$about', '$icon', '$language', '$edition', '$width', '$height', '$pwidth', '$pheight', '$highlight', '$highlightcolor', '$background', '$alpha', '$home', '$target', '$vote0', '$vote10', '$vote20', '$vote30', '$vote40', '$vote50', '$vote60', '$vote70', '$vote80', '$vote90', '$vote100', '$defaultvote', '$voterecord', '$css', '$navxnext', '$navxprev', '$navynext', '$navyprev', '$navznext', '$navzprev', '$navhome', '$navlist')");
		$comindex = mysql_insert_id();
		// save feeds
		queryDB("DELETE FROM dis_feed WHERE fed_community='$dis'");
		if ($feedcount > 0) {
			for ($i=0; $i<$feedcount; $i++) {
				queryDB("INSERT INTO dis_feed (fed_community, fed_name, fed_type, fed_reference) VALUES ('$dis', '" . postString('feedname_' . $i) . "', '" . postString('feedtype_' . $i) . "', '" . postString('feedref_' . $i) . "')");
			}
		}
		// save categories
		queryDB("DELETE FROM dis_category WHERE cat_community='$dis'");
		if ($categories > 0) {
			for ($i=0; $i<$categories; $i++) {
				queryDB("INSERT INTO dis_category (cat_community, cat_name) VALUES ('$dis', '" . postString('category_' . $i) . "')");
			}
		}
		// set current user as admin
		queryDB("DELETE FROM dis_usercommunity WHERE usc_community='$dis' AND usc_user='" . $_SESSION['usr_id'] . "'");
		queryDB("INSERT INTO dis_usercommunity (usc_user, usc_community, usc_level) VALUES ('" . $_SESSION['usr_id'] . "', '$dis', 'admin')");
		// output
		startOutput();
		noError();
		endOutput();
		break;
}
?>