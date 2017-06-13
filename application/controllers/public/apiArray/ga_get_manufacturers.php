<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

ob_start();

require_once(LIB_DIR . '/arrayApi/arrayApi.php');
require_once(MODEL_DIR . '/api/manufacturers.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	die('Brak POST, nie ma co robic');
}
if (!isset($_POST['ARRAY'])) {
	die('Brak ARRAY, nie ma co robic');
}
if (!isset($_POST['TYPE'])) {
	die('Brak TYPE, nie ma co robic');
}

$oApi = new arrayApi();
$oApiManufacturers = new ApiManufacturers();

if ($_POST['TYPE'] == 'ManufacturersGet') {
	$accID = $oApi->getAccountIDFromQuery($_POST['ARRAY']);

	if (!$accID) {
		die('Nieznany accID!');
	}
	if ($accID != GA_ACC_ID) {
		die('Brak konta w systemie');
	}

	$oApi->setAccID(GA_ACC_ID);
	$oApi->setAccKey(GA_ACC_KEY);
	$oApi->setAccName(GA_ACC_NAME);

	if ($aManufacturers = $oApiManufacturers->get_all()) {
		foreach ($aManufacturers as $v) {
			// struktura GA
			$tmp = array('online_manufacturer_id' => 0, 'name' => '', 'order' => 0, 'status_id' => 0);

			$tmp['online_manufacturer_id'] = $v['id'];
			$tmp['name'] = $v['name'];
			$tmp['order'] = $v['order'];
			$tmp['status_id'] = $v['status_id'];
			$array[] = $tmp;
		}
		$oApi->setDataAsArray($array);
		$oApi->encryptData();

		if (CLEAN_OUTPUT) {
			ob_end_clean();
		}

		echo($oApi->getFinalEncryptedDataString());
	}
} else {
	die('Bledna prosba');
}
