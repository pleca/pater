<?php

require_once(MODEL_DIR . '/Variation.php');        
require_once(CLASS_DIR . '/Stock/StockUpdate.php');        
require_once(CLASS_DIR . '/Stock/StockUpdateUrl.php');        

if ($_SERVER['SERVER_NAME'] == 'trade.trec-distribution.co.uk') {
	$_POST['url'] = 'http://b2b-trecnutrition.com/Api/synchronizacja.pobierz.ashx?data=7&key=24f67b73d5a6bf18dd8b44a4fd99c40b';

	$stockUpdate = new StockUpdate();
	$stockUpdate->setStrategy(new StockUpdateUrl);
	$stockUpdate->getStrategy()->update();	
			
	$stockUpdate->getStrategy()->notifyAdminNotUpdated();
	
//	$sendTime = strtotime(date("Y-m-d") . ' 10:06:00');	
//	$now = strtotime(date("Y-m-d H:i:s"));
//	$interval  = $sendTime - $now;
//	$minutes   = round($interval / 60);
//
//	if ($minutes > 0 && $minutes <= 5) {
//		$stockUpdate->getStrategy()->notifyAdminNotUpdated();
//	}	
} else {
	echo 'it` not wholesale. Stop running...';
}

