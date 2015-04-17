<?php
/**
 * Managana server: stream geolocation properties.
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// check for user access level
$community = postString('community');
$stream = postString('stream');
$level = communityLevel($community);
$action = postString('action');
minimumLevel($level, 'author');

// check action
switch ($action) {
	case "list":
		// check geolocation properties for current stream
		$prop = queryDB("SELECT * FROM dis_streamgeodata WHERE sgd_community='$community' AND sgd_stream='$stream'");
		startOutput();
		noError();
		if (mysql_num_rows($prop) > 0) {
			$row = mysql_fetch_assoc($prop);
			echo('<geouse>' . $row['sgd_use'] . '</geouse>');
			echo('<geotarget><![CDATA[' . $row['sgd_target'] . ']]></geotarget>');
			echo('<geomap><![CDATA[' . $row['sgd_map'] . ']]></geomap>');
			echo('<geolattop><![CDATA[' . $row['sgd_latitudetop'] . ']]></geolattop>');
			echo('<geolongtop><![CDATA[' . $row['sgd_longitudetop'] . ']]></geolongtop>');
			echo('<geolatbottom><![CDATA[' . $row['sgd_latitudebottom'] . ']]></geolatbottom>');
			echo('<geolongbottom><![CDATA[' . $row['sgd_longitudebottom'] . ']]></geolongbottom>');
		} else {
			echo('<geouse>0</geouse>');
			echo('<geotarget></geotarget>');
			echo('<geomap></geomap>');
			echo('<geolattop>0</geolattop>');
			echo('<geolongtop>0</geolongtop>');
			echo('<geolatbottom>0</geolatbottom>');
			echo('<geolongbottom>0</geolongbottom>');
		}
		mysql_free_result($prop);
		// check geolocation points
		$points = queryDB("SELECT * FROM dis_streamgeopoint WHERE sgp_community='$community' AND sgp_stream='$stream' ORDER BY sgp_name");
		if (mysql_num_rows($points) > 0) {
			for ($i=0; $i<mysql_num_rows($points); $i++) {
				$row = mysql_fetch_assoc($points);
				echo('<geopoint>');
				echo('<name><![CDATA[' . $row['sgp_name'] . ']]></name>');
				echo('<latitude><![CDATA[' . $row['sgp_latitude'] . ']]></latitude>');
				echo('<longitude><![CDATA[' . $row['sgp_longitude'] . ']]></longitude>');
				echo('<code><![CDATA[' . $row['sgp_code'] . ']]></code>');
				echo('</geopoint>');
			}
		}
		mysql_free_result($points);
		endOutput();
		break;
	case "save":
		// get and save properties
		$geouse = postString('geouse');
		$geotarget = postString('geotarget');
		$geomap = postString('geomap');
		$geolattop = postString('geolattop');
		$geolongtop = postString('geolongtop');
		$geolatbottom = postString('geolatbottom');
		$geolongbottom = postString('geolongbottom');
		queryDB("DELETE FROM dis_streamgeodata WHERE sgd_community='$community' AND sgd_stream='$stream'");
		queryDB("INSERT INTO dis_streamgeodata (sgd_community, sgd_stream, sgd_use, sgd_target, sgd_map, sgd_latitudetop, sgd_longitudetop, sgd_latitudebottom, sgd_longitudebottom) VALUES ('$community', '$stream', '$geouse', '$geotarget', '$geomap', '$geolattop', '$geolongtop', '$geolatbottom', '$geolongbottom')");
		// get and save points
		queryDB("DELETE FROM dis_streamgeopoint WHERE sgp_community='$community' AND sgp_stream='$stream'");
		$numpoints = postInt('numpoints');
		for ($i=0; $i<$numpoints; $i++) {
			queryDB("INSERT INTO dis_streamgeopoint (sgp_community, sgp_stream, sgp_latitude, sgp_longitude, sgp_code, sgp_name) VALUES ('$community', '$stream', '" . postString('geopointlat' . $i) . "', '" . postString('geopointlong' . $i) . "', '" . postString('geopointcode' . $i) . "','" . postString('geopointname' . $i) . "')");
		}
		// return
		startOutput();
		noError();
		endOutput();
		break;
	default:
		exitOnError("ERCVAR-0");
		break;
}
?>