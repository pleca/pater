<?php

define('VER', '');
//error_reporting(E_ALL);
//ini_set('display_errors', 'on');

echo 'update stock...';

require('../application/config/config.php'); // ustawienia indywidualne
require('../application/config/config_common.php'); // ustawienia wspolne

require(SYS_DIR . '/core/Cms.php');
require(SYS_DIR . '/core/ErrorHandling.php');
require(SYS_DIR . '/core/Functions.php');
require(SYS_DIR . '/core/Logger.php');
require(SYS_DIR . '/database/Database.php');

$GLOBALS['counter_db'] = 0;
$GLOBALS['counter_q'] = 0;

Cms::initialize();
setConstsFromConfig();

Cms::$twig->addGlobal('conf', Cms::$conf);
require(CMS_DIR . '/application/languages/admin-en.php');
Cms::$twig->addGlobal('lang', $GLOBALS['LANG']);
foreach (Cms::$langs as $v) { // domyslny jezyk serwisu
	if ($v['default'] == 1) {
		define('_ID', $v['id']);		
		define('LOCALE', $v['code']);		
	}
}

require_once(MODEL_DIR . '/Variation.php');        
require_once(CLASS_DIR . '/Stock/StockUpdate.php');        
require_once(CLASS_DIR . '/Stock/StockUpdateUrl.php');        

if ($_SERVER['SERVER_NAME'] == 'trade.trec-distribution.co.uk') {
	$_POST['url'] = 'http://b2b-trecnutrition.com/Api/synchronizacja.pobierz.ashx?data=7&key=24f67b73d5a6bf18dd8b44a4fd99c40b';

	$stockUpdate = new StockUpdate();
	$stockUpdate->setStrategy(new StockUpdateUrl);
	$stockUpdate->getStrategy()->update();	
	
	$sendTime = strtotime(date("Y-m-d") . ' 10:06:00');	
	$now = strtotime(date("Y-m-d H:i:s"));
	$interval  = $sendTime - $now;
	$minutes   = round($interval / 60);

	if ($minutes > 0 && $minutes <= 5) {
		$stockUpdate->getStrategy()->notifyAdminNotUpdated();
	}	
}

