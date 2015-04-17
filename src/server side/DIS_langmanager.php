<?php
/**
 * Managana server: reader interface text manager.
 */

// get configuration
require_once('DIS_config.php');

// get database connection
require_once('DIS_database.php');

// get common functions
require_once('DIS_common.php');

// common functions for file handling
require_once('DIS_filecommon.php');

// check user level
$level = requestLevel();
minimumLevel($level, 'super');

// get data
$ac = postString('ac');

// check action
switch ($ac) {
	case 'languages':
		// check available languages
		$values = queryDB("SELECT * FROM dis_language GROUP BY lng_language");
		startOutput();
		noError();
		outputData("ac", $ac);
		for ($i=0; $i<mysql_num_rows($values); $i++) {
			$row = mysql_fetch_assoc($values);
			outputData("language", $row['lng_language']);
		}
		endOutput();
		mysql_free_result($values);
		break;
	case 'check':
		// check all language entries
		$lang = postString('lang');
		$values = queryDB("SELECT * FROM dis_language WHERE lng_language='$lang'");
		startOutput();
		noError();
		outputData("ac", $ac);
		for ($i=0; $i<mysql_num_rows($values); $i++) {
			$row = mysql_fetch_assoc($values);
			echo('<language><name>' . $row['lng_name'] . '</name><value><![CDATA[' . decodeApostrophe($row['lng_value']) . ']]></value></language>');
		}
		endOutput();
		mysql_free_result($values);
		break;
	case 'create':
		// create a new language set
		$lang = postString('lang');
		$values = queryDB("SELECT * FROM dis_language WHERE lng_language='$lang'");
		if (mysql_num_rows($values) > 0) {
			mysql_free_result($values);
			exitOnError('ERLMANAGER-2');
		} else {
			mysql_free_result($values);
			$values = queryDB("SELECT * FROM dis_language WHERE lng_language='default'");
			for ($i=0; $i<mysql_num_rows($values); $i++) {
				$row = mysql_fetch_assoc($values);
				queryDB("INSERT INTO dis_language (lng_name, lng_value, lng_language) VALUES ('" . $row['lng_name'] . "', '" . $row['lng_value'] . "', '" . $lang . "')");
			}
			mysql_free_result($values);
			startOutput();
			noError();
			outputData("ac", $ac);
			endOutput();
		}
		break;
	case 'save':
	case 'import':
		$download = postString('download');
		$lang = postString('lang');
		$total = postInt('total');
		// importing? create the language entries on database
		if ($ac == "import") {
			$values = queryDB("SELECT * FROM dis_language WHERE lng_language='$lang'");
			if (mysql_num_rows($values) > 0) {
				mysql_free_result($values);
				exitOnError('ERLMANAGER-4');
			} else {
				mysql_free_result($values);
				$values = queryDB("SELECT * FROM dis_language WHERE lng_language='default'");
				for ($i=0; $i<mysql_num_rows($values); $i++) {
					$row = mysql_fetch_assoc($values);
					queryDB("INSERT INTO dis_language (lng_name, lng_value, lng_language) VALUES ('" . $row['lng_name'] . "', '" . $row['lng_value'] . "', '" . $lang . "')");
				}
				mysql_free_result($values);
			}
		}
		// save the language information
		$file = fopen("language/language_" . $lang . ".xml", "wb");
		fwrite($file, '<?xml version="1.0" encoding="utf-8"?><data>');
		$phpfile = fopen("language/language_" . $lang . ".php", "wb");
		fwrite($phpfile, '<?php' . "\n");
		for ($i=0; $i<$total; $i++) {
			queryDB("UPDATE dis_language SET lng_value='" . encodeApostrophe(postString('value' . $i)) . "' WHERE lng_name='" . postString('name' . $i) . "' AND lng_language='$lang' LIMIT 1");
			fwrite($file, '<language><name>' . postString('name' . $i) . '</name><value><![CDATA[' . postString('value' . $i) . ']]></value></language>');
			fwrite($phpfile, '$text["' . postString('name' . $i) . '"] = "' . str_replace('"', "'", postString('value' . $i)) . '";' . "\n");
		}
		fwrite($file, '</data>');
		fclose($file);
		fwrite($phpfile, '?>');
		fclose($phpfile);
		startOutput();
		noError();
		outputData("ac", $ac);
		outputData("download", $download);
		endOutput();
		break;
	default:
		exitOnError('ERLMANAGER-0');
		break;
}
?>