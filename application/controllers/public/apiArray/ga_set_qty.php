<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

ob_start();

require_once(LIB_DIR . '/arrayApi/arrayApi.php');
require_once(MODEL_DIR . '/api/products.php');

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
$oApiProducts = new ApiProducts();

if ($_POST['TYPE'] == 'QtySet') {
	$oApi->setPreData($_POST['ARRAY']);
	unset($_POST['ARRAY']);

	$accID = $oApi->getAccID();

	if (!$accID) {
		die('Nieznany accID!');
	}
	if ($accID != GA_ACC_ID) {
		die('Brak konta w systemie');
	}

	$oApi->setAccID(GA_ACC_ID);
	$oApi->setAccKey(GA_ACC_KEY);
	$oApi->setAccName(GA_ACC_NAME);

	$oApi->decryptData(); // odszyfrowanie danych

	$array = $oApi->getDataAsArray(); // tablica z danymi

	if (!$array) {
		$oApi->addError('Metoda getDataAsArray nie zwróciła tablicy');
	}

	// struktura w GA
	// array('online_id', 'variation_id', 'sku', 'qty', 'price', 'tax);

	// zwracamy odpowiedz
	$aData = array(); // struktura w GA | 'id'

	foreach ($array as $v) {
		if ($item = $oApiProducts->get_by_id($v['online_id'])) {
			if ($variation = $oApiProducts->get_variation_by_sku($item['id'], $v['sku'])) {
				$oApiProducts->edit_variation_by_id($variation['id2'], $v);
				$aData[] = $v['variation_id']; // zwracamy id poprawnych wpisow
			}
		}
	}
	$oApi->setDataAsArray($aData);
	$oApi->encryptData();

	if (CLEAN_OUTPUT) {
		ob_end_clean();
	}

	echo($oApi->getFinalEncryptedDataString());
}
else {
	die('Bledna prosba');
}
