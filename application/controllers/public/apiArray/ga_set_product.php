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

if ($_POST['TYPE'] == 'ProductSet') {
//	die('Opcja wylaczona');	
	
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
	// [online_id] [model_id] [category_id] [manufacturer_id] [name] [desc] [feature1_name] [feature2_name] [feature3_name] [bullet1] [bullet2] [bullet3] [bullet4] [bullet5] [status_id]
	// images [image_id] [order] [url]
	// variations [sku] [price] [price_rrp] [price_purchase] [tax] [qty] [feature1_value] [feature2_value] [feature3_value] [weight] [size_length] [size_height] [size_width]

	if ($item = $oApiProducts->get_by_id($array['online_id'])) {
		die('Produkt już istnieje, nie można go dodać!');
	} else {
		$id = $oApiProducts->add($array);
		$oApiProducts->add_images($array['images'], $id);
		$oApiProducts->set_variations($array['variations'], $id);
	}

	// zwracamy odpoweidz
	$aData = array('id' => 0); // struktura w GA
	$aData['id'] = $id;

	$oApi->setDataAsArray($aData);
	$oApi->encryptData();

	if (CLEAN_OUTPUT) {
		ob_end_clean();
	}
	
	echo($oApi->getFinalEncryptedDataString());
}
elseif ($_POST['TYPE'] == 'ProductUpdate') {
	die('Opcja wylaczona');	
	
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
	// array('online_id', 'category_id', 'manufacturer_id', 'name', 'desc',
	// 'feature1_name', 'feature2_name', 'feature3_name', 'bullet1', 'bullet2', 'bullet3', 'bullet4', 'bullet5', 'status_id');			
	// images('image_id', 'order', 'url');
	// variations('sku', 'price', 'price_rrp', 'price_purchase', 'tax', 'qty', 'feature1_value', 'feature2_value', 'feature3_value', 'weight', 'size_lenght', 'size_height', 'size_width');

	if ($item = $oApiProducts->get_by_id($array['online_id'])) {
		$id = $oApiProducts->edit($array);

//		$oApiProducts -> edit_images($array['images'], $id);
		$oApiProducts->set_variations($array['variations'], $id);
	} else {
		die('Brak produktu w serwisie!');
	}

	// zwracamy odpoweidz
	$aData = array('id' => 0); // struktura w GA
	$aData['id'] = $id;

	$oApi->setDataAsArray($aData);
	$oApi->encryptData();

	if (CLEAN_OUTPUT) {
		ob_end_clean();
	}
	
	echo($oApi->getFinalEncryptedDataString());
}
elseif ($_POST['TYPE'] == 'ProductArchiveSet') {
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
	// array('online_id', 'status_id');			

	if ($item = $oApiProducts->set_status_id($array)) {
		$id = $item['id'];
	} else {
		die('Brak produktu w systemie!');
	}

	// zwracamy odpoweidz
	$aData = array('id' => 0); // struktura w GA
	$aData['id'] = $id;

	$oApi->setDataAsArray($aData);
	$oApi->encryptData();

	if (CLEAN_OUTPUT) {
		ob_end_clean();
	}

	echo($oApi->getFinalEncryptedDataString());
}
elseif ($_POST['TYPE'] == 'ProductActiveSet') {
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
	// array('online_id', 'status_id');			

	if ($item = $oApiProducts->set_status_id($array)) {
		$id = $item['id'];
	} else {
		die('Brak produktu w systemie!');
	}

	// zwracamy odpoweidz
	$aData = array('id' => 0); // struktura w GA
	$aData['id'] = $id;

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
