<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}
if (Cms::$modules['shop'] != 1) {
	die('This module is disabled!');
}

require_once(MODEL_DIR . '/BasketModel.php');
require_once(MODEL_DIR . '/customer.php');
require_once(MODEL_DIR . '/PaymentModel.php');
require_once(MODEL_DIR . '/shopOrders.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(MODEL_DIR . '/phrase.php');
require_once(CONTROL_DIR . '/classes/Transport.php');
//require_once(MODEL_DIR . '/ShoppingThresholds.php');

class Basket {

	private $basket;
	private $customer;
	private $order;
	private $payment;
	private $product;
	private $transport;
	private $phrase;

	public function __construct() {
		$this->basket = new BasketModel();
		$this->customer = new Customer();
		$this->order = new Orders();
		$this->payment = new PaymentModel();
		$this->product = new Products();
		$this->transport = new TransportController();
        $this->phrase = new Phrase();
	}

	public function init($params = '') {
        $this->basket->processClearBasket();

		$action = isset($_POST['action']) ? $_POST['action'] : 'list';
		$action .= 'Action';
		$this->$action();
	}
	
	public function __call($method = '', $args = '') {
		error_404();
	}	
	
	public function loginAction() {

		$_POST['country'] = $_POST['delivery_country'];
		
		if ($this->customer->login($_POST)) {
			if (Cms::$modules['shop'] == 1) { // po zalogowaniu zapisujemy koszyk klienta
				if($items = $this->basket->getBySession()) {
					foreach($items as $v) {
						if($v['customer_id'] > 0) {
//							$this->basket->deleteById($v['id']);
						} else {
							$aFields = [];
							$aFields['customer_id'] = $_SESSION[CUSTOMER_CODE]['id'];
							$this->basket->updateById($v['id'], $aFields);
						}
					}
				}
			}
			redirect_301(URL . '/' . $GLOBALS['LANG']['url_basket'] . '.html');
		} else {
//            redirect(URL . '/' . $GLOBALS['LANG']['url_basket'] . '.html');
			$this->listAction();
		}
	}
	
	public function orderAction() {

		$_POST['shipping_country'] = $_POST['delivery_country'];

		if ($this->order->checkData($_POST)) {
			
			if (!$basket = $this->getBasket()) {		
                echo Cms::$twig->render('templates/basket/empty.twig');
				return false;
			}

			$_SESSION['order'] = $_POST;	

			redirect_301(URL . '/' . $GLOBALS['LANG']['url_order'] . '.html');
		} else {
			$this->listAction();
		}
	}

    protected function setDiscount() {
        $_POST['promotion_code'] = isset($_POST['promotion_code']) ? $_POST['promotion_code'] : '';

        if (isset($_POST['used_promotion_code']) && $_POST['used_promotion_code'] == 1) {        
            $discount = $this->phrase->getDiscount($_POST['promotion_code']);
            $_SESSION[CUSTOMER_CODE]['discount'] = $discount;
            
            if ($discount) {
                $_SESSION[CUSTOMER_CODE]['promotion_code'] = $_POST['promotion_code'];
            } else {
                $_SESSION[CUSTOMER_CODE]['promotion_code'] = '';
            }
            
        }
        
        if (isset($_SESSION[CUSTOMER_CODE]['id'])) {
            if (isset($_SESSION[CUSTOMER_CODE]['promotion_code']) && !$_SESSION[CUSTOMER_CODE]['promotion_code']) {
                $customer = $this->customer->loadById($_SESSION[CUSTOMER_CODE]['id']);
                $_SESSION[CUSTOMER_CODE]['discount'] = $customer['discount'];                
                $_SESSION[CUSTOMER_CODE]['promotion_code'] = '';   
            }
        }

        // jesli mamy rabat uzytkowanika lub frazy promocyjnej
        if (isset($_SESSION[CUSTOMER_CODE]['discount']) AND $_SESSION[CUSTOMER_CODE]['discount'] > 0) {
            
            if (isset($_SESSION['order']['delivery_service'])) {
                $deliveryOption = $this->transport->getServiceOptionById($_SESSION['order']['delivery_service']);
                $price = isset($deliveryOption['price_gross']) ? $deliveryOption['price_gross'] : 0;
                $summary = $this->basket->getSummary($this->getBasket(), $price);
            } else {
                $summary = $this->basket->getSummary($this->getBasket()); 
            }

            $total = $summary['total'];   
            $sum = $summary['sum'];   

//            $_SESSION[CUSTOMER_CODE]['discount_sum'] = formatPrice($total - $total * $_SESSION[CUSTOMER_CODE]['discount'] / 100);
//            $_SESSION[CUSTOMER_CODE]['discount_saving'] = formatPrice($total - $_SESSION[CUSTOMER_CODE]['discount_sum']);
            $_SESSION[CUSTOMER_CODE]['discount_sum'] = formatPrice($sum - $sum * $_SESSION[CUSTOMER_CODE]['discount'] / 100);
            $_SESSION[CUSTOMER_CODE]['discount_saving'] = formatPrice($sum - $_SESSION[CUSTOMER_CODE]['discount_sum']);
            $_SESSION[CUSTOMER_CODE]['discount_total'] = formatPrice($_SESSION[CUSTOMER_CODE]['discount_sum']);
        }
                
    }
    
	public function listAction() {        
		$params['title'] = $GLOBALS['LANG']['module_basket'];
		$params['pageTitle'] = $GLOBALS['LANG']['module_basket'] . ' - ' . Cms::$seo['title'];
        
		if (!$basket = $this->getBasket()) {	
            echo Cms::$twig->render('templates/basket/empty.twig', $params);
			return false;
		}        

        $this->setDiscount();        

		$summary = $this->basket->getSummary($basket);
		$country = $this->getCountry(); // wszystkie kraje
		$deliveryCountry = $this->getDeliveryCountry(); // kraje do ktorych mozemy wyslac towar
		$deliveryCountrySelected = $this->getDeliveryCountrySelected($deliveryCountry);
		$deliveryService = $this->getDeliveryService($deliveryCountrySelected, '', $summary['weight']); // dostepne uslugi | kraj, kod pocztowy, waga
		$delivery = $this->getDeliverySelect($deliveryService);
		$summary = $this->basket->getSummary($basket, $delivery['price_gross']);
		$payment = $this->getPayment();
		$addressBilling = $this->getAddressBilling();
		$addressDelivery = $this->getAddressDelivery();

		$params['country'] = $country;
		$params['deliveryCountry'] = $deliveryCountry;
		$params['deliveryService'] = $deliveryService;
		$params['payment'] = $payment;
		$params['summary'] = $summary;
		$params['addressBilling'] = $addressBilling;
		$params['addressDelivery'] = $addressDelivery;

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

        $sortedBasket = arrayOrderByKey($basket, 'mega_offer', SORT_ASC); //mega offer products go last
		$params['basket'] = $sortedBasket;

//        dump($params['basket']);
//			$b = new BasketModel;
//			$basket1 = $b->getBasketItems();
//			$b->calculateUnitTransportDelivery($basket1);
		
        if (CMS::$modules['shopping_thresholds']) {
			$params['shoppingThresholdInfo'] = $shoppingThresholdInfo;
			echo Cms::$twig->render('templates/basket/list-shopping-threshold.twig', $params);
        } else {
			echo Cms::$twig->render('templates/basket/list.twig', $params);
        }

				
	}
	
	public function getBasket() {
		if ($items = $this->basket->getByCustomerOrSession()) {
			$items = $this->basket->decoratorItems($items);
//dump($items);
			foreach($items as &$v) {
				$product = $this->product->getById($v['product_id'], $v['variation_id']);

				$v['available'] = $product['qty'];
				$v['sku'] = $product['sku'];
				$v['ean'] = $product['ean'];
				$v['price_purchase'] = $product['price_purchase'];
				$v['url'] = $product['url'];
				if(isset($product['photo']['small'])) $v['image'] = $product['photo']['small'];        
                $v['mega_offer'] = $product['mega_offer'];
			}
			return $items;
		}
		return false;
	}
	
	public function getCountry() {
		if($items = $this->transport->getAllCountry()) {
			foreach($items as $k => &$v) {
				$v['default'] = 0;
				if(isset($_POST['country'])) {
					if($_POST['country'] == $v['id']) {
						$v['default'] = 1;												
					}
				} else {
					if($k == 0) {
						$v['default'] = 1;
					}
				}
			}
			return $items;
		}
		return false;
	}
	
	public function getDeliveryCountry() {
		if($items = $this->transport->getAllDeliveryCountry()) {
			foreach($items as $k => &$v) {
				$v['default'] = 0;
				if(isset($_POST['delivery_country'])) {
					if($_POST['delivery_country'] == $v['id']) {
						$v['default'] = 1;												
					}
				} else {
					if (Cms::$conf['country_id_delivery'] == $v['id']) {
						$v['default'] = 1;
					}
				}
//				} else {
//					if($k == 0) {
//						$v['default'] = 1;
//					}
//				}
			}
			return $items;
		}
		return false;
	}
	
	public function getDeliveryCountrySelected($items = []) {
		if($items) {
			foreach($items as $v) {
				if($v['default'] == 1) {
					return $v['id'];
				}
			}
		}
		return 0;
	}
	
	public function getPayment() {
		if($items = $this->payment->getAll()) {
			foreach ($items as $k => &$v) {
				if($v['active'] != 1) {
					unset($items[$k]);
				} else {
					$v['default'] = 0;
					if(isset($_POST['payment'])) {
						if($_POST['payment'] == $v['id']) {
							$v['default'] = 1;												
						}
					} elseif (isset($_SESSION['order']['payment'])) {
						if($_SESSION['order']['payment'] == $v['id']) {
							$v['default'] = 1;												
						}
					} else {
						if($k == 0) {
							$v['default'] = 1;
						}
					}
				}
			}
			return $items;
		}
		return false;
	}
	
	public function getDeliveryService ($countryId = 0, $postcode = '', $weight = 0) {
		if($items = $this->transport->getAllDeliveryService($countryId, $postcode, $weight)) {
			foreach($items as $k => &$v) {
				$v['default'] = 0;
				if(isset($_POST['delivery_service'])) {
					if($_POST['delivery_service'] == $v['option_id']) {
						$v['default'] = 1;												
					}
				} elseif (isset($_SESSION['order']['delivery_service'])) {
					if($_SESSION['order']['delivery_service'] == $v['option_id']) {
						$v['default'] = 1;												
					}
				} else {
					if($k == 0) {
						$v['default'] = 1;
					}
				}
			}
			return $items;
		}
		return false;
	}
	
	public function getDeliverySelect($items = []) {
		if($items) {
			foreach($items as $v) {
				if($v['default'] == 1) {
					return $v;
				}
			}
		}
		return 0;
	}
	
	public function getAddressBilling() {	
		if (isset($_POST['action'])) {
			$item['type'] = $_POST['type'];
			$item['company_name'] = $_POST['company_name'];
			$item['nip'] = $_POST['nip'];
			$item['first_name'] = $_POST['first_name'];
			$item['last_name'] = $_POST['last_name'];
			$item['address1'] = $_POST['address1'];
			$item['address2'] = $_POST['address2'];
			$item['address3'] = $_POST['address3'];
			$item['post_code'] = $_POST['post_code'];
			$item['city'] = $_POST['city'];
			$item['country'] = isset($_POST['country']) ? $_POST['country']: $_POST['delivery_country'];
			$item['email'] = $_POST['email'];
			$item['phone'] = $_POST['phone'];	
            
            if (isset($_POST['other_shipping'])) {
                $item['other_shipping'] = 1;
            } else {
                $item['other_shipping'] = 0;
            }
			$item['shipping_type'] = $_POST['shipping_type'];
			$item['shipping_company_name'] = $_POST['shipping_company_name'];
			$item['shipping_nip'] = $_POST['shipping_nip'];
			$item['shipping_first_name'] = $_POST['shipping_first_name'];
			$item['shipping_last_name'] = $_POST['shipping_last_name'];
			$item['shipping_address1'] = $_POST['shipping_address1'];
			$item['shipping_address2'] = $_POST['shipping_address2'];
			$item['shipping_address3'] = $_POST['shipping_address3'];
			$item['shipping_post_code'] = $_POST['shipping_post_code'];
			$item['shipping_city'] = $_POST['shipping_city'];
			$item['shipping_country'] = isset($_POST['shipping_country']) ? $_POST['shipping_country']: $_POST['delivery_country'];
			$item['shipping_phone'] = $_POST['shipping_phone'];	  

		} elseif (isset($_SESSION['order'])) {

			$item['type'] = isset($_SESSION['order']['type']) ? $_SESSION['order']['type'] : 1;
			$item['company_name'] = $_SESSION['order']['company_name'];
			$item['nip'] = $_SESSION['order']['nip'];
			$item['first_name'] = $_SESSION['order']['first_name'];
			$item['last_name'] = $_SESSION['order']['last_name'];
			$item['address1'] = $_SESSION['order']['address1'];
			$item['address2'] = $_SESSION['order']['address2'];
			$item['address3'] = $_SESSION['order']['address3'];
			$item['post_code'] = $_SESSION['order']['post_code'];
			$item['city'] = $_SESSION['order']['city'];
			$item['country'] = $_SESSION['order']['country'];
			$item['email'] = $_SESSION['order']['email'];
			$item['phone'] = $_SESSION['order']['phone'];		
            
            $item['other_shipping'] = isset($_SESSION['order']['other_shipping']) ? $_SESSION['order']['other_shipping'] : 0;
			$item['shipping_type'] = $_SESSION['order']['shipping_type'];
			$item['shipping_company_name'] = $_SESSION['order']['shipping_company_name'];
			$item['shipping_nip'] = $_SESSION['order']['shipping_nip'];
			$item['shipping_first_name'] = $_SESSION['order']['shipping_first_name'];
			$item['shipping_last_name'] = $_SESSION['order']['shipping_last_name'];
			$item['shipping_address1'] = $_SESSION['order']['shipping_address1'];
			$item['shipping_address2'] = $_SESSION['order']['shipping_address2'];
			$item['shipping_address3'] = $_SESSION['order']['shipping_address3'];
			$item['shipping_post_code'] = $_SESSION['order']['shipping_post_code'];
			$item['shipping_city'] = $_SESSION['order']['shipping_city'];
			$item['shipping_country'] = $_SESSION['order']['shipping_country'];
			$item['shipping_phone'] = $_SESSION['order']['shipping_phone'];	       
//            dump($item);
		} elseif (isset($_SESSION[CUSTOMER_CODE]['id'])) {
			$item['type'] = $_SESSION[CUSTOMER_CODE]['type'];
//			$item['company_name'] = $_SESSION[CUSTOMER_CODE]['company_name'];
//			$item['nip'] = $_SESSION[CUSTOMER_CODE]['nip'];
//			$item['first_name'] = $_SESSION[CUSTOMER_CODE]['first_name'];
//			$item['last_name'] = $_SESSION[CUSTOMER_CODE]['last_name'];
//			$item['address1'] = $_SESSION[CUSTOMER_CODE]['address1'];
//			$item['address2'] = $_SESSION[CUSTOMER_CODE]['address2'];
//			$item['address3'] = $_SESSION[CUSTOMER_CODE]['address3'];
//			$item['post_code'] = $_SESSION[CUSTOMER_CODE]['post_code'];
//			$item['city'] = $_SESSION[CUSTOMER_CODE]['city'];
//			$item['country'] = $_SESSION[CUSTOMER_CODE]['country'];
//			$item['email'] = $_SESSION[CUSTOMER_CODE]['email'];
//			$item['phone'] = $_SESSION[CUSTOMER_CODE]['phone'];		
            
			$item['shipping_type'] = $_SESSION[CUSTOMER_CODE]['type'];
            $item['shipping_company_name'] = $_SESSION[CUSTOMER_CODE]['company_name'];
            $item['shipping_nip'] = $_SESSION[CUSTOMER_CODE]['nip'];
			$item['shipping_first_name'] = $_SESSION[CUSTOMER_CODE]['first_name'];
			$item['shipping_last_name'] = $_SESSION[CUSTOMER_CODE]['last_name'];               
			$item['shipping_address1'] = $_SESSION[CUSTOMER_CODE]['address1'];
			$item['shipping_address2'] = $_SESSION[CUSTOMER_CODE]['address2'];
			$item['shipping_address3'] = $_SESSION[CUSTOMER_CODE]['address3'];
            
			$item['shipping_post_code'] = $_SESSION[CUSTOMER_CODE]['post_code'];
			$item['shipping_city'] = $_SESSION[CUSTOMER_CODE]['city'];
			$item['shipping_country'] = $_SESSION[CUSTOMER_CODE]['country'];
			$item['email'] = $_SESSION[CUSTOMER_CODE]['email'];
			$item['shipping_phone'] = $_SESSION[CUSTOMER_CODE]['phone'];            
            
		} else {
			$item['type'] = 1;
			$item['company_name'] = '';
			$item['nip'] = '';
			$item['first_name'] = '';
			$item['last_name'] = '';
			$item['address1'] = '';
			$item['address2'] = '';
			$item['address3'] = '';
			$item['post_code'] = '';
			$item['city'] = '';
			$item['country'] = '';
			$item['email'] = '';
			$item['phone'] = '';	
            
			$item['other_shipping'] = 0;
			$item['shipping_type'] = 1;
			$item['shipping_company_name'] = '';
			$item['shipping_first_name'] = '';
			$item['shipping_last_name'] = '';
			$item['shipping_address1'] = '';
			$item['shipping_address2'] = '';
			$item['shipping_address3'] = '';
			$item['shipping_post_code'] = '';
			$item['shipping_city'] = '';
			$item['shipping_country'] = '';
			$item['shipping_phone'] = '';            
		}
		return $item;
	}
	
	public function getAddressDelivery() {
		if (isset($_SESSION[CUSTOMER_CODE]['id'])) {
			$item['type'] = $_SESSION[CUSTOMER_CODE]['type'];
			$item['company_name'] = $_SESSION[CUSTOMER_CODE]['company_name'];
			$item['nip'] = $_SESSION[CUSTOMER_CODE]['nip'];
			$item['first_name'] = $_SESSION[CUSTOMER_CODE]['first_name'];
			$item['last_name'] = $_SESSION[CUSTOMER_CODE]['last_name'];
			$item['address1'] = $_SESSION[CUSTOMER_CODE]['address1'];
			$item['address2'] = $_SESSION[CUSTOMER_CODE]['address2'];
			$item['address3'] = $_SESSION[CUSTOMER_CODE]['address3'];
			$item['post_code'] = $_SESSION[CUSTOMER_CODE]['post_code'];
			$item['city'] = $_SESSION[CUSTOMER_CODE]['city'];
			$item['country'] = $_SESSION[CUSTOMER_CODE]['country'];
			$item['phone'] = $_SESSION[CUSTOMER_CODE]['phone'];		
		} else {
			$item['type'] = 1;
			$item['company_name'] = '';
			$item['nip'] = '';
			$item['first_name'] = '';
			$item['last_name'] = '';
			$item['address1'] = '';
			$item['address2'] = '';
			$item['address3'] = '';
			$item['post_code'] = '';
			$item['city'] = '';
			$item['country'] = '';
			$item['phone'] = '';	
		}
		return $item;
	}
	
}

$Basket = new Basket();
$Basket->init();
