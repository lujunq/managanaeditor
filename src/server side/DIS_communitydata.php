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

// check for user access level
$level = requestLevel();

// get new community data
$id = postString('id');

// check for permission on current community
$level = communityLevel($id);
minimumLevel($level, 'author');

// is provided ID valid?
$dbresult = queryDB("SELECT * FROM dis_community WHERE com_id='$id'");

if (mysql_num_rows($dbresult) == 0) {
	// no community with provided id
	mysql_free_result($dbresult);
	exitOnError('ERCOMMUNITY-2');
} else {
	// get community data
	$row = mysql_fetch_assoc($dbresult);
	startOutput();
	noError();
	outputData('level', $level);
	outputData('index', $row['com_index']);
	outputData('title', decodeApostrophe($row['com_title']));
	outputData('id', $row['com_id']);
	outputData('width', $row['com_width']);
	outputData('height', $row['com_height']);
	outputData('pwidth', $row['com_pwidth']);
	outputData('pheight', $row['com_pheight']);
	outputData('copyleft', decodeApostrophe($row['com_copyleft']));
	outputData('copyright', decodeApostrophe($row['com_copyright']));
	outputData('about', decodeApostrophe($row['com_about']));
	outputData('background', $row['com_background']);
	outputData('alpha', $row['com_alpha']);
	outputData('highlight', $row['com_highlight']);
	outputData('highlightcolor', $row['com_highlightcolor']);
	outputData('language', $row['com_lang']);
	outputData('edition', $row['com_update']);
	outputData('home', $row['com_home']);
	outputData('defaultvote', $row['com_votedefault']);
	outputData('voterecord', $row['com_voterecord']);
	outputData('css', decodeApostrophe($row['com_css']));
	// graphics
	echo('<graphic>');
		echo('<gIcon>' . $row['com_icon'] . '</gIcon>');
		echo('<gTarget>' . $row['com_target'] . '</gTarget>');
		echo('<gVote0>' . $row['com_vote0'] . '</gVote0>');
		echo('<gVote10>' . $row['com_vote10'] . '</gVote10>');
		echo('<gVote20>' . $row['com_vote20'] . '</gVote20>');
		echo('<gVote30>' . $row['com_vote30'] . '</gVote30>');
		echo('<gVote40>' . $row['com_vote40'] . '</gVote40>');
		echo('<gVote50>' . $row['com_vote50'] . '</gVote50>');
		echo('<gVote60>' . $row['com_vote60'] . '</gVote60>');
		echo('<gVote70>' . $row['com_vote70'] . '</gVote70>');
		echo('<gVote80>' . $row['com_vote80'] . '</gVote80>');
		echo('<gVote90>' . $row['com_vote90'] . '</gVote90>');
		echo('<gVote100>' . $row['com_vote100'] . '</gVote100>');
	echo('</graphic>');
	// stream navigation transitions
	echo('<transition>');
		echo('<xnext><![CDATA[' . $row['com_navxnext'] . ']]></xnext>');
		echo('<xprev><![CDATA[' . $row['com_navxprev'] . ']]></xprev>');
		echo('<ynext><![CDATA[' . $row['com_navynext'] . ']]></ynext>');
		echo('<yprev><![CDATA[' . $row['com_navyprev'] . ']]></yprev>');
		echo('<znext><![CDATA[' . $row['com_navznext'] . ']]></znext>');
		echo('<zprev><![CDATA[' . $row['com_navzprev'] . ']]></zprev>');
		echo('<home><![CDATA[' . $row['com_navhome'] . ']]></home>');
		echo('<list><![CDATA[' . $row['com_navlist'] . ']]></list>');
	echo('</transition>');
	// feeds
	$feedresult = queryDB("SELECT * FROM dis_feed WHERE fed_community='$id'");
	echo("<feeds>");
	if (mysql_num_rows($feedresult) > 0) {
		for ($i=0; $i<mysql_num_rows($feedresult); $i++) {
			$feed = mysql_fetch_assoc($feedresult);
			echo('<feed type="' . $feed['fed_type'] . '" reference="' . $feed['fed_reference'] . '"><![CDATA[' . $feed['fed_name'] . ']]></feed>');
		}
	}
	echo("</feeds>");
	mysql_free_result($feedresult);
	// meta data
	$metaresult = queryDB("SELECT * FROM dis_meta WHERE met_community='$id'");
	echo("<meta>");
	if (mysql_num_rows($metaresult) > 0) {
		for ($i=0; $i<mysql_num_rows($metaresult); $i++) {
			$meta = mysql_fetch_assoc($metaresult);
			echo('<field id="' . $meta['met_index'] . '"><![CDATA[' . $meta['met_name'] . ']]></field>');
		}
	}
	echo("</meta>");
	mysql_free_result($metaresult);
	// widgets
	if (is_dir('./community/' . $row['com_id'] . '.dis/media/community/widget/')) {
		$pathsize = strlen('./community/' . $row['com_id'] . '.dis/media/community/widget/');
		echo('<widgets>');
		foreach(glob('./community/' . $row['com_id'] . '.dis/media/community/widget/*.swf') as $wfilename) {
			echo('<widget><![CDATA[' . substr($wfilename, $pathsize) . ']]></widget>');
		}
		echo('</widgets>');
	} else {
		echo('<widgets />');
	}
	endOutput();
	mysql_free_result($dbresult);
}
?>