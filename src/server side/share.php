<?php
/**
 * Managana
 * share.php - Managana share script
 */

// get data
$community = "";
$stream = "";
if (isset($_GET['c'])) $community = trim($_GET['c']);
if (isset($_GET['s'])) $stream = trim($_GET['s']);
$width = "100%"; // requested iframe width
if (isset($_GET["width"])) $width = trim($_GET["width"]);
$height = "100%"; // requested iframe height
if (isset($_GET["height"])) $height = trim($_GET["height"]);
 
// mobile browser check
require_once('DIS_browser.php');
$browser = new DISBrowser();
$use5 = false;
if (isset($_GET["render"])) {
	if (trim($_GET["render"]) == '5') $use5 = true;
}
$showicons = true;
if (isset($_GET["showicons"])) {
	if (trim($_GET["showicons"]) == 'false') $showicons = false;
}

if (($community != "") && ($stream != "")) {
	// look for the community and the stream
	if (is_dir("./community/" . $community . ".dis")) {
		// look for published stream
		if (is_file("./community/" . $community . ".dis/stream/" . $stream . ".xml")) {
			// load community information from xml
			$str = utf8_encode(file_get_contents("./community/" . $community . ".dis/dis.xml"));
			$data = new SimpleXMLElement($str);
			$ctitle = htmlentities(utf8_decode($data->meta[0]->title), ENT_COMPAT, "UTF-8");
			$icon = trim($data->meta[0]->icon);
			// load stream information from xml
			$str = utf8_encode(file_get_contents("./community/" . $community . ".dis/stream/" . $stream . ".xml"));
			$data = new SimpleXMLElement($str);
			$title = htmlentities(utf8_decode($data->meta[0]->title), ENT_COMPAT, "UTF-8");
			$description = htmlentities(utf8_decode($data->meta[0]->about), ENT_COMPAT, "UTF-8");

// check render method
if ($use5) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo($ctitle . " » " . $title); ?></title>
	<meta name="title" content="<?php echo($ctitle . " » " . $title); ?>" />
    <meta name="description" content="<?php echo($description); ?>" />
    <meta property="og:title" content="<?php echo($ctitle . " » " . $title); ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="<?php echo("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?c=" . $community . "&s=" . $stream); ?>"/>
    <?php if ($icon != "") $icon = str_replace("share.php/", "", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "/community/" . $community . ".dis/media/community/picture/" . $icon); ?>
    <meta property="og:image" content="<?php echo($icon); ?>"/>
    <meta property="og:site_name" content="<?php echo($ctitle); ?>"/>
    <meta property="og:description" content="<?php echo($description); ?>"/>
    <meta itemprop="name" content="<?php echo($ctitle . " » " . $title); ?>"/>
	<meta itemprop="description" content="<?php echo($description); ?>"/>
	<style>
	{ -webkit-tap-highlight-color: rgba(0, 0, 0, 0); }
	html, body {
		margin:0;
		padding:0;
		width:100%;
		height:100%;
		background-color: #000000;
	}
	#managanaholder {
		width:100%;
		height:100%;
		background-color: #000000;
	}
	<?php
	if (is_file('font/fontHTML.txt')) echo (file_get_contents('font/fontHTML.txt'));
	?>
	</style>
	<script src="http://code.createjs.com/easeljs-0.6.1.min.js"></script>
	<script src="http://code.createjs.com/tweenjs-0.4.1.min.js"></script>
    <script src="ColorFilter.js"></script>
	<script src="managana.js"></script>
</head>
<body>
<div id="managanaholder" />
<script>
	var opt = new Array();
	var onmobile = false;
	var showicons = true;
	<?php
		// check startup values
		if ($community != '') echo('opt["community"] = "' . $community . '";');
		if ($stream != '') echo('opt["stream"] = "' . $stream . '";');
		if ($browser->isMobile) echo('onmobile = true;');
		if (!$showicons) echo('showicons = false;');
	?>
	var player = new managana.Managana('managanaholder', opt, onmobile, showicons);
</script>
</body>
</html>
<?php } else {
// try to use flash

// prepare community id
if ($community != "") {
	if (substr($community, -4) != ".dis") $community .= ".dis";
	if (substr($community, 0, 7) != "http://") {
		if (substr($community, 0, 12) != "./community/") {
			if (substr($community, 0, 10) != "community/") {
				$community = "./community/" . $community;
			}
		}
	}
}

// prepare flashvars
$flashvars = array();
if ($community != "") $flashvars[] = 'community: "' . $community . '"';
if ($stream != "") $flashvars[] = 'stream: "' . $stream . '"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title><?php echo($ctitle . " » " . $title); ?></title>
	<meta name="title" content="<?php echo($ctitle . " » " . $title); ?>" />
    <meta name="description" content="<?php echo($description); ?>" />
    <meta property="og:title" content="<?php echo($ctitle . " » " . $title); ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="<?php echo("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?c=" . $community . "&s=" . $stream); ?>"/>
    <?php if ($icon != "") $icon = str_replace("share.php/", "", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "/community/" . $community . ".dis/media/community/picture/" . $icon); ?>
    <meta property="og:image" content="<?php echo($icon); ?>"/>
    <meta property="og:site_name" content="<?php echo($ctitle); ?>"/>
    <meta property="og:description" content="<?php echo($description); ?>"/>
    <meta itemprop="name" content="<?php echo($ctitle . " » " . $title); ?>"/>
	<meta itemprop="description" content="<?php echo($description); ?>"/>
	<script src="js/swfobject.js"></script>
	<script>
		// prepare data for flash player
		var flashvars = {<?php if (sizeof($flashvars) > 0) echo(implode(',', $flashvars)); ?>};
		var params = { menu: 'false', scale: 'noScale', allowFullscreen: 'true', allowScriptAccess: 'always', bgcolor: '', wmode: 'window'};
		var attributes = { id:'managana' };
		// is Adobe Flash Player version 11.1 available?
		if (swfobject.hasFlashPlayerVersion('11.1.0')) {
			// load Managana using flash
			swfobject.embedSWF('managana.swf', 'altContent', '<?php echo($width); ?>', '<?php echo($height); ?>', '11.1.0', 'expressInstall.swf', flashvars, params, attributes);
		} else {
			// look for HTML5 canvas support
			var canvasCheck = document.createElement('canvas');
			if (!(canvasCheck.getContext && canvasCheck.getContext('2d'))) {
				// do nothing: show the Managana load failure error message
			} else {
				// load html5 render
				var getparams = '?render=5';
				<?php
				if (isset($_GET["c"])) echo('getparams += "&c=' . trim($_GET["c"]) . '";');
				if (isset($_GET["s"])) echo('getparams += "&s=' . trim($_GET["s"]) . '";');
				?>
				window.location = getparams;
			}
		}
		
		/**
		 * Show the HTML box layer with the provided URL.
		 */
		function showHTMLBox(url) {
			var managana = document.getElementById('managana');
			var htmlbox = document.getElementById('htmlbox');
			htmlbox.src = url;
			htmlbox.style.width = (managana.offsetWidth - 80) + 'px';
			htmlbox.style.height = (managana.offsetHeight - 80) + 'px';
			managana.style.width = '40px';
			managana.style.height = '40px';
			htmlbox.style.display = 'block';
		}
		
		/**
		 * Return to Managana view after showing the HTML box.
		 */
		function showManagana() {
			var managana = document.getElementById('managana');
			var htmlbox = document.getElementById('htmlbox');
			htmlbox.src = "";
			htmlbox.style.display = 'none';
			managana.style.width = '100%';
			managana.style.height = '100%';
		}
		
		/**
		 * Check the htmlbox iframe url for Managana Progress Code.
		 */
		function checkBoxFrame() {	
			var htmlbox = document.getElementById('htmlbox');
			if (htmlbox.contentWindow.location.href.indexOf("|MANAGANACLOSE|") != -1) {	
				var managana = document.getElementById('managana');
				managana.ManaganaBoxClose(htmlbox.contentWindow.location.href);
			}
		}
		
	</script>
	<style>
		html, body { height: 100%; overflow: hidden; background-color: #000000; }
		body { margin:0; }
		#htmlbox { display: none; border: thin #000000; position: fixed; left: 40px; top: 40px; }
		#altContent { color: #FFFFFF; }
		#altContent h1 { font-weigth: bold; }
		#altContent a { color: #FF9900; }
	</style>
</head>
<body>
    <div id="altContent">
		<h1>Oops... Managana failed to load :-(</h1>
		<p>You need <a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player</a> or an <a href="http://www.firefox.com/">HTML5-ready browser</a> to run Managana. Visit <a href="http://www.managana.org/">www.managana.org</a> to learn more about this free, open source, digital publishing solution for web, mobile devices and public presentations.</p>
	</div>
    <iframe id="htmlbox" src="" onload="javascript:checkBoxFrame();" />
</body>
</html>
<?php } ?>
<?php
		}
	}
}
?>