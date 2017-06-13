<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}
if (Cms::$modules['shop'] != 1) {
	die('This module is disabled!');
}

require_once(MODEL_DIR . '/PaymentModel.php');
require_once(MODEL_DIR . '/shopOrders.php');

class Payment {

	private $order;
	private $payment;

	public function __construct() {
		$this->order = new Orders();
		$this->payment = new PaymentModel();
	}

	public function init($params = '') {
		$action = isset($_POST['action']) ? $_POST['action'] : 'list';
		$action .= 'Action';
		$this->$action();
	}
	
	public function __call($method = '', $args = '') {
		error_404();
	}
	
	public function listAction() {
		$id = $_SESSION['order_id'];
		
		$order = $this->order->getById($id);
		$payment = $this->payment->getById($order['payment_id'])[0];
		
		redirect_301(URL . '/payment-' . $payment['name_url'] . '.html');		
	}
	
}

$Payment = new Payment();
$Payment->init();
