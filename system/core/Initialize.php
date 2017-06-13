<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require(SYS_DIR . '/core/Cms.php');
require(SYS_DIR . '/core/ErrorHandling.php');
require(SYS_DIR . '/core/Functions.php');
require(SYS_DIR . '/database/Database.php');

function general_autoloader($class) {
    
    $dirs = array(
        ENTITY_DIR . '/',
        MODEL_DIR . '/',
    );
    
    foreach ($dirs as $dir) {
        if (file_exists($dir . $class . '.php')) {
            require_once($dir . $class . '.php');
            //only require the class once, so quit after to save effort (if you got more, then name them something else
            return;
        }
    }
}

spl_autoload_register('general_autoloader');

Cms::initialize();
Cms::$twig->addGlobal('conf', Cms::$conf);
Cms::$twig->addGlobal('template', Cms::$template);
Cms::$twig->addGlobal('colors', Cms::$colors);

$params = get_module();

if (in_array($params[0], Cms::$locales) && count(Cms::$frontendLanguages) > 1 && Cms::$session->get('locale') != Cms::$defaultLocale) {
    for ($i = 1; $i < count($params); $i++) {
        $params[$i - 1] = $params[$i];
    }    
	array_pop($params);
}

$controller = isset($params[0]) ? $params[0] : null;
$action = isset($params[1]) ? $params[1] : null;
$actionId = isset($params[2]) ? $params[2] : null;

if (in_array($controller, Cms::$locales)) {
    $controller = '';
}
    
Cms::$twig->addGlobal('controller', $controller);
Cms::$twig->addGlobal('action', $action);
Cms::$twig->addGlobal('actionId', $actionId);
Cms::$twig->addGlobal('hide_left_menu', $hide_left_menu);

setHeader();
setConstsFromConfig();

$uri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : '';
$_SERVER['PHP_SELF'] = isset($uri['path']) ? $uri['path'] : '';

$ip = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
define('CLIENT_IP', $ip);

switch ($controller) {
	case 'admin':
		initializeBackend($action);
		break;
    
	case 'api':
		require_once(CLASS_DIR . '/Api/api.php');
		break;
	
	default:
		initializeFrontend($controller, $action);
		break;
}


function initializeBackend($action = '') {
	global $params;

	setConstants();
	setLocaleAdmin();	
	
	require(CMS_DIR . '/application/languages/admin-' . LOCALE_ADMIN . '.php');

	Cms::$twig->addGlobal('language', LOCALE_ADMIN);
	Cms::$twig->addGlobal('lang', $GLOBALS['LANG']);
	Cms::$twig->addGlobal('action', $action);
	
	require_once(CONTROL_DIR . '/admin.php');

	if (isset($action) AND file_exists(CONTROL_DIR . '/admin/' . $action . '.php')) {
		require_once(CONTROL_DIR . '/admin/' . $action . '.php');
	} else {
		require_once(CONTROL_DIR . '/admin/index.php');
	}
}


function initializeFrontend($controller = '', $action = '') {
	global $params;

    if ($controller == 'switch-language') {
        Cms::$session->set('locale', $action);
    }

    if (count(Cms::$frontendLanguages) == 1) {
//        echo '1. jeden jezyk';
        define('URL', SERVER_URL . CMS_URL);
        $language = Cms::$frontendLanguages[0];
    }
    
    if (count(Cms::$frontendLanguages) > 1) {
        if (Cms::$session->get('locale') != Cms::$defaultLocale) {
//            echo '2. kilka jezykow, obency nie jest defaultowy';

            foreach (Cms::$langs as $v) {
                if ($v['code'] == Cms::$session->get('locale')) {
                    $language = $v;
                }
            }

            define('URL', SERVER_URL . CMS_URL . '/' . Cms::$session->get('locale'));
        } else {
//            echo '3. kilka jezykow, obency jest defaultowy';
            $language = Cms::$defaultLanguage;
            define('URL', SERVER_URL . CMS_URL);
        }
    }

	if (!isset($language)) {
		foreach (Cms::$langs as $v) {
			if ($v['default'] == 1) {
				$language = $v;
			}
		}
	}

	define('_ID', $language['id']);  // id jezyka aktywnego w serwisie
	
	if (!Cms::$session->get('locale')) {
		Cms::$session->set('locale', $language['code']);
	}		

	require(CMS_DIR . '/application/languages/site-' . Cms::$session->get('locale') . '.php');

	Cms::$twig->addGlobal('language', $language);
	Cms::$twig->addGlobal('lang', $GLOBALS['LANG']);
	Cms::$twig->addGlobal('logotypes', Cms::$logotypes);

	require_once(CONTROL_DIR . '/templates.php');

	if (isset($controller) && $controller != 'switch-language' AND file_exists(CONTROL_DIR . '/templates/' . $controller . '.php')) {
        require_once(CONTROL_DIR . '/templates/' . $controller . '.php');
	} else {
		require_once(CONTROL_DIR . '/templates/index.php');
	}
}

function setConstants() {
	define('URL', SERVER_URL . CMS_URL);
	
	foreach (Cms::$langs as $v) { // domyslny jezyk serwisu
		if ($v['default'] == 1) {
			define('_ID', $v['id']);
		}
	}
}

function setLocaleAdmin() {
	if (isset($_GET['lang'])) {
		$language = addslashes($_GET['lang']);
		setcookie('language', $language, time() + 30 * 24 * 3600);
		$_SESSION['language'] = $language;
	} elseif (isset($_COOKIE['language'])) {
		$language = $_COOKIE['language'];
	} elseif (isset($_SESSION['language'])) {
		$language = $_SESSION['language'];
	} else {
		$language = 'pl';
	}

	
	define('LOCALE', CMS::$defaultLocale);
	define('LOCALE_ADMIN', $language);
	
	Cms::$session->set('locale_admin', $language);
}
