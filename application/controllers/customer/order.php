<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (!$oCustomer->logged()) {
	redirect_301(URL . '/customer/login.html');
	die;
}

require_once(CMS_DIR . '/application/models/shopOrders.php');

$uid = isset($params[2]) ? $params[2] : '';
if (empty($uid)) {
	showList();
} else {
	showOrder($uid);
}

function decorateOrder(&$entity) 
{
    $productModel = new Products();
    if ($entity['products']) {
        foreach ($entity['products'] as &$item) {
            $product = $productModel->getById($item['product_id'], $item['variation_id']);
            if(isset($product['photo']['small'])) $item['image'] = $product['photo']['small']; 
        }
    }    
}

function showOrder($uid = '') {
	$oOrders = new Orders();

	$entity = $oOrders->loadByCustomerUid($uid);

    decorateOrder($entity);    

	$data = array(
		'entity' => $entity,
		'pageTitle' => $GLOBALS['LANG']['c_order'] . ' - ' . Cms::$seo['title']
	);	
	
	if (isset($_GET['print']) AND $_GET['print'] == 1) {
		echo Cms::$twig->render('templates/customer/order-print.twig', $data);		
	} else {
		echo Cms::$twig->render('templates/customer/order-show.twig', $data);		
	}
		
}

function showList() {
	$oOrders = new Orders();
	$entities = $oOrders->loadByCustomer();

	$data = array(
		'entities' => $entities,
		'pageTitle' => $GLOBALS['LANG']['c_order'] . ' - ' . Cms::$seo['title'],
		'selected'	=> 'order'
	);

	echo Cms::$twig->render('templates/customer/order-list.twig', $data);	
}
