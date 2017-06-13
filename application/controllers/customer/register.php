<?php

/* 2012-03-29 | creative.cms 7.6.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(MODEL_DIR . '/BasketModel.php');

$entity = array("first_name" => '', "last_name" => '', "email" => '', "email2" => '', "login2" => '', "pass2" => '', "pass3" => '', "accept" => '');

if (isset($_POST['action']) AND $_POST['action'] == 'add') {

	$_POST['accept'] = isset($_POST['accept']) ? 1 : 0;

	if (!$oCustomer->add($_POST)) {
		$entity = $_POST;
	} else {
        $data = [];
        $data['login'] = $_POST['login2'];
        $data['pass'] = $_POST['pass2'];
        $oCustomer->login($data);
		
		if (Cms::$modules['shop'] == 1) { // po rejestracji i zalogowaniu zapisujemy koszyk klienta
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
		
		$params['redirect'] = true;
	}
}

$data = array(
	'entity' => $entity,
	'pageTitle' => $GLOBALS['LANG']['c_register'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/register.twig', array_merge($data, $params));
