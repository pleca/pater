<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/BasketModel.php');

if (isset($_POST['action']) AND $_POST['action'] == 'login') {
	
	if (!$oCustomer->login($_POST)) {
		$params['aItem2'] = $_POST;
	} else {
		if (Cms::$modules['shop'] == 1) { // po zalogowaniu zapisujemy koszyk klienta
			$basket = new BasketModel();

			if ($items = $basket->getByCustomerOrSession()) {

				foreach($items as $v) {
					if($v['customer_id'] > 0) {
//						$Basket->deleteById($v['id']);
					} else {
						$aFields = [];
						$aFields['customer_id'] = $_SESSION[CUSTOMER_CODE]['id'];
						$basket->updateById($v['id'], $aFields);
					}
				}
			}
		}
		
		redirect_301(URL . '/customer.html');	
	}
}

$data = array(
	'pageTitle' => $GLOBALS['LANG']['c_login'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/login.twig', array_merge($data, $params)); 
