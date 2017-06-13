<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (!$oCustomer->logged()) {
	redirect_301(URL . '/customer/login.html');
	die;
}

$basket = new BasketModel(); 
$basket = $basket->getByCustomerOrSession();
    
$data = array(
    'basket' => $basket,
	'pageTitle' => $GLOBALS['LANG']['c_profile'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/profile.twig', array_merge($data, $params)); 

