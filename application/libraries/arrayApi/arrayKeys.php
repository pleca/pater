<?php

class arrayKeys extends gaApi {
	
	private $_key 			= NULL;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function __destruct() {
		parent::__destruct();
	}
	
	public function generateKey() {
		
		$this->_key = sha1(uniqid(rand(), TRUE));
		return true;
	}
	
	public function getKey() {
		
		try {
			
			if(empty($this->_key))
				throw new Exception("Key isn't generated.");
			
			return $this->_key;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
	}
	
}