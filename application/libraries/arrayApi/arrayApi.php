<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

// CLEAN_OUTPUT ustawiony na prawdę wyczyści bufor przed wysłaniem gotowego CSV
// WAŻNE: Błędy PHP, wynik echo,var_dump,var_export,print_r mogą nie wyświetlić się
// jeżeli wartość ta ustawiona jest na prawdę.

define("CLEAN_OUTPUT",TRUE); 

function arrayAutoload($className) {

	$filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	
	$includePaths = array();
	
	//$includePaths = explode(PATH_SEPARATOR, get_include_path());   
	
	$includePaths[0] = dirname(__FILE__);
	
	foreach($includePaths as $includePath) {
		if(@file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)) {
			require_once $filePath;
			return;
		}
	}
}


$registeredAutoLoadFunctions = spl_autoload_functions();
if (!isset($registeredAutoLoadFunctions['spl_autoload'])) {
	spl_autoload_register();
}
if(!isset($registeredAutoLoadFunctions['arrayAutoload'])) {
	spl_autoload_register('arrayAutoload');
}

class arrayApi
{
	
	protected $_accName 				= NULL;
	protected $_accID					= NULL;
	protected $_accKey					= NULL;
	
	protected $_aReqVars				= array();
	
	protected $_aErrors 				= array();
	protected $_aInfos					= array();
	
	protected $_sData					= NULL;
	protected $_aData					= NULL;
	protected $_encData					= NULL;
	
	protected $_encType					= NULL;
	
	protected $_sPreData				= NULL;
	protected $_aPreData				= NULL;
	
	protected $_logging					= TRUE;
	
	private $_gaTimeStart				= NULL;
	private $_gaTimeEnd					= NULL;
	private $_gaTimeTotal				= NULL;
	
	protected $_className				= NULL;
	private static $_countObjects		= 0;
	private $_objectNumber				= NULL;
	
	private $_validationToken			= "2a970dcb92fc3ea63012e1197fc776b689f93997";
	
	public function __construct(array $aParams = array())
	{
		
		$this->setRequiredVars();
		
		if(isset($aParams['accName']))
			$this->setAccName($aParams['accName']);
		
		if(isset($aParams['accID']))
			$this->setAccID($aParams['accID']);
		
		if(isset($aParams['accKey']))
			$this->setAccKey($aParams['accKey']);
		
		if(isset($aParams['logging']))
			$this->setLogging($aParams['logging']);
		
		$this -> _gaTimeStart 	= microtime();
		$this -> _gaTimeEnd 	= microtime();
		$this -> _gaTimeTotal 	= NULL;
		
		$this-> _encType		= "PHP5";
		
		$this-> _className		= get_class($this);
		
		$this-> _objectNumber = ++self::$_countObjects;
		
	}
	
	public function __destruct()
	{
		
		if(!$this->_logging)
			return;
		
		$memoryPeak = round(memory_get_peak_usage() / 1024 / 1024,2) . "MB";
		
		$this -> _gaTimeEnd = microtime();
		$aStart = explode(" ", $this -> _gaTimeStart);
		$start = $aStart[1] + $aStart[0];
		$aEnd = explode(" ", $this -> _gaTimeEnd);
		$end = $aEnd[1] + $aEnd[0];
		$this -> _gaTimeTotal = $end - $start;
		
		Cms::$log->LogDebug("*** {$this->_className} {$this->_objectNumber} *** Entire system memory usage when object lived: " . $memoryPeak);
		Cms::$log->LogDebug("*** {$this->_className} {$this->_objectNumber} *** object lifetime: " . round($this -> _gaTimeTotal,2) . "s");
		
		if($this->isInfo())
		foreach($this->getInfos() as $fe_info) {
			
			if(is_string($fe_info))
				Cms::$log->LogDebug($fe_info);
			
		}
		
		if($this->isError())
		foreach($this->getErrors() as $fe_error) {

			if(is_string($fe_error))
				Cms::$log->LogError($fe_error);
				
		}
	}
	
	private function setRequiredVars()
	{
		
		$rv = &$this->_aReqVars;
		
		$rv['accName'] 			= FALSE;
		$rv['accID']			= FALSE;
		$rv['accKey']			= FALSE;
		
	}
	
	protected function parseException(Exception $ex)
	{
		
		$ret = $ex->getMessage();
		$ret .= " ";
		$ret .= $ex->getFile();
		$ret .= ":";
		$ret .= $ex->getLine();
		
		return $ret;
		
	}
	
	public function isSetRequiredVars()
	{
		
		foreach($this->_aReqVars as $fe_key => $fe_var) {
			
			try {
				
				if(!$fe_var) {
				
					throw new Exception("Variable {$fe_key} isn't set");
					
				}
				
				return true;
				
			} catch(Exception $ex) {
				
				$this->addError($this->parseException($ex));
				return false;
			}
			
		}
		
		return false;
		
	}
	
	public function addInfo($i)
	{
	
		if(!empty($i)) {
			if(!is_array($i)) {
				array_push($this -> _aInfos,$i);
				return true;
			} else {
	
				$this -> _aInfos = array_merge($this -> _aInfos,$i);
				return true;
			}
		}
	
		return false;
	}
	
	public function addError($e)
	{
		
		if(!empty($e)) {
			if(!is_array($e)) {
				array_push($this -> _aErrors,$e);
				return true;
			} else {

				$this -> _aErrors = array_merge($this -> _aErrors,$e);
				return true;
			}
		}
	
		return false;
	}
	
	public function getErrors()
	{
	
		if(count($this->_aErrors) > 0) {
			return $this->_aErrors;
		} else {
			return false;
		}
	}
	
	public function getInfos()
	{
	
		if(count($this->_aInfos) > 0) {
			return $this->_aInfos;
		} else {
			return false;
		}
	}
	
	public function isError()
	{
	
		if(count($this->_aErrors) > 0) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function isInfo()
	{
	
		if(count($this->_aInfos) > 0) {
			return true;
		} else {
			return false;
		}
	
	}
	
	public function setAccName($s)
	{
		
		if(!empty($s) && strlen($s) <= 50) {
			
			$this->_accName = $s;
			$this->_aReqVars['accName'] = TRUE;
			return true;
		}
		
		return false;
		
	}
	
	public function getAccName()
	{
		
		return $this->_accName;
		
	}
	
	public function setAccID($s)
	{
		
		if(!empty($s) && strlen($s) <= 50) {
			
			$this->_accID = $s;
			$this->_aReqVars['accID'] = TRUE;
			return true;
		}
		
		return false;
		
	}
	
	public function getAccID()
	{
		
		return $this->_accID;
		
	}
	
	public function setAccKey($s)
	{
		
		if(!empty($s) && strlen($s) == 40) {
				
			$this->_accKey = $s;
			$this->_aReqVars['accKey'] = TRUE;
			return true;
		}
		
		return false;
		
	}
	
	public function setLogging(bool $b)
	{
		
		if(!is_bool($b))
			return false;
		
		$this->_logging = $b;
		
	}
	
	public function encryptPHP5($s)
	{
	    
	    try {
			
			if(empty($this->_accKey))
				throw new Exception("AccKey isn't set! Can't encrypt data.");
		    
						$variab = base64_encode(gzcompress(mcrypt_encrypt(
							   MCRYPT_RIJNDAEL_128, md5($this->_accKey), $s, MCRYPT_MODE_CBC, substr(md5(sha1($this->_accKey)),0,16)),1));

			return $variab;
			
	    } catch (Exception $ex) {
		    
		    $this->addError($this->parseException($ex));
		    return false;
	    }
	}
	
	public function decryptPHP5($s)
	{
	    try {
			
			if(empty($this->_accKey))
				throw new Exception("AccKey isn't set! Can't encrypt data.");
		    
			$variab = rtrim(mcrypt_decrypt(
							   MCRYPT_RIJNDAEL_128, md5($this->_accKey), gzuncompress(base64_decode($s)), MCRYPT_MODE_CBC, substr(md5(sha1($this->_accKey)),0,16)), "\0");
		
			return $variab;
			
	     } catch (Exception $ex) {
		    
		    $this->addError($this->parseException($ex));
		    return false;
	    }
	}
	
	public function decryptData()
	{
		
		try {
			if(empty($this->_encData))
				throw new Exception("_encData isn't set. Did you provide preData?");
			
			if(empty($this->_accKey)) 
				throw new Exception("Account Key isn't set.");
			
			$variab = $this->decryptPHP5($this->_encData);
				
			if($this->setData($variab))
				return true;
			else
				throw new Exception("Can't make DOMDocument with provided string - " . $variab);

		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
		
		return false;
	}
	
	public function encryptData()
	{
		
		try {
			
			if(empty($this->_sData))
				throw new Exception("_sData isn't set.");
			
			if(empty($this->_accKey))
				throw new Exception("Account Key isn't set.");
			
			$this->_encData = $this->encryptPHP5($this->_sData);
			
			return true;
		
		} catch(Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
		
		return false;
	}
	
	public function setData($sData)
	{
		
		try {
			
			if(!is_string($sData))
				throw new Exception("Variable isn't String type.");

			if(!$this->_aData = unserialize($sData))
				throw new Exception("Couldn't unserialize provided string.");
			
			return true;
			
		} catch (Excepiton $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
		
		return false;
		
	}
	
	public function setDataAsArray($aData) {
		
		try {
			
			if(!is_array($aData))
				throw new Exception("Variable isn't array type.");

			if(!$this->_sData = serialize($aData))
				throw new Exception("Couldn't serialize provided array.");
			
			return true;
			
		} catch (Excepiton $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
		
		return false;
		
	}
	
	public function getDataAsArray() {
		
		try {
			
			if(!isset($this->_aData))
				throw new Exception("aData isn't set.");
			
			return $this->_aData;
			
		} catch (Excepiton $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
		
		return false;
		
	}
	
	public function setPreData($sData)
	{
		
		try {
			
			if(!is_string($sData))
				throw new Exception("Variable isn't string type");

			if(!$this->_aPreData = unserialize($sData)) {
				throw new Exception("Can't unserialize provided string.");
			}

			$this->_sPreData = $sData;
			
			$this->setAccID($this->_aPreData['AccountID']);
			$this->_encData = $this->_aPreData['EncryptedBody'];
			
			return true;
		
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
		}
		
		return false;
		
	}
	
	public function getFinalEncryptedDataString()
	{
		
		try {
			
			if(empty($this->_sData))
				throw new Exception("Can't create final data. _sData isn't set.");
			
			if(empty($this->_accID))
				throw new Exception("Can't create final data. AccountID isn't set.");
			
			if(empty($this->_accKey))
				throw new Exception("Can't create final data. Account key isn't set.");
			
			if(empty($this->_encData))
				$this->encryptData();
			
			$aData = array();
			
			$aData['AccountID']			= $this->_accID;
			$aData['EncryptedBody']		= $this->_encData;
			$aData['EncryptionType']	= $this->_encType;
			
			return serialize($aData);
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
		
		return false;
	}
	
	public function getValidationToken()
	{
	    
	    return $this->_validationToken;
	    
	}
	
	public function getQuery($messageType,$aExtraData = NULL)
	{
	    
	    try {
		
			if (!$this->isSetRequiredVars())
				throw new Exception("Required variables isn't set.");

			if(empty($messageType))
				throw new Exception("Variable messageType is empty");

			if(!empty($aExtraData) && !is_array($aExtraData))
				throw new Exception("Variable aExtraData should be an array");

			$aData			= array();

			$aData['MessageType']			= $messageType;
			
			if(isset($aExtraData))
				$aData['ExtraDataArray']	= serialize($aExtraData);
			else
				$aData['ExtraDataArray']	= '';
			
			$aData['AccountID']			= $this->getAccID();;
			$aData['AccountName']		= $this->getAccName();;
			$aData['SecurityCode']		= $this->encryptPHP5($this->getValidationToken());;

			return serialize($aData);
		
	    } catch (Exception $ex) {
		
			$this->addError($this->parseException($ex));
			return false;
		
	    }
	    
	}
	
	public function getAccountIDFromQuery($sData)
	{
	    
	    try {
			
			if(!$aData = unserialize($sData))
				throw new Exception("Can't make an array from provided string.");

			if(!isset($aData['SecurityCode']) || strlen($aData['SecurityCode']) == 0)
				throw new Exception("Can't find SecurityCode in provided string. Did you set AccKey?");
			
			if(!isset($aData['MessageType']) || strlen($aData['MessageType']) == 0)
				throw new Exception("Can't find MessageType in provided string.");

			return $aData['AccountID'];
	    
	    } catch (Exception $ex) {
		
			$this->addError($this->parseException($ex));
			return false;
		
	    }
	
		    
	}
	
	public function getMessageTypeFromQuery($sData)
	{
	    
	    try {
		
			$extraInfo = '';
			
			if(isset($this->_accName))
				$extraInfo .= " accName: {$this->_accName}";
			
			if(isset($this->_accName))
				$extraInfo .= " accID: {$this->_accID}";
			
			if(!$aData = unserialize($sData))
				throw new Exception("Can't make an array from provided string." . $sData);
			
			if(!isset($aData['SecurityCode']) || strlen($aData['SecurityCode']) == 0)
				throw new Exception("Can't find SecurityCode in provided string. Did you set AccKey?");
			
			if(!isset($aData['MessageType']) || strlen($aData['MessageType']) == 0)
				throw new Exception("Can't find MessageType in provided string.");
			
			$decryptedCode = $this->decryptPHP5($aData['SecurityCode']);
			
			if($decryptedCode != $this->getValidationToken())
				throw new Exception("SecurityCode is invalid. $extraInfo");
			
			return $aData['MessageType'];
		
	    
	    } catch (Exception $ex) {
		
			$this->addError($this->parseException($ex));
			return false;
		
	    }
	
		    
	}
	
	public function getExtraDataFromQuery($sData)
	{
	    
	    try {
		
			$extraInfo = '';
			
			if(isset($this->_accName))
				$extraInfo .= " accName: {$this->_accName}";
			
			if(isset($this->_accName))
				$extraInfo .= " accID: {$this->_accID}";
			

			if(!$aData = unserialize($sData))
				throw new Exception("Can't make an array from provided string." . $sData);
			
			if(!isset($aData['SecurityCode']) || strlen($aData['SecurityCode']) == 0)
				throw new Exception("Can't find SecurityCode in provided string. Did you set AccKey?");
			
			if(!isset($aData['MessageType']) || strlen($aData['MessageType']) == 0)
				throw new Exception("Can't find MessageType in provided string.");
			
			if(!isset($aData['ExtraDataArray']) || strlen($aData['ExtraDataArray']) == 0)
				throw new Exception("Can't find ExtraDataArray in provided string.");
			
			$decryptedCode = $this->decryptPHP5($aData['SecurityCode']);
			
			if($decryptedCode != $this->getValidationToken())
				throw new Exception("SecurityCode is invalid. $extraInfo");
			
			return unserialize($aData['ExtraDataArray']);
	    
	    } catch (Exception $ex) {
		
			$this->addError($this->parseException($ex));
			return false;
		
	    }
		    
	}
	
}