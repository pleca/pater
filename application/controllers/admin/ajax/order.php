<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/shopProductsAdmin.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(MODEL_DIR . '/shopProducers.php');
require_once(MODEL_DIR . '/shopOrders.php');

$method = $_POST['method'];

switch ($method) {
	case 'getOrderProduct':
		getOrderProduct($_POST['product_id'], $_POST['variation_id']);
		break;
	case 'getOrder':
		getOrder($_POST['order_id']);
		break;
	case 'getDeliveryServices':
		getDeliveryServices($_POST['order_id'], $_POST['country_id'], $_POST['weight']);
		break;

	default:
		break;
}
die;

function getDeliveryServices($orderId, $countryId = 0, $weight) {
    $order = new Orders();
    $entity = $order->getById($orderId);
    
    $Transport = new TransportController();
	$deliveryService = $Transport->getAllDeliveryService($countryId, '', $weight); // dostepne uslugi | kraj, kod pocztowy, waga
	
	$optionId = 0;
	foreach($deliveryService as $k => &$v) {
		$v['default'] = 0;
		if($k == 0) {
			$v['default'] = 1;
			$optionId = $v['option_id'];
		}
	}

	$data = array(
        'entity' => $entity,
		'deliveryService' => $deliveryService,
		'deliveryPrice' => (float) $entity['transport_price']
	);

	echo Cms::$twig->render('admin/shop/delivery-services.twig', $data);     
}

function getOrder($orderId) {
    $order = new Orders();
    $entity = $order->getById($orderId);

    $transportCountry = new TransportCountryModel();
    $transportEnabledCountries = $transportCountry->getEnabledCountries();    
    $transportCountries = $transportCountry->getAll();
    
    $Transport = new TransportController();
    $deliveryService = $Transport->getAllDeliveryService($entity['shipping_country'], '', $entity['weight']); // dostepne uslugi | kraj, kod pocztowy, waga

    $data = array(
        'module' => 'edit',
        'entity' => $entity,
        'countriesEnabled' => $transportEnabledCountries,
        'countries' => $transportCountries,
        'deliveryService' => $deliveryService,
//        'deliveryPrice' => 0
        'deliveryPrice' => (float) $entity['transport_price']
    );
    
    echo Cms::$twig->render('admin/ajax/order.twig', $data);
}

function getOrderProduct($product_id, $variation_id) {

    $oProductsAdmin = new ProductsAdmin();
    $variation = $oProductsAdmin->loadVariationById($variation_id);
    
    $oProducts = new Products();
    $oProducers = new Producers();

    $entity = $oProducts->getById($product_id, $variation_id);
    $entity['photos'] = $oProducts->getImage($entity['id']);
    $entity['producer'] = $oProducers->getById($entity['producer_id']);

    $taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
    $taxes = $taxRepository->findBy([],['position' => 'ASC']);

	$data = array(
        'taxes' => $taxes,
		'module' => 'product',
		'entity' => $entity,
		'variation' => $variation,
		'variation_id' => $variation_id
	);

	echo Cms::$twig->render('admin/ajax/order.twig', $data);	
}


