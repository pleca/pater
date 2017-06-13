<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

if (Cms::$modules['shop'] != 1) {
	die;
}

$oProducts = new Products();
$oProducers = new Producers();

$name_url = isset($params[1]) ? $params[1] : '';
$producer = $oProducers->getProducer($name_url);

if ($name_url) {
    if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page'])) {
        $_GET['page'] = 1;
    }

    $action = isset($params[1]) ? $params[1] : '';

    if ($action) {
        $action = ': ' . strtoupper($action);
    }  
	
	$limit = 20;

	$params = array(
		'limit' => $limit,
		'start' => ($_GET['page'] - 1) * $limit,
		'resultType'	=> 'list',
		'producer_id'	=> $producer['id'],
		'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'
	);	
	
	$entities = $oProducts->getBy($params);
	$aProducts2 = $oProducts->getBy(array_merge($params, ['resultType' => 'count']));
    $pages = count($aProducts2);
    unset($aProducts2);
    
    if ($pages < 1) {
        $pages = 1;
    }
    
    $pages =  ceil($pages / $limit);
    
    $data = array(
        'entities' => $entities,
        'pages' => $pages,
        'page' => $_GET['page'],
        'qs' => '&sort=' . $params['sort'],
        'title' => $GLOBALS['LANG']['shop_manufacturers_title'] . $action,
        'pageTitle' => $GLOBALS['LANG']['shop_manufacturers_title'] . ' - ' . Cms::$seo['title'],
        'pageKeywords' => Cms::$seo['meta_keywords'],
        'pageDescription' => Cms::$seo['meta_description']			
    );

    echo Cms::$twig->render('templates/shop/list.twig', array_merge($data, $params));     
    
} else {

    $aProducers = $oProducers->loadAll();

    $entities = [];

    $entities['0-9'] = arrayItemsStartedWithNumber($aProducers);
    $entities['a'] = arrayItemsStartedWithLetter($aProducers, 'a');
    $entities['b'] = arrayItemsStartedWithLetter($aProducers, 'b');
    $entities['c'] = arrayItemsStartedWithLetter($aProducers, 'c');
    $entities['d'] = arrayItemsStartedWithLetter($aProducers, 'd');
    $entities['e'] = arrayItemsStartedWithLetter($aProducers, 'e');
    $entities['f'] = arrayItemsStartedWithLetter($aProducers, 'f');
    $entities['g'] = arrayItemsStartedWithLetter($aProducers, 'g');
    $entities['h'] = arrayItemsStartedWithLetter($aProducers, 'h');
    $entities['i'] = arrayItemsStartedWithLetter($aProducers, 'i');
    $entities['j'] = arrayItemsStartedWithLetter($aProducers, 'j');
    $entities['k'] = arrayItemsStartedWithLetter($aProducers, 'k');
    $entities['l'] = arrayItemsStartedWithLetter($aProducers, 'l');
    $entities['m'] = arrayItemsStartedWithLetter($aProducers, 'm');
    $entities['n'] = arrayItemsStartedWithLetter($aProducers, 'n');
    $entities['o'] = arrayItemsStartedWithLetter($aProducers, 'o');
    $entities['p'] = arrayItemsStartedWithLetter($aProducers, 'p');
    $entities['q'] = arrayItemsStartedWithLetter($aProducers, 'q');
    $entities['r'] = arrayItemsStartedWithLetter($aProducers, 'r');
    $entities['s'] = arrayItemsStartedWithLetter($aProducers, 's');
    $entities['t'] = arrayItemsStartedWithLetter($aProducers, 't');
    $entities['u'] = arrayItemsStartedWithLetter($aProducers, 'u');
    $entities['v'] = arrayItemsStartedWithLetter($aProducers, 'v');
    $entities['w'] = arrayItemsStartedWithLetter($aProducers, 'w');
    $entities['x'] = arrayItemsStartedWithLetter($aProducers, 'x');
    $entities['y'] = arrayItemsStartedWithLetter($aProducers, 'y');
    $entities['z'] = arrayItemsStartedWithLetter($aProducers, 'z'); 
    
    $data = array(
        'entities' => $entities,
        'pageTitle' => $GLOBALS['LANG']['shop_producers_title'],
	
    );

    echo Cms::$twig->render('templates/shop/producers.twig', array_merge($data, $params));     
}