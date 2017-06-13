<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/api/products.php');
require_once(MODEL_DIR . '/api/categories.php');

$id = isset($params[2]) ? (int) $params[2] : 0;

if ($id > 0) {
	$oApiProducts = new ApiProducts();
	$oApiCategories = new ApiCategories();

	if ($item = $oApiProducts->get_by_id($id)) {
		if ($category = $oApiCategories->get_by_id($item['category_id'])) {
			$url = SERVER_URL . CMS_URL . '/product/';
			if ($category['parent_id'] > 0) {
				$parent = $oApiCategories->get_by_id($category['parent_id']);
				$url.= $parent['slug'] . '/';
			}
			$url.= $category['slug'] . '/' . $item['slug'] . '.html';
			redirect_301($url);
		}
	}
}


