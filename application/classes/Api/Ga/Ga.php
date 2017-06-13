<?php

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

function my_autoloader($class) {
    
    $dirs = array(
        CLASS_DIR . '/Api/Ga/',
        CLASS_DIR . '/Api/Ga/Methods/',
        CLASS_DIR . '/Api/Ga/Response/',
    );
    
    foreach ($dirs as $dir) {
        if (file_exists($dir.$class . '.php')) {
            require_once($dir . $class . '.php');
            //only require the class once, so quit after to save effort (if you got more, then name them something else
            return;
        }           
    }
}

spl_autoload_register('my_autoloader');

set_time_limit(580);

$apiGa = new ApiGa();
$apiGa->init();

if ($apiGa->getRequestErrors()) {
    $response = $apiGa->getResponse();    
    dump($response);
    return $response;
}

$apiGa->runMethod();
die;
switch ($apiGa->getResponseType()) {
	case 'json':
		$apiGa->setStrategy(new Json);
		break;
	case 'array':
		$apiGa->setStrategy(new ResponseArray);
		break;
	case 'xml':
		$apiGa->setStrategy(new Xml);
		break;
	default:
		throw new Exception(ApiGa::getMessageForCode(ApiGa::RESPONSE_TYPE_INVALID));
		break;
}

$apiGa->getStrategy()->run();