<?php 
require_once(CLASS_DIR . '/Api/Ga/ApiGaInterface.php'); 

class ApiGa {
	
	const REQUEST_METHODS = array('GET', 'POST', 'PUT');
	
	const RESPONSE_TYPES = array('json', 'xml', 'array');	
	
	const METHODS = array(
		array('name' => 'getProducts', 'params' => ['status', 'id']),
		array('name' => 'getManufacturers', 'params' => []),
		array('name' => 'getOrders', 'params' => ['status'])		
		);

	const REQUEST_METHOD_INVALID = 1;
	const RESPONSE_TYPE_INVALID = 2;
	const RESPONSE_TYPE_UNDEFINED = 3;
	const METHOD_NAME_INVALID = 4;
	const METHOD_NAME_UNDEFINED = 5;
	
	private static $messages = array(
		1 => 'Request method` not permitted!',
		2 => '`response` type is not valid!',
		3 => '`response` type is not defined!',
		4 => '`method` name is not valid!',
		5 => '`method` name is not defined!',
	);

	private $strategy;
	private $requestMethod;	
	private $responseType;
	private $method;
	private $requestErrors = [];
	
	public function getMethods() {
		$methods = [];
		
		foreach (self::METHODS as $method) {
			$methods[] = $method['name'];
		}
		
		return $methods;
	}

	public function __construct() {
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->responseType = isset($_REQUEST['response']) ? $_REQUEST['response'] : null;
		$this->method = isset($_REQUEST['method']) ? $_REQUEST['method'] : null;
	}
	
	public function init() {
				
		if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'show_methods') {
			echo ApiGa::getHtmlMethods();
			die;
		}
		
		$this->checkRequest();		
	}
	
	protected function checkRequest() {
		$requestMethod = $this->getRequestMethod();				
		$responseType = $this->getResponseType();
		$method = $this->getMethod();

		if (!in_array($requestMethod, self::REQUEST_METHODS)) {		
			$this->requestErrors[] = self::REQUEST_METHOD_INVALID;			
		}
		
		if ($responseType) {
			if (!in_array($responseType, self::RESPONSE_TYPES)) {
				$this->requestErrors[] = self::RESPONSE_TYPE_INVALID;
			}
			
		} else {
			$this->requestErrors[] = self::RESPONSE_TYPE_UNDEFINED;
		}
			
		if ($method) {
			if (!in_array($method, $this->getMethods())) {
				$this->requestErrors[] = self::METHOD_NAME_INVALID;
			}			
		} else {
			$this->requestErrors[] = self::METHOD_NAME_UNDEFINED;
		}
	}
	
	public function getRequestMethod() {
		return $this->requestMethod;
	}
	
	public function getResponseType() {
		return $this->responseType;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function setStrategy(ApiGaInterface $obj) {
		$this->strategy = $obj;
	}
	
	public function getStrategy() {
		return $this->strategy;
	}
	

	public static function getMessageForCode($code) {
		return self::$messages[$code];
	}
	
	static function getHtmlMethods() {
		$html = '<link href="' . TPL_URL .'/css/bootstrap.min.css" rel="stylesheet">';
		$html .= '<div class="container">';
		$html .= '<h2>Api Ga</h2>';
		$html .= '<table class="table table-bordered">';
		$html .= '<tr><th>Method name</th><th>Available parameters</th></tr>';
		
		foreach (self::METHODS as $method) {
			$html .= '<tr><td>' . $method['name'] . '</td>';
			
			if ($method['params']) {
				$html .= '<td>';
				$html .= implode(',', $method['params']);
				$html .= '</td>';
			}
			
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		$html .= '</div>';
		
		return $html;
	}
	
}