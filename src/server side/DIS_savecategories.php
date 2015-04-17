<?php
/**
 * Managana server: list of a community categories.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$community = postString('community');
$level = communityLevel($community);
minimumLevel($level, 'admin');

// get data
$count = postInt('count');

// remove previously saved categories
queryDB("DELETE FROM dis_category WHERE cat_community='$community'");

// add new categories
for ($i=0; $i<$count; $i++) {
	queryDB("INSERT INTO dis_category (cat_community, cat_name) VALUES ('$community', '" . encodeApostrophe(postString('name' . $i)) . "')");
}

startOutput();
noError();
endOutput();
?>