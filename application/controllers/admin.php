<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (Cms::$modules['admin'] != 1)
	die('This module is disabled!');

//function my_autoloader($class)
//{
//    $filename = CMS_DIR . '/application/models/' . str_replace('\\', '/', $class) . '.php';
//    $filename = CMS_DIR . '/' . str_replace('\\', '/', $class) . '.php';
//    include($filename);
//}
//spl_autoload_register('my_autoloader');


require_once(MODEL_DIR . '/user.php');

$user = new User();

Cms::$twig->addGlobal('tinymce', false);
Cms::$twig->addGlobal('datepicker', false);
Cms::$twig->addGlobal('info', false);
Cms::$twig->addGlobal('error', false);

if (isset($_POST['action']) AND $_POST['action'] == 'login') {
	if ($user->login($_POST)) {
		Cms::$twig->addGlobal('info', 'Logowanie poprawne. Witaj w panelu administracyjnym.');
		Cms::getFlashBag()->get('info');
        
        redirect($_SERVER['HTTP_REFERER']);
	}	
}

if (!$user->logged()) {
	$data = array(
		'pageTitle'	=>	'Creative.CMS',
	);

	echo Cms::$twig->render('admin/login/show.twig', array_merge($data, $params));			
	die;
}

if ($_SESSION[USER_CODE]['privilege']['admin'] != 1)
	die('No permission at this level!');

//Cms::$tpl->assign('user', $_SESSION[USER_CODE]);
//Cms::$tpl->assign('dzis', date('d') . ' ' . date('F') . ' ' . date('Y') . 'r');

Cms::$twig->addGlobal('user', $_SESSION[USER_CODE]);
Cms::$twig->addGlobal('session', $_SESSION);
//Cms::$twig->addGlobal('statuses', $user->load_status());

$productStatus = new ProductStatus();
$statuses = $productStatus->getAll(['locale' => Cms::$session->get('locale_admin')]);
$statuses = getArrayByKey($statuses, 'translatable_id');

Cms::$twig->addGlobal('statuses', $statuses);