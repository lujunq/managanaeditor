<?php
/**
 * Managana server: file handling common functions
 */

/**
 * Check if a file is of a given type according to its filename.
 */
function checkType($name, $type) {
	$fileok = false;
	$fileext = end(explode(".", $name));
	$fileext = strtolower($fileext);
	switch ($type) {
		case "widget":
			if ($fileext == "swf") $fileok = true;
			break;
		case "picture":
			if ($fileext == "jpg") $fileok = true;
			else if ($fileext == "png") $fileok = true;
			else if ($fileext == "gif") $fileok = true;
			else if ($fileext == "jpeg") $fileok = true;
			else if ($fileext == "swf") $fileok = true;
			else if ($fileext == "svg") $fileok = true;
			break;
		case "audio":
			if ($fileext == "mp3") $fileok = true;
			else if ($fileext == "ogg") $fileok = true;
			else if ($fileext == "webm") $fileok = true;
			else if ($fileext == "wav") $fileok = true;
			break;
		case "video":
			if ($fileext == "flv") $fileok = true;
			else if ($fileext == "f4v") $fileok = true;
			else if ($fileext == "mp4") $fileok = true;
			else if ($fileext == "ogv") $fileok = true;
			else if ($fileext == "webm") $fileok = true;
			else if ($fileext == "png") $fileok = true;
			else if ($fileext == "srt") $fileok = true;
			else if ($fileext == "3gp") $fileok = true;
			else if ($fileext == "3g2") $fileok = true;
			break;
		case "html":
			if ($fileext == "htm") $fileok = true;
			else if ($fileext == "html") $fileok = true;
			else if ($fileext == "js") $fileok = true;
			else if ($fileext == "xml") $fileok = true;
			else if ($fileext == "txt") $fileok = true;
			else if ($fileext == "jpg") $fileok = true;
			else if ($fileext == "png") $fileok = true;
			else if ($fileext == "gif") $fileok = true;
			else if ($fileext == "jpeg") $fileok = true;
			else if ($fileext == "swf") $fileok = true;
			else if ($fileext == "mp3") $fileok = true;
			else if ($fileext == "flv") $fileok = true;
			else if ($fileext == "f4v") $fileok = true;
			else if ($fileext == "mp4") $fileok = true;
			else if ($fileext == "3gp") $fileok = true;
			else if ($fileext == "3g2") $fileok = true;
			else if ($fileext == "srt") $fileok = true;
			break;
	}
	return($fileok);
}

/**
 * Get a file subtype.
 */
function subType($name) {
	$subtype = "";
	$fileext = end(explode(".", $name));
	$fileext = strtolower($fileext);
	switch ($fileext) {
		case "jpg":
		case "jpeg":
			$subtype = "jpeg";
			break;
		case "png":
			$subtype = "png";
			break;
		case "swf":
			$subtype = "swf";
			break;
		case "svg":
			$subtype = "svg";
			break;
		case "gif":
			$subtype = "gif";
			break;
		case "mp3":
			$subtype = "mp3";
			break;
		case "flv":
			$subtype = "vp6";
			break;
		case "f4v":
		case "mp4":
		case "3gp":
		case "3g2":
			$subtype = "h264";
		case "webm":
			$subtype = "vp8";
			break;
		case "ogg":
			$subtype = "vorbis";
			break;
		case "ogv":
			$subtype = "theora";
			break;
		case "wav":
			$subtype = "wave";
			break;
		case "srt":
			$subtype = "srt";
			break;
		case "htm":
		case "html":
			$subtype = "html";
			break;
		case "js":
			$subtype = "javascript";
			break;
		case "xml":
			$subtype = "xml";
			break;
		case "txt":
			$subtype = "txt";
			break;
	}
	return($subtype);
}
?>