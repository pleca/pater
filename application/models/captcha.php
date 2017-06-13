<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

$aSigns1 = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
$aSigns2 = array('kwh', 'xqb', 'ghq', 'rwj', 'bsd', 'vkl', 'laj', 'pyf', 'fpu', 'ven', 'rkx', 'yfo', 'pmo', 'ngh', 'tmf', 'laf', 'vwu', 'lwq', 'pio', 'lle', 'huf', 'lij', 'hfc', 'duj', 'ybi', 'uzl', 'ync', 'ubg', 'yrc', 'ufg', 'yvr', 'luf', 'ycu', 'zst', 'vgx', 'rtm');
$amount = count($aSigns1);
$min = 3;
$max = 8;
$i = rand($min, $max);
$sign = '';
$str_captcha = '';
$captcha = array();
for ($i; $i > 0; $i--) {
	$rand = rand(0, $amount - 1);
	$str_captcha.= $aSigns1[$rand];
	$a['src'] = $aSigns2[$rand];
	$a['alt'] = $aSigns1[rand(0, $amount - 1)];
	$a['title'] = $aSigns1[rand(0, $amount - 1)];
	$a['top'] = rand(-3, 3);
	$captcha[] = $a;
}
Cms::$twig->addGlobal('captcha', $captcha);
$_SESSION['captcha'] = base64_encode($str_captcha);

