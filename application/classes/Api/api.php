<?php

$api = isset($params[1]) ? $params[1] : '';

if ($api && file_exists(CLASS_DIR . '/Api/' . ucfirst($api) . '/' . ucfirst($api) .'.php')) {
	require_once(CLASS_DIR . '/Api/' . ucfirst($api) . '/' . ucfirst($api) .'.php');
}

//remove after finish new api
if ($api != 'ga') {
    if ($api AND file_exists(CONTROL_DIR . '/public/apiArray/'.$api.'.php')) {
        require_once(CONTROL_DIR . '/public/apiArray/'.$api.'.php');
    }
}

die;


