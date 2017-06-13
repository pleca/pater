<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/BasketModel.php');

if (isset($_POST['action']) AND $_POST['action'] == 'login') {
	if (!$oCustomer->login($_POST)) {
		Cms::$tpl->assign('aItem2', $_POST);
	} else {
		if (Cms::$modules['shop'] == 1) { // po zalogowaniu zapisujemy koszyk klienta
			$Basket = new BasketModel();
			if($items = $Basket->getBySession()) {
				foreach($items as $v) {
					if($v['customer_id'] > 0) {
						$Basket->deleteById($v['id']);
					} else {
						$aFields = [];
						$aFields['customer_id'] = $_SESSION[CUSTOMER_CODE]['id'];
						$Basket->updateById($v['id'], $aFields);
					}
				}
			}
		}
		Cms::$tpl->assign('logged', true);
		Cms::$tpl->assign('customer', $_SESSION[CUSTOMER_CODE]);
	}
}

Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['c_login'] . ' - ' . Cms::$seo['title']);
Cms::$tpl->assign('redirect', true);
Cms::$tpl->showPage('customer/login3.tpl');

