<?php

use SimpleExcel\SimpleExcel;
/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
    die('No access to files!');
if (Cms::$modules['orders'] != 1)
    die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['orders'] != 1)
    die('No permission at this level!');

require_once(CMS_DIR . '/application/models/shopOrders.php');
require_once(MODEL_DIR . '/PaymentModel.php');

global $oOrders;

$oOrders = new Orders();


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
$entity = $oOrders->getById($id);

if (isset($_GET['action']) AND $_GET['action'] == 'show') {
    showOrder($_GET['id']);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'edit') {
    $oOrders->edit($_POST, $entity);
    showOrder($_POST['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'details') {
    showOrder($_GET['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'pdf') {
    showPdf($_GET['id']);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'csv') {
    showCsv($_GET['id']);
} elseif (isset($_POST['orders']) AND isset($_POST['action']) AND $_POST['action'] == 'print_all') {
    $oOrders->printAll($_POST['orders']);
    showList();
} elseif (isset($_POST['orders']) AND isset($_POST['action']) AND $_POST['action'] == 'change_status') {
    $oOrders->changeStatus($_POST['orders']);
    showList();
} elseif (isset($_POST['action']) AND $_POST['action'] == 'order_edit_save') {
    $id = $oOrders->orderEditSave($_POST);
    showOrder($id);
} elseif (isset($_GET['action']) AND $_GET['action'] == 'confirm_amazon') {
    $oOrders->confirmAmazon();
    showList();
} else {
    showList();
}

function showOrder($id = 0, $print = 0, $params = []) {
    global $oOrders;

    if ($entity = $oOrders->getById($id)) {
		
        if ($entity['lang_id'] == 2)
            require_once(CMS_DIR . '/application/languages/plDocument.php');
        else
            require_once(CMS_DIR . '/application/languages/enDocument.php');
        $orderStatuses = $oOrders->loadStatus();
        $orderStatuses = getArrayByKey($orderStatuses, 'id');

        $orderLogRepository = CMS::$entityManager->getRepository('Application\Entity\OrderLog');
		$orderLogs = $orderLogRepository->findBy(['orderId' => $id], ['date' => 'desc', 'id' => 'desc']);
        
        $payment = new PaymentModel();
        $payments = $payment->getAll();
        $payments = getArrayByKey($payments, 'id');

		$data = array(
			'order_edit' => $_SESSION[USER_CODE]['level'] == 1 ? 'iw6Pqyj5WQ' : false,	// dostep do dodatakowych funkcji w zamowieniu
			'doc' => $GLOBALS['DOC'],
			'entity' => $entity,
			'orderStatuses' => $orderStatuses,
			'payments' => $payments,
			'orderLogs' => $orderLogs,
			'pageTitle' => $GLOBALS['LANG']['order_title'] . ' ' . $entity['id']
		);

		echo Cms::$twig->render('admin/shop/order-show.twig', array_merge($data, $params));			
    }
    else {
        showList();
    }
}

function showPdf($id = 0) {
    global $oCore, $oOrders;

    require_once(CMS_DIR . '/application/models/pdf.php');
    $oPdf = new Pdf($oCore);

    if ($aOrder = $oOrders->getById($id)) {
        require_once(CMS_DIR . '/application/languages/enDocument.php');  // tylko EN
        require_once(CMS_DIR . '/system/libraries/tcpdf/config/lang/eng.php');

        $aStatus = $oOrders->loadStatus();
//		ob_start();
        $oPdf->generatePdf($aOrder, $aStatus, $GLOBALS['DOC'], null);
    } else {
        showList();
    }
}

function showCsv($id = 0) {
	global $oOrders;
	require_once(SYS_DIR . '/libraries/SimpleExcel/SimpleExcel.php');

	$csv = new SimpleExcel('csv');
	$headers = ['kod_kreskowy','kod','ilosc'];
	$data = [];
	
	$data[] = $headers;
	
    if ($aOrder = $oOrders->getById($id)) {

		foreach ($aOrder['products'] as $product) {
			$data[] = array($product['ean'], $product['sku'], $product['qty']);
		}

    } else {
        showList();
    }

	$csv->writer->setData($data);
	$csv->writer->setDelimiter(";");                  // (optional) if delimiter not set, by default comma (",") will be used instead
	$csv->writer->saveFile('order-' . $id);      
}

function showList($params = []) {
    global $oOrders;

    if (isset($_GET['o_name']))
        setcookie('order_name', $_GET['o_name'], time() + 30 * 24 * 3600);
    if (isset($_GET['o_type'])) {
        setcookie('order_type', $_GET['o_type'], time() + 30 * 24 * 3600);
        $_GET['order_name'] = $_GET['o_name'];
        $_GET['order_type'] = $_GET['o_type'];
    } else {
        $_GET['order_name'] = isset($_COOKIE['order_name']) ? $_COOKIE['order_name'] : 'id';
        $_GET['order_type'] = isset($_COOKIE['order_type']) ? $_COOKIE['order_type'] : 'down';
    }
    $_GET['first_name'] = isset($_GET['first_name']) ? $_GET['first_name'] : '';
    $_GET['last_name'] = isset($_GET['last_name']) ? $_GET['last_name'] : '';
    $_GET['email'] = isset($_GET['email']) ? $_GET['email'] : '';
    $_GET['id'] = isset($_GET['id']) ? $_GET['id'] : '';
    $_GET['status'] = isset($_GET['status']) ? $_GET['status'] : '';
    $_GET['transport'] = isset($_GET['transport']) ? $_GET['transport'] : '';
    $_GET['royal_tracking'] = isset($_GET['royal_tracking']) ? $_GET['royal_tracking'] : '';
    $_GET['other'] = isset($_GET['other']) ? $_GET['other'] : '';

    $limit = 100;
    if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
        $_GET['page'] = 1;
	
    $limitStart = ($_GET['page'] - 1) * $limit;
	
    if (isset($_GET['action']) AND $_GET['action'] == 'search') {
		$params['qs'] = '&amp;first_name=' . $_GET['first_name'] . '&amp;last_name=' . $_GET['last_name'] . '&amp;email=' . $_GET['email'] . '&amp;id=' . $_GET['id'] . '&amp;royal_tracking=' . $_GET['royal_tracking'] . '&amp;status=' . $_GET['status'] . '&amp;transport=' . $_GET['transport'] . '&amp;other=' . $_GET['other'] . '&amp;action=search';
    }
	
    $entities = $oOrders->loadOrdersAdmin($_GET, $limitStart, $limit);
    $pages = $oOrders->getPagesOrdersAdmin($_GET, $limit);	
    $orderStatuses = $oOrders->loadStatus();

	$data = array(
		'entities' => $entities,
		'pages' => $pages,
		'page' => $_GET['page'],
		'interval' => $limit * ($_GET['page'] - 1),
		'orderStatuses' => $orderStatuses,
		'pageTitle' => $GLOBALS['LANG']['orders_list']
	);

	echo Cms::$twig->render('admin/shop/order-list.twig', array_merge($data, $params));	
}