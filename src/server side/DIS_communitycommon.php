<?php
/**
 * Managana server: community common functions
 */

// get configuration
require_once('DIS_config.php');

// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

/**
 * Write the XML description of a community.
 */
function writeCommunity($id) {
	// check for community
	$checkid = queryDB("SELECT * FROM dis_community WHERE com_id='$id'");
	if (mysql_num_rows($checkid) > 0) {
		// is there a communty folder?
		@chdir(ROOTFOLDER);
		@chdir("."); // for windows hosts
		@chdir("community");
		@chdir(".");
		if (!is_dir($id . ".dis")) @mkdir($id . ".dis");
		// move to community folder
		@chdir($id . ".dis");
		@chdir(".");
		// create a new, empty file
		if (is_file("dis.xml")) unlink("dis.xml");
		$file = fopen("dis.xml", "wb");
		fwrite($file, '<?xml version="1.0" encoding="utf-8"?><dis>');
		// get data
		$row = mysql_fetch_assoc($checkid);
		fwrite($file, '<meta>');
		fwrite($file, '<id>' . $row['com_id'] . '</id>');
		fwrite($file, '<title>' . $row['com_title'] . '</title>');
		fwrite($file, '<copyleft><![CDATA[' . decodeApostrophe($row['com_copyleft']) . ']]></copyleft>');
		fwrite($file, '<copyright><![CDATA[' . decodeApostrophe($row['com_copyright']) . ']]></copyright>');
		fwrite($file, '<excerpt><![CDATA[' . decodeApostrophe($row['com_excerpt']) . ']]></excerpt>');
		fwrite($file, '<about><![CDATA[' . decodeApostrophe($row['com_about']) . ']]></about>');
		fwrite($file, '<icon><![CDATA[' . $row['com_icon'] . ']]></icon>');
		fwrite($file, '<lang>' . $row['com_lang'] . '</lang>');
		fwrite($file, '<update>' . $row['com_update'] . '</update>');
		// custom meta data
		$custommeta = queryDB("SELECT * FROM dis_meta WHERE met_community='" . $row['com_id'] . "'");
		for ($j=0; $j<mysql_num_rows($custommeta); $j++) {
			$rowcustom = mysql_fetch_assoc($custommeta);
			fwrite($file, '<custom id="' . $rowcustom['met_index'] . '"><![CDATA[' . $rowcustom['met_name'] . ']]></custom>');
		}
		mysql_free_result($custommeta);
		fwrite($file, '</meta>');
		fwrite($file, '<categories>');
		$catresult = queryDB("SELECT * FROM dis_category WHERE cat_community='$id'");
		if (mysql_num_rows($catresult) > 0) {
			for ($i=0; $i<mysql_num_rows($catresult); $i++) {
				$category = mysql_fetch_assoc($catresult);
				fwrite($file, '<category id="' . $category['cat_index'] . '><![CDATA[' . $category['cat_name'] . ']]></category>');
			}
		}
		mysql_free_result($catresult);
		fwrite($file, '</categories>');
		fwrite($file, '<screen>');
		fwrite($file, '<width>' . $row['com_width'] . '</width>');
		fwrite($file, '<height>' . $row['com_height'] . '</height>');
		fwrite($file, '<portrait><width>' . $row['com_pwidth'] . '</width>');
		fwrite($file, '<height>' . $row['com_pheight'] . '</height></portrait>');
		fwrite($file, '<highlight active="' . $row['com_highlight'] . '">' . $row['com_highlightcolor'] . '</highlight>');
		fwrite($file, '<background>' . $row['com_background'] . '</background>');
		fwrite($file, '<alpha>' . $row['com_alpha'] . '</alpha>');
		fwrite($file, '</screen>');
		fwrite($file, '<graphics>');
		fwrite($file, '<target>' . $row['com_target'] . '</target>');
		fwrite($file, '<vote0>' . $row['com_vote0'] . '</vote0>');
		fwrite($file, '<vote10>' . $row['com_vote10'] . '</vote10>');
		fwrite($file, '<vote20>' . $row['com_vote20'] . '</vote20>');
		fwrite($file, '<vote30>' . $row['com_vote30'] . '</vote30>');
		fwrite($file, '<vote40>' . $row['com_vote40'] . '</vote40>');
		fwrite($file, '<vote50>' . $row['com_vote50'] . '</vote50>');
		fwrite($file, '<vote60>' . $row['com_vote60'] . '</vote60>');
		fwrite($file, '<vote70>' . $row['com_vote70'] . '</vote70>');
		fwrite($file, '<vote80>' . $row['com_vote80'] . '</vote80>');
		fwrite($file, '<vote90>' . $row['com_vote90'] . '</vote90>');
		fwrite($file, '<vote100>' . $row['com_vote100'] . '</vote100>');
		fwrite($file, '</graphics>');
		fwrite($file, '<transition>');
		fwrite($file, '<xnext><![CDATA[' . $row['com_navxnext'] . ']]></xnext>');
		fwrite($file, '<xprev><![CDATA[' . $row['com_navxprev'] . ']]></xprev>');
		fwrite($file, '<ynext><![CDATA[' . $row['com_navynext'] . ']]></ynext>');
		fwrite($file, '<yprev><![CDATA[' . $row['com_navyprev'] . ']]></yprev>');
		fwrite($file, '<znext><![CDATA[' . $row['com_navznext'] . ']]></znext>');
		fwrite($file, '<zprev><![CDATA[' . $row['com_navzprev'] . ']]></zprev>');
		fwrite($file, '<home><![CDATA[' . $row['com_navhome'] . ']]></home>');
		fwrite($file, '<list><![CDATA[' . $row['com_navlist'] . ']]></list>');
		fwrite($file, '</transition>');
		fwrite($file, '<home>' . $row['com_home'] . '</home>');
		fwrite($file, '<defaultvote>' . $row['com_votedefault'] . '</defaultvote>');
		fwrite($file, '<voterecord>' . $row['com_voterecord'] . '</voterecord>');
		fwrite($file, '<css><![CDATA[' . $row['com_css'] . ']]></css>');
		fwrite($file, '<feeds>');
		$feedresult = queryDB("SELECT * FROM dis_feed WHERE fed_community='$id'");
		if (mysql_num_rows($feedresult) > 0) {
			for ($i=0; $i<mysql_num_rows($feedresult); $i++) {
				$feed = mysql_fetch_assoc($feedresult);
				fwrite($file, '<feed type="' . $feed['fed_type'] . '" reference="' . $feed['fed_reference'] . '">' . $feed['fed_name'] . '</feed>');
			}
		}
		mysql_free_result($feedresult);
		fwrite($file, '</feeds>');
		// widgets
		if (is_dir('./media/community/widget')) {
			fwrite($file, '<widgets>');
			foreach(glob('./media/community/widget/*.swf') as $wfilename) {
				fwrite($file, '<widget><![CDATA[' . substr($wfilename, 25) . ']]></widget>');
			}
			fwrite($file, '</widgets>');
		} else {
			fwrite($file, '<widgets />');
		}
		// end file
		fwrite($file, '</dis>');
		fclose($file);
	}
	mysql_free_result($checkid);
}

/**
 * Publish a stream.
 */
function publishStream($id, $community) {
	$check = queryDB("SELECT * FROM dis_stream WHERE str_id='$id' AND str_community='$community' AND str_state='publish' ORDER BY str_index DESC LIMIT 1");
	if (mysql_num_rows($check) > 0) {
		$row = mysql_fetch_assoc($check);
		@chdir(ROOTFOLDER);
		@chdir("."); // for windows hosts
		@chdir("community");
		@chdir(".");
		if (!is_dir($community . ".dis")) @mkdir($community . ".dis");
		// move to stream folder
		@chdir($community . ".dis");
		@chdir(".");
		if (!is_dir("stream")) @mkdir("stream");
		@chdir("stream");
		@chdir(".");
		// start writting stream file
		$file = fopen($id . ".xml", "wb");
		fwrite($file, '<?xml version="1.0" encoding="utf-8"?><stream>');
		// write stream data
		fwrite($file, '<id>' . $id . '</id>');
		fwrite($file, '<meta>');
		fwrite($file, '<title><![CDATA[' . decodeApostrophe($row['str_title']) . ']]></title>');
		fwrite($file, '<author id="' . $row['str_authorid'] . '"><![CDATA[' . /*$row['str_author'] .*/ ']]></author>');
		fwrite($file, '<about><![CDATA[' . decodeApostrophe($row['str_excerpt']) . ']]></about>');
		fwrite($file, '<icon>' . $row['str_icon'] . '</icon>');
		fwrite($file, '<tags>' . $row['str_tag'] . '</tags>');
		fwrite($file, '<update>' . $row['str_update'] . '</update>');
		fwrite($file, '<category><![CDATA[' . decodeApostrophe($row['str_category']) . ']]></category>');
		// custom meta data
		$custommeta = queryDB("SELECT * FROM dis_streammeta WHERE smt_community='$community' AND smt_streamid='$id'");
		for ($j=0; $j<mysql_num_rows($custommeta); $j++) {
			$rowcustom = mysql_fetch_assoc($custommeta);
			fwrite($file, '<custom id="' . $rowcustom['smt_metaindex'] . '">');
			fwrite($file, '<metaname><![CDATA[' . $rowcustom['smt_metaname'] . ']]></metaname>');
			fwrite($file, '<metavalue><![CDATA[' . $rowcustom['smt_metavalue'] . ']]></metavalue>');
			fwrite($file, '</custom>');
		}
		mysql_free_result($custommeta);
		fwrite($file, '</meta>');
		fwrite($file, '<code><![CDATA[' . $row['str_pcode'] . ']]></code>');
		fwrite($file, '<remote><alternateid><![CDATA[' . $row['str_remotestream'] . ']]></alternateid></remote>');
		fwrite($file, '<functions>');
		fwrite($file, '<customa><![CDATA[' . $row['str_functiona'] . ']]></customa>');
		fwrite($file, '<customb><![CDATA[' . $row['str_functionb'] . ']]></customb>');
		fwrite($file, '<customc><![CDATA[' . $row['str_functionc'] . ']]></customc>');
		fwrite($file, '<customd><![CDATA[' . $row['str_functiond'] . ']]></customd>');
		fwrite($file, '</functions>');
		fwrite($file, '<wheel>');
		fwrite($file, '<up><![CDATA[' . $row['str_mousewup'] . ']]></up>');
		fwrite($file, '<down><![CDATA[' . $row['str_mousewdown'] . ']]></down>');
		fwrite($file, '</wheel>');
		// geolocation
		fwrite($file, '<geolocation>');
		$geoprop = queryDB("SELECT * FROM dis_streamgeodata WHERE sgd_community='$community' AND sgd_stream='$id'");
		if (mysql_num_rows($geoprop) > 0) {
			$rowgeo = mysql_fetch_assoc($geoprop);
			fwrite($file, '<geouse>' . $rowgeo['sgd_use'] . '</geouse>');
			fwrite($file, '<geotarget><![CDATA[' . $rowgeo['sgd_target'] . ']]></geotarget>');
			fwrite($file, '<geomap><![CDATA[' . $rowgeo['sgd_map'] . ']]></geomap>');
			fwrite($file, '<geolattop><![CDATA[' . $rowgeo['sgd_latitudetop'] . ']]></geolattop>');
			fwrite($file, '<geolongtop><![CDATA[' . $rowgeo['sgd_longitudetop'] . ']]></geolongtop>');
			fwrite($file, '<geolatbottom><![CDATA[' . $rowgeo['sgd_latitudebottom'] . ']]></geolatbottom>');
			fwrite($file, '<geolongbottom><![CDATA[' . $rowgeo['sgd_longitudebottom'] . ']]></geolongbottom>');
		} else {
			fwrite($file, '<geouse>0</geouse>');
			fwrite($file, '<geotarget></geotarget>');
			fwrite($file, '<geomap></geomap>');
			fwrite($file, '<geolattop>0</geolattop>');
			fwrite($file, '<geolongtop>0</geolongtop>');
			fwrite($file, '<geolatbottom>0</geolatbottom>');
			fwrite($file, '<geolongbottom>0</geolongbottom>');
		}
		mysql_free_result($geoprop);
		$geopoints = queryDB("SELECT * FROM dis_streamgeopoint WHERE sgp_community='$community' AND sgp_stream='$id'");
		if (mysql_num_rows($geopoints) > 0) {
			for ($i=0; $i<mysql_num_rows($geopoints); $i++) {
				$rowgeo = mysql_fetch_assoc($geopoints);
				fwrite($file, '<geopoint>');
				fwrite($file, '<name><![CDATA[' . $rowgeo['sgp_name'] . ']]></name>');
				fwrite($file, '<latitude><![CDATA[' . $rowgeo['sgp_latitude'] . ']]></latitude>');
				fwrite($file, '<longitude><![CDATA[' . $rowgeo['sgp_longitude'] . ']]></longitude>');
				fwrite($file, '<code><![CDATA[' . $rowgeo['sgp_code'] . ']]></code>');
				fwrite($file, '</geopoint>');
			}
		}
		mysql_free_result($geopoints);
		fwrite($file, '</geolocation>');
		fwrite($file, '<plugins />');
		// guides
		fwrite($file, '<guides>');
		if ($row['str_guideup'] != "") fwrite($file, '<stream id="' . $row['str_guideup'] . '" type="up" />');
		if ($row['str_guidedown'] != "") fwrite($file, '<stream id="' . $row['str_guidedown'] . '" type="down" />');
		fwrite($file, '</guides>');
		fwrite($file, '<aspect>');
		fwrite($file, '<landscape>' . $row['str_landscape'] . '</landscape>');
		fwrite($file, '<portrait>' . $row['str_portrait'] . '</portrait>');
		fwrite($file, '</aspect>');
		fwrite($file, '<animation>');
		fwrite($file, '<speed>' . $row['str_speed'] . '</speed>');
		fwrite($file, '<tweening>' . $row['str_tweening'] . '</tweening>');
		fwrite($file, '<fade>' . $row['str_fade'] . '</fade>');
		fwrite($file, '<entropy>' . $row['str_entropy'] . '</entropy>');
		fwrite($file, '<distortion>' . $row['str_distortion'] . '</distortion>');
		fwrite($file, '<target>' . $row['str_target'] . '</target>');
		fwrite($file, '</animation>');
		fwrite($file, '<voting>');
		fwrite($file, '<type>' . $row['str_votetype'] . '</type>');
		fwrite($file, '<defaultvote>' . $row['str_votedefault'] . '</defaultvote>');
		fwrite($file, '<startaftervote>' . $row['str_startaftervote'] . '</startaftervote>');
		fwrite($file, '<reference>' . $row['str_votereference'] . '</reference>');
		for ($i=1; $i<=9; $i++) {
			fwrite($file, '<option id="' . $i . '" px="' . $row['str_vote' . $i . 'px'] . '" py="' . $row['str_vote' . $i . 'py'] . '" show="' . $row['str_vote' . $i . 'show'] . '"><![CDATA[' . $row['str_vote' . $i] . ']]></option>');
		}
		fwrite($file, '</voting>');
		fwrite($file, '<navigation>');
		fwrite($file, '<xnext><![CDATA[' . $row['str_xnext'] . ']]></xnext>');
		fwrite($file, '<xprev><![CDATA[' . $row['str_xprev'] . ']]></xprev>');
		fwrite($file, '<ynext><![CDATA[' . $row['str_ynext'] . ']]></ynext>');
		fwrite($file, '<yprev><![CDATA[' . $row['str_yprev'] . ']]></yprev>');
		fwrite($file, '<znext><![CDATA[' . $row['str_znext'] . ']]></znext>');
		fwrite($file, '<zprev><![CDATA[' . $row['str_zprev'] . ']]></zprev>');
		fwrite($file, '</navigation>');
		// keyframes
		$allplaylists = array();
		fwrite($file, '<keyframes>');
		$currentkey = 0;
		$keyframecheck = queryDB("SELECT * FROM dis_instance WHERE ins_community='" . $community . "' AND ins_streamindex='" . $row['str_index'] . "' ORDER BY ins_keyframe ASC");
		if (mysql_num_rows($keyframecheck) > 0) {
			fwrite($file, '<keyframe>');
			// keyframe on enter action
			$acincheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='0' AND kac_keyframe='$currentkey'");
			if (mysql_num_rows($acincheck) > 0) {
				$acin = mysql_fetch_assoc($acincheck);
				fwrite($file, '<actionin><![CDATA[' . $acin['kac_action'] . ']]></actionin>');
			} else {
				fwrite($file, '<actionin />');
			}
			mysql_free_result($acincheck);
			// keyframe on exit action
			$acoutcheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='1' AND kac_keyframe='$currentkey'");
			if (mysql_num_rows($acoutcheck) > 0) {
				$acout = mysql_fetch_assoc($acoutcheck);
				fwrite($file, '<actionout><![CDATA[' . $acout['kac_action'] . ']]></actionout>');
			} else {
				fwrite($file, '<actionout />');
			}
			mysql_free_result($acoutcheck);
			// instances
			for ($ikey=0; $ikey < mysql_num_rows($keyframecheck); $ikey++) {
				$instance = mysql_fetch_assoc($keyframecheck);
				$allplaylists[$instance['ins_playlist']] = $instance['ins_playlist'];
				if ((int)$instance['ins_keyframe'] != $currentkey) {
					fwrite($file, '</keyframe><keyframe>');
					$currentkey = (int)$instance['ins_keyframe'];
					// keyframe on enter action
					$acincheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='0' AND kac_keyframe='$currentkey'");
					if (mysql_num_rows($acincheck) > 0) {
						$acin = mysql_fetch_assoc($acincheck);
						fwrite($file, '<actionin><![CDATA[' . $acin['kac_action'] . ']]></actionin>');
					} else {
						fwrite($file, '<actionin />');
					}
					mysql_free_result($acincheck);
					// keyframe on exit action
					$acoutcheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='1' AND kac_keyframe='$currentkey'");
					if (mysql_num_rows($acoutcheck) > 0) {
						$acout = mysql_fetch_assoc($acoutcheck);
						fwrite($file, '<actionout><![CDATA[' . $acout['kac_action'] . ']]></actionout>');
					} else {
						fwrite($file, '<actionout />');
					}
					mysql_free_result($acoutcheck);
				}
				fwrite($file, '<instance playlist="' . $instance['ins_playlist'] . '" id="' . $instance['ins_id'] . '" element="' . $instance['ins_element'] . '" force="' . $instance['ins_force'] . '" width="' . $instance['ins_width'] . '" height="' . $instance['ins_height'] . '" px="' . $instance['ins_px'] . '" py="' . $instance['ins_py'] . '" pz="' . $instance['ins_pz'] . '" order="' . $instance['ins_order'] . '" alpha="' . $instance['ins_alpha'] . '" play="' . $instance['ins_play'] . '" volume="' . $instance['ins_volume'] . '" rx="' . $instance['ins_rx'] . '" ry="' . $instance['ins_ry'] . '" rz="' . $instance['ins_rz'] . '" active="' . $instance['ins_active'] . '" visible="' . $instance['ins_visible'] . '"  red="' . $instance['ins_red'] . '" green="' . $instance['ins_green'] . '" blue="' . $instance['ins_blue'] . '" blend="' . $instance['ins_blend'] . '" DropShadowFilter="' . $instance['ins_dropshadow'] . '" DSFalpha="' . $instance['ins_dsalpha'] . '" DSFangle="' . $instance['ins_dsangle'] . '" DSFblurX="' . $instance['ins_dsblurx'] . '" DSFblurY="' . $instance['ins_dsblury'] . '" DSFdistance="' . $instance['ins_dsdistance'] . '" BevelFilter="' . $instance['ins_bevel'] . '" BVFangle="' . $instance['ins_bvangle'] . '" BVFblurX="' . $instance['ins_bvblurx'] . '" BVFblurY="' . $instance['ins_bvblury'] . '" BVFdistance="' . $instance['ins_bvdistance'] . '" BVFhighlightAlpha="' . $instance['ins_bvhighlightalpha'] . '" BVFshadowAlpha="' . $instance['ins_bvshadowalpha'] . '" BlurFilter="' . $instance['ins_blur'] . '" BLFblurX="' . $instance['ins_blblurx'] . '" BLFblurY="' . $instance['ins_blblury'] . '" GlowFilter="' . $instance['ins_glow'] . '" GLFalpha="' . $instance['ins_glalpha'] . '" GLFblurX="' . $instance['ins_glblurx'] . '" GLFblurY="' . $instance['ins_glblury'] . '" GLFinner="' . $instance['ins_glinner'] . '" GLFstrength="' . $instance['ins_glstrength'] . '" DSFcolor="' . $instance['ins_dscolor'] . '" BVFhighlightColor="' . $instance['ins_bvhighlightcolor'] . '" BVFshadowColor="' . $instance['ins_bvshadowcolor'] . '" GLFcolor="' . $instance['ins_glcolor'] . '" crop="' . $instance['ins_crop'] . '" smooth="' . $instance['ins_smooth'] . '" font="' . $instance['ins_font'] . '" fontsize="' . $instance['ins_fontsize'] . '" spacing="' . $instance['ins_spacing'] . '" leading="' . $instance['ins_leading'] . '" bold="' . $instance['ins_bold'] . '" italic="' . $instance['ins_italic'] . '" charmax="' . $instance['ins_charmax'] . '" fontcolor="' . $instance['ins_fontcolor'] . '" textalign="' . $instance['ins_textalign'] . '" cssclass="' . $instance['ins_cssclass'] . '" transition="' . $instance['ins_transition'] . '"><![CDATA[' . $instance['ins_action'] . ']]></instance>');
			}
			fwrite($file, '</keyframe>');
		}
		mysql_free_result($keyframecheck);
		fwrite($file, '</keyframes>');
		// playlists
		fwrite($file, '<playlists>');
		foreach ($allplaylists as $value) {
			fwrite($file, '<playlist id="' . $value . '" />');
			publishPlaylist($value, $community);
		}
		fwrite($file, '</playlists>');
		// end file
		fwrite($file, '</stream>');
		fclose($file);
	}
	mysql_free_result($check);
}

/**
 * Write a playlist file.
 */
function publishPlaylist($id, $community) {
	$check = queryDB("SELECT * FROM dis_playlist WHERE ply_id='$id' AND ply_community='$community' ORDER BY ply_index DESC LIMIT 1");
	if (mysql_num_rows($check) > 0) {
		$row = mysql_fetch_assoc($check);
		@chdir(ROOTFOLDER);
		@chdir("."); // for windows hosts
		@chdir("community");
		@chdir(".");
		if (!is_dir($community . ".dis")) @mkdir($community . ".dis");
		// move to stream folder
		@chdir($community . ".dis");
		@chdir(".");
		if (!is_dir("playlist")) @mkdir("playlist");
		@chdir("playlist");
		@chdir(".");
		// start writting stream file
		$file = fopen($id . ".xml", "wb");
		fwrite($file, '<?xml version="1.0" encoding="utf-8"?><playlist>');
		// write stream data
		fwrite($file, '<id>' . $id . '</id>');
		fwrite($file, '<meta>');
		fwrite($file, '<title>' . $row['ply_title'] . '</title>');
		fwrite($file, '<author id="' . $row['ply_authorid'] . '"><![CDATA[' . /*$row['ply_author'] .*/ ']]></author>');
		fwrite($file, '<about><![CDATA[' . decodeApostrophe($row['ply_about']) . ']]></about>');
		fwrite($file, '</meta>');
		fwrite($file, '<elements>');
		$elements = queryDB("SELECT * FROM dis_element WHERE elm_plindex='" . $row['ply_index'] . "' AND elm_community='$community' ORDER BY elm_order ASC");
		if (mysql_num_rows($elements) > 0) {
			for ($j=0; $j<mysql_num_rows($elements); $j++) {
			$element = mysql_fetch_assoc($elements);
				fwrite($file, '<element id="' . $element['elm_id'] . '" time="' . $element['elm_time'] . '" type="' . $element['elm_type'] . '" end="' . $element['elm_end'] . '">');
				$files = queryDB("SELECT * FROM dis_file WHERE fil_plindex='" . $row['ply_index'] . "' AND fil_community='$community' AND fil_element='" . $element['elm_id'] . "'");
				if (mysql_num_rows($files) > 0) {
					for ($k=0; $k<mysql_num_rows($files); $k++) {
						$elmfile = mysql_fetch_assoc($files);
						fwrite($file, '<file format="' . $elmfile['fil_format'] . '" lang="' . $elmfile['fil_lang'] . '" absolute="' . $elmfile['fil_absolute'] . '" feed="' . $elmfile['fil_feed'] . '" feedType="' . $elmfile['fil_feedtype'] . '" field="' . $elmfile['fil_field'] . '"><![CDATA[' . decodeApostrophe($elmfile['fil_url']) . ']]></file>');
					}
				}
				mysql_free_result($files);
				$actions = queryDB("SELECT * FROM dis_action WHERE act_plindex='" . $row['ply_index'] . "' AND act_community='$community' AND act_element='" . $element['elm_id'] . "'");
				if (mysql_num_rows($actions) > 0) {
					for ($k=0; $k<mysql_num_rows($actions); $k++) {
						$action = mysql_fetch_assoc($actions);
						fwrite($file, '<action time="' . $action['act_time'] . '" type="' . $action['act_type'] . '"><![CDATA[' . $action['act_action'] . ']]></action>');
					}
				} else {
					fwrite($file, '<action />');
				}
				mysql_free_result($actions);
				fwrite($file, '</element>');
			}
		}
		mysql_free_result($elements);
		fwrite($file, '</elements>');
		// end output
		fwrite($file, '</playlist>');
		fclose($file);
	}
	mysql_free_result($check);
}

/**
 * Output a stream.
 */
function outputStream($id, $community, $byindex = false, $index = "") {
	if ($byindex) $check = queryDB("SELECT * FROM dis_stream WHERE str_index='$index' AND str_community='$community'");
		else $check = queryDB("SELECT * FROM dis_stream WHERE str_id='$id' AND str_community='$community' ORDER BY str_index DESC LIMIT 1");
	if (mysql_num_rows($check) > 0) {
		$row = mysql_fetch_assoc($check);
		echo('<id>' . $row['str_id'] . '</id>');
		echo('<meta>');
		echo('<title>' . $row['str_title'] . '</title>');
		echo('<author id="' . $row['str_authorid'] . '">' . $row['str_author'] . '</author>');
		echo('<about><![CDATA[' . decodeApostrophe($row['str_excerpt']) . ']]></about>');
		echo('<icon>' . $row['str_icon'] . '</icon>');
		echo('<tags>' . $row['str_tag'] . '</tags>');
		echo('<update>' . $row['str_update'] . '</update>');
		echo('<category><![CDATA[' . decodeApostrophe($row['str_category']) . ']]></category>');
		// custom meta data
		$custommeta = queryDB("SELECT * FROM dis_streammeta WHERE smt_community='$community' AND smt_streamid='" . $row['str_id'] . "'");
		for ($j=0; $j<mysql_num_rows($custommeta); $j++) {
			$rowcustom = mysql_fetch_assoc($custommeta);
			echo('<custom id="' . $rowcustom['smt_metaindex'] . '">');
			echo('<metaname><![CDATA[' . $rowcustom['smt_metaname'] . ']]></metaname>');
			echo('<metavalue><![CDATA[' . $rowcustom['smt_metavalue'] . ']]></metavalue>');
			echo('</custom>');
		}
		mysql_free_result($custommeta);
		echo('</meta>');
		echo('<code><![CDATA[' . $row['str_pcode'] . ']]></code>');
		echo('<remote><alternateid><![CDATA[' . $row['str_remotestream'] . ']]></alternateid></remote>');
		echo('<functions>');
		echo('<customa><![CDATA[' . $row['str_functiona'] . ']]></customa>');
		echo('<customb><![CDATA[' . $row['str_functionb'] . ']]></customb>');
		echo('<customc><![CDATA[' . $row['str_functionc'] . ']]></customc>');
		echo('<customd><![CDATA[' . $row['str_functiond'] . ']]></customd>');
		echo('</functions>');
		echo('<wheel>');
		echo('<up><![CDATA[' . $row['str_mousewup'] . ']]></up>');
		echo('<down><![CDATA[' . $row['str_mousewdown'] . ']]></down>');
		echo('</wheel>');
		echo('<plugins />');
		echo('<guides>');
		echo('<up>' . $row['str_guideup'] . '</up>');
		echo('<down>' . $row['str_guidedown'] . '</down>');
		echo('</guides>');
		echo('<aspect>');
		echo('<landscape>' . $row['str_landscape'] . '</landscape>');
		echo('<portrait>' . $row['str_portrait'] . '</portrait>');
		echo('</aspect>');
		echo('<animation>');
		echo('<speed>' . $row['str_speed'] . '</speed>');
		echo('<tweening>' . $row['str_tweening'] . '</tweening>');
		echo('<fade>' . $row['str_fade'] . '</fade>');
		echo('<entropy>' . $row['str_entropy'] . '</entropy>');
		echo('<distortion>' . $row['str_distortion'] . '</distortion>');
		echo('<target>' . $row['str_target'] . '</target>');
		echo('</animation>');
		echo('<voting>');
		echo('<type>' . $row['str_votetype'] . '</type>');
		echo('<defaultvote>' . $row['str_votedefault'] . '</defaultvote>');
		echo('<startaftervote>' . $row['str_startaftervote'] . '</startaftervote>');
		echo('<reference>' . $row['str_votereference'] . '</reference>');
		for ($i=1; $i<=9; $i++) {
			echo('<option id="' . $i . '" px="' . $row['str_vote' . $i . 'px'] . '" py="' . $row['str_vote' . $i . 'py'] . '" show="' . $row['str_vote' . $i . 'show'] . '"><![CDATA[' . $row['str_vote' . $i] . ']]></option>');
		}
		echo('</voting>');
		echo('<navigation>');
		echo('<xnext><![CDATA[' . $row['str_xnext'] . ']]></xnext>');
		echo('<xprev><![CDATA[' . $row['str_xprev'] . ']]></xprev>');
		echo('<ynext><![CDATA[' . $row['str_ynext'] . ']]></ynext>');
		echo('<yprev><![CDATA[' . $row['str_yprev'] . ']]></yprev>');
		echo('<znext><![CDATA[' . $row['str_znext'] . ']]></znext>');
		echo('<zprev><![CDATA[' . $row['str_zprev'] . ']]></zprev>');
		echo('</navigation>');
		echo('<keyframes>');
		$keyframes = queryDB("SELECT * FROM dis_instance WHERE ins_community='$community' AND ins_streamindex='" . $row['str_index'] . "' ORDER BY ins_keyframe ASC");
		$order = 0;
		$allplaylists = array();
		if (mysql_num_rows($keyframes) > 0) {
			echo("<keyframe>");
			// keyframe on enter action
			$acincheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='0' AND kac_keyframe='$order'");
			if (mysql_num_rows($acincheck) > 0) {
				$acin = mysql_fetch_assoc($acincheck);
				echo('<actionin><![CDATA[' . $acin['kac_action'] . ']]></actionin>');
			} else {
				echo('<actionin />');
			}
			mysql_free_result($acincheck);
			// keyframe on exit action
			$acoutcheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='1' AND kac_keyframe='$order'");
			if (mysql_num_rows($acoutcheck) > 0) {
				$acout = mysql_fetch_assoc($acoutcheck);
				echo('<actionout><![CDATA[' . $acout['kac_action'] . ']]></actionout>');
			} else {
				echo('<actionout />');
			}
			mysql_free_result($acoutcheck);
			// keyframe istances
			for ($i=0; $i<mysql_num_rows($keyframes); $i++) {
				$instance = mysql_fetch_assoc($keyframes);
				if ((int)$instance['ins_keyframe'] != $order) {
					echo("</keyframe><keyframe>");
					$order = (int)$instance['ins_keyframe'];
					// keyframe on enter action
					$acincheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='0' AND kac_keyframe='$order'");
					if (mysql_num_rows($acincheck) > 0) {
						$acin = mysql_fetch_assoc($acincheck);
						echo('<actionin><![CDATA[' . $acin['kac_action'] . ']]></actionin>');
					} else {
						echo('<actionin />');
					}
					mysql_free_result($acincheck);
					// keyframe on exit action
					$acoutcheck = queryDB("SELECT * FROM dis_keyaction WHERE kac_community='$community' AND kac_streamindex='" . $row['str_index'] . "' AND kac_moment='1' AND kac_keyframe='$order'");
					if (mysql_num_rows($acoutcheck) > 0) {
						$acout = mysql_fetch_assoc($acoutcheck);
						echo('<actionout><![CDATA[' . $acout['kac_action'] . ']]></actionout>');
					} else {
						echo('<actionout />');
					}
					mysql_free_result($acoutcheck);
				}
				$allplaylists[$instance['ins_playlist']] = $instance['ins_playlist'];
				echo('<instance playlist="' . $instance['ins_playlist'] . '" id="' . $instance['ins_id'] . '" element="' . $instance['ins_element'] . '" force="' . $instance['ins_force'] . '" width="' . $instance['ins_width'] . '" height="' . $instance['ins_height'] . '" px="' . $instance['ins_px'] . '" py="' . $instance['ins_py'] . '" pz="' . $instance['ins_pz'] . '" order="' . $instance['ins_order'] . '" alpha="' . $instance['ins_alpha'] . '" play="' . $instance['ins_play'] . '" volume="' . $instance['ins_volume'] . '" rx="' . $instance['ins_rx'] . '" ry="' . $instance['ins_ry'] . '" rz="' . $instance['ins_rz'] . '" active="' . $instance['ins_active'] . '" visible="' . $instance['ins_visible'] . '"  red="' . $instance['ins_red'] . '" green="' . $instance['ins_green'] . '" blue="' . $instance['ins_blue'] . '" blend="' . $instance['ins_blend'] . '" DropShadowFilter="' . $instance['ins_dropshadow'] . '" DSFalpha="' . $instance['ins_dsalpha'] . '" DSFangle="' . $instance['ins_dsangle'] . '" DSFblurX="' . $instance['ins_dsblurx'] . '" DSFblurY="' . $instance['ins_dsblury'] . '" DSFdistance="' . $instance['ins_dsdistance'] . '" BevelFilter="' . $instance['ins_bevel'] . '" BVFangle="' . $instance['ins_bvangle'] . '" BVFblurX="' . $instance['ins_bvblurx'] . '" BVFblurY="' . $instance['ins_bvblury'] . '" BVFdistance="' . $instance['ins_bvdistance'] . '" BVFhighlightAlpha="' . $instance['ins_bvhighlightalpha'] . '" BVFshadowAlpha="' . $instance['ins_bvshadowalpha'] . '" BlurFilter="' . $instance['ins_blur'] . '" BLFblurX="' . $instance['ins_blblurx'] . '" BLFblurY="' . $instance['ins_blblury'] . '" GlowFilter="' . $instance['ins_glow'] . '" GLFalpha="' . $instance['ins_glalpha'] . '" GLFblurX="' . $instance['ins_glblurx'] . '" GLFblurY="' . $instance['ins_glblury'] . '" GLFinner="' . $instance['ins_glinner'] . '" GLFstrength="' . $instance['ins_glstrength'] . '" DSFcolor="' . $instance['ins_dscolor'] . '" BVFhighlightColor="' . $instance['ins_bvhighlightcolor'] . '" BVFshadowColor="' . $instance['ins_bvshadowcolor'] . '" GLFcolor="' . $instance['ins_glcolor'] . '" crop="' . $instance['ins_crop'] . '" smooth="' . $instance['ins_smooth'] . '" font="' . $instance['ins_font'] . '" fontsize="' . $instance['ins_fontsize'] . '" spacing="' . $instance['ins_spacing'] . '" leading="' . $instance['ins_leading'] . '" bold="' . $instance['ins_bold'] . '" italic="' . $instance['ins_italic'] . '" charmax="' . $instance['ins_charmax'] . '" fontcolor="' . $instance['ins_fontcolor'] . '" textalign="' . $instance['ins_textalign'] . '" cssclass="' . $instance['ins_cssclass'] . '" transition="' . $instance['ins_transition'] . '"><![CDATA[' . $instance['ins_action'] . ']]></instance>');
			}
			echo("</keyframe>");
		}
		mysql_free_result($keyframes);
		echo('</keyframes>');
		echo('<playlists>');
			foreach ($allplaylists as $value) {
				$playlist = queryDB("SELECT * FROM dis_playlist WHERE ply_id='$value' AND ply_community='$community' ORDER BY ply_index DESC LIMIT 1");
				if (mysql_num_rows($playlist) > 0) {
					for ($i=0; $i<mysql_num_rows($playlist); $i++) {
						echo('<playlist>');
						$info = mysql_fetch_assoc($playlist);
						// is playlist in use by someone else?
						$checkuse = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='playlist' AND cur_id='". $info['ply_id'] . "' AND cur_user NOT LIKE '" . $_SESSION['usr_id'] . "'");
						if (mysql_num_rows($checkuse) == 0) {
							echo('<locked></locked>');
						} else {
							echo('<locked>x</locked>');
						}
						mysql_free_result($checkuse);
						// playlist data
						echo('<id>' . $info['ply_id'] . '</id>');
						echo('<meta>');
						echo('<title>' . $info['ply_title'] . '</title>');
						echo('<author id="' . $info['ply_authorid'] . '">' . $info['ply_author'] . '</author>');
						echo('<about>' . $info['ply_about'] . '</about>');
						echo('</meta>');
						echo('<elements>');
						$elements = queryDB("SELECT * FROM dis_element WHERE elm_plindex='" . $info['ply_index'] . "' AND elm_community='$community' ORDER BY elm_order ASC");
						if (mysql_num_rows($elements) > 0) {
							for ($j=0; $j<mysql_num_rows($elements); $j++) {
								$element = mysql_fetch_assoc($elements);
								echo('<element id="' . $element['elm_id'] . '" time="' . $element['elm_time'] . '" type="' . $element['elm_type'] . '" end="' . $element['elm_end'] . '">');
								$files = queryDB("SELECT * FROM dis_file WHERE fil_plindex='" . $info['ply_index'] . "' AND fil_community='$community' AND fil_element='" . $element['elm_id'] . "'");
								if (mysql_num_rows($files) > 0) {
									for ($k=0; $k<mysql_num_rows($files); $k++) {
										$file = mysql_fetch_assoc($files);
										echo('<file format="' . $file['fil_format'] . '" lang="' . $file['fil_lang'] . '" absolute="' . $file['fil_absolute'] . '" feed="' . $file['fil_feed'] . '" feedType="' . $file['fil_feedtype'] . '" field="' . $file['fil_field'] . '"><![CDATA[' . decodeApostrophe($file['fil_url']) . ']]></file>');
									}
								}
								mysql_free_result($files);
								$actions = queryDB("SELECT * FROM dis_action WHERE act_plindex='" . $info['ply_index'] . "' AND act_community='$community' AND act_element='" . $element['elm_id'] . "'");
								if (mysql_num_rows($actions) > 0) {
									for ($k=0; $k<mysql_num_rows($actions); $k++) {
										$action = mysql_fetch_assoc($actions);
										echo('<action time="' . $action['act_time'] . '" type="' . $action['act_type'] . '"><![CDATA[' . $action['act_action'] . ']]></action>');
									}
								} else {
									echo('<action />');
								}
								mysql_free_result($actions);
								echo('</element>');
							}
						}
						mysql_free_result($elements);
						echo('</elements>');
						echo('</playlist>');
					}
				}
				mysql_free_result($playlist);
			}
		echo('</playlists>');
	}
	mysql_free_result($check);
}

/**
 * Receive playlists description in xml-formatted string and save them.
 */
function processPlaylists($playlists, $community) {
	$xml = new SimpleXMLElement($playlists);
	foreach ($xml->playlist as $node) {
		// can the playlist be updated?
		$checkuse = queryDB("SELECT * FROM dis_current WHERE cur_community='$community' AND cur_ref='playlist' AND cur_id='". $node->id . "' AND cur_user NOT LIKE '" . $_SESSION['usr_id'] . "'");
		if (mysql_num_rows($checkuse) == 0) {
			// check for playlist on database
			$plcheck = queryDB("SELECT * FROM dis_playlist WHERE ply_id='" . $node->id . "' AND ply_community='$community' ORDER BY ply_index DESC");
			// remove old revisions
			if (mysql_num_rows($plcheck) > 0) {
				for ($i=0; $i<mysql_num_rows($plcheck); $i++) {
					$row = mysql_fetch_assoc($plcheck);
					if ($i >= REVISIONS) {
						queryDB("DELETE FROM dis_playlist WHERE ply_index='" . $row['ply_index'] . "' AND ply_id='" . $node->id . "' AND ply_community='$community' LIMIT 1");
						queryDB("DELETE FROM dis_action WHERE act_plindex='" . $row['ply_index'] . "' AND act_playlist='" . $node->id . "' AND act_community='$community'");
						queryDB("DELETE FROM dis_element WHERE elm_plindex='" . $row['ply_index'] . "' AND elm_playlist='" . $node->id . "' AND elm_community='$community'");
						queryDB("DELETE FROM dis_file WHERE fil_plindex='" . $row['ply_index'] . "' AND fil_playlist='" . $node->id . "' AND fil_community='$community'");
					}
				}
			}
			mysql_free_result($plcheck);
			// add new playlist
			queryDB("INSERT INTO dis_playlist (ply_id, ply_community, ply_title, ply_author, ply_authorid, ply_about, ply_date) VALUES ('" . $node->id . "', '$community', '" . $node->meta->title . "', '" . $node->meta->author . "', '" . $node->meta->author['id'] . "', '" . $node->meta->about . "', '" . date("Y-m-d H:i:s") . "')");
			$plindex = mysql_insert_id();
			// add elements
			foreach ($node->elements->element as $element) {
				queryDB("INSERT INTO dis_element (elm_plindex, elm_playlist, elm_community, elm_id, elm_type, elm_end, elm_time, elm_order) VALUES ('$plindex', '" . $node->id . "', '$community', '" . $element['id'] . "', '" . $element['type'] . "', '" . $element['end'] . "', '" . $element['time'] . "', '" . $element['order'] . "')");
				// add files
				foreach($element->file as $file) {
					queryDB("INSERT INTO dis_file (fil_plindex, fil_playlist, fil_community, fil_element, fil_format, fil_lang, fil_absolute, fil_feed, fil_feedtype, fil_field, fil_url) VALUES ('$plindex', '" . $node->id . "', '$community', '" . $element['id'] . "', '" . $file['format'] . "', '" . $file['lang'] . "' , '" . intBool($file['absolute']) . "', '" . $file['feed'] . "', '" . $file['feedType'] . "', '" . $file['field'] . "', '" . encodeApostrophe($file) . "')");
				}
				// add actions
				foreach($element->action as $action) {
					if ($action != "") queryDB("INSERT INTO dis_action (act_plindex, act_playlist, act_community, act_element, act_time, act_type, act_action) VALUES ('$plindex', '" . $node->id . "', '$community', '" . $element['id'] . "', '" . $action['time'] . "', '" . $action['type'] . "' , '" . $action . "')");
				}
			}
		}
		mysql_free_result($checkuse);
	}
}

/**
 * Receive keyframe description in xml-formatted string and save them.
 */
function processKeyframes($keyframes, $community, $index) {
	$xml = new SimpleXMLElement($keyframes);
	foreach ($xml->keyframe as $keyframe) {
		foreach ($keyframe->actionin as $actionin) {
			queryDB("INSERT INTO dis_keyaction (kac_community, kac_streamindex, kac_keyframe, kac_action, kac_moment) VALUES ('$community', '$index', '" . $keyframe->order . "', '" . $actionin . "', '0')");
		}
		foreach ($keyframe->actionout as $actionout) {
			queryDB("INSERT INTO dis_keyaction (kac_community, kac_streamindex, kac_keyframe, kac_action, kac_moment) VALUES ('$community', '$index', '" . $keyframe->order . "', '" . $actionout . "', '1')");
		}
		foreach ($keyframe->instance as $instance) {
			$insert = "INSERT INTO dis_instance (ins_community, ins_streamindex, ins_playlist, ins_keyframe, ins_id, ins_element, ins_width, ins_height, ins_px, ins_py, ins_pz, ins_order, ins_force, ins_play, ins_active, ins_visible, ins_alpha, ins_volume, ins_rx, ins_ry, ins_rz, ins_red, ins_green, ins_blue, ins_blend, ins_dropshadow, ins_dsalpha, ins_dsangle, ins_dsblurx, ins_dsblury, ins_dsdistance, ins_dscolor, ins_bevel, ins_bvangle, ins_bvblurx, ins_bvblury, ins_bvdistance, ins_bvhighlightalpha, ins_bvshadowalpha, ins_bvhighlightcolor, ins_bvshadowcolor, ins_blur, ins_blblurx, ins_blblury, ins_glow, ins_glalpha, ins_glblurx, ins_glblury, ins_glinner, ins_glstrength, ins_glcolor, ins_crop, ins_smooth, ins_font, ins_spacing, ins_leading, ins_bold, ins_italic, ins_charmax, ins_fontcolor, ins_fontsize, ins_textalign, ins_transition, ins_cssclass, ins_action) VALUES (";
			$insert .= "'$community', '$index', '" . $instance['playlist'] . "', '" . $keyframe->order . "', '" . $instance['id'] . "', '" . $instance['element'] . "', ";
			$insert .= "'" . $instance['width'] . "', '" . $instance['height'] . "', '" . $instance['px'] . "', '" . $instance['py'] . "', '" . $instance['pz'] . "', '" . $instance['order'] . "', ";
			$insert .= "'" . intBool($instance['force']) . "', '" . intBool($instance['play']) . "', '" . intBool($instance['active']) . "', '" . intBool($instance['visible']) . "', ";
			$insert .= "'" . $instance['alpha'] . "', '" . $instance['volume'] . "', '" . $instance['rx'] . "', '" . $instance['ry'] . "', '" . $instance['rz'] . "', ";
			$insert .= "'" . $instance['red'] . "', '" . $instance['green'] . "', '" . $instance['blue'] . "', '" . $instance['blend'] . "', ";
			$insert .= "'" . intBool($instance['DropShadowFilter']) . "', '" . $instance['DSFalpha'] . "', '" . $instance['DSFangle'] . "', '" . $instance['DSFblurX'] . "', '" . $instance['DSFblurY'] . "', '" . $instance['DSFdistance'] . "', '" . $instance['DSFcolor'] . "', ";
			$insert .= "'" . intBool($instance['BevelFilter']) . "', '" . $instance['BVFangle'] . "', '" . $instance['BVFblurX'] . "', '" . $instance['BVFblurY'] . "', '" . $instance['BVFdistance'] . "', '" . $instance['BVFhighlightAlpha'] . "', '" . $instance['BVFshadowAlpha'] . "', '" . $instance['BVFhighlightColor'] . "','" . $instance['BVFshadowColor'] . "',";
			$insert .= "'" . intBool($instance['BlurFilter']) . "', '" . $instance['BLFblurX'] . "', '" . $instance['BLFblurY'] . "', ";
			$insert .= "'" . intBool($instance['GlowFilter']) . "', '" . $instance['GLFalpha'] . "', '" . $instance['GLFblurX'] . "', '" . $instance['GLFblurY'] . "', '" . intBool($instance['GLFinner']) . "', '" . $instance['GLFstrength'] . "', '" . $instance['GLFcolor'] . "', ";
			$insert .= "'" . $instance['crop'] . "', '" . intBool($instance['smooth']) . "', ";
			$insert .= "'" . $instance['font'] . "', '" . $instance['spacing'] . "', '" . $instance['leading'] . "', '" . intBool($instance['bold']) . "', '" . intBool($instance['italic']) . "', '" . $instance['charmax'] . "', '" . $instance['fontcolor'] . "', '" . $instance['fontsize'] . "', '" . $instance['textalign'] . "', ";
			$insert .= "'" . $instance['transition'] . "', '" . $instance['cssclass'] . "', ";
			$insert .= "'" . $instance . "')";
			queryDB($insert);
		}
	}
}

// create a xml-formatted sitemap url entry
function sitemapURL($loc, $lastmod, $priority) {
	return('<url><loc><![CDATA[' . $loc . ']]></loc><lastmod>' . $lastmod . '</lastmod><priority>' . $priority . '</priority></url>');
}

// get all files from a folder
function getDirectory($filelist, $path, $level) { 
	$ignore = array('cgi-bin', '.', '..');
	$dh = @opendir($path);
	while (false !== ($file = readdir($dh))) {
		if (!in_array($file, $ignore)) {
			if (is_dir("$path/$file")) {
				getDirectory($filelist, "$path/$file", ($level+1));
			} else {
				fwrite($filelist, '<file size="' . filesize("$path/$file") . '" check="' . filemtime("$path/$file") . '">' . "$path/$file" . '</file>');
			}
		}
	}
	closedir ($dh);
}
?>