<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

//function my_autoloader($class)
//{
//    $filename = CMS_DIR . '/application/models/' . str_replace('\\', '/', $class) . '.php';
////    $filename = CMS_DIR . '/' . str_replace('\\', '/', $class) . '.php';
//
//    if (file_exists($filename)) {
//        include($filename);
//    }
//    
//}
//spl_autoload_register('my_autoloader');

Cms::$twig->addGlobal('pageTitle', Cms::$seo['title']);
Cms::$twig->addGlobal('pageKeywords', Cms::$seo['meta_keywords']);
Cms::$twig->addGlobal('pageDescription', Cms::$seo['meta_description']);

if (Cms::$modules['menu'] == 1) {
	$menu = new Menu();
	
	$menuTop = $menu->getAll(['group' => 'top', 'locale' => Cms::$session->get('locale'), 'parent_id' => 0]);
	$menuLeft = $menu->getAll(['group' => 'left', 'locale' => Cms::$session->get('locale'), 'parent_id' => 0]);	
	$menuBottom = $menu->getAll(['group' => 'bottom', 'locale' => Cms::$session->get('locale'), 'parent_id' => 0]);
    
	$selected = SERVER_URL . $_SERVER['REQUEST_URI'];
	
	$findme   = '?';
	$pos = strpos($selected, $findme);
	
	if ($pos !== false) {
		$selected = explode($findme, $selected);
		$selected = $selected[0];
	}
	
//	dump($selected);
//	if (isset($params[0]) && $params[0] == 'product') {
//		$urlParts = explode('/', $selected);
//		array_pop($urlParts);
//		$selected = implode('/', $urlParts) . '.html';
//	}	
//	dump($selected);

	Cms::$twig->addGlobal('menuTop', $menuTop);
	Cms::$twig->addGlobal('menuLeft', $menuLeft);
	Cms::$twig->addGlobal('menuBottom', $menuBottom);
	Cms::$twig->addGlobal('selected', $selected);
}

if (Cms::$modules['shop'] == 1) {
	$Basket = new BasketModel();
	$oProducts = new Products();
	$category = new Category();
	$oProducers = new Producers();

	$items = $Basket->getByCustomerOrSession();
	$items = $Basket->decoratorItems($items);

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
   
	$summary = $Basket->getSummary($items);
	
	$categories = $category->getAll(['locale' => Cms::$session->get('locale'), 'parent_id' => 0, 'status_id' => ['1','2']]);
	$aProducers = $oProducers->loadAll(['popular' => 1]);

	Cms::$twig->addGlobal('summary', $summary);
	Cms::$twig->addGlobal('basket', $items);
	Cms::$twig->addGlobal('categories', $categories);
	Cms::$twig->addGlobal('aProducers', $aProducers);

    //for tooltip discount
	$page = new Page();

    $pageTooltipDiscount = $page->getByTitle('tooltip_discount');
    $pageTooltipMegaOffer = $page->getByTitle('tooltip_mega_offer');
    $pageTooltipYourPrice = $page->getByTitle('tooltip_your_price');
    $pageAddressTop = $page->getByTitle('address_top');		

	Cms::$twig->addGlobal('pageTooltipMegaOffer', $pageTooltipMegaOffer['content']);
	Cms::$twig->addGlobal('pageTooltipDiscount', $pageTooltipDiscount['content']);
    
    if ($pageAddressTop && $pageAddressTop['active']) {
        Cms::$twig->addGlobal('address_top', $pageAddressTop['content']);
    }
    
	Cms::$twig->addGlobal('pageTooltipYourPrice', $pageTooltipYourPrice['content']);	
}

if (Cms::$modules['customer'] == 1) {
	$oCustomer = new Customer();
	if ($oCustomer->logged()) {
		Cms::$twig->addGlobal('logged', true);
		Cms::$twig->addGlobal('customer', $_SESSION[CUSTOMER_CODE]);
		define('LOGGED', 1);
	} else {		
		Cms::$twig->addGlobal('logged', false);
		Cms::$twig->addGlobal('customer', false);
		
		define('LOGGED', 0);
	}
}

Cms::$twig->addGlobal('session', $_SESSION);
//dump($_SESSION[CUSTOMER_CODE]);