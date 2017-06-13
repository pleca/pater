<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

if (!$oCustomer->logged()) {
	redirect_301(URL . '/customer/login.html');
	die;
}

require_once(CMS_DIR . '/application/models/comments.php');
$oComments = new Comments();

$comments = $oComments->loadCommentsByCustomer($_SESSION[CUSTOMER_CODE]['id']);

$data = array(
	'comments' => $comments,
	'pageTitle' => $GLOBALS['LANG']['c_comment'] . ' - ' . Cms::$seo['title']
);

echo Cms::$twig->render('templates/customer/comment.twig', $data); 

