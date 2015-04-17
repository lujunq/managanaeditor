<?php
/**
 * Managana server: save community data.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// community handle common functions
require_once('DIS_communitycommon.php');

// check user access to this community
$id = postString('id');
$level = communityLevel($id);
minimumLevel($level, 'admin');

// get new community data
$title = encodeApostrophe(postString('title'));
$width = postInt('width');
$height = postInt('height');
$pwidth = postInt('pwidth');
$pheight = postInt('pheight');
$copyleft = encodeApostrophe(postString('copyleft'));
$copyright = encodeApostrophe(postString('copyright'));
$about = encodeApostrophe(postString('about'));
$background = postColor('background');
$alpha = postInt('alpha') / 100;
$highlight = postBool('highlight');
$highlightcolor = postColor('highlightcolor');
$language = postString('language');
$edition = postDate('edition');
$home = postString('home');
$feedcount = postInt('feedcount');
$cindex = postString('index');
$savegraphic = postString('savegraphic');
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

// is provided ID valid?
$dbresult = queryDB("SELECT * FROM dis_community WHERE com_id='$id'");

if (mysql_num_rows($dbresult) == 0) {
	// community not found
	mysql_free_result($dbresult);
	exitOnError('ERCOMMUNITY-3');
} else {
	// update community on database
	mysql_free_result($dbresult);
	if ($savegraphic == "true") {
		queryDB("UPDATE dis_community SET com_title='$title', com_copyleft='$copyleft', com_copyright='$copyright', com_about='$about', com_lang='$language', com_update='$edition', com_width='$width', com_height='$height', com_pwidth='$pwidth', com_pheight='$pheight', com_highlight='$highlight', com_highlightcolor='$highlightcolor', com_background='$background', com_alpha='$alpha', com_home='$home', com_icon='" . postString('gIcon') . "', com_target='" . postString('gTarget') . "', com_vote0='" . postString('gVote0') . "', com_vote10='" . postString('gVote10') . "', com_vote20='" . postString('gVote20') . "', com_vote30='" . postString('gVote30') . "', com_vote40='" . postString('gVote40') . "', com_vote50='" . postString('gVote50') . "', com_vote60='" . postString('gVote60') . "', com_vote70='" . postString('gVote70') . "', com_vote80='" . postString('gVote80') . "', com_vote90='" . postString('gVote90') . "', com_vote100='" . postString('gVote100') . "', com_votedefault='$defaultvote', com_voterecord='$voterecord', com_css='$css', com_navxnext='$navxnext', com_navxprev='$navxprev', com_navynext='$navynext', com_navyprev='$navyprev', com_navznext='$navznext', com_navzprev='$navzprev', com_navhome='$navhome', com_navlist='$navlist' WHERE com_id='$id'");
	} else {
		queryDB("UPDATE dis_community SET com_title='$title', com_copyleft='$copyleft', com_copyright='$copyright', com_about='$about', com_lang='$language', com_update='$edition', com_width='$width', com_height='$height', com_pwidth='$pwidth', com_pheight='$pheight', com_highlight='$highlight', com_highlightcolor='$highlightcolor', com_background='$background', com_alpha='$alpha', com_home='$home', com_votedefault='$defaultvote', com_voterecord='$voterecord', com_css='$css', com_navxnext='$navxnext', com_navxprev='$navxprev', com_navynext='$navynext', com_navyprev='$navyprev', com_navznext='$navznext', com_navzprev='$navzprev', com_navhome='$navhome', com_navlist='$navlist' WHERE com_id='$id'");
	}
	// update community feeds
	queryDB("DELETE FROM dis_feed WHERE fed_community='$id'");
	for ($i=0; $i<$feedcount; $i++) {
		queryDB("INSERT INTO dis_feed (fed_community, fed_name, fed_type, fed_reference) VALUES ('$id', '" . postString('feedname' . $i) . "', '" . postString('feedtype' . $i) . "', '" . postString('feedreference' . $i) . "')");
	}
	// write community xml descriptor
	writeCommunity($id);
}

// if no error until now, return an OK response
startOutput();
noError();
outputData('index', $cindex);
outputData('level', $level);
outputData('title', decodeApostrophe($title));
outputData('id', $id);
outputData('width', $width);
outputData('height', $height);
outputData('pwidth', $pwidth);
outputData('pheight', $pheight);
outputData('copyleft', decodeApostrophe($copyleft));
outputData('copyright', decodeApostrophe($copyright));
outputData('about', decodeApostrophe($about));
outputData('background', $background);
outputData('alpha', $alpha);
outputData('highlight', $highlight);
outputData('highlightcolor', $highlightcolor);
outputData('language', $language);
outputData('edition', $edition);
outputData('home', $home);
outputData('defaultvote', $defaultvote);
outputData('voterecord', $voterecord);
outputData('css', decodeApostrophe($css));
// stream navigation transitions
echo('<transition>');
	echo('<xnext><![CDATA[' . $navxnext . ']]></xnext>');
	echo('<xprev><![CDATA[' . $navxprev . ']]></xprev>');
	echo('<ynext><![CDATA[' . $navynext . ']]></ynext>');
	echo('<yprev><![CDATA[' . $navyprev . ']]></yprev>');
	echo('<znext><![CDATA[' . $navznext . ']]></znext>');
	echo('<zprev><![CDATA[' . $navzprev . ']]></zprev>');
	echo('<home><![CDATA[' . $navhome . ']]></home>');
	echo('<list><![CDATA[' . $navlist . ']]></list>');
echo('</transition>');
if ($savegraphic == "true") {
	echo('<graphic>');
		echo('<gIcon>' . postString('gIcon') . '</gIcon>');
		echo('<gTarget>' . postString('gTarget') . '</gTarget>');
		echo('<gVote0>' . postString('gVote0') . '</gVote0>');
		echo('<gVote10>' . postString('gVote10') . '</gVote10>');
		echo('<gVote20>' . postString('gVote20') . '</gVote20>');
		echo('<gVote30>' . postString('gVote30') . '</gVote30>');
		echo('<gVote40>' . postString('gVote40') . '</gVote40>');
		echo('<gVote50>' . postString('gVote50') . '</gVote50>');
		echo('<gVote60>' . postString('gVote60') . '</gVote60>');
		echo('<gVote70>' . postString('gVote70') . '</gVote70>');
		echo('<gVote80>' . postString('gVote80') . '</gVote80>');
		echo('<gVote90>' . postString('gVote90') . '</gVote90>');
		echo('<gVote100>' . postString('gVote100') . '</gVote100>');
	echo('</graphic>');
}
// feeds
echo("<feeds>");
	for ($i=0; $i<$feedcount; $i++) {
		echo('<feed type="' . postString('feedtype' . $i) . '" reference="' . postString('feedreference' . $i) . '">' . postString('feedname' . $i) . '</feed>');
	}
echo("</feeds>");
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
// widgets
if (is_dir('./media/community/widget')) {
	echo('<widgets>');
	foreach(glob('./media/community/widget/*.swf') as $wfilename) {
		echo('<widget><![CDATA[' . substr($wfilename, 25) . ']]></widget>');
	}
	echo('</widgets>');
} else {
	echo('<widgets />');
}
mysql_free_result($metaresult);
endOutput();
?>