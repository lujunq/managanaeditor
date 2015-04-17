<?php
/**
 * Managana server: community edition handling.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// get community common functions
require_once('DIS_communitycommon.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'editor');

// list streams
$ac = postString('ac');
switch ($ac) {
	case 'get': // get current community edition date
		$result = queryDB("SELECT * FROM dis_community WHERE com_id='$community' LIMIT 1");
		if (mysql_num_rows($result) > 0) {
			startOutput();
			noError();
			$row = mysql_fetch_assoc($result);
			$date = explode("-", (string)$row['com_update']);
			outputData("year", $date[0]);
			outputData("month", $date[1]);
			outputData("day", $date[2]);
			endOutput();
		} else {
			exitOnError('EREDITION-0');
		}
		break;
	case 'set': // set the current community edition
		$date = postString('date');
		queryDB("UPDATE dis_community SET com_update='$date' WHERE com_id='$community'");
		writeCommunity($community);
		chdir("..");
		chdir(".");
		chdir("..");
		chdir(".");
		$comresult = queryDB("SELECT * FROM dis_community WHERE com_id='$community'");
		if (mysql_num_rows($comresult) == 0) {
			mysql_free_result($comresult);
			exitOnError('EREDITION-1');
		} else {
			// get community information
			$rowcom = mysql_fetch_assoc($comresult);
			// write the sitemap
			$baseurl = endSlash(INSTALLFOLDER) . "share.php?c=" . $community . "&s=";
			$sitemap = fopen("community/sitemap_" . $community . ".xml", "wb");
			fwrite($sitemap, '<?xml version="1.0" encoding="utf-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
			// home stream (higher priority)
			$stream = queryDB("SELECT * FROM dis_stream WHERE str_community='$community' AND str_state='publish' AND str_id='" . $rowcom['com_home'] . "' LIMIT 1");
			if (mysql_num_rows($stream) > 0) {
				$row = mysql_fetch_assoc($stream);
				$lastmod = explode(" ", $row['str_update']);
				fwrite($sitemap, sitemapURL(($baseurl . $row['str_id']), $lastmod[0], "1.0"));
			}
			mysql_free_result($stream);
			// other streams
			$stream = queryDB("SELECT * FROM dis_stream WHERE str_community='$community' AND str_state='publish' AND str_id!='" . $rowcom['com_home'] . "'");
			if (mysql_num_rows($stream) > 0) {
				for ($i=0; $i<mysql_num_rows($stream); $i++) {
					$row = mysql_fetch_assoc($stream);
					$lastmod = explode(" ", $row['str_update']);
					fwrite($sitemap, sitemapURL(($baseurl . $row['str_id']), $lastmod[0], "0.5"));
				}
			}
			mysql_free_result($stream);
			// end sitemap file
			fwrite($sitemap, '</urlset>');
			fclose($sitemap);
			mysql_free_result($comresult);
			// check all commnunity files for offline display
			$filelist = fopen("community/filelist_" . $community . ".xml", "wb");
			fwrite($filelist, '<?xml version="1.0" encoding="utf-8"?><filelist>');
			// open community folder
			chdir(".");
			chdir("community");
			chdir(".");
			chdir($community . ".dis");
			chdir(".");
			// check all files
			getDirectory($filelist, ".", 0);
			// end offline community file
			fwrite($filelist, '</filelist>');
			fclose($filelist);
			startOutput();
			noError();
			endOutput();
		}
		break;
	default: // unrecognized action
		exitOnError('ERGUIDE-0');
		break;
}
?>