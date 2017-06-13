<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$module = CMS_DIR . '/public/sitemap/' . Cms::$session->get('locale') . '.xml';
if (file_exists($module)) {
	echo file_get_contents($module);
	die;
} else {
	if (!URL)
		redirect_301('/');
	else
		redirect_301(URL);
	die;
}


