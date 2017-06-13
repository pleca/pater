<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

foreach (Cms::$langs as $v) {
	if ($v['code'] == $params[0]) {
		$lang = $v;
	}
}
for ($i = 1; $i < count($params); $i++) {
	$params[$i - 1] = $params[$i];
}
array_pop($params);
