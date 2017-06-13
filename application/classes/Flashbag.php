<?php

//namespace classes;

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}


class Flashbag {
	
	
	public function add($type, $msg) {
		$_SESSION['flashbag'][$type][] = $msg;
	}
	
	// removes msg after displaying
	public function get($type, array $default = []) {
		if (!isset($_SESSION['flashbag'][$type])) {
			return false;
		}
		
		$tmp = $_SESSION['flashbag'][$type];
		unset($_SESSION['flashbag'][$type]);
		return isset($tmp) ? $tmp : false;
	}
	
	public function has($type, $key = null) {
		if (!isset($_SESSION['flashbag'][$type])) {
			return false;
		}
		
		if ($key && isset($_SESSION['flashbag'][$type][$key])) {
			return true;
		}

		return true;
	}
	
	public function count($type) {
		if (!isset($_SESSION['flashbag'][$type])) {
			return false;
		}

		return count($_SESSION['flashbag'][$type]);
	}
	
}
