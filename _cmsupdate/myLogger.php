<?php
	
	/* 
	 * Usage: 
	 *		$log = new myLogger ( "log.txt" , myLogger::INFO );
	 *		$log->LogInfo("Returned a million search results");	//Prints to the log file
	 *		$log->LogFATAL("Oh dear.");				//Prints to the log file
	 *		$log->LogDebug("x = 5");					//Prints nothing due to priority setting
	*/
	
	class myLogger
	{
		
		const DEBUG 	= 1;	// Most Verbose
		const INFO 		= 2;	// ...
		const WARN 		= 3;	// ...
		const ERROR 	= 4;	// ...
		const FATAL 	= 5;	// Least Verbose
		const OFF 		= 6;	// Nothing at all.
		
		const LOG_OPEN 		= 1;
		const OPEN_FAILED 	= 2;
		const LOG_CLOSED 	= 3;
		
		/* Public members: Not so much of an example of encapsulation, but that's okay. */
		public $Log_Status 	= myLogger::LOG_CLOSED;
		public $DateFormat	= "Y-m-d H:i:s";
		public $MessageQueue;
	
		private $log_file;
		private $priority = myLogger::INFO;
		
		private $file_handle;
		
		public function __construct( $filepath , $filename, $priority )
		{
			if ( $priority == myLogger::OFF ) return;
			
			if(!file_exists($filepath))
				mkdir($filepath, 0755, true);
			
			$filename = date("Y-m-d") . "_" .  $filename;
			$filepath =  $filepath . DIRECTORY_SEPARATOR . $filename . ".log";
			//echo($filepath);die;
			
			$this->log_file = $filepath;
			$this->MessageQueue = array();
			$this->priority = $priority;
			
			if ( file_exists( $this->log_file ) )
			{
				if ( !is_writable($this->log_file) )
				{
					$this->Log_Status = myLogger::OPEN_FAILED;
					$this->MessageQueue[] = "The file exists, but could not be opened for writing. Check that appropriate permissions have been set.";
					return;
				}
			}
			
			if ( $this->file_handle = fopen( $this->log_file , "a" ) )
			{
				$this->Log_Status = myLogger::LOG_OPEN;
				$this->MessageQueue[] = "The log file was opened successfully.";
			}
			else
			{
				$this->Log_Status = myLogger::OPEN_FAILED;
				$this->MessageQueue[] = "The file could not be opened. Check permissions.";
			}
			
			return;
		}
		
		public function __destruct()
		{
			if ( $this->file_handle )
				fclose( $this->file_handle );
		}
		
		public function LogInfo($line)
		{
			$this->Log( $line , myLogger::INFO );
		}
		
		public function LogDebug($line)
		{
			$this->Log( $line , myLogger::DEBUG );
		}
		
		public function LogWarn($line)
		{
			$this->Log( $line , myLogger::WARN );	
		}
		
		public function LogError($line)
		{
			$this->Log( $line , myLogger::ERROR );		
		}

		public function LogFatal($line)
		{
			$this->Log( $line , myLogger::FATAL );
		}
		
		public function Log($line, $priority)
		{
			if ( $this->priority <= $priority )
			{
				$status = $this->getTimeLine( $priority );
				$this->WriteFreeFormLine ( "$status $line \n" );
			}
		}
		
		public function WriteFreeFormLine( $line )
		{
			if ( $this->Log_Status == myLogger::LOG_OPEN && $this->priority != myLogger::OFF )
			{
			    if (fwrite( $this->file_handle , $line ) === false) {
			        $this->MessageQueue[] = "The file could not be written to. Check that appropriate permissions have been set.";
			    }
			}
		}
		
		private function getTimeLine( $level )
		{
			$time = date( $this->DateFormat );
		
			switch( $level )
			{
				case myLogger::INFO:
					return "$time - INFO  -->";
				case myLogger::WARN:
					return "$time - WARN  -->";				
				case myLogger::DEBUG:
					return "$time - DEBUG -->";				
				case myLogger::ERROR:
					return "$time - ERROR -->";
				case myLogger::FATAL:
					return "$time - FATAL -->";
				default:
					return "$time - LOG   -->";
			}
		}
		
	}
