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

// clear session
@session_start();
unset($_SESSION['usr_index']);
unset($_SESSION['usr_id']);
unset($_SESSION['usr_email']);
unset($_SESSION['usr_name']);
unset($_SESSION['usr_level']);
	
// output
startOutput();
noError();
endOutput();
?>