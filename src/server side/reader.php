<?php
// check for configured system
$showerror = true;
if (is_file("DIS_config.php")) {
	// get system configuration
	require_once("DIS_config.php");
	// common functions
	require_once("DIS_common.php");
	// check requested language
	require_once('language/language_default.php');
	$lang = postString('lang');
	if ($lang != "") {
		if (is_file("language/language_" . $lang . ".php")) {
			require_once('language/language_' . $lang . '.php');
		} else {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				if (is_file("language/language_" . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . ".php")) {
					require_once('language/language_' . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . '.php');
				}
			}
		}
	} else {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			if (is_file("language/language_" . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . ".php")) {
				require_once('language/language_' . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . '.php');
			}
		}
	}
	// check post data
	if (isPost("ac") && isPost("key")) {
		// check the server key
		if (md5(postString('key')) == KEY) {
			// start session
			@session_start();
			// check the community
			$community = postString('community');
				// check requested action
				$showerror = false;
				switch (postString('ac')) {
					case "system":	// information about reader system
						// get system version
						$managanaVersion = postInt('managanaVersion');
						// clear logged user
						if (isset($_SESSION['user'])) unset($_SESSION['user']);
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('<stats>' . USESTATS . '</stats>');
						echo('<rate>' . USERATE . '</rate>');
						echo('<comment>' . COMMENTMODE . '</comment>');
						echo('<share>' . USESHARE . '</share>');
						echo('<search>' . ALLOWSEARCH . '</search>');
						echo('<searchall>' . SEARCHONALL . '</searchall>');
						echo('<remote>' . ALLOWREMOTE . '</remote>');
						echo('<offline>' . ALLOWOFFLINE . '</offline>');
						if ($managanaVersion < 5) { // for Managana versions 1.4.0 and before - remove on future versions
							echo('<showui>' . READERUI . '</showui>');
							echo('<showrate>' . SHOWRATE . '</showrate>');
							echo('<showcomment>' . SHOWCOMMENT . '</showcomment>');
							echo('<showtime>' . SHOWTIME . '</showtime>');
							echo('<showuser>' . SHOWUSER . '</showuser>');
							echo('<ratetext><![CDATA[' . $text['RATETEXT'] . ']]></ratetext>');
							echo('<commenttext><![CDATA[' . $text['COMMENTTEXT'] . ']]></commenttext>');
							echo('<commentadd><![CDATA[' . $text['COMMENTADD'] . ']]></commentadd>');
							echo('<commentwait><![CDATA[' . $text['COMMENTWAIT'] . ']]></commentwait>');
							echo('<timemenu><![CDATA[' . $text['TIMEMENU'] . ']]></timemenu>');
							echo('<ratemenu><![CDATA[' . $text['RATEMENU'] . ']]></ratemenu>');
							echo('<votemenu><![CDATA[' . $text['VOTEMENU'] . ']]></votemenu>');
							echo('<remotemenu><![CDATA[' . $text['REMOTEMENU'] . ']]></remotemenu>');
							echo('<commentmenu><![CDATA[' . $text['COMMENTMENU'] . ']]></commentmenu>');
							echo('<usermenu><![CDATA[' . $text['USERMENU'] . ']]></usermenu>');
							echo('<commentbutton><![CDATA[' . $text['COMMENTBUTTON'] . ']]></commentbutton>');
							echo('<loginrequired><![CDATA[' . $text['LOGINREQUIRED'] . ']]></loginrequired>');
							echo('<loginbutton><![CDATA[' . $text['LOGINBUTTON'] . ']]></loginbutton>');
							echo('<logoutbutton><![CDATA[' . $text['LOGOUTBUTTON'] . ']]></logoutbutton>');
							echo('<loginsuccess><![CDATA[' . $text['LOGINSUCCESS'] . ']]></loginsuccess>');
							echo('<loginerror><![CDATA[' . $text['LOGINERROR'] . ']]></loginerror>');
							echo('<comerror><![CDATA[' . $text['READERERROR'] . ']]></comerror>');
						}
						echo('<cirrus><![CDATA[' . CIRRUS . ']]></cirrus>');
						echo('</data>');
						break;
					case "info": // information about the community
						// look for community data
						if (is_file("./community/" . postString('community') . ".dis/dis.xml")) {
							// parse community data
							$str = file_get_contents("./community/" . postString('community') . ".dis/dis.xml");
							$data = new SimpleXMLElement($str);
							// return data
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('<id><![CDATA[' . $data->meta->id . ']]></id>');
							echo('<name><![CDATA[' . $data->meta->title . ']]></name>');
							echo('</data>');
						} else {
							// no comunity found
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>1</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('</data>');
						}
						break;
					case "streaminfo":
						if (isPost('stream') && isPost('category')) {
							// connect to the database
							require_once("DIS_database.php");
							// begin output
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							// rate
							if (USERATE == "true") {
								$request = queryDB("SELECT AVG(rat_rate) FROM dis_rate WHERE rat_community='" . postString('community') . "' AND rat_stream='" . postString('stream') . "'");
								$row = mysql_fetch_array($request);
								mysql_free_result($request);
								echo('<rate>' . round($row[0]) . '</rate>');
								// user rate
								if (isset($_SESSION['user'])) {
									$request = queryDB("SELECT * FROM dis_rate WHERE rat_community='" . postString('community') . "' AND rat_stream='" . postString('stream') . "' AND rat_user='" . $_SESSION['user'] . "'");
									if (mysql_num_rows($request) > 0) {
										$row = mysql_fetch_assoc($request);
										echo('<userrate>' . $row['rat_rate'] . '</userrate>');
									} else {
										echo('<userrate>0</userrate>');
									}
									mysql_free_result($request);
								} else {
									echo('<userrate>0</userrate>');
								}
							} else {
								echo('<rate>0</rate><userrate>0</userrate>');
							}
							// comments
							echo('<comments>');
							$request = queryDB("SELECT * FROM dis_comment WHERE cmt_community='" . postString('community') . "' AND cmt_stream='" . postString('stream') . "' AND cmt_status='0' ORDER BY cmt_time ASC");
							if (mysql_num_rows($request) > 0) for ($i=0; $i<mysql_num_rows($request); $i++) {
								$row = mysql_fetch_assoc($request);
								echo('<comment name="' . $row['cmt_username'] . '" date="' . dateString($row['cmt_time']) . '"><![CDATA[' . $row['cmt_comment'] . ']]></comment>');
							}
							mysql_free_result($request);
							echo('</comments>');
							// voting
							echo('<votes>');
							$voteresult = array("v1" => 0, "v2" => 0, "v3" => 0, "v4" => 0, "v5" => 0, "v6" => 0, "v7" => 0, "v8" => 0, "v9" => 0);
							$request = queryDB("SELECT * FROM dis_statstime WHERE tim_community='" .  postString('community') . "' AND tim_stream='" . postString('stream') . "' AND tim_vote<>'0' ORDER BY tim_record DESC LIMIT 10");
							if (mysql_num_rows($request) > 0) {
								for ($ivote=0; $ivote < mysql_num_rows($request); $ivote++) {
									$row = mysql_fetch_assoc($request);
									$voteresult["v" . (string)$row['tim_vote']] += 1;
								}
							}
							mysql_free_result($request);
							for ($ivote=1; $ivote<=9; $ivote++) {
								echo('<vote id="' . $ivote . '" >' . $voteresult["v" . (string)$ivote] . '</vote>');
							}
							echo('</votes>');
							// stats
							if (USESTATS == "true") {
								if (isset($_SESSION['name'])) {
									$username = utf8_encode($_SESSION['name']);
									$usermail = $_SESSION['user'];
								} else {
									$usermail = "";
									$username = "guest";
								}
								// current stream
								queryDB("INSERT INTO dis_stats (sta_community, sta_stream, sta_category, sta_ip, sta_user, sta_username, sta_time, sta_timezone) VALUES ('" . postString('community') . "', '" . postString('stream') . "', '" . postString('category') . "', '" . checkIP() . "', '" . $usermail . "', '" . $username . "', '" . time() . "', '" . date("O") . "')");
								// previous stream
								if (isset($_POST['previous']) && isset($_POST['time'])) {
									$prevcom = postString('previouscom');
									if ($prevcom == "") $prevcom = postString('community');
									if ((postString('previous') != "") && (postString('time') != "0")) queryDB("INSERT INTO dis_statstime (tim_community, tim_stream, tim_category, tim_ip, tim_user, tim_username, tim_time, tim_next, tim_vote, tim_record) VALUES ('" . $prevcom . "', '" . postString('previous') . "', '" . postString('category') . "', '" . checkIP() . "', '" . $usermail . "', '" . $username . "', '" . postString('time') . "', '" . postString('stream') . "', '" . postString('vote') . "', '" . time() . "')");
								}
							}
							// end output
							echo('</data>');
						} else {
							// required data not sent
							$showerror = true;
						}
						break;
					case "rate": // rate a stream
						if (isPost('stream') && isPost('category') && isPost('rate') && isPost('user') && (USERATE == "true") && isset($_SESSION['user']) && ($_SESSION['user'] == postString('user'))) {
							// connect to the database
							require_once("DIS_database.php");
							// prepare values
							$user = postString('user');
							$ip = checkIP();
							// check for previous rating from user					
							$request = queryDB("SELECT * FROM dis_rate WHERE rat_community='" . postString('community') . "' AND rat_stream='" . postString('stream') . "' AND rat_user='$user'");
							if (mysql_num_rows($request) > 0) {
								queryDB("UPDATE dis_rate SET rat_rate='" . postString('rate') . "', rat_time='" . time() . "', rat_timezone='" . date("O") . "', rat_category='" . postString("category") . "', rat_ip='$ip', rat_username='" . utf8_encode($_SESSION['name']) . "' WHERE rat_community='" . postString('community') . "' AND rat_stream='" . postString('stream') . "' AND rat_user='$user'");
							} else {
								queryDB("INSERT INTO dis_rate (rat_community, rat_stream, rat_category, rat_rate, rat_ip, rat_user, rat_time, rat_username, rat_timezone) VALUES ('" . postString('community') . "', '" . postString('stream') . "', '" . postString('category') . "', '" . postString('rate') . "', '$ip', '$user', '" . time() . "', '" . utf8_encode($_SESSION['name']) . "', '" . date("O") . "')");
								}
							mysql_free_result($request);
							// return rate data to user
							$request = queryDB("SELECT AVG(rat_rate) FROM dis_rate WHERE rat_community='" . postString('community') . "' AND rat_stream='" . postString('stream') . "'");
							$row = mysql_fetch_array($request);
							mysql_free_result($request);
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('<rate>' . round($row[0]) . '</rate>');
							echo('</data>');
						} else {
							// required data not sent
							$showerror = true;
						}
						break;
					case "search":
						if (isPost('query') && isPost('all')) {
							// connect to the database
							require_once("DIS_database.php");
							// process query
							$search = explode(" ", postString('query'));
							$allc = postBool('all');
							$query = "SELECT dis_stream.*, dis_community.* FROM dis_stream, dis_community WHERE (";
							$foundone = false;
							$fields = explode("|", SEARCHBY);
							for ($isearch=0; $isearch<sizeof($search); $isearch++) {
								if (strlen($search[$isearch]) > 2) {
									$foundone = true;
									for($ifield=0; $ifield<sizeof($fields); $ifield++) {
										$query .= "dis_stream.str_" . $fields[$ifield] . " LIKE '%" . $search[$isearch] . "%' OR ";
									}
								}
							}
							if ($foundone) {
								$query = substr($query, 0, (sizeof($query) - 4)) . ") ";
								$query .= "AND dis_stream.str_state='publish' ";
								if (!$allc) $query .= "AND dis_stream.str_community='" . postString('community') . "' ";
								$comms = explode("|", SEARCHEXCLUDE);
								for ($icomms=0; $icomms<sizeof($comms); $icomms++) {
									if ($comms[$icomms] != postString('community')) $query .= "AND dis_stream.str_community!='" . $comms[$icomms] . "' ";
								}
								$query .= "AND dis_stream.str_community=dis_community.com_id ";
								$query .= "ORDER BY dis_stream.str_index DESC LIMIT 20";
								$results = queryDB($query);
								if (mysql_num_rows($results) > 0) {
									echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
									echo('<action>' . postString('ac') . '</action>');
									echo('<message><![CDATA[');
									for ($isearch=0; $isearch<mysql_num_rows($results); $isearch++) {
										$row = mysql_fetch_assoc($results);
										echo('<result>');
										echo('<stream>' . $row['str_id'] . '</stream>');
										echo('<community>' . $row['str_community'] . '</community>');
										echo('<title>' . $row['com_title'] . " » " . $row['str_title'] . '</title>');
										echo('<about>' . $row['str_excerpt'] . '</about>');
										echo('</result>');
									}
									echo(']]></message>');
									echo('</data>');
								} else {
									echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
									echo('<action>' . postString('ac') . '</action>');
									echo('<message>0</message>');
									echo('</data>');
								}
								mysql_free_result($results);
							} else {
								echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
								echo('<action>' . postString('ac') . '</action>');
								echo('<message>0</message>');
								echo('</data>');
							}
						} else {
							// required data not sent
							$showerror = true;
						}
						break;
					case "comment": // comment a stream
						if (isPost('stream') && isPost('category') && isPost('comment') && isPost('user') && (COMMENTMODE != "none") && isset($_SESSION['user']) && ($_SESSION['user'] == postString('user'))) {
							// connect to the database
							require_once("DIS_database.php");
							// prepare values
							$user = postString('user');
							$name = $_SESSION['name'];
							$ip = checkIP();
							// comment status: 0 = approved, 1 = waiting for review, 2 = removed
							$status = 0;
							if (COMMENTMODE == "moderated") $status = 1;
							// add comment
							queryDB("INSERT INTO dis_comment (cmt_community, cmt_stream, cmt_category, cmt_comment, cmt_ip, cmt_user, cmt_username, cmt_time, cmt_timezone, cmt_status) VALUES ('" . postString('community') . "', '" . postString('stream') . "', '" . postString('category') . "', '" . encodeApostrophe(postString('comment')) . "', '$ip', '$user', '" . utf8_encode($name) . "', '" . time() . "', '" . date("O") . "', '$status')");
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							if ($status == 0) echo('<message><![CDATA[' . $text['COMMENTADD'] . ']]></message>');
								else echo('<message><![CDATA[' . $text['COMMENTWAIT'] . ']]></message>');
							echo('</data>');
						} else {
							// required data not sent
							$showerror = true;
						}
						break;
					case "logout": // logout user
						if (isset($_SESSION['user'])) unset($_SESSION['user']);
						if (isset($_SESSION['name'])) unset($_SESSION['name']);
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "login": // check user login
						require_once('DIS_database.php');
						$result = 0;
						$message = $text['LOGINERROR'];
						if (isPost('loginkey')) {
							$loginkey = postString('loginkey');
							$check = queryDB("SELECT * FROM dis_openusers WHERE opn_key='$loginkey'");
							if (mysql_num_rows($check) > 0) {
								$row = mysql_fetch_assoc($check);
								$result = 1;
								$remoteKey = randKey(32);
								$_SESSION['user'] = $row['opn_mail'];
								$_SESSION['name'] = $row['opn_name'];
								$message = $text['LOGINSUCCESS'];
								queryDB("UPDATE dis_openusers SET opn_key='', opn_remote='" . $remoteKey . "' WHERE opn_mail='" . $row['opn_mail'] . "'");
							}
							mysql_free_result($check);
						}						
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						if ($result == 0) {
							echo('<result>0</result>');
						} else {
							echo('<result>1</result>');
							echo('<mail>' . $_SESSION['user'] . '</mail>');
							// is this a super user?
							$checkLevel = queryDB("SELECT * FROM dis_user WHERE usr_email='" . $_SESSION['user'] . "'");
							if (mysql_num_rows($checkLevel) > 0) {
								$rowLevel = mysql_fetch_assoc($checkLevel);
								if (($rowLevel['usr_level'] == 'super') || ($rowLevel['usr_level'] == 'admin')) {
									echo('<level>0</level>');
								} else {
									echo('<level>1</level>');
								}
							} else {
								echo('<level>1</level>');
							}
							mysql_free_result($checkLevel);
							// remote key
							echo('<remotekey>' . $remoteKey . '</remotekey>');
							echo('<name><![CDATA[' . $_SESSION['name'] . ']]></name>');
						}
						echo('<message><![CDATA[' . $message . ']]></message>');
						echo('</data>');
						break;
					case "loginremote": // check user login for remote control
						require_once('DIS_database.php');
						$result = 0;
						$message = $text['LOGINERROR'];
						if (isPost('loginkey')) {
							$loginkey = postString('loginkey');
							$check = queryDB("SELECT * FROM dis_openusers WHERE opn_key='$loginkey'");
							if (mysql_num_rows($check) > 0) {
								$row = mysql_fetch_assoc($check);
								$result = 1;
								$_SESSION['user'] = $row['opn_mail'];
								$_SESSION['name'] = $row['opn_name'];
								$remoteKey = $row['opn_remote'];
								$message = $text['LOGINSUCCESS'];
								if (postString('mode') != "tcp") queryDB("UPDATE dis_openusers SET opn_key='' WHERE opn_mail='" . $row['opn_mail'] . "'");
							}
							mysql_free_result($check);
						}
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						if ($result == 0) {
							echo('<result>0</result>');
						} else {
							echo('<result>1</result>');
							echo('<mail>' . $_SESSION['user'] . '</mail>');
							echo('<name><![CDATA[' . $_SESSION['name'] . ']]></name>');
							// is this a super user?
							$checkLevel = queryDB("SELECT * FROM dis_user WHERE usr_email='" . $_SESSION['user'] . "' AND usr_level='super'");
							if (mysql_num_rows($checkLevel) > 0) {
								echo('<level>0</level>');
							} else {
								// remote user?
								if ((postString('pkey') == "") || (postString('pkey') == "null")) {
									echo('<level>1</level>');
								} else {
									$checkRLevel = queryDB("SELECT * FROM dis_remoteusers WHERE rus_login='" . $_SESSION['user'] . "' AND rus_publickey='" . postString('pkey') . "'");
									if (mysql_num_rows($checkRLevel) > 0) {
										echo('<level>0</level>');
									} else {
										echo('<level>1</level>');
									}
									mysql_free_result($checkRLevel);
								}
							}
							// remote key
							echo('<remotekey>' . $remoteKey . '</remotekey>');
							mysql_free_result($checkLevel);
						}
						echo('<message><![CDATA[' . $message . ']]></message>');
						echo('</data>');
						break;
					case "remotecheck": // remote control connection
						require_once('DIS_database.php');
						$peer = postString('peer');
						$group = postString('group');
						// look for a registered player
						$result = queryDB("SELECT * FROM dis_remote WHERE rem_mode='player' AND rem_group='$group'");
						if (mysql_num_rows($result) == 0) {
							// no player
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>1</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('</data>');
						} else {
							// register remote
							queryDB("INSERT INTO dis_remote (rem_mode, rem_ip, rem_peer, rem_group) VALUES ('remote', '" . checkIP() . "', '$peer', '$group')");
							// return data
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							$row = mysql_fetch_assoc($result);
							echo('<playerpeer>' . $row['rem_peer'] . '</playerpeer>');
							echo('<playerip>' . $row['rem_ip'] . '</playerip>');
							echo('</data>');
						}
						mysql_free_result($result);
						break;
					case "playerremote":
						require_once('DIS_database.php');
						$peer = postString('peer');
						$group = postString('group');
						// remove previous registry for player and remotes
						queryDB("DELETE FROM dis_remote WHERE rem_group='$group'");
						// add current player
						queryDB("INSERT INTO dis_remote (rem_mode, rem_ip, rem_peer, rem_group) VALUES ('player', '" . checkIP() . "', '$peer', '$group')");
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "playercheckremote":
						require_once('DIS_database.php');
						// check remotes connected to current player
						$group = postString('group');
						$result = queryDB("SELECT * FROM dis_remote WHERE rem_mode='remote' AND rem_group='$group'");
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						for ($i=0; $i<mysql_num_rows($result); $i++) {
							$row = mysql_fetch_assoc($result);
							echo('<remote ip="' . $row['rem_ip'] . '">' . $row['rem_peer'] . '</remote>');
						}
						echo('</data>');
						mysql_free_result($result);
						break;
					case "playerdeleteremote":
						require_once('DIS_database.php');
						// remove selected remote
						$group = postString('group');
						$peer = postString('peer');
						queryDB("DELETE FROM dis_remote WHERE rem_mode='remote' AND rem_group='$group' AND rem_peer='$peer'");
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "registertcp":
						require_once('DIS_database.php');
						// register a tcp remote server
						$id = postString('id');
						$port = postString('port');
						$akey = postString('akey');
						$local = postString('local');
						queryDB("DELETE FROM dis_remotetcp WHERE rtc_id='$id'");
						queryDB("INSERT INTO dis_remotetcp (rtc_ip, rtc_localip, rtc_port, rtc_id, rtc_key) VALUES ('" . checkIP() . "', '$local', '$port', '$id', '$akey')");
						// return
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "checkserverip":
						require_once('DIS_database.php');
						// look for an ip
						$id = postString('id');
						$check = queryDB("SELECT * FROM dis_remotetcp WHERE rtc_id='$id'");
						if (mysql_num_rows($check) == 0) {
							// no ip available
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>1</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('</data>');
						} else {
							// send ip and port
							$row = mysql_fetch_assoc($check);
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('<ip>' . $row['rtc_ip'] . '</ip>');
							echo('<localip>' . $row['rtc_localip'] . '</localip>');
							echo('<port>' . $row['rtc_port'] . '</port>');
							echo('</data>');
						}
						mysql_free_result($check);
						break;
					case "communitylist":
						require_once('DIS_database.php');
						// list all communities
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						$list = queryDB("SELECT * FROM dis_community order by com_title ASC");
						for ($i=0; $i<mysql_num_rows($list); $i++) {
							$com = mysql_fetch_assoc($list);
							echo('<community id="' . $com['com_id'] . '"><![CDATA[' . $com['com_title'] . ']]></community>');
						}
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "streamlist":
						require_once('DIS_database.php');
						$com = postString("listcommunity");
						// list all published streams for a community
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						$list = queryDB("SELECT * FROM dis_stream WHERE str_state='publish' AND str_community='$com' order by str_title ASC");
						for ($i=0; $i<mysql_num_rows($list); $i++) {
							$stream = mysql_fetch_assoc($list);
							echo('<stream id="' . $stream['str_id'] . '"><title><![CDATA[' . $stream['str_title'] . ']]></title><category><![CDATA[' . $stream['str_category'] . ']]></category></stream>');
						}
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "checkpkey":
						require_once('DIS_database.php');
						$pkey = postString("pkey");
						$password = postString("password");
						// is the public key listed as reserved?
						$checkKey = queryDB("SELECT * FROM dis_publickey WHERE pky_key='$pkey'");
						if (mysql_num_rows($checkKey) > 0) {
							// reserved key, check the password
							$rowKey = mysql_fetch_assoc($checkKey);
							if ($password != $rowKey['pky_pass']) {
								// password did not match: provide another public key
								$pkey = $pkey . rand(0, 1000) . rand(0, 1000);
							}
						}
						mysql_free_result($checkKey);
						// return a public key to be used
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<pkey>' . $pkey . '</pkey>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "savedata":
						require_once('DIS_database.php');
						$strValues = postString("strValues");
						$numValues = postString("numValues");
						$saveCom = postString("saveCom");
						$saveUsr = postString("saveUsr");
						// remove any previous saved data
						queryDB("DELETE FROM dis_usersaves WHERE usv_user='$saveUsr' AND usv_community='$saveCom'");
						// save received data
						queryDB("INSERT INTO dis_usersaves (usv_user, usv_community, usv_string, usv_number, usv_time) VALUES ('$saveUsr', '$saveCom', '$strValues', '$numValues', '" . time() . "')");
						// return ok
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "loaddata":
						require_once('DIS_database.php');
						$saveCom = postString("saveCom");
						$saveUsr = postString("saveUsr");
						// check data
						$checkData = queryDB("SELECT * FROM dis_usersaves WHERE usv_user='$saveUsr' AND usv_community='$saveCom'");
						$strValues = "";
						$numValues = "";
						if (mysql_num_rows($checkData) > 0) {
							$rowData = mysql_fetch_assoc($checkData);
							$strValues = $rowData['usv_string'];
							$numValues = $rowData['usv_number'];
						}
						mysql_free_result($checkData);
						// return ok
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('<strValues><![CDATA[' . urlencode($strValues) . ']]></strValues>');
						echo('<numValues><![CDATA[' . urlencode($numValues) . ']]></numValues>');
						echo('</data>');
						break;
					case "savecomvalue":
						require_once('DIS_database.php');
						$varCom = postString("varCom");
						$varName = postString("varName");
						$varValue = postString('varValue');
						// set the value
						queryDB("UPDATE dis_communityvalues SET cvl_value='$varValue', cvl_time='" . time() . "' WHERE cvl_community='$varCom' AND cvl_name='$varName'");
						// return ok
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "changecomvalue":
						require_once('DIS_database.php');
						$varCom = postString("varCom");
						$varName = postString("varName");
						$varValue = postString('varValue');
						$varAction = postString('varAction');
						$check = queryDB("SELECT * FROM dis_communityvalues WHERE cvl_community='$varCom' AND cvl_name='$varName'");
						if (mysql_num_rows($check) > 0) {
							$row = mysql_fetch_assoc($check);
							if (is_numeric($row['cvl_value']) && is_numeric($varValue)) {
								if ($varAction == "add") $newValue = (float)$row['cvl_value'] + (float)$varValue;
								else if ($varAction == "subtract") $newValue = (float)$row['cvl_value'] - (float)$varValue;
								else if ($varAction == "multiply") $newValue = (float)$row['cvl_value'] * (float)$varValue;
								else if ($varAction == "divide") {
									if ((float)$varValue != 0) $newValue = (float)$row['cvl_value'] / (float)$varValue;
								}
								queryDB("UPDATE dis_communityvalues SET cvl_value='$newValue', cvl_time='" . time() . "' WHERE cvl_community='$varCom' AND cvl_name='$varName'");
							}
						}
						mysql_free_result($check);
						// return ok
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "getcomvalues":
						require_once('DIS_database.php');
						$varCom = postString("varCom");
						$check = queryDB("SELECT * FROM dis_communityvalues WHERE cvl_community='$varCom'");
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						if (mysql_num_rows($check) > 0) {
							for ($i=0; $i<mysql_num_rows($check); $i++) {
								$row = mysql_fetch_assoc($check);
								echo('<variable>');
								echo('<name><![CDATA[' . $row['cvl_name'] . ']]></name>');
								echo('<value><![CDATA[' . $row['cvl_value'] . ']]></value>');
								echo('</variable>');
							}
						}
						mysql_free_result($check);
						echo('</data>');
						break;
					case "removenote":
						require_once('DIS_database.php');
						$user = postString('user');
						$type = postString('type');
						$id = postString('id');
						queryDB("DELETE FROM dis_usernotes WHERE unt_user='$user' AND unt_type='$type' AND unt_id='$id' LIMIT 1");
						// return ok
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('</data>');
						break;
					case "syncnotes":
						require_once('DIS_database.php');
						$user = postString('user');
						$type = postString('type');
						$current = postString('current');
						// break and process current information
						$arr1 = explode("|br|", $current);
						if (sizeof($arr1) > 0) {
							for ($i=0; $i<sizeof($arr1); $i++) {
								$arr2 = explode("|it|", $arr1[$i]);
								if (sizeof($arr2) == 5) {
									queryDB("DELETE FROM dis_usernotes WHERE unt_user='$user' AND unt_type='$type' AND unt_id='" . $arr2[0] . "' LIMIT 1");
									queryDB("INSERT INTO dis_usernotes (unt_user, unt_type, unt_id, unt_title, unt_text, unt_stream, unt_community) VALUES ('$user', '$type', '" . $arr2[0] . "', '" . $arr2[1] . "', '" . $arr2[2] . "', '" . $arr2[3] . "', '" . $arr2[4] . "')");
								}
							}
						}
						// retrive saved data
						$message = "";
						$result = queryDB("SELECT * FROM dis_usernotes WHERE unt_user='$user' AND unt_type='$type'");
						if (mysql_num_rows($result) > 0) {
							for ($i=0; $i<mysql_num_rows($result); $i++) {
								$row = mysql_fetch_assoc($result);
								$message .= $row['unt_id'] . '|it|' . $row['unt_title'] . '|it|' . $row['unt_text'] . '|it|' . $row['unt_stream'] . '|it|' . $row['unt_community'] . '|br|';
							}
						}
						if (strlen($message) > 0) $message = substr($message, 0, (strlen($message) - 4));
						// return data
						echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
						echo('<action>' . postString('ac') . '</action>');
						echo('<message><![CDATA[' . $message . ']]></message>');
						echo('</data>');
						break;
					case "availableoffline":
						require_once('DIS_database.php');
						$offlinecomms = explode("|", OFFLINEAVAILABLE);
						$result = queryDB("SELECT * FROM dis_community");
						if (mysql_num_rows($result) > 0) {
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							for ($i=0; $i<mysql_num_rows($result); $i++) {
								$row = mysql_fetch_assoc($result);
								if (in_array($row['com_id'], $offlinecomms)) {
									echo('<community id="' . $row['com_id'] . '" update="' . $row['com_update'] . '" index="' . $row['com_index'] . '"><![CDATA[' . $row['com_title'] . ']]></community>');
								}
							}
							echo('</data>');
						} else {
							$showerror = true;
						}
						mysql_free_result($result);
						break;
					case "getofflineinfo":
						require_once('DIS_database.php');
						$com = postString('com');
						// check for offline-enabled
						
						// read file list
						if (is_file("community/filelist_" . $com . ".xml")) {
							echo('<?xml version="1.0" encoding="utf-8"?><data><error>0</error>');
							echo('<action>' . postString('ac') . '</action>');
							echo('<community>' . $com . '</community>');
							echo('<filelist><![CDATA[' . file_get_contents("community/filelist_" . $com . ".xml") . ']]></filelist>');
							echo('</data>');
						} else {
							$showerror = true;
						}
						break;
					default: // requested action not found
						$showerror = true;
						break;
				}
		}
	}
}

// return error?
if ($showerror) {
	echo('<?xml version="1.0" encoding="utf-8"?><data><error>1</error></data>');
}
?>