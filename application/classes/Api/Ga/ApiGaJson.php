<?php 

require_once(CLASS_DIR . '/Api/Ga/ApiGaInterface.php'); 

class ApiGaJson extends ApiGa implements ApiGaInterface {

	public function __construct() {
		parent::__construct();
	}

	public function run() {
		


		
		echo 'strategia json';
		
		switch ($this->getRequestMethod()) {
			case 'GET':
			case 'POST':
				break;
			case 'PUT':
				//update
				break;

			default:
				throw new Exception("Not valid request method!");
				break;
		}
	}

}