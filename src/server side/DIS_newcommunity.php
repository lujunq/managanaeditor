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
minimumLevel($level, 'admin');

// get new community creation data
$title = encodeApostrophe(postString('title'));
$id = postString('id');
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
$css = encodeApostrophe(postString('css'));

// is provided ID valid?
$dbresult = queryDB("SELECT * FROM dis_community WHERE com_id='$id'");
if (mysql_num_rows($dbresult) > 0) {
	// the community id is taken
	mysql_free_result($dbresult);
	exitOnError('ERCOMMUNITY-0');
} else {
	// save new community on database
	mysql_free_result($dbresult);
	queryDB("INSERT INTO dis_community (com_id, com_title, com_copyleft, com_copyright, com_about, com_lang, com_update, com_width, com_height, com_pwidth, com_pheight, com_highlight, com_highlightcolor, com_background, com_alpha, com_css) VALUES ('$id', '$title', '$copyleft', '$copyright', '$about', '$language', '$edition', '$width', '$height', '$pwidth', '$pheight', '$highlight', '$highlightcolor', '$background', '$alpha', '$css')");
	$newindex = mysql_insert_id();
	// create community folder
	@chdir("community");
	@mkdir($id . '.dis');
	// write community xml descriptor
	writeCommunity($id);
	// update community permissions
	queryDB("INSERT INTO dis_usercommunity (usc_user, usc_community, usc_level) VALUES ('" . $_SESSION['usr_id'] . "', '$id', 'admin')");
}

// if no error until now, return an OK response
startOutput();
noError();
outputData('index', $newindex);
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
outputData('css', decodeApostrophe($css));
endOutput();
?>