<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['shop'] != 1)
	die;

$id = isset($params[1]) ? (int) $params[1] : 0;

if ($id > 0) {
	$DIR_MODELS = CMS_DIR . DS . 'application' . DS . 'models';
	require_once($DIR_MODELS . DS . 'api' . DS . 'products.php');
	require_once($DIR_MODELS . DS . 'api' . DS . 'categories.php');
	require_once($DIR_MODELS . DS . 'api' . DS . 'languages.php');

	$oApiProducts = new ApiProducts();
	$oApiCategories = new ApiCategories();
	$oApiLanguages = new ApiLanguages();

	if ($item = $oApiProducts->get_by_id($id)) {
		$lang = $oApiLanguages->get_by_id($item['lang_id']);
		if ($lang['default'] == 1)
			$lan = '';
		else
			$lan = $lang['code'] . '/';
		if ($category = $oApiCategories->get_by_product_id($item)) {
//			$var = $oApiProducts -> get_first_variation_by_product_id($item['id']);
			$url = URL . '/' . $lan . $category['url'] . '/' . $item['title_url'] . '.html';
			redirect_301($url);
		}
	}
}


