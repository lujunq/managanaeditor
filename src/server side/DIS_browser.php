<?php
class DISBrowser {
	
	var $brString = '';
	var $isAndroid = false;
	var $isIOS = false;
	var $isMobile = false;
	var $isFirefoxOS = false;
	var $isWindowsMobile = false;
	var $isLinux = false;
	var $isOSX = false;
	var $isWindowsDesktop = false;
	
	
	function DISBrowser() {
		$this->brString = strtolower($_SERVER['HTTP_USER_AGENT']);
		// check for android
		if (strpos($this->brString, 'android') === false) {
			$this->isAndroid = false;
			// check for ios
			if ((strpos($this->brString, 'ipad') === false) && (strpos($this->brString, 'ipod') === false) && (strpos($this->brString, 'iphone') === false)) {
				$this->isIOS = false;
			} else {
				// system is ios
				$this->isIOS = true;
			}
		} else {
			// system is android
			$this->isAndroid = true;
		}
		// mobile browser (android/ios only)
		if (strpos($this->brString, 'mobile') === false) {
			$this->isMobile = $this->isAndroid || $this->isIOS;
		} else {
			$this->isMobile = true;
		}
		// mobile ie?
		if (strpos($this->brString, 'iemobile') === false) {
			// do nothing
		} else {
			$this->isWindowsMobile = true;
			$this->isMobile = true;
		}
		// firefox os?
		if (strpos($this->brString, 'firefox') === false) {
			// do nothing
		} else {
			if ($this->isMobile && !$this->isAndroid) $this->isFirefoxOS = true;
		}
		// desktop linux
		if (strpos($this->brString, 'linux') === false) {
			// do nothing
		} else {
			if (!$this->isAndroid) $this->isLinux = true;
		}
		// desktop osx
		if (strpos($this->brString, 'macintosh') === false) {
			// do nothing
		} else {
			if (!$this->isMobile) $this->isOSX = true;
		}
		// desktop windows
		if (strpos($this->brString, 'windows') === false) {
			// do nothing
		} else {
			if (!$this->isMobile) $this->isWindowsDesktop = true;
		}
		
	}
	
}
?>