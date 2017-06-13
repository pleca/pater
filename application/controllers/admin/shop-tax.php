<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');
if (Cms::$modules['shop'] != 1)
	die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
	die('No permission at this level!');

require_once(ENTITY_DIR . '/Tax.php');
use Application\Entity\Tax;

$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');

if (isset($_POST['action']) AND $_POST['action'] == 'add') {
	$tax = new Tax();
	$tax->setValue($_POST['value']);
	$tax->setPosition($tax->getMaxPosition()[0]+1);

	if ($tax->validate()) {
		CMS::$entityManager->persist($tax);
		CMS::$entityManager->flush($tax);		
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
	} else {
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
	}
	
	redirect(URL . '/admin/shop-tax');

} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	$params['item'] = $taxRepository->find($_GET['id']);
	showList($params);
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {

	$tax = $taxRepository->find($_POST['id']);
	$tax->setValue($_POST['value']);

	if ($tax->validate()) {
		CMS::$entityManager->persist($tax);
		CMS::$entityManager->flush();
		Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_edit']);
	} else {
		$params['item'] = $tax;
	}
	
	showList($params);	

} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	$entity =  $taxRepository->find($_REQUEST['id']);
	CMS::$entityManager->remove($entity);
	CMS::$entityManager->flush();	
	Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
	redirect(URL . '/admin/shop-tax');
} else {
	showList();
}

function showEdit($params = []) {
	echo 'tuuu';die;
}

function showList($params = []) {
	$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
	$entities = $taxRepository->findBy([],['position' => 'ASC']);

	$data = array(
		'entities' => $entities,
		'pageTitle' => $GLOBALS['LANG']['shop_tax_title']
	);

	echo Cms::$twig->render('admin/shop/tax.twig', array_merge($data, $params));	
}