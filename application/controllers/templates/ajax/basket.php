<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/BasketModel.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(CONTROL_DIR . '/classes/Transport.php');
require_once(MODEL_DIR . '/shopProductsAdmin.php');
require_once(MODEL_DIR . '/ShoppingThresholds.php');

$method = $_POST['method'];
$id = isset($_POST['id']) ? $_POST['id'] : 0;
$payment = isset($_POST['payment']) ? $_POST['payment'] : 0;

switch ($method) {
	case 'add':
		addProduct($_POST['id'], $_POST['variation_id'], $_POST['qty']);
		break;
	
	case 'changeQty':
		changeQty($_POST['id'], $_POST['qty']);
		break;
	
	case 'getAvailableQty':
		getAvailableQty($_POST['id']);
		break;
    
	case 'drawTable':
		drawTable($_POST['tableName']);
		break;
	
	case 'deleteProduct':
		deleteProduct($_POST['id']);
		break;

	case 'getDeliveryServices':
		getDeliveryServices($_POST['id']);
		break;
	
	case 'getSummary':
		getSummary();
		break;
    
	case 'getMiniBasketList':
		getMiniBasketList();
		break;
	
	case 'getCost':
		getCost($id, $payment);
		break;
    
	case 'getFeature2Variations':
		getFeature2Variations($_POST['id'], $_POST['feature1_value_id']);
		break;
    
	case 'getFeature3Variations':
		getFeature3Variations($_POST['id'], $_POST['feature1_value_id'], $_POST['feature2_value_id']);
		break;
    
	case 'getDiscount':
		getDiscount($id);
		break;   
    
	case 'getVariationPhotos':
		getVariationPhotos($_POST['product_id'], $_POST['variation_id']);
		break;    
    
	case 'getVariation':
		getVariation($_POST['variation_id']);
		break;    

	default:
		break;
}
die;

function getFeature2Variations($id = 0, $feature1_value_id) {
    $oProduct = new Products();
	
	$data = array(
		'product_id' => $id,
		'feature1_value_id' => $feature1_value_id
	);
	
    $entities = $oProduct->getVariationsBy($data);
//    $entities = $oProduct->getVariationsByProductId($id, $feature1_value_id);

    //exclude null feature2 variations
    foreach ($entities as $key => $variation) {
        if ($variation['feature1_value_id'] && !$variation['feature2_value_id']) {
            unset($entities[$key]);           
        }
    }
    
    if (!$entities) {
        return false;
    }

	$data = array(
		'entities' => $entities,
		'feature' => 2
	);

	echo Cms::$twig->render('templates/basket/variations.twig', $data); 	    		
}

function getFeature3Variations($id = 0, $feature1_value_id, $feature2_value_id) {
    $oProduct = new Products();
	
	$data = array(
		'product_id' => $id,
		'feature1_value_id' => $feature1_value_id,
		'feature2_value_id' => $feature2_value_id
	);	
	$entities = $oProduct->getVariationsBy($data);

    //exclude null feature3 variations
    if ($feature1_value_id && $feature2_value_id) {
        foreach ($entities as $key => $variation) {
            if (!$variation['feature3_value_id']) {
                unset($entities[$key]);           
            }
        }        
    }  

    if (!$entities) {
        return false;
    }
    
	$data = array(
		'entities' => $entities,
		'feature' => 3
	);

	echo Cms::$twig->render('templates/basket/variations.twig', $data);     
}

function addProduct($id = 0, $variation_id = 0, $qty = 1) {
    
	$Basket = new BasketModel();
	$oProduct = new Products();

	if($product = $oProduct->getById($id, $variation_id)) {
		$aFields = [];
		$aFields['time_add'] = date('Y-m-d H:i:s');
		$aFields['qty'] = $qty;
		
		if($qty > $product['qty']) {		
			$aFields['qty'] = $product['qty'];
            echo $GLOBALS['LANG']['lack_of_stock'];
		}
		$aFields2 = [];
		$aFields2['qty'] = $product['qty'] - $aFields['qty'];
		
		if($item = $Basket->getByProductIdVariationId($product['id'], $variation_id)[0]) { // sprawdzamy czy produkt jest w koszyku

			$aFields['qty'] += $item['qty'];

			if($item['qty'] != $aFields['qty']) {		
				$Basket->updateById($item['id'], $aFields);
				$oProduct->updateVariationById($product['id2'], $aFields2);
                echo $GLOBALS['LANG']['increased_product_in_cart'];
			} else {					
                echo $GLOBALS['LANG']['not_increased_state'];
			}
		} else {

			$aFields['product_id'] = $product['id'];
			$aFields['variation_id'] = $variation_id;
			$aFields['name'] = $product['name'];

			$desc = '';
			if ($product['feature1_name'] && $product['feature1_value']) {
				$desc.= $product['feature1_name'] . ': ' . $product['feature1_value'];
				if ($product['feature2_name'] && $product['feature2_value']) {
					$desc.= ', ' . $product['feature2_name'] . ': ' . $product['feature2_value'];
					if ($product['feature3_name'] && $product['feature3_value']) {
						$desc.= ', ' . $product['feature3_name'] . ': ' . $product['feature3_value'];
					}
				}
			}
			$aFields['desc'] = $desc;
            $aFields['price'] = $product['price'];          
			
			$aFields['tax'] = $product['tax'];
			$aFields['weight'] = $product['weight'];
            
			if ($Basket->set($aFields)) {	
				$oProduct->updateVariationById($product['id2'], $aFields2);
                echo $GLOBALS['LANG']['basket_product_added'];				
			} else {
                echo $GLOBALS['LANG']['adding_product_into_cart_error'];
			}			
		}
		
	} else {
        echo $GLOBALS['LANG']['lack_product_on_site'];
	}
}

function changeQty($id = 0, $qty = 1) {
	$Basket = new BasketModel();
	$oProduct = new Products();
	
	if($qty < 1) {
		$qty = 1;
	}
	
	if($basket = $Basket->getById($id)[0]) {
		$product = $oProduct->getById($basket['product_id'], $basket['variation_id']);

		$qtyChange = $qty - $basket['qty'];
		if($qtyChange > $product['qty']) {
			$qtyChange = $product['qty'];
		}

		$aFields = [];
		$aFields['qty'] = $basket['qty'] + $qtyChange;
		$Basket->updateById($basket['id'], $aFields);

		$priceGross = formatPrice($basket['price'], $basket['tax']);
        $total = formatPrice($priceGross * $aFields['qty']);

		$aFields = [];
		$aFields['qty'] = $product['qty'] - $qtyChange;
		$oProduct->updateVariationById($product['id2'], $aFields);

		echo $total;
	}
}

function deleteProduct($id = 0) {
	$Basket = new BasketModel();
	$oProduct = new Products();
	
	if($basket = $Basket->getById($id)[0]) {
		$product = $oProduct->getById($basket['product_id'], $basket['variation_id']);
		$aFields['qty'] = $product['qty'] + $basket['qty'];		
		if($Basket->deleteById($basket['id'])) {
//			$oProduct->updateById($product['id'], $aFields);
            $oProduct->updateVariationById($product['id2'], $aFields);
			echo 1;
			die;
		}
	}

	echo 0;
}

function getDeliveryServices($countryId = 0) {
	if (Cms::$modules['unit_transport']) {
		return false;
	}
	
	$Basket = new BasketModel();
	$Transport = new TransportController();
	
	if ($basket = $Basket->getByCustomerOrSession()) {
		$basket = $Basket->decoratorItems($basket);		
	}
    
	$summary = $Basket->getSummary($basket);
	$deliveryService = $Transport->getAllDeliveryService($countryId, '', $summary['weight']); // dostepne uslugi | kraj, kod pocztowy, waga
	
	$optionId = 0;
	foreach($deliveryService as $k => &$v) {
		$v['default'] = 0;
		if($k == 0) {
			$v['default'] = 1;
			$optionId = $v['option_id'];
		}
	}

	$deliveryPrice = $Basket->getDeliveryPrice($optionId);
	
	$data = array(
		'deliveryService' => $deliveryService,
		'deliveryPrice' => $deliveryPrice
	);

	echo Cms::$twig->render('templates/basket/delivery-services.twig', $data);     
}

function getSummary() {
	$Basket = new BasketModel();
	
	if ($basket = $Basket->getByCustomerOrSession()) {
		$basket = $Basket->decoratorItems($basket);		
	}
    
	$summary = $Basket->getSummary($basket);

	$data = array(
		'summary' => $summary
	);

	echo Cms::$twig->render('templates/basket/summary.twig', $data); 	
}

function getMiniBasketList() {
	$basket = new BasketModel();
	$oProducts = new Products();

    $basket->processClearBasket();
    
	$items = $basket->getByCustomerOrSession();
	$items = $basket->decoratorItems($items);

    if ($items) {           
        foreach($items as &$v) {
            $product = $oProducts->getById($v['product_id'], $v['variation_id']);
            $v['available'] = $product['qty'];
            $v['sku'] = $product['sku'];
            $v['price_purchase'] = $product['price_purchase'];
            $v['url'] = $product['url'];
            if(isset($product['photo']['small'])) $v['image'] = $product['photo']['small'];
        }
    }
            
	$summary = $basket->getSummary($items);  

	$data = array(
		'summary' => $summary,
		'basket' => $items
	);

	echo Cms::$twig->render('templates/basket/mini-basket-list.twig', $data); 	
}

function getCost($optionId = 0, $paymentId = 0) {
	$Basket = new BasketModel();	
	
	if($basket = $Basket->getByCustomerOrSession()) {
		$basket = $Basket->decoratorItems($basket);		
	}
    
    $productModel = new Products();
	if ($basket) {
		foreach($basket as &$v) {
			$product = $productModel->getById($v['product_id'], $v['variation_id']);
			$v['mega_offer'] = $product['mega_offer'];             
		}
	}
    
    $shoppingThresholdInfo = Cms::$shoppingThresholds->getInfo();
    if ($basket) {
        foreach($basket as &$v) {
            if (Cms::$modules['shopping_thresholds'] && $shoppingThresholdInfo && isset($shoppingThresholdInfo['hasThreshold'])) {
                if (!$v['mega_offer']) {
                    $v['price_after_discount'] = formatPrice($v['price_gross'] - $v['price_gross'] * $shoppingThresholdInfo['hasThreshold']['discount'] / 100);
                } else {
                    $v['price_after_discount'] = $v['price_gross'];
                }
                
                $v['sum'] = formatPrice($v['qty'] * $v['price_after_discount']); 
            }
        }
    }

	$deliveryPrice = $Basket->getDeliveryPrice($optionId, $paymentId);
	$summary = $Basket->getSummary($basket, $deliveryPrice);

    $params = [];

    if (isset($_SESSION[CUSTOMER_CODE]['discount']) && $_SESSION[CUSTOMER_CODE]['discount'] > 0) {

        $params = array(
            'discountTotal' => $_SESSION[CUSTOMER_CODE]['discount_total'],
            'summaryTotal' => formatPrice($_SESSION[CUSTOMER_CODE]['discount_total'] + $deliveryPrice),       
        );
    }
           
	$data = array(
		'summary' => $summary,
	);

	echo Cms::$twig->render('templates/basket/cost.twig', array_merge($data, $params));     
}

function getDiscount($optionId = 0) {
	$Basket = new BasketModel();
	$Transport = new TransportController();

	if($basket = $Basket->getByCustomerOrSession()) {
		$basket = $Basket->decoratorItems($basket);		
	}
	$deliveryOption = $Transport->getServiceOptionById($optionId);
	$price = isset($deliveryOption['price_gross']) ? $deliveryOption['price_gross'] : 0;
	$summary = $Basket->getSummary($basket, $price);
    
    $total = $summary['total'];   

    if (CMS::$modules['shopping_thresholds']) {
        $shoppingThresholdInfo = Cms::$shoppingThresholds->getInfo();   
        
        $data = array(
            'shoppingThresholdInfo' => $shoppingThresholdInfo
        );
        
        echo Cms::$twig->render('templates/basket/discount.twig', $data); 
    } else {
        
        if (!isset($_SESSION[CUSTOMER_CODE]['discount'])) {
            return false;
        }   

        $_SESSION[CUSTOMER_CODE]['discount_sum'] = formatPrice($summary['sum'] - $summary['sum'] * $_SESSION[CUSTOMER_CODE]['discount'] / 100);
        $_SESSION[CUSTOMER_CODE]['discount_saving'] = formatPrice($summary['sum'] - $_SESSION[CUSTOMER_CODE]['discount_sum']);
        $_SESSION[CUSTOMER_CODE]['discount_total'] = formatPrice($_SESSION[CUSTOMER_CODE]['discount_sum']);

//        $_SESSION[CUSTOMER_CODE]['discount_sum'] = formatPrice($total - $total * $_SESSION[CUSTOMER_CODE]['discount'] / 100);
//        $_SESSION[CUSTOMER_CODE]['discount_saving'] = formatPrice($total - $_SESSION[CUSTOMER_CODE]['discount_sum']);
//        $_SESSION[CUSTOMER_CODE]['discount_total'] = formatPrice($_SESSION[CUSTOMER_CODE]['discount_sum']);    
                
		$data = array(
			'discountSaving' => $_SESSION[CUSTOMER_CODE]['discount_saving'],
			'discountTotal' => $_SESSION[CUSTOMER_CODE]['discount_total']
		);
		
        echo Cms::$twig->render('templates/basket/discount.twig', $data);
    }
}

function getVariationPhotos($product_id, $variation_id) {
    $oProductAdmin = new ProductsAdmin();
    $variationImages = $oProductAdmin->getImage($product_id, $variation_id);
    $productImages = $oProductAdmin->getImage($product_id);
    $entity = $oProductAdmin->loadByIdAdmin($product_id);
	
	$data = array(
		'variationImages' => $variationImages,
		'productImages' => $productImages,
		'entity' => $entity
	);

	echo Cms::$twig->render('templates/basket/variation-photos.twig', $data); 	
}

function getVariation($variation_id) {
    $oProductAdmin = new ProductsAdmin();
    $variation = $oProductAdmin->loadVariationById($variation_id);

    echo json_encode($variation);
}

function drawTable($tableName = '') {    
    $basketModel = new BasketModel();
    $basket = $basketModel->getBasketItems();
    
    $shoppingThresholdInfo = Cms::$shoppingThresholds->getInfo();

	if ($basket) {
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
	
    $sortedBasket = arrayOrderByKey($basket, 'mega_offer', SORT_ASC); //mega offer products go last

	$data = array(
		'shoppingThresholdInfo' => $shoppingThresholdInfo,
		'basket' => $sortedBasket,
	);

    if ($tableName) {
        $tableName = '-' . $tableName;
    }
    
	echo Cms::$twig->render('templates/basket/basket-table' . $tableName . '.twig', $data);     
}

function getAvailableQty($id = 0) {
	$Basket = new BasketModel();
	$oProduct = new Products();

	if ($basket = $Basket->getById($id)[0]) {
		$product = $oProduct->getById($basket['product_id'], $basket['variation_id']);

		$availableQty = $product['qty'] + $basket['qty'];
		
		echo $availableQty;
	}
}