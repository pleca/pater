<?php

//namespace classes;

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/customer.php');
require_once(MODEL_DIR . '/ShoppingThresholds.php');

class ShoppingThresholdsHelper {
    public static $shoppingThresholdsModel;
    
    public function __construct() {
        self::$shoppingThresholdsModel = new ShoppingThresholds();
    }       
    
    public function getLast() {
        if (!CMS::$modules['shopping_thresholds']) {
            return false; 
        }
        
        $shoppingThresholds = self::$shoppingThresholdsModel->getAll();
        
        if (!$shoppingThresholds) {
            return false; 
        }
        
        $sortedShoppingThresholds = arrayOrderByKey($shoppingThresholds, 'value', SORT_DESC);
        
        return reset($sortedShoppingThresholds);
    }
    
    /*
     * if you add deliveryPrice then discount will be calculated with it
     */
    public function getInfo($deliveryPrice = 0) {
        
        if (!CMS::$modules['shopping_thresholds']) {            
            return false; 
        }
                        
        $info = [];

        //case with customer discount
        $info['customerDiscount'] = null;
        $info['finalDiscount'] = null;
        if (isset($_SESSION[CUSTOMER_CODE]['id'])) {
            $customer = new Customer();
            $customer = $customer->getById($_SESSION[CUSTOMER_CODE]['id']);
            $info['customerDiscount'] = $customer['discount'];
            $info['finalDiscount'] = $info['customerDiscount'];
        }
        
        $shoppingThresholds = self::$shoppingThresholdsModel->getAll();
        
        if (!$shoppingThresholds) {
            return false;
        }
        
        $sortedShoppingThresholds = arrayOrderByKey($shoppingThresholds, 'value', SORT_DESC);

        $basket = new BasketModel();
        $basketItems = $basket->getBasketItems();     
 
        //exclude mega_offer products from calculation
		if ($basketItems) {
			foreach ($basketItems as $key => $item) {
				if ($item['mega_offer']) {
					unset($basketItems[$key]);
				}
			}
		}

        $summary = $basket->getSummary($basketItems, $deliveryPrice);

        $totalBasket = (float) $summary['total'];              
        $sumBasket = (float) $summary['sum'];              
        $summary = $basket->getSummary($basketItems, $deliveryPrice);
        $hasThreshold = false;
        foreach ($sortedShoppingThresholds as $shoppingThreshold) {
            if ($totalBasket >= $shoppingThreshold['value']) {
                $hasThreshold = $shoppingThreshold;
                break;
            }
        }

        //exclude thresholds lower and equal then customer discount
        if ($info['customerDiscount']) {
            foreach ($sortedShoppingThresholds as $key => $shoppingThreshold) {
                if ($shoppingThreshold['discount'] <= $info['customerDiscount']) {
                    unset($sortedShoppingThresholds[$key]);
                }
            }            
        }

        //exclude thresholds lower and equal then phrase discount
        if (isset($_SESSION[CUSTOMER_CODE]['promotion_code']) && $_SESSION[CUSTOMER_CODE]['promotion_code']) {
            foreach ($sortedShoppingThresholds as $key => $shoppingThreshold) {
                if ($shoppingThreshold['discount'] <= $_SESSION[CUSTOMER_CODE]['discount']) {
                    unset($sortedShoppingThresholds[$key]);
                }
            }            
        }

        $diff = [];
        $needToDiscount = null;
        if ($totalBasket) {
            foreach ($sortedShoppingThresholds as $shoppingThreshold) {  
//                $difference = (float) $shoppingThreshold['value'] - $totalBasket;  
                $difference = (float) $shoppingThreshold['value'] - $sumBasket;  
                $shoppingThreshold['discount'] = (int) $shoppingThreshold['discount'];
                $shoppingThreshold['value'] = (int) $shoppingThreshold['value'];
                if ($difference > 0) {
                    $diff["$difference"] = $shoppingThreshold;
                }                
            }

            if ($diff) {
                $needToDiscount = min(array_keys($diff));
                $info['nerestThreshold'] = $diff["$needToDiscount"];
            }

        } else {
            $last = end($sortedShoppingThresholds);
            $last['discount'] = (int) $last['discount'];
            $last['value'] = (int) $last['value'];
            $needToDiscount = $last['value'];
            $info['nerestThreshold'] = $last;
        }

        $info['hasThreshold'] = $hasThreshold;
        $info['needToDiscount'] = $needToDiscount;

        if ($info['hasThreshold'] && $info['customerDiscount']) {
            if ($info['hasThreshold']['discount'] > $info['customerDiscount']) {
                $info['finalDiscount'] = $info['hasThreshold']['discount'];
            } else {
                $info['finalDiscount'] = $info['customerDiscount'];
            }
        }

        //check phrase discount
        if (isset($_SESSION[CUSTOMER_CODE]['promotion_code']) && $_SESSION[CUSTOMER_CODE]['promotion_code']) {
            if ($_SESSION[CUSTOMER_CODE]['discount'] > $info['finalDiscount']) {
                $info['finalDiscount'] = $_SESSION[CUSTOMER_CODE]['discount'];
            }
        }

        if (!$info['finalDiscount'] && $info['hasThreshold']) {
            $info['finalDiscount'] = $info['hasThreshold']['discount'];
        }

        $_SESSION[CUSTOMER_CODE]['discount'] = $info['finalDiscount'];          
        $_SESSION[CUSTOMER_CODE]['discount_sum'] = formatPrice($sumBasket - $sumBasket * $_SESSION[CUSTOMER_CODE]['discount'] / 100);
        $_SESSION[CUSTOMER_CODE]['discount_saving'] = formatPrice($sumBasket - $_SESSION[CUSTOMER_CODE]['discount_sum']);
        $_SESSION[CUSTOMER_CODE]['discount_total'] = formatPrice($_SESSION[CUSTOMER_CODE]['discount_sum']);        

        return $info;
    }
    
//   public function getInfo($deliveryPrice = 0) {        
//        $basket = new BasketModel();
//        $basketItems = $basket->getBasketItems();                
//        $summary = $basket->getSummary($basketItems, $deliveryPrice);
//
//        $totalBasket = (float) $summary['total'];
//        $sumBasket = (float) $summary['sum'];
//        $info = [];
//
//        //case with customer discount
//        $info['customerDiscount'] = null;
//        $info['finalDiscount'] = null;
//        if (isset($_SESSION[CUSTOMER_CODE]['id'])) {
//            $customer = new Customer();
//            $customer = $customer->getById($_SESSION[CUSTOMER_CODE]['id']);
//            $info['customerDiscount'] = $customer['discount'];
//            $info['finalDiscount'] = $info['customerDiscount'];
//        }
//        
//        $shoppingThresholds = $this->getAll();
//        
//        if (!$shoppingThresholds) {
//            return false;
//        }
//        
//        $sortedShoppingThresholds = arrayOrderByKey($shoppingThresholds, 'value', SORT_DESC);
//
//        $hasThreshold = false;
//        foreach ($sortedShoppingThresholds as $shoppingThreshold) {
//            if ($totalBasket >= $shoppingThreshold['value']) {
//                $hasThreshold = $shoppingThreshold;
//                break;
//            }
//        }
//
//        //exclude thresholds lower and equal then customer discount
//        if ($info['customerDiscount']) {
//            foreach ($sortedShoppingThresholds as $key => $shoppingThreshold) {
//                if ($shoppingThreshold['discount'] <= $info['customerDiscount']) {
//                    unset($sortedShoppingThresholds[$key]);
//                }
//            }            
//        }
//
//        //exclude thresholds lower and equal then phrase discount
//        if (isset($_SESSION[CUSTOMER_CODE]['promotion_code']) && $_SESSION[CUSTOMER_CODE]['promotion_code']) {
//            foreach ($sortedShoppingThresholds as $key => $shoppingThreshold) {
//                if ($shoppingThreshold['discount'] <= $_SESSION[CUSTOMER_CODE]['discount']) {
//                    unset($sortedShoppingThresholds[$key]);
//                }
//            }            
//        }
//
//        $diff = [];
//        $needToDiscount = null;
//        if ($totalBasket) {
//            foreach ($sortedShoppingThresholds as $shoppingThreshold) {  
////                $difference = (float) $shoppingThreshold['value'] - $totalBasket;  
//                $difference = (float) $shoppingThreshold['value'] - $sumBasket;  
//                $shoppingThreshold['discount'] = (int) $shoppingThreshold['discount'];
//                $shoppingThreshold['value'] = (int) $shoppingThreshold['value'];
//                if ($difference > 0) {
//                    $diff["$difference"] = $shoppingThreshold;
//                }                
//            }
//
//            if ($diff) {
//                $needToDiscount = min(array_keys($diff));
//                $info['nerestThreshold'] = $diff["$needToDiscount"];
//            }
//
//        } else {
//            $last = end($sortedShoppingThresholds);
//            $last['discount'] = (int) $last['discount'];
//            $last['value'] = (int) $last['value'];
//            $needToDiscount = $last['value'];
//            $info['nerestThreshold'] = $last;
//        }
//
//        $info['hasThreshold'] = $hasThreshold;
//        $info['needToDiscount'] = $needToDiscount;
//
//        if ($info['hasThreshold'] && $info['customerDiscount']) {
//            if ($info['hasThreshold']['discount'] > $info['customerDiscount']) {
//                $info['finalDiscount'] = $info['hasThreshold']['discount'];
//            } else {
//                $info['finalDiscount'] = $info['customerDiscount'];
//            }
//        }
//
//        //check phrase discount
//        if (isset($_SESSION[CUSTOMER_CODE]['promotion_code']) && $_SESSION[CUSTOMER_CODE]['promotion_code']) {
//            if ($_SESSION[CUSTOMER_CODE]['discount'] > $info['finalDiscount']) {
//                $info['finalDiscount'] = $_SESSION[CUSTOMER_CODE]['discount'];
//            }
//        }
//
//        if (!$info['finalDiscount'] && $info['hasThreshold']) {
//            $info['finalDiscount'] = $info['hasThreshold']['discount'];
//        }
//        
////        dump($info);
//        $sumBasket = (float) $summary['sum'];
//        
//        $_SESSION[CUSTOMER_CODE]['discount'] = $info['finalDiscount'];          
//        $_SESSION[CUSTOMER_CODE]['discount_sum'] = formatPrice($sumBasket - $sumBasket * $_SESSION[CUSTOMER_CODE]['discount'] / 100);
//        $_SESSION[CUSTOMER_CODE]['discount_saving'] = formatPrice($sumBasket - $_SESSION[CUSTOMER_CODE]['discount_sum']);
//        $_SESSION[CUSTOMER_CODE]['discount_total'] = formatPrice($_SESSION[CUSTOMER_CODE]['discount_sum']);        
//
//        return $info;
//    }    
}
