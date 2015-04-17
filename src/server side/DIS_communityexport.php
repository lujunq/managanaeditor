<?php
/**
 * Managana server: community file export.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// check for user access level
//requestLevel('admin');

// let script run for unlimited time
ini_set("max_execution_time", 0);

// get community data
$id = postString('community');

// check for the export folder
@chdir(ROOTFOLDER);
@chdir(".");
@chdir(CFOLDER);
@chdir(".");
if (!is_dir(EXPORTFOLDER)) @mkdir(EXPORTFOLDER);
@chdir(ROOTFOLDER);
@chdir(".");

// check for previous export
if (is_file(CFOLDER . "/" . EXPORTFOLDER . "/" . $id . ".zip")) unlink(CFOLDER . "/" . EXPORTFOLDER . "/" . $id . ".zip");

// create export zip file
$export = new ZipArchive();
if ($export->open(CFOLDER . "/" . EXPORTFOLDER . "/" . $id . ".zip", ZIPARCHIVE::CREATE) !== true) {
	exitOnError('ERCOMMUNITY-4');
} else {
	// add standard export content
	$export->addFile("player/expressInstall.swf", "expressInstall.swf") or exitOnError('ERCOMMUNITY-4');
	$export->addFile("player/index.html", "index.html") or exitOnError('ERCOMMUNITY-4');
	$export->addFile("player/lgpl-3.0.txt", "lgpl-3.0.txt") or exitOnError('ERCOMMUNITY-4');
	$export->addFile("player/managana.swf", "managana.swf") or exitOnError('ERCOMMUNITY-4');
	$export->addFile("player/readme.txt", "readme.txt") or exitOnError('ERCOMMUNITY-4');
	$export->addFile("player/swfobject.js", "swfobject.js") or exitOnError('ERCOMMUNITY-4');
	// add configuration file
	$conf = '<?xml version="1.0" encoding="utf-8"?><data><community>' . $id . '.dis</community></data>';
	$export->addFromString("managana.xml", $conf) or exitOnError('ERCOMMUNITY-4');
	// add community files
	@chdir(ROOTFOLDER);
	@chdir(".");
	@chdir(CFOLDER);
	@chdir(".");
	$disfolder = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($id . ".dis/"));
	foreach ($disfolder as $key=>$value) {
		$export->addFile(realpath($key), $key) or exitOnError('ERCOMMUNITY-4');
	}
	$export->close();
	// prepare output
	startOutput();
	noError();
	outputData('export', (INSTALLFOLDER . "/" . CFOLDER . "/" . EXPORTFOLDER . "/" . $id . ".zip"));
	endOutput();
}
?>