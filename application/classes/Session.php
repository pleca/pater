<?php

//namespace classes;

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

class Session {
	public function get($name) {
		return isset($_SESSION[$name]) ? $_SESSION[$name] : false;
	}	
	
	public function set($name, $value) {
		$_SESSION[$name] = $value;
	}	
}
