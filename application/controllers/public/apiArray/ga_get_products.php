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

if ($_POST['TYPE'] == 'ProductsGet') {
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

	if ($aProducts = $oApiProducts->get_products_updated(1)) { // 1 day
		foreach ($aProducts as $v) {
			// struktura GA
			$tmp = array('online_id' => 0, 'model_id' => 0, 'online_category_id' => 0, 'online_manufacturer_id' => 0, 'name' => '', 'desc' => '',
				'feature1_name' => '', 'feature2_name' => '', 'feature3_name' => '', 'bullet1' => '', 'bullet2' => '', 'bullet3' => '', 'bullet4' => '', 'bullet5' => '',
				'status_id' => 0);

			$tmp['online_id'] = $v['id'];
			$tmp['model_id'] = $v['type']; // 1-wariacje, 2-bez wariacji
			$tmp['online_category_id'] = $v['category_id'];
			$tmp['online_manufacturer_id'] = $v['producer_id'];
			$tmp['name'] = $v['name'];
			$tmp['desc'] = $v['content'];
//			$tmp['feature1_name'] = $v['feature1_name'];
//			$tmp['feature2_name'] = $v['feature2_name'];
//			$tmp['feature3_name'] = $v['feature3_name'];
			$tmp['bullet1'] = '';
			$tmp['bullet2'] = '';
			$tmp['bullet3'] = '';
			$tmp['bullet4'] = '';
			$tmp['bullet5'] = '';
			$tmp['status_id'] = $v['status_id'];
			$tmp['variations'] = array();
			$tmp['images'] = array();

			foreach ($v['variations'] as $v2) {
				// struktura GA
				$tmp2 = array('sku2' => '', 'ean2' => '', 'price_rrp' => 0, 'price_purchase' => 0, 'price' => 0, 'qty' => 0, 'sold' => 0,
					'feature1_value' => '', 'feature2_value' => '', 'feature3_value' => '', 'weight' => 0, 'size_lenght' => '', 'size_height' => '', 'size_width' => '');

				$tmp2['sku2'] = $v2['sku'];
				$tmp2['ean2'] = '';
				$tmp2['price_rrp'] = $v2['price_rrp'];
				$tmp2['price_purchase'] = $v2['price_purchase'] + $v2['price_purchase'] * $v2['tax'] / 100;
				$tmp2['price'] = $v2['price'] + $v2['price'] * $v2['tax'] / 100;
				$tmp2['qty'] = $v2['qty'];
				$tmp2['sold'] = '';
//				$tmp2['feature1_value'] = $v2['feature1_value'];
//				$tmp2['feature2_value'] = $v2['feature2_value'];
//				$tmp2['feature3_value'] = $v2['feature3_value'];
				$tmp2['weight'] = $v2['weight'];
				$tmp2['size_lenght'] = '';
				$tmp2['size_height'] = '';
				$tmp2['size_width'] = '';
				$tmp['variations'][] = $tmp2;
			}

			foreach ($v['images'] as $v3) {
				// struktura GA
				$tmp3 = array('online_image_id' => 0, 'url' => '', 'order' => '');

				$tmp3['online_image_id'] = $v3['id'];
				$tmp3['url'] = $v3['url'];
				$tmp3['order'] = $v3['order'];
				$tmp['images'][] = $tmp3;
			}

			$array[] = $tmp;
		}

		$oApi->setDataAsArray($array);
		$oApi->encryptData();

		if (CLEAN_OUTPUT)
			ob_end_clean();

		echo($oApi->getFinalEncryptedDataString());
	}
}
else {
	die('Bledna prosba');
}
