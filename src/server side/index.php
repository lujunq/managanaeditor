<?php
/**
 * Managana
 * index.php - home page for php-based servers
 */
 
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
 
// receive variables
$configurl = ""; // url for the configuration url
if (isset($_GET["configurl"])) $configurl = trim($_GET["configurl"]);
$community = ""; // initial community
if (isset($_GET["community"])) $community = trim($_GET["community"]);
$stream = ""; // initial stream
if (isset($_GET["stream"])) $stream = trim($_GET["stream"]);
$readerkey = ""; // access key for the server
if (isset($_GET["readerkey"])) $readerkey = trim($_GET["readerkey"]);
$method = ""; // access method for server
if (isset($_GET["method"])) $method = trim($_GET["method"]);
$ending = ""; // file ending for server scripts
if (isset($_GET["ending"])) $ending = trim($_GET["ending"]);
$server = ""; // url of the server
if (isset($_GET["server"])) $server = trim($_GET["server"]);
$ui = ""; // show user interface (string true/false)
if (isset($_GET["ui"])) $ui = trim($_GET["ui"]);
$width = "100%"; // requested iframe width
if (isset($_GET["width"])) $width = trim($_GET["width"]);
$height = "100%"; // requested iframe height
if (isset($_GET["height"])) $height = trim($_GET["height"]);
$loginkey = "";// OpenID/oAuth login key
if (isset($_GET["loginkey"])) $loginkey = trim($_GET["loginkey"]);
$bgcolor = ""; // background color
if (isset($_GET["bgcolor"])) $bgcolor = trim($_GET["bgcolor"]);
$logo = "";
if (isset($_GET["logo"])) $logo = trim($_GET["logo"]);
$showinterface = "";
if (isset($_GET["showinterface"])) $showinterface = trim($_GET["showinterface"]);
$showclock = "";
if (isset($_GET["showclock"])) $showclock = trim($_GET["showclock"]);
$showvote = "";
if (isset($_GET["showvote"])) $showvote = trim($_GET["showvote"]);
$showcomment = "";
if (isset($_GET["showcomment"])) $showcomment = trim($_GET["showcomment"]);
$showrate = "";
if (isset($_GET["showrate"])) $showrate = trim($_GET["showrate"]);
$showzoom = "";
if (isset($_GET["showzoom"])) $showzoom = trim($_GET["showzoom"]);
$shownote = "";
if (isset($_GET["shownote"])) $shownote = trim($_GET["shownote"]);
$showuser = "";
if (isset($_GET["showuser"])) $showuser = trim($_GET["showuser"]);

// check render method
if ($use5) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Managana</title>
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
		if ($server != '') echo('opt["server"] = "' . $server . '";');
		if ($ui != '') echo('opt["ui"] = "' . $ui . '";');
		if ($bgcolor != '') echo('opt["bgcolor"] = "' . $bgcolor . '";');
		if ($logo != '') echo('opt["logo"] = "' . $logo . '";');
		if ($showinterface != '') echo('opt["showinterface"] = "' . $showinterface . '";');
		if ($showclock != '') echo('opt["showclock"] = "' . $showclock . '";');
		if ($showvote != '') echo('opt["showvote"] = "' . $showvote . '";');
		if ($showcomment != '') echo('opt["showcomment"] = "' . $showcomment . '";');
		if ($showrate != '') echo('opt["showrate"] = "' . $showrate . '";');
		if ($showzoom != '') echo('opt["showzoom"] = "' . $showzoom . '";');
		if ($shownote != '') echo('opt["shownote"] = "' . $shownote . '";');
		if ($showuser != '') echo('opt["showuser"] = "' . $showuser . '";');
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
if ($configurl != "") $flashvars[] = 'configurl: "' . $configurl . '"';
if ($community != "") $flashvars[] = 'community: "' . $community . '"';
if ($stream != "") $flashvars[] = 'stream: "' . $stream . '"';
if ($readerkey != "") $flashvars[] = 'readerkey: "' . $readerkey . '"';
if ($method != "") $flashvars[] = 'method: "' . $method . '"';
if ($ending != "") $flashvars[] = 'ending: "' . $ending . '"';
if ($server != "") $flashvars[] = 'server: "' . $server . '"';
if ($bgcolor != "") $flashvars[] = 'bgcolor: "' . $bgcolor . '"';
if ($ui != "") $flashvars[] = 'ui: "' . $ui . '"';
if ($loginkey != "") $flashvars[] = 'loginkey: "' . $loginkey . '"';
if ($logo != "") $flashvars[] = 'logo: "' . $logo . '"';
if ($showinterface != "") $flashvars[] = 'showinterface: "' . $showinterface . '"';
if ($showclock != "") $flashvars[] = 'showclock: "' . $showclock . '"';
if ($showvote != "") $flashvars[] = 'showvote: "' . $showvote . '"';
if ($showcomment != "") $flashvars[] = 'showcomment: "' . $showcomment . '"';
if ($showrate != "") $flashvars[] = 'showrate: "' . $showrate . '"';
if ($showzoom != "") $flashvars[] = 'showzoom: "' . $showzoom . '"';
if ($shownote != "") $flashvars[] = 'shownote: "' . $shownote . '"';
if ($showuser != "") $flashvars[] = 'showuser: "' . $showuser . '"';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Managana</title>
	<meta name="description" content="" />
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
				if (isset($_GET["showicons"])) if (trim($_GET["showicons"]) == 'false') echo('getparams += "&showicons=false";');
				if (isset($_GET["configurl"])) echo('getparams += "&configurl=' . trim($_GET["configurl"]) . '";');
				if (isset($_GET["community"])) echo('getparams += "&community=' . trim($_GET["community"]) . '";');
				if (isset($_GET["stream"])) echo('getparams += "&stream=' . trim($_GET["stream"]) . '";');
				if (isset($_GET["readerkey"])) echo('getparams += "&readerkey=' . trim($_GET["readerkey"]) . '";');
				if (isset($_GET["method"])) echo('getparams += "&method=' . trim($_GET["method"]) . '";');
				if (isset($_GET["ending"])) echo('getparams += "&ending=' . trim($_GET["ending"]) . '";');
				if (isset($_GET["server"])) echo('getparams += "&server=' . trim($_GET["server"]) . '";');
				if (isset($_GET["ui"])) echo('getparams += "&ui=' . trim($_GET["ui"]) . '";');
				if (isset($_GET["width"])) echo('getparams += "&width=' . trim($_GET["width"]) . '";');
				if (isset($_GET["height"])) echo('getparams += "&height=' . trim($_GET["height"]) . '";');
				if (isset($_GET["bgcolor"])) echo('getparams += "&bgcolor=' . trim($_GET["bgcolor"]) . '";');
				if (isset($_GET["logo"])) echo('getparams += "&logo=' . trim($_GET["logo"]) . '";');
				if (isset($_GET["showinterface"])) echo('getparams += "&showinterface=' . trim($_GET["showinterface"]) . '";');
				if (isset($_GET["showclock"])) echo('getparams += "&showclock=' . trim($_GET["showclock"]) . '";');
				if (isset($_GET["showvote"])) echo('getparams += "&showvote=' . trim($_GET["showvote"]) . '";');
				if (isset($_GET["showcomment"])) echo('getparams += "&showcomment=' . trim($_GET["showcomment"]) . '";');
				if (isset($_GET["showrate"])) echo('getparams += "&showrate=' . trim($_GET["showrate"]) . '";');
				if (isset($_GET["showzoom"])) echo('getparams += "&showzoom=' . trim($_GET["showzoom"]) . '";');
				if (isset($_GET["shownote"])) echo('getparams += "&shownote=' . trim($_GET["shownote"]) . '";');
				if (isset($_GET["showuser"])) echo('getparams += "&showuser=' . trim($_GET["showuser"]) . '";');
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