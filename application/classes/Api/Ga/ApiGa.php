<?php 
require_once(CLASS_DIR . '/Api/Ga/ApiGaInterface.php'); 
//require_once(CLASS_DIR . '/Api/Ga/Methods/GetManufacturers.php'); 



class ApiGa {
	
	const REQUEST_METHODS = array('GET', 'POST', 'PUT');
	
	const RESPONSE_TYPES = array('json', 'xml', 'array');	
	
	const METHODS = array(
		array('name' => 'getProducts', 'params' => ['status', 'id']),
		array('name' => 'getManufacturers', 'params' => []),
		array('name' => 'getCategories', 'params' => []),
		array('name' => 'getOrders', 'params' => ['status'])		
		);

	const REQUEST_METHOD_INVALID = 1;
	const RESPONSE_TYPE_INVALID = 2;
	const RESPONSE_TYPE_UNDEFINED = 3;
	const METHOD_NAME_INVALID = 4;
	const METHOD_NAME_UNDEFINED = 5;
	const PARAMETER_INVALID = 6;
	
	private static $errorMessages = array(
		1 => 'Request method` not permitted!',
		2 => '`response` type is not valid!',
		3 => '`response` type is not defined!',
		4 => '`method` name is not valid!',
		5 => '`method` name is not defined!',
		6 => '`parameter` is invalid!',
	);

	private $strategy;
	private $requestMethod;	
	private $responseType;
	private $method;
	private $params;
	private $requestErrors = [];
	protected $response;

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
    
    public function getRequestErrors() {
        return $this->requestErrors;
    }
    
    public function getParamsForMethod($method) {
        foreach (self::METHODS as $method) {
            if ($method['name'] == $method) {
                return $method['params'];
            }
        }
        
        return false;
    }

	public function __construct() {
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->responseType = isset($_REQUEST['response']) ? $_REQUEST['response'] : null;
		$this->method = isset($_REQUEST['method']) ? $_REQUEST['method'] : null;
        
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
	}
	
	public function init() {
				
        if (isset($_GET['doc'])) {
            echo $this->showDocumentation();
            die;
        }
		
        $this->checkRequest();
     
	}
	
    public function runMethod() {
        $method = ucfirst($this->getMethod());
        
        $method =  new $method;
        $method->execute();        
        
//        $responseType = $this->getResponseType();        
                
//$page1 = new Page(new PDFFactory());
//$page1->render(); // wyswietli "Dokument PDF"
//$page2 = new Page(new HTMLFactory());
//$page2->render(); // wyswietli "Dokument HTML"        
        

//        switch ($responseType) {
//            case 'json':
//                $method = new Method(new Json());   //wynik metody w json
//                break;
//            case 'array':
//                $response = $result;
//                break;
//            case 'xml':
//                $method = new Method(new XML());
//                break;
//            default:
//                $response = $result;
//                break;
//        } 
    }
    
//    protected function showResponse() {
//        $result = [];
//        
//        $result['data'] = [];
//        
//        if ($this->requestErrors) {
//            foreach ($this->requestErrors as $code) {
//                $entity = array(
//                    'code' => $code,
//                    'msg' => $this->getMessageForCode($code)
//                );
//                
//                $result['errors'][] = $entity;
//            }            
//        }
//        
//        $responseType = $this->getResponseType();
//                
//        switch ($responseType) {
//            case 'json':
//                $response = json_encode($result);
//                echo $response;
//                break;
//            case 'array':
//                $response = $result;
//                break;
//            case 'xml':
//                break;
//            default:
//                $response = $result;
//                break;
//        } 
//    }
    
	protected function checkRequest() {        
        
		$requestMethod = $this->getRequestMethod();				
		$responseType = $this->getResponseType();
		$method = $this->getMethod();
		$params = $this->getParams();

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
                foreach ($params as $param) {
                    if (!in_array($param, $methodParams)) {
                        $this->requestErrors[] = self::PARAMETER_INVALID;
                    }
                }
            } else {
                $this->requestErrors[] = self::PARAMETER_INVALID;
            }
        }
        
        if ($this->requestErrors) {            
            return false;
        }
        
        return true;
	}
	
    public function getResponse() {
        $result = [];
        
        $result['data'] = [];
        
        if ($this->requestErrors) {
            foreach ($this->requestErrors as $code) {
                $entity = array(
                    'code' => $code,
                    'msg' => $this->getMessageForCode($code)
                );
                
                $result['errors'][] = $entity;
            }            
        }
        
        $responseType = $this->getResponseType();
                
        switch ($responseType) {
            case 'json':
                $response = json_encode($result);
                break;
            case 'array':
                $response = $result;
                break;
            case 'xml':
                break;
            default:
                $response = $result;
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
	
	public function setStrategy(ApiGaInterface $obj) {
		$this->strategy = $obj;
	}
	
	public function getStrategy() {
		return $this->strategy;
	}
	

	public static function getMessageForCode($code) {
		return self::$errorMessages[$code];
	}
	
    public function showDocumentation() {
        Cms::$loader->addPath(CMS_DIR . '/application/classes/Api/Ga/Doc');
   
            $data = array(
                'methods' => self::METHODS,
                'errors' => self::$errorMessages
            );
            
        echo Cms::$twig->render('show.twig', $data);
    }
	
}
