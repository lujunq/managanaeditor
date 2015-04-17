<?php
/**
 * Managana server: file manager handling.
 */

// get configuration
require_once('DIS_config.php');

// get database connection
require_once('DIS_database.php');

// get common functions
require_once('DIS_common.php');

// common functions for file handling
require_once('DIS_filecommon.php');

// common functions for community handling
require_once('DIS_communitycommon.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'author');

// get data
$ac = postString('ac');
$type = postString('type');
$target = postString('target');
$file = postString('file');
$name = postString('name');
$htmlpath = postString('htmlpath');

// check action
switch ($ac) {
	case 'list':
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
		if ($target == "community") {
			if (!is_dir("community")) @mkdir("community");
			chdir ("community");
			chdir (".");
		} else {
			@session_start();
			if (!is_dir($_SESSION['usr_id'])) @mkdir($_SESSION['usr_id']);
			chdir ($_SESSION['usr_id']);
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
		// show list
		startOutput();
		noError();
		$handler = opendir(".");
		while ($file = readdir($handler)) {
			if ($file != "." && $file != "..") {
				if ($type != "html") {
					if (checkType($file, $type)) {
						echo('<file subtype="' . subType($file) . '" size="' . filesize($file) . '" date="' . date ("Y/m/d - H:i:s", filectime($file)) . '">' . $file . '</file>');
					}
				} else {
					if (is_dir($file)) {
						echo('<file subtype="dir" size="0" date="' . date ("Y/m/d - H:i:s", filectime($file)) . '">' . $file . '</file>');
					} else {
						echo('<file subtype="' . subType($file) . '" size="' . filesize($file) . '" date="' . date ("Y/m/d - H:i:s", filectime($file)) . '">' . $file . '</file>');
					}
				}
			}
		}
		closedir($handler);
		endOutput();
		break;
	case 'delete':
		if (($target == "community") && ($level == "author")) {
			exitOnError('ERFMANAGER-1');
		} else {
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
			if ($target == "community") {
				if (!is_dir("community")) @mkdir("community");
				chdir ("community");
				chdir (".");
			} else {
				@session_start();
				if (!is_dir($_SESSION['usr_id'])) @mkdir($_SESSION['usr_id']);
				chdir ($_SESSION['usr_id']);
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
			// html? delete folder?
			if ($type == "html") {
				if (is_dir($file)) {
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
					foreach ($files as $fileinfo) {
						$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
						$todo($fileinfo->getRealPath());
					}
					@rmdir($file);
				} else {
					@unlink($file);
				}
			} else {
				// just delete a file
				@unlink($file);
			}
			// return
			startOutput();
			outputData("ac", $ac);
			noError();
			endOutput();
		}
		break;
	case 'playlist':
		// getting playlist information
		$plname = postString('plname');
		$totalElements = postInt('total');
		if ($totalElements <= 0) {
			exitOnError('ERFMANAGER-2');
		} else {
			// check playlist name and ID
			if ($plname == "") $plname = postString('file');
			$plid = noSpecial($plname) . "_" . (string)time();
			// create playlist xml
			$playlist = '<?xml version="1.0" encoding="UTF-8"?><data><playlist>';
			$playlist .= '<id>' . $plid . '</id>';
			$playlist .= '<meta>';
			$playlist .= '<title>' . $plname . '</title>';
			$playlist .= '<author id="' . $_SESSION['usr_id'] . '">' . $_SESSION['usr_name'] . '</author>';
			$playlist .= '<about></about></meta><elements>';
			$order = -1;
			for ($i=0; $i<$totalElements; $i++) {
				if ($target == "community") {
					$filecheck = "./community/" . $community . ".dis/media/community/" . $type . "/" . postString('file' . $i);
					$filepath = "media/community/" . $type . "/" . postString('file' . $i);
				} else {
					$filecheck = "./community/" . $community . ".dis/media/" . $_SESSION['usr_id'] . "/" . $type . "/" . postString('file' . $i);
					$filepath = "media/" . $_SESSION['usr_id'] . "/" . $type . "/" . postString('file' . $i);
				}
				if (@is_file($filecheck)) {
					$order++;
					$playlist .= '<element id="' . postString('file' . $i) . '" time="10" type="' . $type . '" end="stop" order="' . $order . '">';
					$playlist .= '<file format="' . subType(postString('file' . $i)) . '" lang="" absolute="0" feed="" feedType="" field=""><![CDATA[' . $filepath . ']]></file>';
					$playlist .= '<action /></element>';
				}
			}
			$playlist .= '</elements></playlist></data>';
			processPlaylists($playlist, $community);
			startOutput();
			outputData("ac", $ac);
			outputData("plname", $plname);
			outputData("plsize", ($order + 1));
			noError();
			endOutput();
		}
		break;
	case 'createfolder':
		if ($type == "html") {
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
			if ($target == "community") {
				if (!is_dir("community")) @mkdir("community");
				chdir ("community");
				chdir (".");
			} else {
				@session_start();
				if (!is_dir($_SESSION['usr_id'])) @mkdir($_SESSION['usr_id']);
				chdir ($_SESSION['usr_id']);
				chdir (".");
			}
			// file type folder
			if (!is_dir("html")) @mkdir("html");
			chdir ("html");
			chdir (".");
			// html path?
			if ($htmlpath != "") {
				$patharray = explode("/", $htmlpath);
				for ($i=0; $i<sizeof($patharray); $i++) {
					if (!is_dir($patharray[$i])) @mkdir($patharray[$i]);
					chdir ($patharray[$i]);
					chdir (".");
				}
			}
			if (is_dir($name)) {
				exitOnError('ERFMANAGER-4');
			} else {
				@mkdir($name);
				startOutput();
				outputData("ac", $ac);
				outputData("name", $name);
				noError();
				endOutput();
			}
		} else {
			exitOnError('ERFMANAGER-3');
		}
		break;
	default:
		exitOnError('ERFMANAGER-0');
		break;
}
?>