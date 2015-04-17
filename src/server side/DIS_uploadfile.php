<?php
/**
 * Managana server: upload a file.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// get database functions
require_once('DIS_database.php');

// common functions for file handling
require_once('DIS_filecommon.php');

// get list options
$community = postString('community');
$type = postString('type');
$user = postString('userid');
$index = postString('userindex');
$name = postString('name');
$cfiles = postBool('cfiles');
$language = postString('language');
$islang = postBool('islang');
$islogo = postBool('islogo');
$htmlpath = postString('htmlpath');

// does the user have permission for uploading?
@session_start();

// check for user access
// BUG: the saved session isn't available to a php script receiving a file uploaded by flash, so we'll need to check the database for permission. this happens on remote servers, not local ones
$checkuser = queryDB("SELECT * FROM dis_user WHERE usr_id='$user' AND usr_index='$index'");
if (mysql_num_rows($checkuser) == 0) {
	// user not found
	exitOnError('ERFILE-1');
} else {
	$row = mysql_fetch_assoc($checkuser);
	$level = $row['usr_level'];
	if ($level != "super") {
		// is uploading a language file?
		if ($islang || $islogo) {
			// only super users can upload language and logo files
			exitOnError('ERFILE-5');
		} else {
			// must check community permissions
			$checkcommunity = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$community'");
			if (mysql_num_rows($checkcommunity) == 0) {
				// user not found
				exitOnError('ERFILE-1');
				mysql_free_result($checkcommunity);
			} else {
				// get user level for current community
				$rowcom = mysql_fetch_assoc($checkcommunity);
				$level = $rowcom['usc_level'];
				mysql_free_result($checkcommunity);
			}
		}
	}
}
mysql_free_result($checkuser);

if ($islang) { // uploading a language file?
	if ($level == "super") {
		// is the selected language already available?
		$checklang = queryDB("SELECT * FROM dis_language WHERE lng_language='$language'");
		if (mysql_num_rows($checklang) > 0) {
			mysql_free_result($checklang);
			exitOnError('ERFILE-6');
		} else {
			mysql_free_result($checklang);
			chdir("language");
			chdir(".");
			@move_uploaded_file($_FILES['Filedata']['tmp_name'], ("language_" . $language . ".xml"));
		}
	} else {
		exitOnError('ERFILE-5');
	}
} else if ($islogo) { // uploading a logo file?
	if ($level == "super") {
		mysql_free_result($checklang);
		chdir("pics");
		chdir(".");
		@move_uploaded_file($_FILES['Filedata']['tmp_name'], ("customlogo.png"));
	} else {
		exitOnError('ERFILE-5');
	}
} else {
	// authors can't save on community folder
	if ($cfiles && ($level == "author")) {
		exitOnError('ERFILE-1');
	}
	// check for successfull upload
	if (($type == "") || ($community == "") || ($name == "")) {
		exitOnError('ERFILE-2');
	}
	// does the requested folder exist?
	chdir ("community");
	chdir (".");
	// community folder
	if (!is_dir($community . ".dis")) @mkdir($community . ".dis");
	chdir ($community . ".dis");
	chdir (".");
	// media folder
	if (!is_dir("media")) @mkdir("media");
	chdir ("media");
	chdir (".");
	// community or personal folder
	if ($cfiles) {
		if (!is_dir("community")) @mkdir("community");
		chdir ("community");
		chdir (".");
	} else {
		if (!is_dir($user)) @mkdir($user);
		chdir ($user);
		chdir (".");
	}
	// file type folder
	if (!is_dir($type)) @mkdir($type);
	chdir ($type);
	chdir (".");
	// html path?
	if (($type == "html") && ($htmlpath != "")) {
		$patharray = explode("/", $htmlpath);
		for ($i=0; $i<sizeof($patharray); $i++) {
			if (!is_dir($patharray[$i])) @mkdir($patharray[$i]);
			chdir ($patharray[$i]);
			chdir (".");
		}
	}
	// upload file
	$newname = trim($_FILES['Filedata']['name']);
	@move_uploaded_file($_FILES['Filedata']['tmp_name'], $name);
}

// upload ok
startOutput();
noError();
endOutput();
?>