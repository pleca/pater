<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

if (Cms::$modules['shop'] != 1) {
	die;
}

global $oProducts, $category;

$oProducts = new Products();
$category = new Category();

$url1 = isset($params[1]) ? $params[1] : '';
$url2 = isset($params[2]) ? $params[2] : '';

$params = [];

if ($mainCategory = $category->findBySlug($url1)) {

	if ($subCategory = $category->findBySlug($url2, $mainCategory)) {		
		showProducts(['category' => $subCategory, 'mainCategory' => $mainCategory]);
	} else {		
		showProducts(['category' => $mainCategory]);
	}
	
} else {
	showProducts([]);
}

function showProducts(array $data = []) {
	global $oProducts, $category;

	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
		$_GET['page'] = 1;
	}

	$limit = 20;
	$limit = isset(Cms::$conf['category_page_limit']) ? Cms::$conf['category_page_limit'] : $limit;

	$params = array(
		'limit' => $limit,
		'start' => ($_GET['page'] - 1) * $limit,
		'resultType'	=> 'list',
		'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'
	);			
	
	$qs = '&sort=' . $params['sort'];
	
	if (isset($data['category']['id'])) {
		$params['category_id'] = $data['category']['id'];
	}	
	
	if (isset($_GET['producers'])) {
		$params['producers'] = $_GET['producers'];
		foreach ($params['producers'] as $producer) {
			$qs .= '&producers[]=' . $producer;
		}		
	}
	
	if (isset($_GET['price_from']) && !empty($_GET['price_from'])) {
		$params['price_from'] = $_GET['price_from'];
		$qs .= '&price_from=' . $params['price_from'];
	}
	
	if (isset($_GET['price_to']) && !empty($_GET['price_to'])) {
		$params['price_to'] = $_GET['price_to'];
		$qs .= '&price_to=' . $params['price_to'];
	}

    $entities = $oProducts->getBy($params);	
//dump($entities);
	$aProducts2 = $oProducts->getBy(array_merge($params, ['resultType' => 'count']));
	$pages = count($aProducts2);
	unset($aProducts2);
	if ($pages < 1) {
		$pages = 1;
	}
	$pages =  ceil($pages / $params['limit']);

	$title = isset($data['category']['name']) && $data['category']['name'] ? $data['category']['name'] : $GLOBALS['LANG']['shop_products'];

	if (isset($data['category'])) {
		$pageTitle = isset($data['category']['seo_title']) && $data['category']['seo_title'] ? $data['category']['seo_title'] : $data['category']['name'];	
	} else {
		$pageTitle = $title;
	}
	
	$metaDescription = isset($data['category']['meta_description']) && $data['category']['meta_description'] ? $data['category']['meta_description'] : Cms::$seo['meta_description'];	
	
	if (isset($data['mainCategory'])) {
		$metaDescription = $data['category']['meta_description'] ? $data['category']['meta_description'] : $data['mainCategory']['meta_description'];
	}		

	if (isset($data['category'])) {
		$seoAccordion = filterArrayKeyByPattern($data['category'], 'accordion');
		Cms::$twig->addGlobal('seoAccordion', $seoAccordion);		
	}
	
	$data = array(
		'entities' => $entities,
		'pages' => $pages,
		'page' => $_GET['page'],
		'qs' => $qs,
		'title' => $title,
		'pageTitle' => $pageTitle,
		'pageKeywords' => Cms::$seo['meta_keywords'],
		'pageDescription' => $metaDescription			
	);

	echo Cms::$twig->render('templates/shop/list.twig', $data);	
}