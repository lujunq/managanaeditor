<?php
/**
 * Managana server: check database connection.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check file upload size
$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

// if no error until now, return an OK response
startOutput();
noError();
outputData('cfolder', "community");
outputData('uploadsize', $upload_mb);
endOutput();
?>