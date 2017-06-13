<?php
/* 2014-01-02 | creative.cms 14.1 */

if(!defined('NO_ACCESS')) die('No access to files!');
if (Cms::$modules['logistic_service'] != 1)
	die('This module is disabled!');
if($_SESSION[USER_CODE]['privilege']['logistic_service'] != 1) die('No permission at this level!');

require_once(MODEL_DIR . '/LogisticService.php');
global $logisticService;

$logisticService = new LogisticService();

if (isset($_GET['action']) AND $_GET['action'] == 'add') {
   show_add();
   
} elseif (isset($_POST['action']) AND $_POST['action'] == 'add') {
   $id = $logisticService->add($_POST);
   show_edit($id);
   
} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
   show_edit($_GET['id']);
   
} elseif (isset($_POST['action']) AND $_POST['action'] == 'edit') {
   $logisticService->edit($_POST);
   show_edit($_POST['id']);
   
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
   $logisticService->deleteById($_GET['id']);
   show_list();
   
} elseif (isset($_REQUEST['action']) AND $_REQUEST['action'] == 'generate') {
	if (isset($_POST['action']) AND isset($_POST['id'])) {
		$logisticService->edit($_POST);
	}
	
	show_pdf($_REQUEST['id']);
	
} else {	
   show_list();
}

function show_add() {
   global $logisticService;

	$last_id = $logisticService->get_last_id();	// pobieramy ostatni ID
	
	if ($last_id > 0) {
		$entity = $logisticService->getBy(['id' => $last_id])[0];	// pobieramy dane ostatniego dokumentu
		
	} else {
		$fields = $logisticService->get_table_fields();	// gdy nie ma wpisu w tabeli, pobieramy struktura i na tej podstawie tworzymy array z pustymi danymi
		
		foreach($fields as $v) {
			$entity[$v] = '';
		}
	}
	
	$entity = isset($_POST['action']) ? $_POST : $entity;	// jesli dane pochodza z formularza
	
	// generowanie kolejnego numeru SSCC
	$entity['sscc'] = $logisticService->generate_sscc($last_id);
   
	$data = array(
		'entity' => $entity,
		'pageTitle' => 'Logistic Service | ' . $GLOBALS['LANG']['add']
	);

	echo Cms::$twig->render('admin/logistic_services/add.twig', $data);
}

function show_edit($id) {
	global $logisticService;

	$entity = $logisticService->getBy(['id' => $id])[0];

	$data = array(
		'entity' => $entity,
		'pageTitle' => 'Logistic Service | ' . $GLOBALS['LANG']['edit']
	);

	echo Cms::$twig->render('admin/logistic_services/edit.twig', $data);
}

function show_pdf($id) {
	global $logisticService;

	$entity = $logisticService->getBy(['id' => $id])[0];
	$logisticService->generate($entity);
	die;
}

function show_list() {
   global $logisticService;

	$entities = $logisticService->getAll();

	$data = array(
		'entities' => $entities,
		'pageTitle' => 'Logistic Service | ' . $GLOBALS['LANG']['list']
	);

	echo Cms::$twig->render('admin/logistic_services/list.twig', $data);
}

?>
