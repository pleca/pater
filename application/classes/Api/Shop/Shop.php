<?php

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/ApiShopLog.php'); 

function my_autoloader($class) {
    
    $dirs = array(
        CLASS_DIR . '/Api/Shop/',
        CLASS_DIR . '/Api/Shop/Methods/',
        CLASS_DIR . '/Api/Shop/Response/',
    );
    
    foreach ($dirs as $dir) {
        if (file_exists($dir . $class . '.php')) {
            require_once($dir . $class . '.php');
            //only require the class once, so quit after to save effort (if you got more, then name them something else
            return;
        }           
    }
}

spl_autoload_register('my_autoloader');

set_time_limit(580);

$apiShop = new ApiShop();
$apiShop->init();

if ($apiShop->getRequestErrors()) {
    $response = $apiShop->getResponse();    
	$apiShop->log();
    dump($response);
    return $response;
}

$apiShop->runMethod();
$apiShop->log();
die;
//switch ($apiGa->getResponseType()) {
//	case 'json':
//		$apiGa->setStrategy(new Json);
//		break;
//	case 'array':
//		$apiGa->setStrategy(new ResponseArray);
//		break;
//	case 'xml':
//		$apiGa->setStrategy(new Xml);
//		break;
//	default:
//		throw new Exception(ApiGa::getMessageForCode(ApiGa::RESPONSE_TYPE_INVALID));
//		break;
//}
//
//$apiShop->getStrategy()->run();