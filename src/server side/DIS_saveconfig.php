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
			$FEEDTIMEOUT = postString('FEEDTIMEOUT');
			$ALLOWGUEST = postString('ALLOWGUEST');
			$READERUI = postString('READERUI');
			$INDEXCOMMUNITY = postString('INDEXCOMMUNITY');
			$LOGINTITLE = encodeApostrophe(nlToN(postString('LOGINTITLE')));
			$LOGINWITHGUEST = encodeApostrophe(nlToN(postString('LOGINWITHGUEST')));
			$LOGINNOGUEST = encodeApostrophe(nlToN(postString('LOGINNOGUEST')));
			$LOGINNORETURN = encodeApostrophe(nlToN(postString('LOGINNORETURN')));
			$LOGINOPENCANCEL = encodeApostrophe(nlToN(postString('LOGINOPENCANCEL')));
			$LOGINFAIL = encodeApostrophe(nlToN(postString('LOGINFAIL')));
			$LOGINOKRETURN = encodeApostrophe(nlToN(postString('LOGINOKRETURN')));
			$LOGINWAIT = encodeApostrophe(nlToN(postString('LOGINWAIT')));
			$LOGINOK = encodeApostrophe(nlToN(postString('LOGINOK')));
			$LOGINMAILFIELD = encodeApostrophe(nlToN(postString('LOGINMAILFIELD')));
			$LOGINPASSFIELD = encodeApostrophe(nlToN(postString('LOGINPASSFIELD')));
			$LOGINCHECKLABEL = encodeApostrophe(nlToN(postString('LOGINCHECKLABEL')));
			$LOGINRECOVERPASS = encodeApostrophe(nlToN(postString('LOGINRECOVERPASS')));
			$USESTATS = postString('USESTATS');
			$USERATE = postString('USERATE');
			$COMMENTMODE = postString('COMMENTMODE');
			$USESHARE = postString('USESHARE');
			$SHOWRATE = postString('SHOWRATE');
			$SHOWCOMMENT = postString('SHOWCOMMENT');
			$SHOWTIME = postString('SHOWTIME');
			$SHOWUSER = postString('SHOWUSER');
			$RATETEXT = encodeApostrophe(nlToN(postString('RATETEXT')));
			$COMMENTTEXT = encodeApostrophe(nlToN(postString('COMMENTTEXT')));
			$COMMENTADD = encodeApostrophe(nlToN(postString('COMMENTADD')));
			$COMMENTWAIT = encodeApostrophe(nlToN(postString('COMMENTWAIT')));
			$TIMEMENU = encodeApostrophe(nlToN(postString('TIMEMENU')));
			$RATEMENU = encodeApostrophe(nlToN(postString('RATEMENU')));
			$COMMENTMENU = encodeApostrophe(nlToN(postString('COMMENTMENU')));
			$VOTEMENU = encodeApostrophe(nlToN(postString('VOTEMENU')));
			$REMOTEMENU = encodeApostrophe(nlToN(postString('REMOTEMENU')));
			$USERMENU = encodeApostrophe(nlToN(postString('USERMENU')));
			$COMMENTBUTTON = encodeApostrophe(nlToN(postString('COMMENTBUTTON')));
			$LOGINREQUIRED = encodeApostrophe(nlToN(postString('LOGINREQUIRED')));
			$LOGINRECOVERPASS = encodeApostrophe(nlToN(postString('LOGINRECOVERPASS')));
			$LOGINBUTTON = encodeApostrophe(nlToN(postString('LOGINBUTTON')));
			$LOGOUTBUTTON = encodeApostrophe(nlToN(postString('LOGOUTBUTTON')));
			$LOGINSUCCESS = encodeApostrophe(nlToN(postString('LOGINSUCCESS')));
			$LOGINERROR = encodeApostrophe(nlToN(postString('LOGINERROR')));
			$READERERROR = encodeApostrophe(nlToN(postString('READERERROR')));
			$CREATEALLOW = postString('CREATEALLOW');
			$CREATETEXT = encodeApostrophe(nlToN(postString('CREATETEXT')));
			$CREATELINK = encodeApostrophe(nlToN(postString('CREATELINK')));
			$MONTH1 = encodeApostrophe(nlToN(postString('MONTH1')));
			$MONTH2 = encodeApostrophe(nlToN(postString('MONTH2')));
			$MONTH3 = encodeApostrophe(nlToN(postString('MONTH3')));
			$MONTH4 = encodeApostrophe(nlToN(postString('MONTH4')));
			$MONTH5 = encodeApostrophe(nlToN(postString('MONTH5')));
			$MONTH6 = encodeApostrophe(nlToN(postString('MONTH6')));
			$MONTH7 = encodeApostrophe(nlToN(postString('MONTH7')));
			$MONTH8 = encodeApostrophe(nlToN(postString('MONTH8')));
			$MONTH9 = encodeApostrophe(nlToN(postString('MONTH9')));
			$MONTH10 = encodeApostrophe(nlToN(postString('MONTH10')));
			$MONTH11 = encodeApostrophe(nlToN(postString('MONTH11')));
			$MONTH12 = encodeApostrophe(nlToN(postString('MONTH12')));
			$CIRRUS = postString('CIRRUS');
			queryDB("UPDATE dis_options SET opt_value='$CIRRUS' WHERE opt_name='CIRRUS'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH1' WHERE opt_name='MONTH1'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH2' WHERE opt_name='MONTH2'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH3' WHERE opt_name='MONTH3'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH4' WHERE opt_name='MONTH4'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH5' WHERE opt_name='MONTH5'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH6' WHERE opt_name='MONTH6'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH7' WHERE opt_name='MONTH7'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH8' WHERE opt_name='MONTH8'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH9' WHERE opt_name='MONTH9'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH10' WHERE opt_name='MONTH10'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH11' WHERE opt_name='MONTH11'");
			queryDB("UPDATE dis_options SET opt_value='$MONTH12' WHERE opt_name='MONTH12'");
			queryDB("UPDATE dis_options SET opt_value='$FEEDTIMEOUT' WHERE opt_name='FEEDTIMEOUT'");
			queryDB("UPDATE dis_options SET opt_value='$ALLOWGUEST' WHERE opt_name='ALLOWGUEST'");
			queryDB("UPDATE dis_options SET opt_value='$READERUI' WHERE opt_name='READERUI'");
			queryDB("UPDATE dis_options SET opt_value='$INDEXCOMMUNITY' WHERE opt_name='INDEXCOMMUNITY'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINTITLE' WHERE opt_name='LOGINTITLE'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINWITHGUEST' WHERE opt_name='LOGINWITHGUEST'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINNOGUEST' WHERE opt_name='LOGINNOGUEST'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINNORETURN' WHERE opt_name='LOGINNORETURN'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINOPENCANCEL' WHERE opt_name='LOGINOPENCANCEL'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINFAIL' WHERE opt_name='LOGINFAIL'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINOKRETURN' WHERE opt_name='LOGINOKRETURN'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINWAIT' WHERE opt_name='LOGINWAIT'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINOK' WHERE opt_name='LOGINOK'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINMAILFIELD' WHERE opt_name='LOGINMAILFIELD'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINPASSFIELD' WHERE opt_name='LOGINPASSFIELD'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINCHECKLABEL' WHERE opt_name='LOGINCHECKLABEL'");
			queryDB("UPDATE dis_options SET opt_value='$USESTATS' WHERE opt_name='USESTATS'");
			queryDB("UPDATE dis_options SET opt_value='$USERATE' WHERE opt_name='USERATE'");
			queryDB("UPDATE dis_options SET opt_value='$COMMENTMODE' WHERE opt_name='COMMENTMODE'");
			queryDB("UPDATE dis_options SET opt_value='$USESHARE' WHERE opt_name='USESHARE'");
			queryDB("UPDATE dis_options SET opt_value='$SHOWRATE' WHERE opt_name='SHOWRATE'");
			queryDB("UPDATE dis_options SET opt_value='$SHOWCOMMENT' WHERE opt_name='SHOWCOMMENT'");
			queryDB("UPDATE dis_options SET opt_value='$SHOWTIME' WHERE opt_name='SHOWTIME'");
			queryDB("UPDATE dis_options SET opt_value='$SHOWUSER' WHERE opt_name='SHOWUSER'");
			queryDB("UPDATE dis_options SET opt_value='$RATETEXT' WHERE opt_name='RATETEXT'");
			queryDB("UPDATE dis_options SET opt_value='$COMMENTTEXT' WHERE opt_name='COMMENTTEXT'");
			queryDB("UPDATE dis_options SET opt_value='$COMMENTADD' WHERE opt_name='COMMENTADD'");
			queryDB("UPDATE dis_options SET opt_value='$COMMENTWAIT' WHERE opt_name='COMMENTWAIT'");
			queryDB("UPDATE dis_options SET opt_value='$TIMEMENU' WHERE opt_name='TIMEMENU'");
			queryDB("UPDATE dis_options SET opt_value='$RATEMENU' WHERE opt_name='RATEMENU'");
			queryDB("UPDATE dis_options SET opt_value='$COMMENTMENU' WHERE opt_name='COMMENTMENU'");
			queryDB("UPDATE dis_options SET opt_value='$VOTEMENU' WHERE opt_name='VOTEMENU'");
			queryDB("UPDATE dis_options SET opt_value='$REMOTEMENU' WHERE opt_name='REMOTEMENU'");
			queryDB("UPDATE dis_options SET opt_value='$USERMENU' WHERE opt_name='USERMENU'");
			queryDB("UPDATE dis_options SET opt_value='$COMMENTBUTTON' WHERE opt_name='COMMENTBUTTON'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINREQUIRED' WHERE opt_name='LOGINREQUIRED'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINBUTTON' WHERE opt_name='LOGINBUTTON'");
			queryDB("UPDATE dis_options SET opt_value='$LOGOUTBUTTON' WHERE opt_name='LOGOUTBUTTON'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINSUCCESS' WHERE opt_name='LOGINSUCCESS'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINERROR' WHERE opt_name='LOGINERROR'");
			queryDB("UPDATE dis_options SET opt_value='$READERERROR' WHERE opt_name='READERERROR'");
			queryDB("UPDATE dis_options SET opt_value='$CREATEALLOW' WHERE opt_name='CREATEALLOW'");
			queryDB("UPDATE dis_options SET opt_value='$CREATETEXT' WHERE opt_name='CREATETEXT'");
			queryDB("UPDATE dis_options SET opt_value='$CREATELINK' WHERE opt_name='CREATELINK'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVERPASS' WHERE opt_name='LOGINRECOVERPASS'");
			break;
		case "editor":
			$REVISIONS = postString('REVISIONS');
			queryDB("UPDATE dis_options SET opt_value='$REVISIONS' WHERE opt_name='REVISIONS'");
			break;
		case "mailing":
			$MAILSENDER = postString('MAILSENDER');
			$MAILRECOVERSUBJECT = encodeApostrophe(nlToN(postString('MAILRECOVERSUBJECT')));
			$MAILRECOVERBODY = encodeApostrophe(nlToN(postString('MAILRECOVERBODY')));
			$MAILNEWSUBJECT = encodeApostrophe(nlToN(postString('MAILNEWSUBJECT')));
			$MAILNEWBODY = encodeApostrophe(nlToN(postString('MAILNEWBODY')));
			$MAILNEWBODYSUBSCRIBER = encodeApostrophe(nlToN(postString('MAILNEWBODYSUBSCRIBER')));
			queryDB("UPDATE dis_options SET opt_value='$MAILSENDER' WHERE opt_name='MAILSENDER'");
			queryDB("UPDATE dis_options SET opt_value='$MAILRECOVERSUBJECT' WHERE opt_name='MAILRECOVERSUBJECT'");
			queryDB("UPDATE dis_options SET opt_value='$MAILRECOVERBODY' WHERE opt_name='MAILRECOVERBODY'");
			queryDB("UPDATE dis_options SET opt_value='$MAILNEWSUBJECT' WHERE opt_name='MAILNEWSUBJECT'");
			queryDB("UPDATE dis_options SET opt_value='$MAILNEWBODY' WHERE opt_name='MAILNEWBODY'");
			queryDB("UPDATE dis_options SET opt_value='$MAILNEWBODYSUBSCRIBER' WHERE opt_name='MAILNEWBODYSUBSCRIBER'");
			break;
		case "recover":
			$RECOVERMESSAGEOK = encodeApostrophe(nlToN(postString('RECOVERMESSAGEOK')));
			$RECOVERMESSAGEERROR = encodeApostrophe(nlToN(postString('RECOVERMESSAGEERROR')));
			$LOGINRECOVERABOUT = encodeApostrophe(nlToN(postString('LOGINRECOVERABOUT')));
			$LOGINRECOVERNOTFOUND = encodeApostrophe(nlToN(postString('LOGINRECOVERNOTFOUND')));
			$LOGINRECOVEROK = encodeApostrophe(nlToN(postString('LOGINRECOVEROK')));
			$LOGINRECOVERBUTTON = encodeApostrophe(nlToN(postString('LOGINRECOVERBUTTON')));
			$LOGINRECOVERMAILFIELD = encodeApostrophe(nlToN(postString('LOGINRECOVERMAILFIELD')));
			$LOGINRECOVERPASSFIELD = encodeApostrophe(nlToN(postString('LOGINRECOVERPASSFIELD')));
			queryDB("UPDATE dis_options SET opt_value='$RECOVERMESSAGEOK' WHERE opt_name='RECOVERMESSAGEOK'");
			queryDB("UPDATE dis_options SET opt_value='$RECOVERMESSAGEERROR' WHERE opt_name='RECOVERMESSAGEERROR'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVERABOUT' WHERE opt_name='LOGINRECOVERABOUT'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVERNOTFOUND' WHERE opt_name='LOGINRECOVERNOTFOUND'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVEROK' WHERE opt_name='LOGINRECOVEROK'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVERBUTTON' WHERE opt_name='LOGINRECOVERBUTTON'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVERMAILFIELD' WHERE opt_name='LOGINRECOVERMAILFIELD'");
			queryDB("UPDATE dis_options SET opt_value='$LOGINRECOVERPASSFIELD' WHERE opt_name='LOGINRECOVERPASSFIELD'");
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
		if ($row['opt_name'] == 'READERUI') $outputreader .= '<ui>' . $row['opt_value'] . '</ui>';
		if ($row['opt_name'] == 'INSTALLFOLDER') $outputreader .= '<server>' . $row['opt_value'] . '/</server>';
		if ($row['opt_name'] == 'MULTICASTIP') $outputreader .= '<multicastip>' . $row['opt_value'] . '</multicastip>';
		if ($row['opt_name'] == 'MULTICASTPORT') $outputreader .= '<multicastport>' . $row['opt_value'] . '</multicastport>';
		if ($row['opt_name'] == 'REMOTEGROUP') $outputreader .= '<remotegroup>' . $row['opt_value'] . '</remotegroup>';
		if ($row['opt_name'] == 'INDEXCOMMUNITY') {
			if ($row['opt_value'] != "") $outputreader .= '<community>./community/' . $row['opt_value'] . '.dis</community>';
				else $outputreader .= '<community></community>';
		}
	}
	$output .= "?>";
	$outputreader .= '</data>';
	mysql_free_result($check);
	$file = fopen("DIS_config.php", 'wb');
	fputs($file, $output);
	fclose($file);
	$file = fopen("managana.xml", 'wb');
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