<?php
/**
 * Managana server: save stream data.
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
minimumLevel($level, 'author');

// get stream data
$new = postBool('newStream');
$publish = postBool('publish');
$title = encodeApostrophe(postString('title'));
$id = postString('id');
$tags = postString('tags');
$about = encodeApostrophe(postString('about'));
$speed = postInt('speed');
$entropy = postInt('entropy');
$distortion = postInt('distortion');
$tweening = postString('tweening');
$fade = postString('fade');
$target = postString('target');
$playlists = postString('playlists');
$keyframes = postString('keyframes');
$guideup = postString('guideup');
$guidedown = postString('guidedown');
$landscape = postString('landscape');
$portrait = postString('portrait');
$category = encodeApostrophe(postString('category'));
$votetype = postString('votetype');
$votereference = postString('votereference');
$votedefault = postString('votedefault');
$startaftervote = postInt('startaftervote');
$remotestream = postString('remotestream');
$xnext = postString('xnext');
$xprev = postString('xprev');
$ynext = postString('ynext');
$yprev = postString('yprev');
$znext = postString('znext');
$zprev = postString('zprev');
$pcode = postString('pcode');
$fa = postString('fa');
$fb = postString('fb');
$fc = postString('fc');
$fd = postString('fd');
$mwup = postString('mwup');
$mwdown = postString('mwdown');
$totalmeta = postInt('totalmeta');
// get vote information
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

@session_start();
$author = $_SESSION['usr_name'];
$authorid = $_SESSION['usr_id'];

// does the stream id exist?
$checkexist = queryDB("SELECT * FROM dis_stream WHERE str_id='$id' AND str_community='$community'");
$savestream = false;

// is stream in use by someone else?
$checkuse = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='stream' AND cur_id='$id' AND cur_user NOT LIKE '" . $_SESSION['usr_id'] . "'");
if (mysql_num_rows($checkuse) != 0) {
	// the stream is already in use
	$savestream = false;
	mysql_free_result($checkuse);
	exitOnError('ERSTREAM-6');
} else {
	mysql_free_result($checkuse);
}

// avoid acidental override
if (mysql_num_rows($checkexist) == 0) {
	// first usage of stream
	$savestream = true;
} else {
	// is this user allowed to save over this stream
	if (($level == 'super') || ($level == 'admin') || ($level == 'editor')) {
		// user can save over stream
		if ($new) {
			// ask user to confirm override
			mysql_free_result($checkexist);
			exitOnError('ERSTREAM-1');
		} else {
			$savestream = true;
		}
	} else {
		// is current user the author?
		$row = mysql_fetch_assoc($checkexist);
		if ($row['str_authorid'] == $_SESSION['usr_id']) {
			// user is the author of the stream
			if ($new) {
				// ask user to confirm override
				mysql_free_result($checkexist);
				exitOnError('ERSTREAM-1');
			} else {
				$savestream = true;
			}
		} else {
			// user can't save over selected stream
			mysql_free_result($checkexist);
			exitOnError('ERSTREAM-0');
		}
	}
}
mysql_free_result($checkexist);

// stream can be saved
if ($savestream) {
	// remove old revisions
	$revisioncheck = queryDB("SELECT str_index FROM dis_stream WHERE str_id='$id' AND str_community='$community' AND str_state='' ORDER BY str_index DESC");
	for ($i=0; $i<mysql_num_rows($revisioncheck); $i++) {
		$row = mysql_fetch_assoc($revisioncheck);
		if ($i >= REVISIONS) {
			queryDB("DELETE FROM dis_stream WHERE str_index='" . $row['str_index'] . "' AND str_state='' AND str_id='$id' AND str_community='$community' LIMIT 1");
			queryDB("DELETE FROM dis_instance WHERE ins_streamindex='" . $row['str_index'] . "'");
			queryDB("DELETE FROM dis_keyaction WHERE kac_streamindex='" . $row['str_index'] . "'");
		}
	}
	mysql_free_result($revisioncheck);
	// publishing?
	$update = date("Y-m-d H:i:s");
	if ($publish) {		
		queryDB("UPDATE dis_stream SET str_state='' WHERE str_id='$id' AND str_community='$community'");
		queryDB("INSERT INTO dis_stream (str_id, str_community, str_state, str_title, str_author, str_authorid, str_excerpt, str_tag, str_update, str_speed, str_tweening, str_fade, str_entropy, str_distortion, str_target, str_guideup, str_guidedown, str_landscape, str_portrait, str_category, str_votetype, str_votereference, str_xnext, str_xprev, str_ynext, str_yprev, str_znext, str_zprev, str_pcode, str_functiona, str_functionb, str_functionc, str_functiond, str_mousewup, str_mousewdown, str_vote1, str_vote1px, str_vote1py, str_vote1show, str_vote2, str_vote2px, str_vote2py, str_vote2show, str_vote3, str_vote3px, str_vote3py, str_vote3show, str_vote4, str_vote4px, str_vote4py, str_vote4show, str_vote5, str_vote5px, str_vote5py, str_vote5show, str_vote6, str_vote6px, str_vote6py, str_vote6show, str_vote7, str_vote7px, str_vote7py, str_vote7show, str_vote8, str_vote8px, str_vote8py, str_vote8show, str_vote9, str_vote9px, str_vote9py, str_vote9show, str_votedefault, str_remotestream, str_startaftervote) VALUES ('$id', '$community', 'publish', '$title', '$author', '$authorid', '$about', '$tags', '$update', '$speed', '$tweening', '$fade', '$entropy', '$distortion', '$target', '$guideup', '$guidedown', '$landscape', '$portrait', '$category', '$votetype', '$votereference', '$xnext', '$xprev', '$ynext', '$yprev', '$znext', '$zprev', '$pcode', '$fa', '$fb', '$fc', '$fd', '$mwup', '$mwdown', '" . $voteaction[0] . "', '" . $votepx[0] . "', '" . $votepy[0] . "', '" . $voteshow[0] . "', '" . $voteaction[1] . "', '" . $votepx[1] . "', '" . $votepy[1] . "', '" . $voteshow[1] . "', '" . $voteaction[2] . "', '" . $votepx[2] . "', '" . $votepy[2] . "', '" . $voteshow[2] . "', '" . $voteaction[3] . "', '" . $votepx[3] . "', '" . $votepy[3] . "', '" . $voteshow[3] . "', '" . $voteaction[4] . "', '" . $votepx[4] . "', '" . $votepy[4] . "', '" . $voteshow[4] . "', '" . $voteaction[5] . "', '" . $votepx[5] . "', '" . $votepy[5] . "', '" . $voteshow[5] . "', '" . $voteaction[6] . "', '" . $votepx[6] . "', '" . $votepy[6] . "', '" . $voteshow[6] . "', '" . $voteaction[7] . "', '" . $votepx[7] . "', '" . $votepy[7] . "', '" . $voteshow[7] . "', '" . $voteaction[8] . "', '" . $votepx[8] . "', '" . $votepy[8] . "', '" . $voteshow[8] . "', '$votedefault', '$remotestream', '$startaftervote')");
		$index = mysql_insert_id();
		// meta data
		queryDB("DELETE FROM dis_streammeta WHERE smt_community='$community' AND smt_streamid='$id'");
		if ($totalmeta > 0) {
			for ($j=0; $j<$totalmeta; $j++) {
				queryDB("INSERT INTO dis_streammeta (smt_community, smt_streamid, smt_metaindex, smt_metaname, smt_metavalue) VALUES ('$community', '$id', '" . postString('metaindex' . $j) . "', '" . postString('metaname' . $j) . "', '" . postString('metavalue' . $j) . "')");
			}
		}
		// get playlists and keyframes
		processPlaylists($playlists, $community);
		processKeyframes($keyframes, $community, $index);
		// publish
		publishStream($id, $community);
	} else {
		// just save revision
		queryDB("INSERT INTO dis_stream (str_id, str_community, str_state, str_title, str_author, str_authorid, str_excerpt, str_tag, str_update, str_speed, str_tweening, str_fade, str_entropy, str_distortion, str_target, str_guideup, str_guidedown, str_landscape, str_portrait, str_category, str_votetype, str_votereference, str_xnext, str_xprev, str_ynext, str_yprev, str_znext, str_zprev, str_pcode, str_functiona, str_functionb, str_functionc, str_functiond, str_mousewup, str_mousewdown, str_vote1, str_vote1px, str_vote1py, str_vote1show, str_vote2, str_vote2px, str_vote2py, str_vote2show, str_vote3, str_vote3px, str_vote3py, str_vote3show, str_vote4, str_vote4px, str_vote4py, str_vote4show, str_vote5, str_vote5px, str_vote5py, str_vote5show, str_vote6, str_vote6px, str_vote6py, str_vote6show, str_vote7, str_vote7px, str_vote7py, str_vote7show, str_vote8, str_vote8px, str_vote8py, str_vote8show, str_vote9, str_vote9px, str_vote9py, str_vote9show, str_votedefault, str_remotestream, str_startaftervote) VALUES ('$id', '$community', '', '$title', '$author', '$authorid', '$about', '$tags', '$update', '$speed', '$tweening', '$fade', '$entropy', '$distortion', '$target', '$guideup', '$guidedown', '$landscape', '$portrait', '$category', '$votetype', '$votereference', '$xnext', '$xprev', '$ynext', '$yprev', '$znext', '$zprev', '$pcode', '$fa', '$fb', '$fc', '$fd', '$mwup', '$mwdown', '" . $voteaction[0] . "', '" . $votepx[0] . "', '" . $votepy[0] . "', '" . $voteshow[0] . "', '" . $voteaction[1] . "', '" . $votepx[1] . "', '" . $votepy[1] . "', '" . $voteshow[1] . "', '" . $voteaction[2] . "', '" . $votepx[2] . "', '" . $votepy[2] . "', '" . $voteshow[2] . "', '" . $voteaction[3] . "', '" . $votepx[3] . "', '" . $votepy[3] . "', '" . $voteshow[3] . "', '" . $voteaction[4] . "', '" . $votepx[4] . "', '" . $votepy[4] . "', '" . $voteshow[4] . "', '" . $voteaction[5] . "', '" . $votepx[5] . "', '" . $votepy[5] . "', '" . $voteshow[5] . "', '" . $voteaction[6] . "', '" . $votepx[6] . "', '" . $votepy[6] . "', '" . $voteshow[6] . "', '" . $voteaction[7] . "', '" . $votepx[7] . "', '" . $votepy[7] . "', '" . $voteshow[7] . "', '" . $voteaction[8] . "', '" . $votepx[8] . "', '" . $votepy[8] . "', '" . $voteshow[8] . "', '$votedefault', '$remotestream', '$startaftervote')");
		$index = mysql_insert_id();
		// meta data
		queryDB("DELETE FROM dis_streammeta WHERE smt_community='$community' AND smt_streamid='$id'");
		if ($totalmeta > 0) {
			for ($j=0; $j<$totalmeta; $j++) {
				queryDB("INSERT INTO dis_streammeta (smt_community, smt_streamid, smt_metaindex, smt_metaname, smt_metavalue) VALUES ('$community', '$id', '" . postString('metaindex' . $j) . "', '" . postString('metaname' . $j) . "', '" . postString('metavalue' . $j) . "')");
			}
		}
		// get playlists and keyframes
		processPlaylists($playlists, $community);
		processKeyframes($keyframes, $community, $index);
	}
}

// if no error until now, return an OK response
startOutput();
noError();
endOutput();
?>