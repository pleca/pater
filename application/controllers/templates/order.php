<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}
if (Cms::$modules['shop'] != 1) {
	die('This module is disabled!');
}

require_once(MODEL_DIR . '/BasketModel.php');
require_once(MODEL_DIR . '/shopOrders.php');
require_once(MODEL_DIR . '/PaymentModel.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(CONTROL_DIR . '/classes/Transport.php');
require_once(MODEL_DIR . '/phrase.php');

class Order {

	private $basket;
	private $order;
	private $payment;
	private $product;
	private $transport;
    private $customer;
    private $phrase;

	public function __construct() {
		$this->basket = new BasketModel();
		$this->order = new Orders();
		$this->payment = new PaymentModel();
		$this->product = new Products();
		$this->transport = new TransportController();
        $this->phrase = new Phrase();
        $this->customer = new Customer();
	}

	public function init($params = '') {
		$action = isset($_POST['action']) ? $_POST['action'] : 'list';
		$action .= 'Action';
		$this->$action();
	}
	
	public function __call($method = '', $args = '') {
		error_404();
	}
	
//    protected function usePhrase() {
//        // dopisuje do bazy użycie frazy promocyjnej, o ile ta fraza istnieje
//        if(isset($_SESSION[CUSTOMER_CODE]['promotion_code']))
//        {
//            $customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : '';
//            $this->phrase->usePhrase($_SESSION[CUSTOMER_CODE]['promotion_code'], $_SESSION['order_id'], $customer_id);
//            $customer = $this->customer->loadById($customer_id);
//
//            $_SESSION[CUSTOMER_CODE]['discount'] = $customer['discount'];
//            $_SESSION[CUSTOMER_CODE]['promotion_code'] = '';
//        }        
//    }
            
	public function saveAction() {     
                
		if($this->order->checkAccept($_POST)) {
			
			if (!$basket = $this->getBasket()) {	
				echo Cms::$twig->render('templates/basket/empty.twig');
				return false;
			}
                        
			$data = $_SESSION['order'];		
			$summary = $this->basket->getSummary($basket);
//			$delivery = $this->transport->getServiceOptionById($data['delivery_service']);
     
			$data['delivery_service'] = isset($data['delivery_service']) ? $data['delivery_service'] : 0;
			$delivery = $this->basket->getDelivery($data['delivery_service'], $data['payment']);		
		    
			if($id = $this->order->add($data, $basket, $summary, $delivery)) {
				$_SESSION['order_id'] = $id;	
                
                $this->order->sendEmailOrderAdd($id);
                
				unset($_SESSION['order']);

				$this->deleteBasketByCustomerOrSession();

				redirect_301(URL . '/payment.html');
			} else {			
				$this->listAction();
			}					
		} else {
			$this->listAction();
		}
	}

	public function listAction() {		
		if(!isset($_SESSION['order'])) {
			redirect_301(URL . '/basket.html');
		}
		if (!$basket = $this->getBasket()) {	
			echo Cms::$twig->render('templates/basket/empty.twig');
			return false;
		}
        
		$data = $_SESSION['order'];                		
  
		$payment = $this->payment->getById($data['payment'])[0];
		$shippingCountry = $this->transport->getCountryById($data['shipping_country']);
		$country = $this->transport->getCountryById($data['country']);
		$addressBilling = $this->getAddressBilling($data);
        
        if (CMS::$modules['shopping_thresholds']) {
            $shoppingThresholdInfo = Cms::$shoppingThresholds->getInfo();
            
            foreach($basket as &$v) {
                if ($shoppingThresholdInfo && isset($shoppingThresholdInfo['hasThreshold'])) {
                    if (!$v['mega_offer']) {
                        $v['price_after_discount'] = formatPrice($v['price_gross'] - $v['price_gross'] * $shoppingThresholdInfo['hasThreshold']['discount'] / 100);
                    } else {
                        $v['price_after_discount'] = $v['price_gross'];
                    }

                    $v['sum'] = formatPrice($v['qty'] * $v['price_after_discount']); 
                }
            }   
        }
        
		$data['delivery_service'] = isset($data['delivery_service']) ? $data['delivery_service'] : 0;
        
		$delivery = $this->basket->getDelivery($data['delivery_service'], $payment['id']);        
        $summary = $this->basket->getSummary($basket, $delivery['price_gross']);        
        $sortedBasket = arrayOrderByKey($basket, 'mega_offer', SORT_ASC); //mega offer products go last
				
		$params['basket'] = $sortedBasket;
		$params['shippingCountry'] = $shippingCountry;
		$params['country'] = $country;
		$params['delivery'] = $delivery;
		$params['payment'] = $payment;
		$params['summary'] = $summary;
		$params['addressBilling'] = $addressBilling;
//		$params['addressDelivery'] = $addressDelivery;        
        $deliveryPrice = $this->basket->getDeliveryPrice($data['delivery_service'], $payment['id']);
        
        if (isset($_SESSION[CUSTOMER_CODE]['discount_total'])) {
            $params['summaryTotal'] = formatPrice($_SESSION[CUSTOMER_CODE]['discount_total'] + $deliveryPrice);
        }        

        if (CMS::$modules['shopping_thresholds']) {
            $params['shoppingThresholdInfo'] = $shoppingThresholdInfo;
			echo Cms::$twig->render('templates/order/list-shopping-threshold.twig', $params);
        } else {
			echo Cms::$twig->render('templates/order/list.twig', $params);
        }
	}
	
	public function getBasket() {
		if($items = $this->basket->getByCustomerOrSession()) {
			$items = $this->basket->decoratorItems($items);
			foreach($items as &$v) {
				$product = $this->product->getById($v['product_id'], $v['variation_id']);
				$v['available'] = $product['qty'];
				$v['sku'] = $product['sku'];
				$v['ean'] = $product['ean'];
				$v['price_purchase'] = $product['price_purchase'];
				$v['url'] = $product['url'];
				$v['image'] = isset($product['photo']['small']) ? $product['photo']['small'] : false;
                $v['mega_offer'] = $product['mega_offer'];
			}
			return $items;
		}
		return false;
	}
	
	public function getAddressBilling($data = []) {		
		if ($data) {
			$item['type'] = $data['type'];
			$item['company_name'] = $data['company_name'];
			$item['nip'] = $data['nip'];
			$item['first_name'] = $data['first_name'];
			$item['last_name'] = $data['last_name'];
			$item['address1'] = $data['address1'];
			$item['address2'] = $data['address2'];
			$item['address3'] = $data['address3'];
			$item['post_code'] = $data['post_code'];
			$item['city'] = $data['city'];
			$item['country'] = $data['country'];
			$item['email'] = $data['email'];
			$item['phone'] = $data['phone'];	
            
            $item['other_shipping'] = isset($data['other_shipping']) ? $data['other_shipping'] : 0;
			$item['shipping_type'] = $data['shipping_type'];
			$item['shipping_company_name'] = $data['shipping_company_name'];
			$item['shipping_first_name'] = $data['shipping_first_name'];
			$item['shipping_last_name'] = $data['shipping_last_name'];
			$item['shipping_address1'] = $data['shipping_address1'];
			$item['shipping_address2'] = $data['shipping_address2'];
			$item['shipping_address3'] = $data['shipping_address3'];
			$item['shipping_post_code'] = $data['shipping_post_code'];
			$item['shipping_city'] = $data['shipping_city'];
			$item['shipping_country'] = $data['shipping_country'];
			$item['shipping_phone'] = $data['shipping_phone'];            
			
			return $item;
		} 
		return false;
	}
	
    public function deleteBasketByCustomerOrSession() {
		if (!$basket = $this->basket->getByCustomerOrSession()) {		
			return false;
		}
		foreach ($basket as $v) {
			$this->basket->deleteById($v['id']);
		}
		return true;        
    }
	
}

$Order = new Order();
$Order->init();



















/* 2015-10-14 | 4me.CMS 15.3 */

/* 2012-08-03 | integracja platnosci PAYPAL
  modyfikowane pliki:
  \application\controllers\templates\order.php
  \application\languages\enSite.php
  \application\languages\plSite.php
  \application\views\templates\header.tpl
  \application\views\templates\shop\finish.tpl

  dodane pliki:
  \library\cardinal\
  \library\paypal\
  \application\controllers\templates\mcs_learn_more.php
  \application\controllers\templates\vbv_learn_more.php
  \application\views\templates\paypal\
  \public\scripts\jquery.tipTip.js
  \public\styles\tipTip.css

  zmiany w bazie zrzuty zapytan
  ALTER TABLE `shop_orders` ADD `paypal_respone` TEXT NOT NULL ;
  INSERT INTO `shop_payment` (`id`, `name`, `order`, `active`) VALUES ('5', 'PayPal', '4', '0'), ('6', 'CreditCard 	', '3', '0');

  ustawienia:
  \library\paypal\constants.php
  \library\cardinal\lib\curl\CentinelConfig.php
  https://paypal.cardinalcommerce.com/MerchAdmin/viewprofile.asp - tutaj ustawiamy haslo
  https://www.paypal.com/uk/cgi-bin/webscr?cmd=_profile-api-signature - api access
 */

//if (!defined('NO_ACCESS'))
//	die('No access to files!');
//
//if (Cms::$modules['orders'] != 1)
//	die;
//
//require_once(LIB_DIR . '/paypalApi/class.PayPalApi.php');
//$oPayPal = new PayPalApi();
//$sExceptionError = 'There was problem with our system. Please contact with us in order to resolve it.';
//
//require_once(LIB_DIR . '/cmsLogger.php');
//$oLog = new cmsLogger('cms', cmsLogger::DEBUG);
//
//require_once(MODEL_DIR . '/shopOrders.php');
//$oOrders = new Orders();
//
//if (!isset($_SESSION['customer']['access']) AND ! $oCustomer->logged()) { // formularz z danymi jesli nie zalogowani lub nie wprowadzilismy formularza
//	if (isset($_SERVER['HTTP_USER_AGENT']) AND strpos($_SERVER['HTTP_USER_AGENT'], "Safari") == true AND strpos($_SERVER['HTTP_USER_AGENT'], "Chrome") === false) {
//		Cms::$tpl->assign('redirect', true);
//	} else {
//		redirect_301(URLS . '/customer/login2.html');
//		die;
//	}
//}
//if (isset($_SESSION['customer']['access']) AND $_SESSION['customer']['access'] == 'without_login') { // jesli mam dane z formualrza to wrzucamy je do szablonu
//	Cms::$tpl->assign('customer', $_SESSION['customer']);
//}
//if (isset($_POST['action']) AND $_POST['action'] == 'promotion_code') {
//	require_once(CMS_DIR . '/application/models/phrase.php');
//	$oPhrase = new Phrase();
//
//	if ($discount = $oPhrase->getDiscount($_POST['promotion_code'])) {
//		$_SESSION['customer']['discount'] = $discount;
//		$_SESSION['customer']['promotion_code'] = $_POST['promotion_code'];
//	} else {
//		$customer = $oCustomer->loadById($_SESSION['customer']['id']);
//		$_SESSION['customer']['discount'] = $customer['discount'];
//		$_SESSION['customer']['promotion_code'] = '';
//	}
//
//	// > PAYPAL
//	$_SESSION['ppCustomerDiscount'] = $_SESSION['customer']['discount'];
//
//	showOrder();
//} elseif (isset($_POST['action']) AND $_POST['action'] == 'save_order') {
//	Cms::$tpl->assign('customer', $_POST);   // dane klienta z formualrza
//
//	$aBasket = $oBasket->loadBasketOrder();
//	$basketInfo = $oBasket->getInfo();
//
//	if (isset($_POST['transport']) AND $_POST['transport']) {
//		$transport = $oOrders->getTransport($_POST['transport'], $basketInfo['sum']);
//
//		if ($data = $oOrders->add($_POST, $aBasket, $basketInfo, $transport)) {  // dodano zamowenienie do systmeu
//			// dopisuje do bazy użycie frazy promocyjnej, o ile ta fraza istnieje
//			if (isset($_SESSION['customer']['promotion_code'])) {
//				require_once(CMS_DIR . '/application/models/phrase.php');
//				$oPhrase = new Phrase();
//				$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
//				$oPhrase->usePhrase($_SESSION['customer']['promotion_code'], $data['id'], $customer_id);
//				$customer = $oCustomer->loadById($customer_id);
//				$_SESSION['customer']['discount'] = $customer['discount'];
//				$_SESSION['customer']['promotion_code'] = '';
//			}
//
//			$oBasket->updateProducts($aBasket);
////         $oBasket -> deleteBasketAfterOrder();
////         
//			// nowy paypal
//
//			$oPayPal->setOrderId($data['id']);
//			$oPayPal->setOrderPriceTotal($data['total']);
//			$oPayPal->setOrderTransportPrice($transport['price']);
//
//			// \ nowy paypal
//			// > PAYPAL
//
//			$_SESSION['ppAmount'] = $data['total'];
//			$_SESSION['ppData'] = $data;
//			$_SESSION['ppTransport'] = $transport;
//
//			showFinish($data, $transport);
//		} else {
//			showOrder();
//		}
//	} else {
//		Cms::$tpl->setError($GLOBALS['LANG']['order_deliver2']);
//		showOrder();
//	}
//}
//
//// > PAYPAL
//elseif ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'centinel')) {
//
//	try {
//
//		if (!isset($_SESSION['ppData']) || !isset($_SESSION['ppTransport'])) {
//			redirectBrowser(URLS . '/basket.html');
//			die();
//		}
//
//		if (isset($_REQUEST['action_type']) && $_REQUEST['action_type'] == "ACSForm") {
//
//			$sAcsForm = $oPayPal->getACSForm();
//			Cms::$tpl->assign('sAcsForm', $sAcsForm);
//			Cms::$tpl->showPage('paypal/centinel_acs.tpl');
//		} elseif (isset($_REQUEST['action_type']) && $_REQUEST['action_type'] == "ACSResp") {
//
//			$sPayPalTransactionId = $oPayPal->processCentinelACSResponse();
//
//			if (!$sPayPalTransactionId) {
//
//				if ($oPayPal->isError()) {
//
//					sendPaymentEmail($oCore, "Błąd w płatności kartą", $oPayPal->getErrors()[0], [EMAIL_ADMIN]);
//
//					showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $oPayPal->getErrors()[0]);
//					die();
//				} else {
//
//					$oLog->LogError("There was error in PayPalApi but wasn't predicted! centinelResult: {$sPayPalTransactionId} - " . __FILE__ . ':' . __LINE__);
//					showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//					die();
//				}
//			} else {
//
//				$id_zam = $oPayPal->getOrderId();
//				$q = "SELECT * FROM `" . DB_PREFIX . "shop_orders` WHERE `id`='" . $id_zam . "' ";
//				$zam = Cms::$db->getRow($q);
//
//				// płatność przyjęta
//				$q = "UPDATE `" . DB_PREFIX . "shop_orders` SET `status_id`='2', `date_payment`=NOW(),`paypal_respone`='" . $sPayPalTransactionId . "' WHERE `id`='" . $id_zam . "' LIMIT 1 ";
//				Cms::$db->update($q);
//
//				sendPaymentEmail($oCore, "PayPal payment", "Pay Pal payment for " . $id_zam . " received ", [EMAIL_ADMIN]);
//
//				unset($_SESSION['ppData']);
//				unset($_SESSION['ppAmout']);
//				unset($_SESSION['ppTransport']);
//				unset($_SESSION['pp_postData']);
//
//				$oBasket->deleteBasketAfterOrder();
//
//				if (LOGGED == 1)
//					redirectBrowser(URLS . '/customer/order/' . md5($zam['id'] . $zam['date_add']));
//				else {
//					showSuccess();
//				}
//			}
//		} else {
//
//			$centinelResult = $oPayPal->processCentinel($_POST);
//
//			if (!$centinelResult) {
//
//				if ($oPayPal->isError()) {
//
//					showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $oPayPal->getErrors()[0]);
//				} else {
//
//					$oLog->LogError("There was error in PayPalApi but wasn't predicted! centinelResult: {$centinelResult} - " . __FILE__ . ':' . __LINE__);
//					showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//					die();
//				}
//			} else {
//
//				switch ($centinelResult) {
//
//					case 'redirectACSForm': {
//
//							header("Location: " . URLS . '/order.html?action=centinel&action_type=ACSForm');
//						} break;
//
//					case 'redirectDirect': {
//
//							header("Location: " . URLS . '/order.html?action=website_payments_direct');
//						} break;
//
//					default: {
//
//							$oLog->LogError("There was error in PayPalApi but wasn't predicted! centinelResult: {$centinelResult} - " . __FILE__ . ':' . __LINE__);
//							showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//							die();
//						}
//				}
//			}
//		}
//	} catch (Exception $ex) {
//
//		$oLog->LogError(print_r($ex, true));
//		showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//	}
//	/*
//	 * CARDINAL AUTH END
//	 */
//} elseif (isset($_REQUEST['action']) && isset($_SESSION['pp_postData']) && $_REQUEST['action'] == 'website_payments_direct') {
//
//	require_once(CMS_DIR . '/application/models/mailer.php');
//
//	$oMailer = new Mailer();
//	$oMailer->CharSet = 'utf-8';
//	$oMailer->isHTML(true);
//	$oMailer->AddAddress("test@vitamin-shop.co.uk", "test@vitamin-shop.co.uk");
//
//	try {
//
//		$sPayPalTransactionId = $oPayPal->processDirect();
//
//		if (!$sPayPalTransactionId) {
//
//			if ($oPayPal->isError()) {
//
//				sendPaymentEmail($oCore, "Błąd w płatności kartą", $oPayPal->getErrors()[0], [EMAIL_ADMIN]);
//
//				showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $oPayPal->getErrors()[0]);
//				die();
//			} else {
//
//				$oLog->LogError("There was error in PayPalApi but wasn't predicted! centinelResult: {$sPayPalTransactionId} - " . __FILE__ . ':' . __LINE__);
//				showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//				die();
//			}
//		} else {
//
//			$id_zam = $oPayPal->getOrderId();
//			$q = "SELECT * FROM `" . DB_PREFIX . "shop_orders` WHERE `id`='" . $id_zam . "' ";
//			$zam = Cms::$db->getRow($q);
//
//			// płatność przyjęta
//			$q = "UPDATE `" . DB_PREFIX . "shop_orders` SET `status_id`='2', `date_payment`=NOW(),`paypal_respone`='" . $sPayPalTransactionId . "' WHERE `id`='" . $id_zam . "' LIMIT 1 ";
//			Cms::$db->update($q);
//
//			sendPaymentEmail($oCore, "PayPal payment", "Pay Pal payment for " . $id_zam . " received ", [EMAIL_ADMIN]);
//
//			unset($_SESSION['ppData']);
//			unset($_SESSION['ppAmout']);
//			unset($_SESSION['ppTransport']);
//			unset($_SESSION['pp_postData']);
//
//			$oBasket->deleteBasketAfterOrder();
//
//			if (LOGGED == 1)
//				redirectBrowser(URLS . '/customer/order/' . md5($zam['id'] . $zam['date_add']));
//			else {
//				showSuccess();
//			}
//		}
//	} catch (Exception $ex) {
//
//		$oLog->LogError(print_r($ex, true));
//		showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//	}
//} elseif (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'website_payments_express') {
//
//	require_once(CMS_DIR . '/application/models/mailer.php');
//
//	$oMailer = new Mailer();
//	$oMailer->CharSet = 'utf-8';
//	$oMailer->isHTML(true);
//	$oMailer->AddAddress("test@vitamin-shop.co.uk", "test@vitamin-shop.co.uk");
//
//	$aBasket = $oBasket->loadBasketOrder();
//	$basketInfo = $oBasket->getInfo();
////dump($_REQUEST, 'request');
////die;
//
//	try {
//
//		foreach ($aBasket as $item) {
//
//			if (!isset($item['price_promotion_gross']))
//				$oPayPal->setExpressProduct($item['id'], $item['title'], $item['price_gross'], $item['amount']);
//			else
//				$oPayPal->setExpressProduct($item['id'], $item['title'], $item['price_promotion_gross'], $item['amount']);
//		}
//
//		// discount
//		//$nDiscount = round((($_SESSION['ppData']['sum'] * $_SESSION['ppData']['discount'] / 100)), 2);
//		// zeby nie bylo problemu z liczeniem znizki jest ona po prostu obliczana z ceny total - transport
//		$nSumWithDicount = $_SESSION['ppData']['total'] - $_SESSION['ppTransport']['price'];
//		$nDiscount = $_SESSION['ppData']['sum'] - $nSumWithDicount;
//
//		if ($nDiscount > 0) {
//			$oPayPal->setOrderDiscountPrice($nDiscount);
//		}
//
//		$sReturnUrl = 'https://' . $_SERVER['SERVER_NAME'] . '/order.html?action=website_payments_express';
//		$sCancelUrl = 'https://' . $_SERVER['SERVER_NAME'] . '/order.html';
//		$sLogoUrl = 'http://www.vital-max.co.uk/public/img/logo.png';
//
//		$sPayPalTransactionId = $oPayPal->processExpress($sReturnUrl, $sCancelUrl, $sLogoUrl);
//
//		if (!$sPayPalTransactionId) {
//
//			if ($oPayPal->isError()) {
//
//				sendPaymentEmail($oCore, "Błąd w płatności kartą", $oPayPal->getErrors()[0], [EMAIL_ADMIN]);
//
//				showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $oPayPal->getErrors()[0]);
//				die();
//			} else {
//
//				$oLog->LogError("There was error in PayPalApi but wasn't predicted! centinelResult: {$sPayPalTransactionId} - " . __FILE__ . ':' . __LINE__);
//				showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//				die();
//			}
//		} else {
//
//			$id_zam = $_SESSION['ppData']['id'];
//
//			// pobieramy zamowienie
//			$q = "SELECT * FROM `" . DB_PREFIX . "shop_orders` WHERE `id`='" . $id_zam . "' ";
//			$zam = Cms::$db->getRow($q);
//
//			// płatność przyjęta
//			$q = "UPDATE `" . DB_PREFIX . "shop_orders` SET `status_id`='2', `date_payment`=NOW(),`paypal_respone`='" . $sPayPalTransactionId . "' WHERE `id`='" . $id_zam . "' LIMIT 1 ";
//			Cms::$db->update($q);
//
//			sendPaymentEmail($oCore, "PayPal payment", "Pay Pal payment for " . $id_zam . " received ", [EMAIL_ADMIN]);
//
//			unset($_SESSION['ppData']);
//			unset($_SESSION['ppAmout']);
//			unset($_SESSION['ppTransport']);
//			$oBasket->deleteBasketAfterOrder();
//
//			if (LOGGED == 1)
//				redirectBrowser(URLS . '/customer/order/' . md5($zam['id'] . $zam['date_add']));
//			else {
//				showSuccess();
//			}
//		}
//	} catch (Exception $ex) {
//
//		$oLog->LogError(print_r($ex, true));
//		showFinish($_SESSION['ppData'], $_SESSION['ppTransport'], $sExceptionError);
//	}
//}
//// < PAYPAL
//else {
//	showOrder();
//}
//
//function showOrder() {
//	global $oBasket, $oOrders, $oLojal, $oCustomer;
//
//	if (!$aBasket = $oBasket->loadBasket()) {  //sprawdzamy czy koszyk nie jest pusty
//		Cms::$tpl->showInfo($GLOBALS['LANG']['basket_empty']);
//	}
//
//	$basketInfo = $oBasket->getInfo();
//	$aPayment = $oOrders->loadPayment();
//	$country = $oCustomer->loadCountry();
//
//	// jesli mamy rabat uzytkowanika lub frazy promocyjnej
//	if (isset($_SESSION['customer']['discount']) AND $_SESSION['customer']['discount'] > 0) {
//
//		$sum = $basketInfo['sum'];
//		$discount['discount'] = $_SESSION['customer']['discount'];
//		$discount['sum'] = formatPrice($sum - $sum * $discount['discount'] / 100);
//		$discount['saving'] = formatPrice($sum - $discount['sum']);
//		$discount['total'] = formatPrice($discount['sum']);
//		Cms::$tpl->assign('discount', $discount);
//	}
//
//	Cms::$tpl->assign('aBasket', $aBasket);
//	Cms::$tpl->assign('aPayment', $aPayment);
//	Cms::$tpl->assign('country', $country);
//	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['order_title'] . ' - ' . Cms::$seo['title']);
//	Cms::$tpl->assign('pageKeywords', Cms::$seo['meta_keywords']);
//	Cms::$tpl->assign('pageDescription', Cms::$seo['meta_description']);
//	Cms::$tpl->showPage('shop/order.tpl');
//}
//
//function showFinish($data, $transport, $error = false) {
//	global $oPayPal;
//
//	// > PAYPAL
//	if ($data['payment']['id'] == 1 && isset($aPayPal_CONF)) {
//		$aPP = array();
//
//		$aPP['firstName'] = //$data['first_name'];
//				$aPP['lastName'] = //$data['last_name'];
//				$aPP['creditCardType'] = '';
//		$aPP['creditCardNumber'] = '';
//		$aPP['expDateMonth'] = '';
//		$aPP['expDateYear'] = '';
//		$aPP['cvv2Number'] = '';
//		$aPP['address1'] = //$data['address1'];
//				$aPP['address2'] = //$data['address2'];
//				$aPP['city'] = //$data['city'];
//				$aPP['state'] = '';
//		$aPP['zip'] = //trim($data['post_code1']) . trim($data['post_code2']);	
//				Cms::$tpl->assign('aPP', $aPP);
//	}
//	Cms::$tpl->assign('finish', true);
//	// < PAYPAL
//
//	if ($error)
//		Cms::$tpl->assign('error', $error);
//
//	$sPayPalCreditCardUrl = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '/order.html?action=centinel';
//	$sPayPalExpressUrl = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . '/order.html?action=website_payments_express';
//
//	Cms::$tpl->assign('sFormPayPalCreditCard', $oPayPal->getCreditCardForm($sPayPalCreditCardUrl));
//	Cms::$tpl->assign('sFormPayPalExpress', $oPayPal->getExpressForm($sPayPalExpressUrl));
//
//	Cms::$tpl->assign('basketInfo', false);
//	Cms::$tpl->assign('data', $data);
//	Cms::$tpl->assign('transport', $transport);
//	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['order_title'] . ' - ' . Cms::$seo['title']);
//	Cms::$tpl->assign('pageKeywords', Cms::$seo['meta_keywords']);
//	Cms::$tpl->assign('pageDescription', Cms::$seo['meta_description']);
//	Cms::$tpl->showPage('shop/finish.tpl');
//}
//
//// > PAYPAL
//function showSuccess() {
//	Cms::$tpl->showPage('paypal/success.tpl');
//}
//
//// < PAYPAL  
//
//function sendPaymentEmail($oCore, $subject, $body, $aEmails) {
//
//	require_once(CMS_DIR . '/application/models/mailer.php');
//	$oMailer = new Mailer();
//
//	$oMailer->Subject = $subject;
//	$oMailer->Body = $body;
//	$oMailer->CharSet = 'utf-8';
//	$oMailer->isHTML(true);
//
//	foreach ($aEmails as $email) {
//		$oMailer->AddAddress($email, $email);
//	}
//
//	$oMailer->Send();
//}
