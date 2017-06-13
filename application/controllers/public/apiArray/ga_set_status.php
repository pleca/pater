<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

ob_start();

require_once(LIB_DIR . '/arrayApi/arrayApi.php');
require_once(MODEL_DIR . '/api/orders.php');

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
$oApiOrders = new ApiOrders();

if ($_POST['TYPE'] == 'StatusSet') {
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

	if (!$array)
		$oApi->addError('Metoda getDataAsArray nie zwróciła tablicy');

	// struktura w GA
	// array('id', 'online_id', 'status_id', 'date_complete', 'comment', 'tracking');
	// zwracamy odpowiedz
	$aData = array(); // struktura w GA | 'id'

	foreach ($array as $v) {
		if ($item = $oApiOrders->get_by_id($v['online_id'])) {
			if ($oApiOrders->set_status($item['id'], $v)) {
				$aData[] = $v['id']; // zwracamy id poprawnych wpisow
			}
		}
	}

	$oApi->setDataAsArray($aData);
	$oApi->encryptData();

    //pobranie statusu z GA (kazdy status)
    //pobranie numeru nadania/śledzenia (wartość z ,na)
    
    
	if (CLEAN_OUTPUT) {
		ob_end_clean();
	}
	
	echo($oApi->getFinalEncryptedDataString());
}
else {
	die('Bledna prosba');
}
