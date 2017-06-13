<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(MODEL_DIR . '/ProductStatus.php');
require_once(MODEL_DIR . '/Variation.php');
require_once(MODEL_DIR . '/Order.php');
require_once(MODEL_DIR . '/CustomerModel.php');

require_once(ENTITY_DIR . '/ProductReview.php');
use Application\Entity\ProductReview;

$order = new Order();
$orders = $order->getAll();

$customer = new CustomerModel();
$customers = $customer->getAll();

$productReviewRepository = CMS::$entityManager->getRepository('Application\Entity\ProductReview');

$productReviewsNumber = $productReviewRepository->createQueryBuilder('r')
    ->select('count(r.id)')
    ->getQuery()
    ->getSingleScalarResult();

$productStatus = new ProductStatus();
$statuses = $productStatus->getAll(['locale' => Cms::$session->get('locale_admin')]);
$statuses = getArrayByKey($statuses, 'translatable_id');

$product = new Product();
$productNumber = $product->count();

echo Cms::$twig->render('admin/main.twig',
		array(
			'statuses' => $statuses,
            'ordersNumber' => count($orders),
            'customersNumber' => count($customers),
            'productReviewsNumber' => $productReviewsNumber,
            'productNumber' => $productNumber,
			'pageTitle' => $GLOBALS['LANG']['panel_cms']
		));
