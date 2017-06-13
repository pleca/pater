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
	
if ($_POST['TYPE'] == 'OrdersGet') {
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

	if ($aOrders = $oApiOrders->get_all(2)) { // 2 - status_id | payment_accept
		foreach ($aOrders as $v) {
			// struktura GA
			$tmp = array('item_id' => 0, 'transport_name_online' => '', 'payment_id' => 0, 'status_id' => 0, 'price' => 0, 'currency' => '', 'transport_name' => '',
				'transport_price' => 0, 'transport_currency' => '', 'weight_transport' => 0, 'date_add' => '', 'date_payment' => '', 'date_complete' => '', 'name' => '', 'surname' => '',
				'address2' => '', 'address2' => '', 'address3' => '', 'postcode' => '', 'city' => '', 'county' => '', 'country_code' => '', 'phone' => '', 'email' => '',
				'comment' => '');		

			$tmp['item_id'] = $v['id'];
			$tmp['transport_name_online'] = $v['transport']['name_online'];
			$tmp['payment_id'] = $v['payment_id']; // 1 - PayPal, 2 - CreditCard, 3 - Other
			$tmp['status_id'] = 2; // 2 - payment accepted
			$tmp['price'] = $v['price'];
			$tmp['currency'] = Cms::$conf['currency']; // GBP, EUR, PLN, USD
			$tmp['transport_name'] = $v['transport']['service_name'];
			$tmp['transport_price'] = $v['transport']['price_gross'];
			$tmp['transport_currency'] = Cms::$conf['currency'];
			$tmp['transport_weight'] = $v['weight'];
			$tmp['date_add'] = $v['time_add'];
			$tmp['date_payment'] = $v['time_payment'];
			$tmp['date_complete'] = $v['time_complete'];
			$tmp['name'] = $v['address']['shipping_first_name'];
			$tmp['surname'] = $v['address']['shipping_last_name'];
			$tmp['address1'] = $v['address']['shipping_address1'];
			$tmp['address2'] = $v['address']['shipping_address2'];
			$tmp['address3'] = $v['address']['shipping_address3'];
			$tmp['postcode'] = $v['address']['shipping_post_code'];
			$tmp['city'] = $v['address']['shipping_city'];
			$tmp['county'] = '';
			$tmp['country_code'] = $v['address']['shipping_country_code'];
			$tmp['phone'] = $v['address']['shipping_phone'];
			$tmp['email'] = $v['address']['email'];
			$tmp['comment'] = $v['comment'];
			$tmp['paypal_transaction_id'] = $v['paypal_transaction_id'];
			$tmp['paypal_amount_fee'] = $v['paypal_amount_fee'];

			foreach ($v['products'] as $v2) {
				// struktura GA
				$tmp2 = array('item_id' => 0, 'name' => '', 'sku' => '', 'promo' => 0, 'qty' => 0, 'price_purchase' => 0, 'price' => 0, 'currency' => '', 'tax' => 0);

				$tmp2['item_id'] = $v2['id'];
				$tmp2['name'] = $v2['name'];
				$tmp2['sku'] = $v2['sku'];
				$tmp2['promo'] = 0;
				$tmp2['qty'] = $v2['qty'];
				$tmp2['price_purchase'] = $v2['price_purchase'];
				$tmp2['currency'] = Cms::$conf['currency']; // GBP, EUR, PLN, USD
				$tmp2['price'] = $v2['price'];
				$tmp2['tax'] = $v2['tax_val'];
				$tmp['products'][] = $tmp2;
			}
			$array[] = $tmp;
		}

		$oApi->setDataAsArray($array);
		$oApi->encryptData();

		if (CLEAN_OUTPUT) {
			ob_end_clean();
		}
		
		echo($oApi->getFinalEncryptedDataString());
	}
}
else {
	die('Bledna prosba');
}
