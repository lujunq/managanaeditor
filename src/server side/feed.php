<?php
/**
 * Managana server: feed proxy
 */
 
// try to avoid timeout (fetch feeds may consume some time)
set_time_limit(0);

// get configuration
require_once('DIS_config.php');
	
// get common functions
require_once('DIS_common.php');

// try to connect to the database
require_once('DIS_database.php');

// error management
$error = false;
	
// get feed information
$ref = "";
if (isset($_GET['ref'])) $ref = trim($_GET['ref']);
$type = "";
if (isset($_GET['type'])) $type = trim($_GET['type']);
$com = "";
if (isset($_GET['com'])) $com = trim($_GET['com']);

// all required information?
if (($ref != "") && ($type != "") && ($com != "")) {
	// check for last time the feed was retrieved
	$check = queryDB("SELECT * FROM dis_feed WHERE fed_type='$type' AND fed_reference='$ref' AND fed_community='$com'");
	if (mysql_num_rows($check) > 0) {
		$about = mysql_fetch_assoc($check);
		$feedfolder = 'feeds/' . $about['fed_index'];
		if (!is_dir($feedfolder)) mkdir($feedfolder);
		if (time() > ((int)$about['fed_last'] + (FEEDTIMEOUT * 3600))) {
			// check for feed type
			switch($type) {
				case "Facebook":
					// is facebook available?
					if (is_file('feeds/facebook.php') && is_file('feeds/base_facebook.php') && (FBAPPID != "") && (FBAPPSECRET != "")) {
						// retreive facebook data
						require_once('feeds/facebook.php');
						$facebook = new Facebook(array('appId'  => FBAPPID, 'secret' => FBAPPSECRET));
						$info = $facebook->api("/" . $ref);
						$wall = $facebook->api("/" . $ref . "/feed");
						// start output
						$file = fopen($feedfolder . "/feed.rss", "wb");
						fputs($file, '<?xml version="1.0" encoding="UTF-8"?>');
						fputs($file, '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" >');
						fputs($file, '<channel>');
						fputs($file, '<title>Facebook wall of ' . $info['id'] . '</title>');
						fputs($file, '<atom:link href="' . INSTALLFOLDER . "/" . $feedfolder . '/feed.rss" rel="self" type="application/rss+xml" />');
						fputs($file, '<description>Facebook wall of ' . $info['id'] . '</description>');
						fputs($file, '<lastBuildDate>' . date("D, d M Y H:i:s O") . '</lastBuildDate>');
						fputs($file, '<language>en</language>');
						fputs($file, '<link>https://www.facebook.com/profile.php?id=' . $info['id'] . '</link>');
						fputs($file, '<generator>http://www.managana.org/</generator>');
						// write each item
						for ($i=0; $i<sizeof($wall['data']); $i++) {
							$title = "";
							$link = "";
							$pubDate = "";
							$guid = "";
							$description = "";
							$content = "";
							reset($wall['data'][$i]);
							while (list($key, $value) = each($wall['data'][$i])) {
								switch ($key) {
									case 'name': $title = $value; break;
									case 'link': if ($link == "") $link = $value; break;
									case 'source': $link = $value; break;
									case 'description': if (strlen($value) > strlen($description)) $description = $value; break;
									case 'message': if (strlen($value) > strlen($description)) $description = $value; break;
									case 'caption': if (strlen($value) > strlen($description)) $description = $value; break;
									case 'created_time':
										// 2011-10-07T18:55:08+0000
										$dateArr = explode("T", $value);
										$dateDay = explode("-", $dateArr[0]);
										$splitHour = explode("+", $dateArr[1]);
										$dateHour = explode(":", $splitHour[0]);
										$time = mktime((int)$dateHour[0], (int)$dateHour[1], (int)$dateHour[2], (int)$dateDay[1], (int)$dateDay[2], (int)$dateDay[0]);
										$pubDate = date("D, d M Y H:i:s O", $time);
										break;
								}
							}
							if ($title != "") {
								$image = loadPictureFromURL($link, ("pic" . $i), ($feedfolder . "/"));
								if ($image === false) $image = "";
								// write post data
								fputs($file, '<item>');
								fputs($file, '<title><![CDATA[' . $title . ']]></title>');
								fputs($file, '<link><![CDATA[' . $link . ']]></link>');
								fputs($file, '<pubDate>' . $pubDate . '</pubDate>');
								fputs($file, '<creator>Managana.org</creator>');
								fputs($file, '<category>Managana.org</category>');
								fputs($file, '<guid isPermaLink="false">' . $link . '</guid>');
								fputs($file, '<description><![CDATA[' . $description . ']]></description>');
								if ($image != "") fputs($file, '<picture>' . INSTALLFOLDER . "/" . $feedfolder . "/" . $image . '</picture>');
								fputs($file, '</item>');
							}
						}
						// end output
						fputs($file, "</channel></rss>");
						fclose($file);
						// set current retrieve time
						queryDB("UPDATE dis_feed SET fed_last='" . time() . "' WHERE fed_index='" . $about['fed_index'] . "'");
					} else {
						// no facebook application found - write an empty feed
						$file = fopen($feedfolder . "/feed.rss", "wb");
						fputs($file, '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/"><channel><title></title><atom:link href="" rel="self" type="application/rss+xml" /><link></link><description></description><lastBuildDate></lastBuildDate><language></language><sy:updatePeriod></sy:updatePeriod><sy:updateFrequency></sy:updateFrequency><generator></generator></channel></rss>');
						fclose($file);
					}
					break;
				case "Twitter":
					// twitter account or search?
					$lookfor = "";
					if (strpos($ref, "@") === false) {
						if (strpos($ref, "#") === false) {
							$error = true;
						} else {
							$lookfor = "#";
							$url = 'http://search.twitter.com/search.rss?rpp=50&q=' . substr($ref, 1);
						}
					} else {
						$lookfor = "@";
						$url = 'http://api.twitter.com/1/statuses/user_timeline.rss?&count=50&screen_name=' . substr($ref, 1);
					}
					// retrieve reference
					if (!$error) {
						$str = file_get_contents($url);		// server must allow url acces for file_get_contents
						$feed = new SimpleXMLElement($str);
						// start output
						$file = fopen($feedfolder . "/feed.rss", "wb");
						fputs($file, '<?xml version="1.0" encoding="UTF-8"?>');
						fputs($file, '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" >');
						fputs($file, '<channel>');
						fputs($file, '<title>' . $feed->channel->title . '</title>');
						fputs($file, '<atom:link href="' . INSTALLFOLDER . "/" . $feedfolder . '/feed.rss" rel="self" type="application/rss+xml" />');
						fputs($file, '<description>' . $feed->channel->description . '</description>');
						fputs($file, '<lastBuildDate>' . date("D, d M Y H:i:s O") . '</lastBuildDate>');
						fputs($file, '<language>' . $feed->channel->language . '</language>');
						fputs($file, '<link>' . $url . '</link>');
						fputs($file, '<generator>http://www.managana.org/</generator>');
						// write each item
						for ($i=0; $i<sizeof($feed->channel->item); $i++) {
							// get post data
							$item = $feed->channel->item[$i];
							if ($lookfor == "@") $title = twitterRemoveUser($ref, $item->title);
								else $title = $item->title;
							$description = $title;
							$link = twitterGetLink($title);
							$image = loadPictureFromURL($link, ("pic" . $i), ($feedfolder . "/"));
							if ($image === false) $image = "";
							$pubDate = $item->pubDate;
							// write post data
							fputs($file, '<item>');
							fputs($file, '<title><![CDATA[' . $title . ']]></title>');
							fputs($file, '<link><![CDATA[' . $link . ']]></link>');
							fputs($file, '<pubDate>' . $pubDate . '</pubDate>');
							fputs($file, '<creator>Managana.org</creator>');
							fputs($file, '<category>Managana.org</category>');
							fputs($file, '<guid isPermaLink="false">' . $link . '</guid>');
							fputs($file, '<description><![CDATA[' . $description . ']]></description>');
							if ($image != "") fputs($file, '<picture>' . INSTALLFOLDER . "/" . $feedfolder . "/" . $image . '</picture>');
							fputs($file, '</item>');
						}
						// end output
						fputs($file, "</channel></rss>");
						fclose($file);
						// set current retrieve time
						queryDB("UPDATE dis_feed SET fed_last='" . time() . "' WHERE fed_index='" . $about['fed_index'] . "'");
					}
					break;
				case "Wordpress":
					// retrieve reference
					$str = file_get_contents($ref);		// server must allow url acces for file_get_contents
					$feed = new SimpleXMLElement($str);
					// start output
					$file = fopen($feedfolder . "/feed.rss", "wb");
					fputs($file, '<?xml version="1.0" encoding="UTF-8"?>');
					fputs($file, '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" >');
					fputs($file, '<channel>');
					fputs($file, '<title>' . $feed->channel->title . '</title>');
					fputs($file, '<atom:link href="' . INSTALLFOLDER . "/" . $feedfolder . '/feed.rss" rel="self" type="application/rss+xml" />');
					fputs($file, '<description>' . $feed->channel->description . '</description>');
					fputs($file, '<lastBuildDate>' . date("D, d M Y H:i:s O") . '</lastBuildDate>');
					fputs($file, '<language>' . $feed->channel->language . '</language>');
					fputs($file, '<link>' . $feed->channel->link . '</link>');
					fputs($file, '<generator>http://www.managana.org/</generator>');
					// write each item
					for ($i=0; $i<sizeof($feed->channel->item); $i++) {
						// get post data
						$item = $feed->channel->item[$i];
						$title = $item->title;
						$description = trim(strip_tags($item->description));
						$description = html_entity_decode($description);
						$description = str_replace("&ndash;", "", $description);
						$image = loadPictureFromWordpress($item->picture, ("pic" . $i), ($feedfolder . "/"));
						if ($image === false) $image = "";
						$link = utf8_decode($item->link);
						$pubDate = utf8_decode($item->pubDate);
						// write post data
						fputs($file, '<item>');
						fputs($file, '<title><![CDATA[' . $title . ']]></title>');
						fputs($file, '<link><![CDATA[' . $link . ']]></link>');
						fputs($file, '<pubDate>' . $pubDate . '</pubDate>');
						fputs($file, '<creator>Managana.org</creator>');
						fputs($file, '<category>Managana.org</category>');
						fputs($file, '<guid isPermaLink="false">' . $link . '</guid>');
						fputs($file, '<description><![CDATA[' . $description . ']]></description>');
						if ($image != "") fputs($file, '<picture>' . INSTALLFOLDER . "/" . $feedfolder . "/" . $image . '</picture>');
						fputs($file, '<video><![CDATA[' . $item->video . ']]></video>');
						fputs($file, '<audio><![CDATA[' . $item->audio . ']]></audio>');
						fputs($file, '<author><![CDATA[' . $item->author . ']]></author>');
						fputs($file, '</item>');
					}
					// end output
					fputs($file, "</channel></rss>");
					fclose($file);
					// set current retrieve time
					queryDB("UPDATE dis_feed SET fed_last='" . time() . "' WHERE fed_index='" . $about['fed_index'] . "'");
					break;
				case "RSS2":
					// retrieve reference
					$str = file_get_contents($ref);		// server must allow url acces for file_get_contents
					$feed = new SimpleXMLElement($str);
					// start output
					$file = fopen($feedfolder . "/feed.rss", "wb");
					fputs($file, '<?xml version="1.0" encoding="UTF-8"?>');
					fputs($file, '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" >');
					fputs($file, '<channel>');
					fputs($file, '<title>' . $feed->channel->title . '</title>');
					fputs($file, '<atom:link href="' . INSTALLFOLDER . "/" . $feedfolder . '/feed.rss" rel="self" type="application/rss+xml" />');
					fputs($file, '<description>' . $feed->channel->description . '</description>');
					fputs($file, '<lastBuildDate>' . date("D, d M Y H:i:s O") . '</lastBuildDate>');
					fputs($file, '<language>' . $feed->channel->language . '</language>');
					fputs($file, '<link>' . $feed->channel->link . '</link>');
					fputs($file, '<generator>http://www.managana.org/</generator>');
					// write each item
					for ($i=0; $i<sizeof($feed->channel->item); $i++) {
						// get post data
						$item = $feed->channel->item[$i];
						$title = $item->title;
						$description = trim(strip_tags($item->description));
						$description = html_entity_decode($description);
						$description = str_replace("&ndash;", "", $description);
						$image = loadPictureFromURL($item->link, ("pic" . $i), ($feedfolder . "/"));
						if ($image === false) $image = "";
						$link = utf8_decode($item->link);
						$pubDate = utf8_decode($item->pubDate);
						// write post data
						fputs($file, '<item>');
						fputs($file, '<title><![CDATA[' . $title . ']]></title>');
						fputs($file, '<link><![CDATA[' . $link . ']]></link>');
						fputs($file, '<pubDate>' . $pubDate . '</pubDate>');
						fputs($file, '<creator>Managana.org</creator>');
						fputs($file, '<category>Managana.org</category>');
						fputs($file, '<guid isPermaLink="false">' . $link . '</guid>');
						fputs($file, '<description><![CDATA[' . $description . ']]></description>');
						if ($image != "") fputs($file, '<picture>' . INSTALLFOLDER . "/" . $feedfolder . "/" . $image . '</picture>');
						fputs($file, '</item>');
					}
					// end output
					fputs($file, "</channel></rss>");
					fclose($file);
					// set current retrieve time
					queryDB("UPDATE dis_feed SET fed_last='" . time() . "' WHERE fed_index='" . $about['fed_index'] . "'");
					break;
			}
		}
		// return feed information
		if (!$error) {
			if (is_file($feedfolder . "/feed.rss")) {
				$str = file_get_contents($feedfolder . "/feed.rss");
				echo($str);
			} else {
				// no feed found
				$error = true;
			}
		}
	} else {
		// no feed found
		$error = true;
	}
	mysql_free_result($check);
} else {
	$error = true;
}

// write an empty rss2 feed on error
if ($error) {
	echo('<?xml version="1.0" encoding="utf-8"?>');
	?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
	<channel>
		<title></title>
		<atom:link href="" rel="self" type="application/rss+xml" />
		<link></link>
		<description></description>
		<lastBuildDate></lastBuildDate>
		<language></language>
		<sy:updatePeriod></sy:updatePeriod>
		<sy:updateFrequency></sy:updateFrequency>
		<generator></generator>
	</channel>
</rss>
    <?php
}

/**
 * This function load a html-formatted text from an URL and try to find and download the largest picture found on it.
 */
function loadPictureFromURL($url, $name, $path) {
	$picture = array();
	$str = @file_get_contents($url);
	$picarr1 = explode('<img', $str);
	if (sizeof($picarr1) > 1) {
		for ($i=1; $i<sizeof($picarr1); $i++) {
			$picarr2 = explode('src="', $picarr1[$i]);
			if (sizeof($picarr2) > 1) {
				$picarr3 = explode('"', $picarr2[1]);
				if (sizeof($picarr3) > 1) {
					$pos = strpos($picarr3[0], ".gif");
					if ($pos === false) {
						$picture[] = $picarr3[0];
					}
				}
			}
		}
	}
	
	$chosen = "";
	$width = 0;
	$height = 0;
	if (sizeof($picture) == 0) {
		// do nothing
	} else {
		for ($i=0; $i<sizeof($picture); $i++) {
			$pos = strpos($picarr3[0], "http");
			if ($pos === false) {
				// do nothing
			} else {
				$imagesize = @getimagesize($picture[$i]);
				if (($imagesize[0] == $width) && ($imagesize[1] == $height)) {
					if (rand(0, 10) < 5) {
						$width = $imagesize[0];
						$height = $imagesize[1];
						$chosen = $picture[$i];
					}
				} else if (($imagesize[0] > $width) && ($imagesize[1] >= 100)) {
					$width = $imagesize[0];
					$height = $imagesize[1];
					$chosen = $picture[$i];
				}
			}
		}
		if ($chosen == "") {
			// do nothing
		} else {
			$ext = explode (".", $chosen);
			@copy($chosen, ($path . $name . "." . $ext[sizeof($ext) - 1]));
		}
	}
	if ($chosen == "") return (false);
	else return (($name . "." . $ext[sizeof($ext) - 1]));
}

/**
 * This function copies a picture from a wordpress page using the managana wordpress page theme.
 */
function loadPictureFromWordpress($url, $name, $path) {
	if ($url != "") {
		$ext = explode (".", $url);
		@copy($url, ($path . $name . "." . $ext[sizeof($ext) - 1]));
		return (($name . "." . $ext[sizeof($ext) - 1]));
	} else {
		return(false);
	}
}

/**
 * Remove the username from twitter posts.
 */
function twitterRemoveUser($user, $text) {
	if ($user != "") {
		$text = str_replace((substr($user, 1) . ": "), "", $text);
	}
	return($text);
}

/**
 * Get a link from a twitter post text
 */
function twitterGetLink($text) {
	$link = "";
	$temp = explode("http://", $text);
	if (sizeof($temp) > 1) {
		$link = "http://" . $temp[1];
		if (strpos($link, " ") === false) {
			// do nothing
		} else {
			$link = substr($link, 0, strpos($link, " "));
		}
	}
	return($link);
}
?>