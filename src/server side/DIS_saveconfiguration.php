<?php
/**
 * Managana server: save configuration values.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check user level
$level = requestLevel();
minimumLevel($level, 'super');

// get values to update
$error = "";
$action = postString('action');

if ($action == "") {
	$error = "ERCONF-0";
} else {
	// check what to change
	switch ($action) {
		case "facebook":
			$FBAPPID = postString('FBAPPID');
			$FBAPPSECRET = postString('FBAPPSECRET');
			queryDB("UPDATE dis_options SET opt_value='$FBAPPID' WHERE opt_name='FBAPPID'");
			queryDB("UPDATE dis_options SET opt_value='$FBAPPSECRET' WHERE opt_name='FBAPPSECRET'");
			break;
		case "reader":
			$total = postInt('total');
			for ($i=0; $i<$total; $i++) {
				queryDB("UPDATE dis_options SET opt_value='" . encodeApostrophe(nlToN(postString('value' . $i))) . "' WHERE opt_name='" . postString('name' . $i) . "'");
			}
			break;
		case "editor":
			$REVISIONS = postString('REVISIONS');
			queryDB("UPDATE dis_options SET opt_value='$REVISIONS' WHERE opt_name='REVISIONS'");
			break;
	}
	// write configuration files
	$check = queryDB("SELECT * FROM dis_options");
	$output = "<?php\n";
	$output .= "// Managana configuration file\n";
	$outputreader = '<?xml version="1.0" encoding="utf-8"?><data>';
	for ($i=0; $i<mysql_num_rows($check); $i++) {
		$row = mysql_fetch_assoc($check);
		$output .= 'define("' . $row['opt_name'] . '", "' . $row['opt_value'] . '");' . "\n";
		if (strrpos($row['opt_file'], "managana") === false) {
			// do not write on xml config file
		} else {
			$outputreader .= '<config><name>' . $row['opt_xmlname'] . '</name><value>' . $row['opt_value'] . '</value></config>';
		}
	}
	$output .= "?>";
	$outputreader .= '</data>';
	mysql_free_result($check);
	$file = fopen("DIS_config.php", 'wb');
	fputs($file, $output);
	fclose($file);
	$file = fopen("managanaconfig.xml", 'wb');
	fputs($file, $outputreader);
	fclose($file);
}

// write output
if ($error != "") {
	exitOnError($error);
} else {
	startOutput();
	noError();
	endOutput();	
}
?>