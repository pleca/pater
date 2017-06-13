<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('newsletter'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/newsletter.php');

global $oNewsletter;

$oNewsletter = new Newsletter();

$action = isset($_POST['action']) ? $action = $_POST['action'] : '';
$action2 = isset($_GET['action']) ? $action2 = $_GET['action'] : '';
        
if (isset($_GET['action2']) AND $_GET['action2'] == 'getCsv') {
	$oNewsletter->getCsv($_GET);
	showUsersList();
}

switch($action) {
    case 'addContinue':
        $id = $oNewsletter->add($_POST);
        showEdit($id);
        break;
    case 'saveUser':
        if ($oNewsletter->editUser($_POST)) {
            $_GET['qs'] = 'listUsers';
			$params['info'] = $GLOBALS['LANG']['info_edit'];
            showUsersList($params);
        } else {
			$params['error'] = $GLOBALS['LANG']['error_change'];
            showUserEdit($_POST['id'], $params);
        }
        break;
    case 'savePublish':
        $oNewsletter->edit($_POST);
        showList();
        break;
    case 'saveContinue':
        $oNewsletter->edit($_POST);
        showEdit($_POST['id']);
        break;
    case 'addUser':
        if ($oNewsletter->addUser($_POST)) {
            $_GET['qs'] = 'listUsers';
			$params['info'] = $GLOBALS['LANG']['info_add'];
            showUsersList($params);
        } else {
			$params['error'] = $GLOBALS['LANG']['error_add'];
            showUserAdd($params);
        }
        break;
    case 'send':
        if ($oNewsletter->send($_POST)) {
            showNewsletterSend($_POST);
        } else {
            showNewsletter($_POST['template_id']);
        }
        break;
}

if (!$action) {
    switch($action2) {
        case 'addForm':
            showAdd();
            break;
        case 'addPublish':
            $oNewsletter->add($_POST);
            showList();
            break;
        case 'listUsers':
            showUsersList();
            break;
        case 'deleteUser':
			if ($oNewsletter->deleteUser($_GET['id'])) {
				$params['info'] = $GLOBALS['LANG']['newsletter_user_deleted'];
			} else {
				$params['error'] = $GLOBALS['LANG']['newsletter_user_error_deleted'];
			}
			
            showUsersList($params);
            break;
        case 'sendNewsletter':
            showNewsletter();
            break;
        case 'editUser':
            showUserEdit($_GET['id']);
            break;
        case 'delete':
            $oNewsletter->delete($_GET['id']);
            showList();
            break;
        case 'edit':
            showEdit($_GET['id']);
            break;
        case 'addUserForm':
            showUserAdd();
            break;
    }
}

function showAdd() {
	global $oNewsletter;

	Cms::$tpl->assign('aItem', $_POST);
	Cms::$tpl->assign('tinyMce', true);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['newsletter_add']);
	Cms::$tpl->showPage('newsletter/add.tpl');
}

function showEdit($id) {
	global $oNewsletter;

	$aItem = $oNewsletter->loadAdminById($id);
	Cms::$tpl->assign('aItem', $aItem);
	Cms::$tpl->assign('tinyMce', true);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['newsletter_edit']);
	Cms::$tpl->showPage('newsletter/edit.tpl');
}

function showList() {
	global $oNewsletter;

	$limit = 25;
	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
		$_GET['page'] = 1;
	$limitStart = ($_GET['page'] - 1) * $limit;
	$aItems = $oNewsletter->loadAdmin($limitStart, $limit);
	$pages = $oNewsletter->getPagesAdmin($limit);
	Cms::$tpl->assign('aItems', $aItems);
	Cms::$tpl->assign('pages', $pages);
	Cms::$tpl->assign('page', $_GET['page']);
	Cms::$tpl->assign('interval', $limit * ($_GET['page'] - 1));
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['newsletter_title']);
	Cms::$tpl->showPage('newsletter/list.tpl');
}

function showUserAdd($params = []) {
	global $oNewsletter;

	$data = array(
		'entity' => $_POST,
		'pageTitle' => $GLOBALS['LANG']['newsletter_user_add']
	);

	echo Cms::$twig->render('admin/newsletter/users-add.twig', array_merge($data, $params));	
}

function showUserEdit($id, $params = []) {
	global $oNewsletter;

	$entity = $oNewsletter->loadUserAdminById($id);
	
	$data = array(
		'entity' => $entity,
		'pageTitle' => $GLOBALS['LANG']['newsletter_user_edit']
	);

	echo Cms::$twig->render('admin/newsletter/users-edit.twig', array_merge($data, $params));	
}

function showUsersList($params = []) {
	global $oNewsletter;

	$limit = 100;
	if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
		$_GET['page'] = 1;
	$limitStart = ($_GET['page'] - 1) * $limit;

	if (isset($_GET['action2']) AND $_GET['action2'] == 'search') {
		$params['qs'] = '&amp;first_name=' . $_GET['first_name'] . '&amp;last_name=' . $_GET['last_name'] . '&amp;email=' . $_GET['email'] . '&amp;active=' . $_GET['active'] . '&amp;lang_id=' . $_GET['lang_id'] . '&amp;action=' . $_GET['action'] . '&amp;action2=search';
	} else {
		$params['qs'] = '&amp;action=listUsers';
	}
	
	$entities = $oNewsletter->loadUsersAdmin($_GET, $limitStart, $limit);
	$pages = $oNewsletter->getUsersAdmin($_GET, $limit);
	
	$data = array(
		'entities' => $entities,
		'pages' => $pages,
		'interval' => $limit * ($_GET['page'] - 1),
		'pageTitle' => $GLOBALS['LANG']['newsletter_users_title']
	);

	echo Cms::$twig->render('admin/newsletter/users-list.twig', array_merge($data, $params));	
}

function showNewsletter($template_id = 0) {
	global $oNewsletter;

	if (isset($_GET['template_id']))
		$template_id = $_GET['template_id'];

	if ($template_id > 0) {
		$aItem = $oNewsletter->loadAdminById($template_id);
		$aUsers = $oNewsletter->getUsers();
		Cms::$tpl->assign('aItem', $aItem);
		Cms::$tpl->assign('aUsers', $aUsers);
	}

	$templatesSelect = $oNewsletter->loadTemplatesSelect();
	Cms::$tpl->assign('templatesSelect', $templatesSelect);
	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['newsletter_send']);
	Cms::$tpl->showPage('newsletter/send.tpl');
}

function showNewsletterSend($post) {
	global $oNewsletter;

	Cms::$tpl->assign('pageTitle', $GLOBALS['LANG']['newsletter_send']);
	Cms::$tpl->showPage('newsletter/sendConfirm.tpl');
}
