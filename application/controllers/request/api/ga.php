<?php

require_once(CLASS_DIR . '/Api/Ga/ApiGa.php');        
require_once(CLASS_DIR . '/Api/Ga/ApiGaJson.php');        
require_once(CLASS_DIR . '/Api/Ga/ApiGaXml.php');        
require_once(CLASS_DIR . '/Api/Ga/ApiGaArray.php');        

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

set_time_limit(580);

$apiGa = new ApiGa();
$apiGa->init();

switch ($apiGa->getResponseType()) {
	case 'json':
		$apiGa->setStrategy(new ApiGaJson);
		break;
	case 'array':
		$apiGa->setStrategy(new ApiGaArray);
		break;
	case 'xml':
		$apiGa->setStrategy(new ApiGaXml);
		break;
	default:
		throw new Exception(ApiGa::getMessageForCode(ApiGa::RESPONSE_TYPE_INVALID));
		break;
}

$apiGa->getStrategy()->run();