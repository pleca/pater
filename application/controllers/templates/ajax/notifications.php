<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/NotificationsStockAvailability.php');

$method = $_POST['method'];
$id = isset($_POST['id']) ? $_POST['id'] : 0;

switch ($method) {
	case 'notificationsStockAvailability':
		notificationsStockAvailability();
		break;    

	default:
		break;
}
die;

function notificationsStockAvailability() {
	if (!$_POST['variation_id']) {
		return false;
	}
	
	$nsa = new NotificationsStockAvailability();

	$params = array(
		'email' => $_POST['email'],
		'variation_id' => $_POST['variation_id']		
	);

	$entities = $nsa->findBy($params);
		
	if (!$entities) {
		if ($nsa->set($params)) {
			echo $GLOBALS['LANG']['stock_availability_email_add_success'];
		} else {
			echo $GLOBALS['LANG']['stock_availability_email_add_error'];			
		}
	}
}

