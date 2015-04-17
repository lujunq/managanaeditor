<?php
/**
 * Managana server: font management
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$user = postString('user');
$queryUser = queryDB("SELECT * FROM dis_user WHERE usr_email='$user'");
if (mysql_num_rows($queryUser) > 0) {
	$rowUser = mysql_fetch_assoc($queryUser);
	if ($rowUser['usr_level'] != 'super') {
		mysql_free_result($queryUser);
		exitOnError('ERACCESS-0');
	}
	mysql_free_result($queryUser);
} else {
	mysql_free_result($queryUser);
	exitOnError('ERACCESS-0');
}

// check action
$ac = postString('ac');
switch ($ac) {
	case 'list':
		// send the font list information
		outputFontList();
		break;
	case 'delete':
		// remove the requested font
		$font = postString('font');
		$for = postString('tech');
		queryDB("DELETE FROM dis_font WHERE fnt_file='$font' AND fnt_for='$for'");
		if (!is_dir('font')) @mkdir('font');
		chdir('font');
		chdir('.');
		@unlink($font);
		// save the current font xml list
		writeFontList();
		// send font list information
		outputFontList();
		break;
	case 'new':
		// check fonts folder
		if (!is_dir('font')) @mkdir('font');
		chdir('font');
		chdir('.');
		// get data
		$name = postString('name');
		$file = postString('file');
		$about = postString('about');
		$for = postString('tech');
		$type = postString('type');
		// get file
		$newname = trim($_FILES['Filedata']['name']);
		@move_uploaded_file($_FILES['Filedata']['tmp_name'], $file);
		// save font at database
		queryDB("DELETE FROM dis_font WHERE fnt_file='$file'");
		queryDB("INSERT INTO dis_font (fnt_name, fnt_file, fnt_about, fnt_type, fnt_for) VALUES ('$name', '$file', '$about', '$type', '$for')");
		// save font xml information
		writeFontList();
		break;
	default:
		exitOnError('ERCONF-1');
		break;
}

// output the font list
function outputFontList() {
	$query = queryDB("SELECT * FROM dis_font ORDER BY fnt_name ASC");
	startOutput();
	noError();
	if (mysql_num_rows($query) > 0) {
		for ($i=0; $i<mysql_num_rows($query); $i++) {
			$row = mysql_fetch_assoc($query);
			echo('<font>');
			echo('<name><![CDATA[' . $row['fnt_name'] . ']]></name>');
			echo('<file><![CDATA[' . $row['fnt_file'] . ']]></file>');
			echo('<about><![CDATA[' . $row['fnt_about'] . ']]></about>');
			echo('<type><![CDATA[' . $row['fnt_type'] . ']]></type>');
			echo('<tech><![CDATA[' . $row['fnt_for'] . ']]></tech>');
			echo('</font>');
		}
	}
	mysql_free_result($query);
	endOutput();
}

// write the current flash fonts list
function writeFontList() {
	$fontFile = fopen('font.xml', "wb");
	$fontFileHTML = fopen('fontHTML.txt', "wb");
	fputs($fontFile, '<?xml version="1.0" encoding="utf-8" ?><data>');
	$fontQuery = queryDB("SELECT * FROM dis_font ORDER BY fnt_name ASC");
	if (mysql_num_rows($fontQuery) > 0) {
		for ($i=0; $i<mysql_num_rows($fontQuery); $i++) {
			$fontRow = mysql_fetch_assoc($fontQuery);
			if ($fontRow['fnt_for'] != 'html') {
				fputs($fontFile, '<font>');
				fputs($fontFile, '<name><![CDATA[' . $fontRow['fnt_name'] . ']]></name>');
				fputs($fontFile, '<file><![CDATA[' . $fontRow['fnt_file'] . ']]></file>');
				fputs($fontFile, '<about><![CDATA[' . $fontRow['fnt_about'] . ']]></about>');
				fputs($fontFile, '</font>');
			} else {
				$weigth = 'normal';
				$style = 'normal';
				if (($fontRow['fnt_type'] == 'bold') || ($fontRow['fnt_type'] == 'bolditalic')) $weigth = 'bold';
				if (($fontRow['fnt_type'] == 'italic') || ($fontRow['fnt_type'] == 'bolditalic')) $style = 'italic';
				fputs($fontFileHTML, '@font-face { font-family: "' . $fontRow['fnt_name'] . '"; src: url(font/' . $fontRow['fnt_file'] . '); font-weight: ' . $weigth . '; font-style: ' . $style . '; }' . "\n");
			}
		}
	}
	fputs($fontFile, '</data>');
	fclose($fontFile);
	fclose($fontFileHTML);
}
?>