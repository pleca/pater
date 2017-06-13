<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

if ($oCustomer->logout($_POST)) {
//	$basketInfo = $oBasket->getInfo();
//	$basketSmallItems = $oBasket->loadSmallItems();
//	Cms::$tpl->assign('basketInfo', $basketInfo);
//	Cms::$tpl->assign('basketSmallItems', $basketSmallItems);
	$params['logged'] = false;
	$params['customer'] = false;
}

$data = array(
	'pageTitle' => $GLOBALS['LANG']['c_logout'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/logout.twig', array_merge($data, $params));

