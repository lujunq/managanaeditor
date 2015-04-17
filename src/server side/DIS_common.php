<?php
/**
 * Managana server common functions.
 */
 
// timezone fix
date_default_timezone_set("America/Sao_Paulo"); 

// get configuration
require_once('DIS_config.php');

/**
 * Script root folder.
 */
define('ROOTFOLDER', getcwd());
 
/**
 * Start XML output.
 */
function startOutput() {
	echo('<?xml version="1.0" encoding="utf-8"?><data>');
	echo('<agent>' . AGENT . '</agent>');
}

/**
 * End XML output.
 */
function endOutput() {
	echo('</data>');
}

/**
 * Add a non-error sign to XML output.
 */
function noError() {
	echo('<error id="0" />');
}

/**
 * Finish output with an error.
 */
function exitOnError($level) {
	startOutput();
	echo('<error id="' . $level . '" />');
	endOutput();
	exit();
}

/**
 * Output a xml node with name/value provided.
 */
function outputData($name, $value) {
	echo("<$name>$value</$name>");
}

/**
 * Output a xml node with search result information.
 */
function outputResult($label, $data) {
	echo("<result><label><![CDATA[$label]]></label><data><![CDATA[$data]]></data></result>");
}

/**
 * Check if the script can run according to logged user access level.
 */
function requestLevel() {
	@session_start();
	if (isset($_SESSION['usr_level'])) {
		// check level
		$return = "";
		$allow = false;
		switch($_SESSION['usr_level']) {
			case 'super':
				$return = 'super';
				$allow = true;
				break;
			case 'admin':
				$return = 'admin';
				$allow = true;
				break;
			case 'user':
				$return = 'user';
				$allow = true;
				break;
			case 'subscriber':
				$return = 'subscriber';
				$allow = true;
				break;
		}
		if (!$allow) {
			exitOnError('ERACCESS-0');
		} else {
			return($return);
		}
	} else {
		// no user logged
		exitOnError('ERACCESS-0');
	}
}

/**
 * Check the current user access level to a community,
 */
function communityLevel($id) {
	@session_start();
	$level = "";
	if (isset($_SESSION['usr_id'])) {
		if ($_SESSION['usr_level'] == "super") {
			$level = "super";
		} else {
			$levelcheck = queryDB("SELECT * FROM dis_usercommunity WHERE usc_community='$id' AND usc_user='" . $_SESSION['usr_id'] . "'");
			if (mysql_num_rows($levelcheck) == 0) {
				mysql_free_result($levelcheck);
				exitOnError('ERACCESS-0');
			} else {
				$row = mysql_fetch_assoc($levelcheck);
				$level = $row['usc_level'];
				mysql_free_result($levelcheck);
			}
		}
	} else {
		// no user logged
		exitOnError('ERACCESS-0');
	}
	return($level);
}

/**
 * Exit script if minimum requested level is not met.
 */
function minimumLevel($level, $requested) {
	switch ($requested) {
		case "super":
			if ($level == "super") {
				// ok
			} else {
				// access error
				exitOnError('ERACCESS-0');
			}
			break;
		case "admin":
			if (($level == "super") || ($level == "admin")) {
				// ok
			} else {
				// access error
				exitOnError('ERACCESS-0');
			}
			break;
		case "editor":
			if (($level == "super") || ($level == "admin") || ($level == "editor")) {
				// ok
			} else {
				// access error
				exitOnError('ERACCESS-0');
			}
			break;
		case "author":
			if (($level == "super") || ($level == "admin") || ($level == "editor") || ($level == "author")) {
				// ok
			} else {
				// access error
				exitOnError('ERACCESS-0');
			}
			break;
		case "user":
			if (($level == "super") || ($level == "admin") || ($level == "editor") || ($level == "author") || ($level == "user")) {
				// ok
			} else {
				// access error
				exitOnError('ERACCESS-0');
			}
			break;
	}
}

// is a get variable available?
function isGet($name) {
	return(isset($_GET[$name]));
}

// get a string passed by get method
function getString($name) {
	if (isGet($name)) return (trim($_GET[$name]));
		else return("");
}

// is a post variable available?
function isPost($name) {
	return(isset($_POST[$name]));
}

// encode apostrophes for database storage
function encodeApostrophe($text) {
	$text = str_replace("'", "&#39;", $text);
	$text = str_replace('"', "&#34;", $text);
	return ($text);
}

// decode apostrophes for database storage
function decodeApostrophe($text) {
	$text = str_replace("&#39;", "'", $text);
	$text = str_replace("&#34;", '"', $text);
	return ($text);
}

/**
 * Receive a POST string.
 */
function postString($name) {
	if (isset($_POST[$name])) {
		$return = trim($_POST[$name]);
		$return = str_replace('\\"', '"', $return);
		$return = str_replace("\\'", "'", $return);
		return ($return);
	} else {
		return ("");
	}
}

/**
 * Receive a POST integer.
 */
function postInt($name) {
	if (isset($_POST[$name])) {
		if (is_numeric($_POST[$name])) {
			return ((int)$_POST[$name]);
		} else {
			return (0);
		}
	} else {
		return (0);
	}
}

/**
 * Receive a POST float.
 */
function postFloat($name) {
	if (isset($_POST[$name])) {
		if (is_numeric($_POST[$name])) {
			return ((float)$_POST[$name]);
		} else {
			return (0);
		}
	} else {
		return (0);
	}
}

/**
 * Receive a POST boolean.
 */
function postBool($name) {
	$return = false;
	if (isset($_POST[$name])) {
		if (($_POST[$name] == "true") || ($_POST[$name] == "1")) {
			$return = true;
		}
	}
	return($return);
}

/**
 * Convert a true/false string to numeric value (0 or 1).
 */
function intBool($bool) {
	if ($bool === true) return (1);
		else if ($bool === false) return (0);
		else if ($bool == "true") return (1);
		else if ($bool == "1") return (1);
		else if ($bool == 1) return (1);
		else return (0);
}

/**
 * Receive a POST color value (convert to hex).
 */
function postColor($name) {
	return("0x" . dechex(postInt($name)));
}

/**
 * Receive a POST date.
 */
function postDate($name) {
	$date = explode("/", postString($name));
	if (sizeof($date) == 3) return($date[2] . "-" . $date[0] . "-" . $date[1]);
		else return('0000-00-00');
}

/**
 * Open a debug file output and return its reference.
 */
function beginDebug($name) {
	return(fopen($name, 'wb'));
}

/**
 * Add a text to the debug file.
 */
function showDebug($file, $text) {
	fputs($file, ($text . "\n"));
}

/**
 * Close the debug file.
 */
function closeDebug($file) {
	fclose($file);
}

// create a random char key
function randKey($size) {
	$return = "";
	for ($i=0; $i<$size; $i++) {
		$return .= chr(rand(65, 90));
	}
	return ($return);
}

// current script name
function scriptName() {
	$namearr = explode("/", $_SERVER['SCRIPT_NAME']);
	return($namearr[sizeof($namearr) - 1]);
}

// return the connection IP
function checkIP() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
		else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else $ip = $_SERVER['REMOTE_ADDR'];
	return($ip);
}

// return a string representation of provided date
function dateString($date) {
	global $datetext;
	$month = "";
	switch (date("n", $date)) {
		case 1: $month = MONTH1 . ", "; break;
		case 2: $month = MONTH2 . ", "; break;
		case 3: $month = MONTH3 . ", "; break;
		case 4: $month = MONTH4 . ", "; break;
		case 5: $month = MONTH5 . ", "; break;
		case 6: $month = MONTH6 . ", "; break;
		case 7: $month = MONTH7 . ", "; break;
		case 8: $month = MONTH8 . ", "; break;
		case 9: $month = MONTH9 . ", "; break;
		case 10: $month = MONTH10 . ", "; break;
		case 11: $month = MONTH11 . ", "; break;
		case 12: $month = MONTH12 . ", "; break;
	}
	return($month . date("d, Y - H:i", $date));
}

// conver a string into an url-safe one without spaces or other special chars
function noSpecial($string) {
	$output = "";
	$string = trim($string);
	for ($i=0; $i<strlen($string); $i++) {
		$char = substr($string, $i, 1);
		if ($char != urlencode($char)) $char = "_";
		$output .= $char;
	}
	return($output);
}

// convert new lines to \n
function nlToN($text) {
	return(str_replace("<br />", "\n", str_replace("<br>", "\n", str_replace("\r", "", str_replace("\n", "", nl2br($text))))));
}

// check a string for ending /
function endSlash($url) {
	if (substr($url, -1) != "/") $url = $url . "/";
	return ($url);
}
?>