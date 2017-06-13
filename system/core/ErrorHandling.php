<?php

function exceptionHandler($exception) {
	$msg = $exception->getMessage();
	if (error_reporting() != 0) {
		echo "Uncaught exception: " . $msg . "\n";
	}
	Cms::$log->LogFatal("Uncaught exception: " . $msg);
}

function shutdownHandler() {
	$error = error_get_last();
	if ($error !== NULL) {
		$info = "SHUTDOWN: " . $error['message'] . " - " . $error['file'] . ":" . $error['line'];
		Cms::$log->LogFatal($info);
	}
}

function errorHandler($errno, $errstr, $error_file, $error_line) {
	/*
	 * DEBUG - Najwiecej informacji
	 * INFO
	 * WARN
	 * ERROR
	 * FATAL - Najmniej informacji
	 * OFF - Logowanie wylaczone
	 */
	$smartyErrors = false;

	if (preg_match('/compile\/.*\.file\..*\.tpl\.php/', $error_file))
		$smartyErrors = true;

	switch ($errno) {
		case E_USER_NOTICE:
		case E_NOTICE:
		case E_STRICT: {

				if (error_reporting() >= $errno && !$smartyErrors) {

					echo "<b>NOTICE:</b> [$errno] $errstr - $error_file:$error_line";
					echo "<br />";
				}
				Cms::$log->LogInfo("NOTICE: [$errno] $errstr - $error_file:$error_line");
			} break;
		case E_USER_WARNING:
		case E_WARNING: {

				if (error_reporting() >= $errno && !$smartyErrors) {

					echo "<b>WARNING:</b> [$errno] $errstr - $error_file:$error_line";
					echo "<br />";
				}
				Cms::$log->LogWarn("WARNING: [$errno] $errstr - $error_file:$error_line");
			} break;
		case E_USER_ERROR:
		case E_ERROR: {

				@ob_clean();

				Cms::$log->LogError("FATAL ERROR [$errno] $errstr at $error_file:$error_line");
				exit("FATAL ERROR [$errno] $errstr at $error_file:$error_line");
			} break;
		default: {

				@ob_clean();

				Cms::$log->LogError("FATAL ERROR [$errno] $errstr at $error_file:$error_line");
				exit("FATAL ERROR [$errno] $errstr at $error_file:$error_line");
			} break;
	}
}

set_exception_handler('exceptionHandler');
set_error_handler("errorHandler");
register_shutdown_function('shutdownHandler');
