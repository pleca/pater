<?php

class arrayConnect {

	private $_sURL			= NULL;
	private $_sData			= NULL;
	private $_sMsgType		= NULL;
	private $_logging		= TRUE;
	private $_objLogger		= NULL;
	private $_gaTimeStart	= NULL;
	private $_gaTimeEnd		= NULL;
	private $_gaTimeTotal	= NULL;
	private $_className		= NULL;
	private static $_countObjects = 0;
	private $_objectNumber	= NULL;
	private $_aErrors		= array();
	private $_aInfos		= array();
	private $_httpRequest	= NULL;

	public function __construct() {

		$this->_objLogger = new cmsLogger('csvapi', cmsLogger::DEBUG);

		$this->_gaTimeStart = microtime();
		$this->_gaTimeEnd = microtime();
		$this->_gaTimeTotal = NULL;

		$this->_className = get_class($this);

		$this->_objectNumber = ++self::$_countObjects;
		$this->_sMsgType = "No message type";
	}

	public function __destruct() {

		if (!$this->_logging)
			return;

		$memoryPeak = round(memory_get_peak_usage() / 1024 / 1024, 2) . "MB";

		$this->_gaTimeEnd = microtime();
		$aStart = explode(" ", $this->_gaTimeStart);
		$start = $aStart[1] + $aStart[0];
		$aEnd = explode(" ", $this->_gaTimeEnd);
		$end = $aEnd[1] + $aEnd[0];
		$this->_gaTimeTotal = $end - $start;

		$this->_objLogger->LogDebug("*** {$this->_className} {$this->_objectNumber} *** Entire system memory usage when object lived: " . $memoryPeak);
		$this->_objLogger->LogDebug("*** {$this->_className} {$this->_objectNumber} *** object lifetime: " . round($this->_gaTimeTotal, 2) . "s");

		if ($this->isInfo())
			foreach ($this->getInfos() as $fe_info) {

				if (is_string($fe_info))
					$this->_objLogger->LogDebug($fe_info);
			}

		if ($this->isError())
			foreach ($this->getErrors() as $fe_error) {

				if (is_string($fe_error))
					$this->_objLogger->LogError($fe_error);
			}
	}

	public function exec() {

		try {

			if (empty($this->_sURL))
				throw new Exception("_sURL is not set.");

			if (empty($this->_sData))
				throw new Exception("_sData is not set.");

			if (empty($this->_sMsgType))
				throw new Exception("_sMsgType is not set.");

			$aPOST['ARRAY'] = $this->_sData;
			$aPOST['TYPE'] = $this->_sMsgType;

			$this->_httpRequest = curl_init();

			curl_setopt($this->_httpRequest, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->_httpRequest, CURLOPT_POST, 1);

			curl_setopt($this->_httpRequest, CURLOPT_URL, $this->_sURL);
			curl_setopt($this->_httpRequest, CURLOPT_POSTFIELDS, $aPOST);

			$response = curl_exec($this->_httpRequest);

			if ($response === false) {

				$curlErr = curl_error($this->_httpRequest);
				throw new Exception("There was an error trying to establish connection.\n" . $curlErr);
			} else {

				//$this->addInfo($response);
				@curl_close($this->_httpRequest);
				return $response;
			}

			@curl_close($this->_httpRequest);
		} catch (Exception $ex) {

			$this->addError($this->parseException($ex));

			if (!empty($this->_httpRequest))
				@curl_close($this->_httpRequest);

			return false;
		}
	}

	public function setData($s, $type = NULL) {

		try {

			if (empty($s))
				throw new Exception("Data is empty.");

			if (!is_string($s))
				throw new Exception("Data isn't string type.");

			$this->_sData = $s;

			if($type && is_string($type))
				$this->_sMsgType = $type;


			return true;
			
		} catch (Exception $ex) {

			$this->addError($this->parseException($ex));
			return false;
		}
	}

	public function setURL($s) {

		try {

			if (empty($s))
				throw new Exception("URL is empty.");

			if (!is_string($s))
				throw new Exception("URL isn't string type.");

			$this->_sURL = $s;

			return true;
		} catch (Exception $ex) {

			$this->addError($this->parseException($ex));
			return false;
		}
	}

	private function parseException(Exception $ex) {

		$ret = $ex->getMessage();
		$ret .= " ";
		$ret .= $ex->getFile();
		$ret .= ":";
		$ret .= $ex->getLine();

		return $ret;
	}

	public function addInfo($i) {

		if (!empty($i)) {
			if (!is_array($i)) {
				array_push($this->_aInfos, $i);
				return true;
			} else {

				$this->_aInfos = array_merge($this->_aInfos, $i);
				return true;
			}
		}

		return false;
	}

	public function addError($e) {

		if (!empty($e)) {
			if (!is_array($e)) {
				array_push($this->_aErrors, $e);
				return true;
			} else {

				$this->_aErrors = array_merge($this->_aErrors, $e);
				return true;
			}
		}

		return false;
	}

	public function getErrors() {

		if (count($this->_aErrors) > 0) {
			return $this->_aErrors;
		} else {
			return false;
		}
	}

	public function getInfos() {

		if (count($this->_aInfos) > 0) {
			return $this->_aInfos;
		} else {
			return false;
		}
	}

	public function isError() {
		if (count($this->_aErrors) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function isInfo() {

		if (count($this->_aInfos) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function setLogging(bool $b) {

		if (!is_bool($b))
			return false;

		$this->_logging = $b;
	}

}

?>
