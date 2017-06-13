<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('config'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/transportCountry.php');

if (isset($_POST['action']) AND $_POST['action'] == 'save') {
    Cms::save($_POST);
	$params['info'] = $GLOBALS['LANG']['config_save_change'];
}

$module = isset($params[2]) ? $params[2]: 0;

$country = new TransportCountryModel();
$countries = $country->getAll();
$countries = getArrayByKey($countries, 'id');

$entities = Cms::loadAdmin($module);

$data = array(
	'countries' => $countries,
	'entities'	=> $entities,
	'pageTitle' => $GLOBALS['LANG']['settings']			
);

echo Cms::$twig->render('admin/other/config.twig', array_merge($data, $params));