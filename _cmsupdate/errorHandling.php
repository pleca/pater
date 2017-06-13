<?php

function exceptionHandler($exception) {
	
	require_once (dirname(__FILE__) . 'myLogger.php');
	
	$msg = $exception -> getMessage();
	
	$oLogger = new myLogger(DR . "/application/logs",'update', myLogger::DEBUG);
	
	if(error_reporting() != 0)
		echo "Uncaught exception: " . $msg . "\n";
	
	$oLogger->LogFatal("Uncaught exception: " . $msg);
}

function shutdownHandler() {
	
	require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'myLogger.php');
	
	$oLogger = new myLogger(DR . "/application/logs",'update', myLogger::DEBUG);
	
	$error = error_get_last();
	if($error !== NULL){
		$info = "SHUTDOWN: " . $error['message'] . " - " . $error['file'] . ":" . $error['line'];
		
		$oLogger->LogFatal($info);
	}
	else{
		//$oLogger->LogFatal("SHUTDOWN: NULL ERROR");
	}
}

    
function errorHandler($errno, $errstr,$error_file,$error_line)
{
	require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'myLogger.php');
	
		/*
		 * DEBUG - Najwiecej informacji
		 * INFO
		 * WARN
		 * ERROR
		 * FATAL - Najmniej informacji
		 * OFF - Logowanie wylaczone
		 */

	
	$oLogger = new myLogger(DR . "/application/logs",'update', myLogger::DEBUG);
	
	$smartyErrors = false;
	
	if(preg_match('/compile\/.*\.file\..*\.tpl\.php/', $error_file))
		$smartyErrors = true;
	
    switch ($errno) {
    	case E_USER_NOTICE:
        case E_NOTICE:
        case E_STRICT: {
        	
        	if(error_reporting() >= $errno && !$smartyErrors) {
        		
	            echo "<b>NOTICE:</b> [$errno] $errstr - $error_file:$error_line";
				echo "<br />";
				
        	}
			$oLogger->LogInfo("NOTICE: [$errno] $errstr - $error_file:$error_line");
            
        } break;
        case E_USER_WARNING:
        case E_WARNING:{
        	
        	if(error_reporting() >= $errno && !$smartyErrors) {
        		
	            echo "<b>WARNING:</b> [$errno] $errstr - $error_file:$error_line";
				echo "<br />";
				
        	}
			$oLogger->LogWarn("WARNING: [$errno] $errstr - $error_file:$error_line");
			
        } break;
        case E_USER_ERROR:
        case E_ERROR:{
        	
        	@ob_clean();
        	
        	$oLogger->LogError("FATAL ERROR $errstr at $errfile:$errline");
            exit("FATAL ERROR $errstr at $errfile:$errline");
            
            
        } break;
        default: {
        	
        	@ob_clean();
        	
        	$oLogger->LogFatal("Unknown error");
            exit("Unknown error");
            
        } break;
    }
}

set_exception_handler('exceptionHandler');

set_error_handler("errorHandler");

register_shutdown_function('shutdownHandler');