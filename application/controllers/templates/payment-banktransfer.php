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

class BankTransfer {

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
		if(!isset($_SESSION['order_id'])) { // czy w sesji jest ID nowego zamowienia
			Cms::getFlashBag()->add('info', 'Brak ID zamówienia');
			return false;
		}
		if(!$order = $this->order->getById($_SESSION['order_id'])) { // czy w bazie jest zamoweinie
			Cms::getFlashBag()->add('info', 'Brak zamówienia w systemie.');
			return false;
		}
		if($order['status_id'] != 1) { // czy nie oplacone
			Cms::getFlashBag()->add('info', 'Zamówienie już opłacone.');
			return false;
		}

		$payment = $this->payment->getById($order['payment_id'])[0];			
		if($payment['name_url'] != 'banktransfer') { // wybrano inna metode platnosci
			Cms::getFlashBag()->add('info', 'Wybrano inna metode platnosci.');
			return false;
		}

		$data = array(
			'order'	=>	$order,
			'payment' => $payment,
			'title' => 'Bank transfer'

		);

		echo Cms::$twig->render('templates/payment/payment-succeed.twig', $data);			
	}
  
	
}

$controller = new BankTransfer();
$controller->init();













//
//
//// obsluga komunikacji paypal serwis po dokonaniu platnosci
//// read the post from PayPal system and add 'cmd'
//$req = 'cmd=_notify-validate';
//$msg = '';
//define('EMAIL_PAYPAL', 'payments@vitamin-shop.co.uk');
//
////$_POST = unserialize('a:39:{s:8:"mc_gross";s:4:"5.20";s:22:"protection_eligibility";s:8:"Eligible";s:14:"address_status";s:9:"confirmed";s:8:"payer_id";s:13:"WBHV6KKBFXXT4";s:3:"tax";s:4:"0.00";s:14:"address_street";s:21:"107 Sandringham Court";s:12:"payment_date";s:25:"00:21:56 Mar 11, 2011 PST";s:14:"payment_status";s:9:"Completed";s:7:"charset";s:12:"windows-1252";s:11:"address_zip";s:7:"SL1 6JU";s:10:"first_name";s:8:"Jaroslaw";s:6:"mc_fee";s:4:"0.38";s:20:"address_country_code";s:2:"GB";s:12:"address_name";s:17:"Jaroslaw Cybulski";s:14:"notify_version";s:3:"3.1";s:6:"custom";s:0:"";s:12:"payer_status";s:8:"verified";s:8:"business";s:27:"payments@vitamin-shop.co.uk";s:15:"address_country";s:14:"United Kingdom";s:12:"address_city";s:7:"Burnham";s:8:"quantity";s:1:"1";s:11:"verify_sign";s:56:"AFUhKvG.hUdp5nbFdrFGZHB7E92YAI9y31w3HbZxIr78riF6HKSzHAqX";s:11:"payer_email";s:24:"donpascal@poczta.onet.pl";s:6:"txn_id";s:17:"23H11232XT330954C";s:12:"payment_type";s:7:"instant";s:9:"last_name";s:8:"Cybulski";s:13:"address_state";s:9:"Berkshire";s:14:"receiver_email";s:27:"payments@vitamin-shop.co.uk";s:11:"payment_fee";s:0:"";s:11:"receiver_id";s:13:"EQB7FT9UHM4U6";s:8:"txn_type";s:10:"web_accept";s:9:"item_name";s:29:"Vitamin Shop, zamowienie 4173";s:11:"mc_currency";s:3:"GBP";s:11:"item_number";s:0:"";s:17:"residence_country";s:2:"GB";s:15:"handling_amount";s:4:"0.00";s:19:"transaction_subject";s:29:"Vitamin Shop, zamowienie 4173";s:13:"payment_gross";s:0:"";s:8:"shipping";s:4:"0.00";}');
////dump($_POST);
////$fp = fopen(CMS_DIR.'/log/paypal-'.date('Y-m-d_H-i-s').'.txt', 'w');   // logi
////$content = serialize($_POST);      
////fwrite($fp, $content);
////fclose($fp);
//
//if (isset($_POST['txn_id'])) {
//	foreach ($_POST as $key => $value) {
//		$value = urlencode(stripslashes($value));
//		$req .= "&$key=$value";
//	}
//
//	// post back to PayPal system to validate
//	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
//	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
//	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
//	$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);
//
//	// assign posted variables to local variables
//	$item_name = $_POST['item_name'];
//	$item_number = $_POST['item_number'];
//	$payment_status = $_POST['payment_status'];
//	$payment_amount = $_POST['mc_gross'];
//	$payment_currency = $_POST['mc_currency'];
//	$txn_id = $_POST['txn_id'];
//	$receiver_email = $_POST['receiver_email'];
//	$payer_email = $_POST['payer_email'];
//
//	if ($fp) {
//		fputs($fp, $header . $req);
//		while (!feof($fp)) {
//			$res = fgets($fp, 1024);
//			if (strcmp($res, "VERIFIED") == 0)
//				$msg = "NO VERIFIED";
//			if ($res == 'VERIFIED') {
//				if ($payment_status == "Completed") {
//					if (EMAIL_PAYPAL == $receiver_email) {
//						$id_zam = str_replace("Vital Max order ", "", $item_name);
//
//						// pobieramy zamowienie
//						$q = "SELECT * FROM `" . DB_PREFIX . "shop_orders` WHERE `id`='" . $id_zam . "' ";
//						$zam = Cms::$db->getRow($q);
//						$sum = round(($zam['price'] - $zam['price'] * $zam['discount'] / 100 + $zam['transport_price']), 2);
//						if ($sum == $payment_amount) {
//							// płatność przyjęta
//							$q = "UPDATE `" . DB_PREFIX . "shop_orders` SET `status_id`='2', `date_payment`=NOW() WHERE `id`='" . $id_zam . "' LIMIT 1 ";
//							Cms::$db->update($q);
//							saveHistory('o', 'PayPal', $id_zam, 16);
//
//							// naliczenie punktów z programu lojalnościowego
//							require_once(CMS_DIR . '/application/models/shopLojal.php');
//							$oLojal = new Lojal();
//							$data['id_user'] = $zam['customer_id'];
//							$data['login'] = 'online';
//							$data['date'] = date("Y-m-d H:i:s");
//							$data['source'] = 'o-' . $zam['id'];
//							$data['info'] = 'Order ' . $zam['id'];
//							$data['amount'] = $zam['price'];   // bez transportu
//							$data['barcode'] = '';
//							$data['hash'] = '';
//							// obliczanie hash'a niepotrzebne
//							$oLojal->naliczPunkty($data, true);
//
//							//mail
//							require_once(CMS_DIR . '/application/models/mailer.php');
//							$oMailer = new Mailer();
//
//							$oMailer->Subject = "PayPal payment";
//							$oMailer->Body = "Pay Pal payment for " . $id_zam . " received ";
//							$oMailer->CharSet = 'utf-8';
//							$oMailer->isHTML(true);
//							$oMailer->AddAddress(EMAIL_ADMIN, EMAIL_ADMIN);
//							$oMailer->Send();
//							$msg = "Your payment has been successful.";
//						} else {
//							$msg = "Email or amount incorrect";
//						}
//					} else {
//						$msg = "Merchant email incorrect";
//					}
//				} else {
//					$msg = "Status other then completed";
//				}
//			} else {
//				if (strcmp($res, "INVALID") == 0) {
//					// log for manual investigation
//					$msg = "Information from Pay Pal (invalid) please check in your Order Account";
//				} else {
//					$msg = "Information from Pay Pal (No Data) please check in your Order Account";
//				}
//			}
//		}
//
//		$q = "INSERT INTO `" . DB_PREFIX . "reports_paypal` SET `txn_id`='" . $txn_id . "', `post`='" . serialize($_POST) . "', `msg`='" . $msg . "' ";
//		Cms::$db->insert($q);
//		fclose($fp);
//	} else {
//		$msg = "HTTP ERROR";
//	}
//} else {
//	$msg = 'Transaction data incorrect.';
//}
//
//Cms::$tpl->showInfo($msg);
//
