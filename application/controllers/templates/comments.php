<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/comments.php');

$oComments = new Comments($comments_group);

if (LOGGED == 1) {
	Cms::$twig->addGlobal('comments_show', true);
	Cms::$twig->addGlobal('comments_write', true);
} else {
	Cms::$twig->addGlobal('comments_show', true);
	Cms::$twig->addGlobal('comments_write', true);
}

if (isset($_POST['action']) AND $_POST['action'] == 'addComment') {
	if ($tmp = $oComments->add($_POST, $comments_parent_id, $comments_table)) {
		
	}
}

$limit = 10;
if (!isset($_GET['page']) OR (int) $_GET['page'] < 1 OR ! is_numeric($_GET['page']))
	$_GET['page'] = 1;
$limitStart = ($_GET['page'] - 1) * $limit;
$aComments = $oComments->loadComments($comments_parent_id, $limitStart, $limit);
$pages = $oComments->getPages($comments_parent_id, $limit);
if (LOGGED != 1)
	require_once(CMS_DIR . '/application/models/captcha.php');  // captcha obrakowy

Cms::$twig->addGlobal('aComments', $aComments);
Cms::$twig->addGlobal('rate', $pages[2]);
Cms::$twig->addGlobal('pages', $pages[1]);
Cms::$twig->addGlobal('page', $_GET['page']);
