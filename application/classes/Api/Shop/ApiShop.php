<?php 
require_once(CLASS_DIR . '/Api/Shop/ApiShopInterface.php'); 

class ApiShop {		
	const REQUEST_METHODS = array('GET', 'POST', 'PUT');
	const ALLOWED_IPS = array(
		'51.255.122.9',		//central-ga
		'51.255.122.14',	//central-vf
		'193.19.165.96',	//Marek
//		'193.19.165.96',	//Krzysiek
		'83.144.75.170',	//Biuro
//		'127.0.0.1',	//Biuro
	);
	
	const RESPONSE_TYPES = array('json', 'xml', 'array');	
	const RESPONSE_STATUS_ERROR = 0;
	const RESPONSE_STATUS_SUCCESS = 1;
	
	const METHODS = array(
		array('name' => 'getManufacturers', 
			'params' => ['id','status_id','name','popular'], 
			'fields' => ['id','name','name_url','status_id','popular']),
		array('name' => 'getCategories', 
			'params' => ['id','parent_id', 'locale', 'name', 'slug'],
			'fields' => ['locale', 'parent_id', 'status_id', 'name', 'slug','parent_url', 'url']),
		array('name' => 'getProducts', 
			'params' => ['status_id', 'id', 'locale', 'type', 'category_id', 'producer_id', 'date_add', 'date_mod'],
			'fields' => []),		
		array('name' => 'getOrders', 
			'params' => ['status_id', 'time_add'],
			'fields' => []),
		array('name' => 'setOrder', 
			'params' => ['id', 'status_id', 'date_complete', 'tracking'],
			'fields' => [],	
			'desc' => 'Required order array with params'),	
		array('name' => 'setProducts', 
			'params' => ['id', 'variation_id', 'qty', 'price'],
			'fields' => [],		
			'desc' => 'Required products array and product array within it'),
		);
			
	const REQUEST_METHOD_INVALID = 1;
	const RESPONSE_TYPE_INVALID = 2;
	const RESPONSE_TYPE_UNDEFINED = 3;
	const METHOD_NAME_INVALID = 4;
	const METHOD_NAME_UNDEFINED = 5;
	const PARAMETER_INVALID = 6;
	const PARAMETER_UNDEFINED = 7;
	const FIELD_INVALID = 8;
	const API_KEY_INVALID = 9;
	const API_KEY_UNDEFINED = 10;
	const IP_NOT_ALLOWED = 11;
	
	private static $errorMessages = array(
		1 => 'Request method` not permitted!',
		2 => '`response` type is not valid!',
		3 => '`response` type is not defined!',
		4 => '`method` name is not valid!',
		5 => '`method` name is not defined!',
		6 => '`parameter` is invalid!',
		7 => '`parameter` is not defined!',
		8 => '`fields` is invalid!',
		9 => '`api_key` is invalid!',
		10 => '`api_key` is not defined!',
		11 => '`ip` not allowed!',
	);

	private $strategy;
	private $requestMethod;	
	private $responseType;
	private $apiKey;
	private $method;
	private $params;
	private $fields;
	private $requestErrors = [];
	protected $response;
	
	public function getApiKey() {
		return $this->apiKey;
	}
	
	public function getMethods() {
		$methods = [];
		
		foreach (self::METHODS as $method) {
			$methods[] = $method['name'];
		}
		
		return $methods;
	}
    
    public function getParams() {
        return $this->params;
    }
	
    public function getFields() {
        return $this->fields;
    }
    
    public function getRequestErrors() {
        return $this->requestErrors;
    }
    
    public function getParamsForMethod($method) {

        foreach (self::METHODS as $row) {

            if ($row['name'] == $method) {		
                return $row['params'];
            }
        }
        
        return false;
    }

	public function __construct() {
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->responseType = isset($_REQUEST['response']) ? $_REQUEST['response'] : null;
		$this->method = isset($_REQUEST['method']) ? $_REQUEST['method'] : null;
		$this->apiKey = isset($_REQUEST['api_key']) ? $_REQUEST['api_key'] : null;
        
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

		$this->params = isset($_REQUEST['params']) ? $_REQUEST['params'] : [];
		$this->fields = isset($_REQUEST['fields']) ? array_keys($_REQUEST['fields']) : [];
	}
	
	public function init() {

        if (isset($_GET['doc']) && in_array($_SERVER['REMOTE_ADDR'], self::ALLOWED_IPS)) {
            echo $this->showDocumentation();
            die;
        }
		
        $this->checkRequest();
     
		$resposne = [];        
        $resposne['data'] = [];
        
        if ($this->requestErrors) {
			$resposne['status'] = 0;
            foreach ($this->requestErrors as $code) {
                $entity = array(
                    'code' => $code,
                    'msg' => $this->getMessageForCode($code)
                );
                
                $resposne['errors'][] = $entity;                
            }            
		} else {
			$resposne['status'] = ApiShop::RESPONSE_STATUS_ERROR;
		}

		$this->setResponse($resposne);
	}
	
    public function runMethod() {

        $method = ucfirst($this->getMethod());      

		$method = new $method($this);
        $method->execute();
    }

	protected function checkRequest() {        
        
		$requestMethod = $this->getRequestMethod();				
		$responseType = $this->getResponseType();
		$method = $this->getMethod();
		$params = $this->getParams();
		$apiKey = $this->getApiKey();

		if (!in_array($_SERVER['REMOTE_ADDR'], self::ALLOWED_IPS)) {
			$this->requestErrors[] = self::IP_NOT_ALLOWED;	
		}
				
//		if ($apiKey) {
//			if ($apiKey != API_KEY) {
//				$this->requestErrors[] = self::API_KEY_INVALID;	
//			}
//			
//		} else {
//			$this->requestErrors[] = self::API_KEY_UNDEFINED;	
//		}
		
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
        
        if ($method && $params) {
            $methodParams = $this->getParamsForMethod($method);

            if ($methodParams) {
                foreach ($params as $paramName => $value) {
                    if (!in_array($paramName, $methodParams)) {
                        $this->requestErrors[] = self::PARAMETER_INVALID;
                    }
                }
            } else {
                $this->requestErrors[] = self::PARAMETER_INVALID;
            }
        }
        
		if ($method == 'setOrder' && !isset($params['order'])) {
			$this->requestErrors[] = self::PARAMETER_UNDEFINED;			
		}
		
		if ($method == 'setProducts' && !isset($params['products'])) {
			$this->requestErrors[] = self::PARAMETER_UNDEFINED;			
		}
		
        if ($this->requestErrors) {            
            return false;
        }
        
        return true;
	}
	
    public function setResponse($response) {
		$this->response = $response;
	}	
	
	public function getResponse($responseType = null) {
		
		if (!$responseType) {
			$responseType = $this->getResponseType();	
		}			    
                
        switch ($responseType) {
            case 'json':
                $response = json_encode($this->response);
                break;
            case 'array':
                $response = $this->response;
                break;
            case 'xml':
                break;
            default:
                $response = $this->response;
                break;
        }
		
		return $response;
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
	
	public function setStrategy(ApiShopInterface $obj) {
		$this->strategy = $obj;
	}
	
	public function getStrategy() {
		return $this->strategy;
	}
	

	public static function getMessageForCode($code) {
		return self::$errorMessages[$code];
	}
	
    public function showDocumentation() {
        Cms::$loader->addPath(CMS_DIR . '/application/classes/Api/Shop/Doc');
   
            $data = array(
                'methods' => self::METHODS,
                'errors' => self::$errorMessages
            );
            
        echo Cms::$twig->render('show.twig', $data);
    }
	
	public function log($response = null) {
		$response = $this->getResponse('json');
		$apiShopLog = new ApiShopLog();

		$params = $this->getParams() ? json_encode($this->getParams()) : '';
		$fields = $this->getFields() ? json_encode($this->getFields()) : '';

		$data = array(
			'url' => $_SERVER['REQUEST_URI'],
			'method' => $this->getMethod(),
			'params' => $params,
			'fields' => $fields,
			'result' => $response,
			'ip' => $_SERVER['REMOTE_ADDR']
		);
		
		$apiShopLog->insert($apiShopLog->table, $data);
	}
	
}
