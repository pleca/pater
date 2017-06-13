<?php

/*
 * Biblioteka pobiera TYLKO ostatnia aktualizacje.
 * W przypadku kiedy na FTP beda lezec dwa pliki
 * update1.zip i update2.zip
 * Zostanie pobrany tylko update2.zip
 * a update1.zip zostanie calkowicie zignorowany
 * rowniez w przyszlych aktualizacjach
 * 
 * Bierz to pod uwage jak wrzucisz jedna aktualizacje
 * po czym stwierdzisz, ze chcesz dodac jeszcze dwa pliki
 * i umiescisz tylko te dwa pliki w drugiej aktualizacji
 * to TYLKO one zostana zaktualizowane a wczesniejszy update zignorowany
 * 
 * Kazda nowa aktualizacja ma miec komplet plikow ktore chcesz zaktualizowac
 * nie mozna rozbijac aktualizacji na dwa pliki .zip
 * 
 * Jest to zabezpieczenie przed tym, zeby w nastepnych systemach gateway
 * jak rowniez gdyby ktos usunal historie pobran (updates.db)
 * aktualizator nie pobieral z FTP starych aktualizacji
 * 
 */

/*
 * Plik SQL ktory dolaczysz do paczki absolutnie MUSI spelniac te wymagania:
 * 
 * 1. Kazda instrukcja musi sie konczyc srednikiem (;) i znakiem nowej linii
 * nie moze wystapic sytuacja w ktorej instrukcja konczy sie srednikiem po ktorym
 * jest np spacja i dopiero znacznik nowej linii bo caly SQL sie rozsypie
 * 
 * 2. W pliku nie moze byc nic innego niz tylko zapytania SQL. Zadnych komentarzy
 * 
 */

/*
 * Niemal kazde niepowodzenie powinno zostawic cos w logach.
 * Dlatego jak cos pojdzie nie tak raczej na pewno w logach
 * bedzie informacja co dokladnie zawinilo
 * Nawet jesli przy aktualizacji jakis plik nie mogl zostac skopiowany/nadpisany
 * inforamcja o tym powinna byc w logack
 * 
 * Sprawdzaj logi
 * 
 * Plik z logami do aktualizacji ma w nazwie _UPDATE_.txt
 * 
 * Przynajmniej przez pierwszych kilka aktualizacji warto zawsze sprawdzac logi
 * bo nie wszystko moze dzialac tak jak powinno
 * 
 */

class classCmsUpdate {

    protected $_aErrors				= array();
	protected $_aInfos				= array();
	protected $_logging				= TRUE;
	protected $_objLogger			= NULL;
	protected $_timeStart			= NULL;
	protected $_timeEnd				= NULL;
	protected $_timeTotal			= NULL;
	protected $_className			= NULL;
	protected static $_countObjects	= 0;
	protected $_objectNumber		= NULL;
	
    const FTP_CONNECTION_TYPE = 'ssl';
	protected $_ftpUser				= 'ftp002';
	protected $_ftpPass				= 'VxmlES275TfN';
	protected $_ftpHost				= '51.255.122.10';
	protected $_ftpDir				= 'private/updates/4me.cms';
	
	protected $_objFTP				= '';

	protected $_updateFileName		= NULL;
	protected $_updateDBFileName	= NULL;
	
	protected $_localDir			= NULL;
	protected $_localDirZip			= NULL;
	protected $_localDBFileName		= NULL;
	
	protected $_aSkipFiles			= array();
	
	protected $_UPDATE_DIR			= NULL;
	
	protected $_sqlConn				= NULL;
	
	protected $_aTimes				= array();
	
	public function __construct() {

		require_once(dirname(__FILE__) . "/myLogger.php");
		require_once(dirname(__FILE__) . "/classCmsFile.php");
		
		$this->_objLogger		= new myLogger(DR . "/application/logs",'update', myLogger::DEBUG);

		$this->_timeStart		= microtime();
		$this->_timeEnd			= microtime();
		$this->_timeTotal		= NULL;
		
		$this->_className		= get_class($this);

		$this->_objectNumber	= ++self::$_countObjects;

		$this->_localDir		= dirname(__FILE__) . '/tmp_update';
		$this->_localDirZip		= dirname(__FILE__) . '/tmp_update_zip';
		$this->_localDBFileName = dirname(__FILE__) . '/updates.db';
		$this->removeList = $this->_localDir . '/remove_list.txt';

		$this->_initDir();
        
        $this->cleanLogs();
		
		/*
		* 
		* KATALOG DO KTOREGO BEDA WYPAKOWANE AKTUALIZACJE
		* 
		*/
		
		$this->_UPDATE_DIR = DR;
		
	}
	
	public function __destruct() {
		
		if(isset($this->_sqlConn))
			$this->_sqlConn->close();
		
		if (!$this->_logging)
			return;

		$memoryPeak = round(memory_get_peak_usage() / 1024 / 1024, 2) . "MB";

		$this->_timeEnd = microtime();
		$aStart			= explode(" ", $this->_timeStart);
		$start			= $aStart[1] + $aStart[0];
		$aEnd			= explode(" ", $this->_timeEnd);
		$end			= $aEnd[1] + $aEnd[0];
		$this->_timeTotal = $end - $start;

		$this->_objLogger->LogDebug("*** classCmsUpdate {$this->_objectNumber} child {$this->_className} *** Entire system memory usage when object lived: " . $memoryPeak);
		$this->_objLogger->LogDebug("*** classCmsUpdate {$this->_objectNumber} child {$this->_className} *** object lifetime: " . round($this->_timeTotal, 2) . "s");

		if ($this->isInfo())
			foreach ($this->getInfos() as $fe_info) {

				if (is_string($fe_info))
					$this->_objLogger->LogInfo($fe_info);
			}

		if ($this->isError())
			foreach ($this->getErrors() as $fe_error) {

				if (is_string($fe_error))
					$this->_objLogger->LogError($fe_error);
			}
	}
	
	
	protected function _initDir() {
		
		if (!file_exists($this->_localDir)) {
			mkdir($this->_localDir, 0777, true);
		}
		
		if (!file_exists($this->_localDirZip)) {
			mkdir($this->_localDirZip, 0777, true);
		}
	}
	
	protected function _initDB() {
		
		try {
			
			$config_location = DR . "/application/config/config.php";
			
			if(!file_exists($config_location))
				throw new Exception("Can't initiate database. config.php file doesn't exists.");
			
			include_once($config_location);
			
			if(!defined('DB_SERVER') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_NAME'))
				throw new Exception("Looks like config.php file isn't right. Can't find constants.");
			
			$this->_sqlConn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
				
			if (mysqli_connect_errno()) {

				$this->_sqlConn = NULL;
				throw new Exception("Can't connect to database. Error No. " . mysqli_connect_error() );
			}
			
			$this->_sqlConn->set_charset("utf8");
			
			return true;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
		
	}
	
	protected function parseException(Exception $ex) {

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
	
	public function setLogging($b) {
		try {
		if (!is_bool($b))
			throw new Exception("variable \$b isn't bool type.");

			$this->_logging = $b;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
	}
	
	public function skipFile($filename) {
		
		if(!empty($filename)) {
			
			$this->_aSkipFiles[] = $filename;
			
		}
		
	}
	
	private function _isFTPDirectoryExists($ftp, $dir) {

		$origin = ftp_pwd($ftp);


		if (@ftp_chdir($ftp, $dir)) {
			// If the directory exists, set back to origin
			ftp_chdir($ftp, $origin);
			return true;
		}

		return false;
	}
	
	private function _setTime($id) {
		
		$this->_aTimes[$id] = microtime();
		
	}
	
	private function _getTime($id) {
		
		try {
			
			if(!isset($this->_aTimes[$id]))
				throw new Exception ("Can't measure time! ID doesn't exists.");
			
			$timeEnd			= microtime();

			$aTimeStart			= explode(" ", $this->_aTimes[$id]);
			$formatStart		= $aTimeStart[1] + $aTimeStart[0];
			$aEnd				= explode(" ", $timeEnd);
			$formatEnd			= $aEnd[1] + $aEnd[0];
			$timeTotal			= round($formatEnd - $formatStart,2);
			
			return $timeTotal;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return "";
			
		}
	}
	
	private function _dbGetUpdates() {
		
		if(!file_exists($this->_localDBFileName))
			file_put_contents ($this->_localDBFileName, gzcompress(serialize(array()),1));
		
		$aUpdates = unserialize(gzuncompress(file_get_contents($this->_localDBFileName)));
		return $aUpdates;
	}
	
	private function _dbSetUpdate($filename) {
		
		$aUpdates = $this->_dbGetUpdates();
		$aUpdates[] = $filename;
		
		file_put_contents($this->_localDBFileName, gzcompress(serialize($aUpdates),1));
		
	}
	
	public function setUpdateDBFilename($filename) {
		
		if(is_string($filename))
			$this->_updateDBFileName = $filename;
		
	}
	
	public function checkUpdate() {
		
		$conn_id = $this->ftpConnect();
		$this->_setTime(__METHOD__);
		
		try {
		
			$aUpdatesFiles = $this->_dbGetUpdates();
			
			if(!is_array($aUpdatesFiles)) {
				
				$this->addInfo ("No entries in database.");
				$aUpdates = array();
			}
			
			$conn_result	= @ftp_login($conn_id, $this->_ftpUser, $this->_ftpPass);
			
			if(!$conn_result)
				throw new Exception("Can't connect to remote server {$this->_ftpHost}.");
			
			if(!$this->_isFTPDirectoryExists($conn_id, $this->_ftpDir))
				throw new Exception("FTP directory isn't exist.");
			
			ftp_chdir($conn_id,$this->_ftpDir);
			
			$aFileListDirty = ftp_nlist($conn_id, ".");
			$aFileListClean = array();
			
			foreach($aFileListDirty as $fe_file) {
				
				$aExp = explode(".","." . $fe_file);
				$expCount = count($aExp);
				
				if($aExp[$expCount - 1] == "zip")
					$aFileListClean[] = $fe_file;
				
			}
			
			$aNewUpdates = array();
			
			foreach($aFileListClean as $fe_file_clean) {
				
				if(!in_array($fe_file_clean, $aUpdatesFiles))
					$aNewUpdates[] = $fe_file_clean;
				
			}
			
			if(!count($aNewUpdates)) {
				$this->addInfo("NO NEW UPDATES. " . $this->_getTime(__METHOD__) . "s");
				return false;
			}
			
			natcasesort($aNewUpdates);
			
			$this->_updateFileName = array_pop($aNewUpdates);
			
			if(count($aNewUpdates)) {
				
				foreach($aNewUpdates as $fe_update) {
					
					$this->addInfo("IGNORING PREVIOUS UPDATE {$fe_update}. IT WILL BE NEVER UPDATED!");
					$this->_dbSetUpdate($fe_update);

				}
				
			}
			
			ftp_close($conn_id);
			return true;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			ftp_close($conn_id);
			return false;
		}
	}
	
	public function donwloadUpdate() {
		
		$conn_id = $this->ftpConnect();
		$this->_setTime(__METHOD__);
		
		try {
			
			if(empty($this->_updateFileName))
				throw new Exception("Update filename isn't set. Probably there is no update available.");

			$conn_result	= @ftp_login($conn_id, $this->_ftpUser, $this->_ftpPass);
			
			if(!$conn_result)
				throw new Exception("Can't connect to remote server {$this->_ftpHost}.");
			
			if(!$this->_isFTPDirectoryExists($conn_id, $this->_ftpDir))
				throw new Exception("FTP directory isn't exist.");
			
			ftp_chdir($conn_id,$this->_ftpDir);
			
			if (ftp_get($conn_id, $this->_localDirZip . DIRECTORY_SEPARATOR . $this->_updateFileName, $this->_updateFileName, FTP_BINARY)) {

				$this->addInfo("DOWNLOADING COMPLETE. " . $this->_getTime(__METHOD__) . "s");
				return true;
				
			} else {
				
				throw new Exception("Couldn't download file from remote server.");

			}

			ftp_close($conn_id);
			
				
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			ftp_close($conn_id);
			return false;
		}
	}
	
	public function unpackUpdate() {
		
		$this->_setTime(__METHOD__);
		
		try {
			
			if(empty($this->_updateFileName))
				throw new Exception("Update filename isn't set. Probably there is no update available.");
			
			if(!file_exists($this->_localDirZip . DIRECTORY_SEPARATOR . $this->_updateFileName))
				throw new Exception ("Seems that file isn't exist.");
			
			$objZip = new ZipArchive;
			if ($objZip->open($this->_localDirZip . DIRECTORY_SEPARATOR . $this->_updateFileName) === TRUE) {
				
				$objZip->extractTo($this->_localDir . DIRECTORY_SEPARATOR);
				$objZip->close();
				
				$this->addInfo("UNPACKING COMPLETE. " . $this->_getTime(__METHOD__) . "s");
				
				return true;
				
			} else {
				
				throw new Exception("Couldn't open zip archive.");
				
			}
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
		
	}
	
	public function doUpdate() {
		
		$this->_setTime(__METHOD__);
		
		try {
			
			if(empty($this->_updateFileName))
				throw new Exception("Update filename isn't set. Probably there is no update available.");
			
			if(!file_exists($this->_localDir))
				throw new Exception ("Seems that directory with unpacked content doesn't exists.");
			
			$objFolder = new classCmsFolder($this->_localDir);
                        
            $options = array(
                'from' => $this->_localDir,
                'to' => $this->_UPDATE_DIR,
                'mode' => 0755,
                'skip' => $this->_aSkipFiles,
                'scheme' => classCmsFolder::MERGE                
            );            
                                                            
            if (file_exists($this->removeList)) {
                $result = $objFolder->remove($this->removeList, $options);
            } else {
                $result = $objFolder->copy($options);
            }
            
			$this->addError($objFolder->errors(false));
			//$this->addInfo($objFolder->messages(false));
			
			if(!$result)
				throw new Exception ("There was problem while copying files to destination.");
			
			if($result)
				$this->_dbSetUpdate ($this->_updateFileName);
			
			$this->addInfo("UPDATING {$this->_updateFileName} COMPLETE. " . $this->_getTime(__METHOD__) . "s");
			
			return true;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
	}
	
	public function doUpdateDB() {
		
		$this->_setTime(__METHOD__);
		
		try {
			
			$DS = DIRECTORY_SEPARATOR;
			
			if(!$this->_initDB())
				return false;
			
			if(empty($this->_updateDBFileName))
				throw new Exception("Update DB filename isn't set.");
			
			if(!file_exists($this->_localDir . $DS . $this->_updateDBFileName))
				throw new Exception("Update DB file doesn't exists.");
			
			$sqlFile = file_get_contents($this->_localDir . $DS . $this->_updateDBFileName);
			
			if(empty($sqlFile))
				throw new Exception("SQL file is empty.");
			
			$sqlFile = mb_convert_encoding($sqlFile, 'HTML-ENTITIES', "UTF-8, ASCII");
			$sqlFile = html_entity_decode($sqlFile);
			
			$sqlFile = str_replace(";\r\n", "__MYSQL__ODSTEP__", $sqlFile);
			$sqlFile = str_replace("\r\n", "", $sqlFile);
			$sqlFile = str_replace("__MYSQL__ODSTEP__", "\r\n", $sqlFile);
			
			$aSqlFile = explode("\r\n", $sqlFile);
//echo '<pre>';
//var_dump($aSqlFile);
//echo '</pre>';
			foreach($aSqlFile as $fe_query) {
	
				if(!$this->_sqlConn->query($fe_query))
					$this->addError("One of the SQL queries failed to execute:\n\n" . $fe_query . "\n");
				
			}
			
			$this->addInfo("UPDATING DATABASE {$this->_updateDBFileName} COMPLETE. " . $this->_getTime(__METHOD__) . "s");
			return true;
			
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
		
	}
	
	public function cleanupUpdate() {
		
		$this->_setTime(__METHOD__);
		
		try {
			
			$objFolder = new classCmsFolder($this->_localDir);
			
			if (!$objFolder->delete()) {
				
				$this->addError($objFolder->errors(false));
				//$this->addInfo($objFolder->messages(false));
				throw new Exception("There was problem during cleaning up. Can't delete contents of folder {$this->_localDir}.");
			}
			
			$this->addError($objFolder->errors(false));
			//$this->addInfo($objFolder->messages(false));
			
			unset($objFolder);
			
			$objFolder = new classCmsFolder($this->_localDirZip);

			if (!$objFolder->delete()) {
				
				$this->addError($objFolder->errors(false));
				//$this->addInfo($objFolder->messages(false));
				throw new Exception("There was problem during cleaning up. Can't delete contents of folder {$this->_localDirZip}.");
			}
			
			$this->addError($objFolder->errors(false));
			//$this->addInfo($objFolder->messages(false));
			
			$this->addInfo("CLEANING UP COMPLETE. " . $this->_getTime(__METHOD__) . "s");
				
		} catch (Exception $ex) {
			
			$this->addError($this->parseException($ex));
			return false;
			
		}
	}
    
    /*
     * Cleans logs older than 3 days
     */
    public function cleanLogs() {
       
        $dir = dirname(__FILE__) . "/../application/logs/";

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && filemtime($dir.$file) <= time()-60*60*72 ) {// 60 (sec) * 60 (min) * 72 (hour) 
                    unlink($dir.$file);
                }                   
            }
            closedir($handle);
        }          
    }
    
    public function logUpdate($status) {
        $updateFileLog = dirname(__FILE__) . '/update_log.txt';        
        
        $zipFile = $this->getUpdateFileName();
        if ($zipFile) {
            $zipFile = substr($zipFile, 0, -4);
            $zipFile = explode('-', $zipFile);

            $ver = $zipFile[1];
            $dataRow = date('Y-m-d H:i:s') . ' ' . $ver . ' '. $status . PHP_EOL;

            // Write the contents to the file, 
            // using the FILE_APPEND flag to append the content to the end of the file
            // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            file_put_contents($updateFileLog, $dataRow, FILE_APPEND | LOCK_EX); 
        }
    }
    
    public function getUpdateFileName() {
        return $this->_updateFileName;
    }
    
    protected function ftpConnect() {
        
        switch (self::FTP_CONNECTION_TYPE) {
            case 'ssl':
                $conn_id = ftp_ssl_connect($this->_ftpHost);
                break;

            default:
                $conn_id = ftp_connect($this->_ftpHost);
                break;
        }
        
        return $conn_id;
    }
	
}
