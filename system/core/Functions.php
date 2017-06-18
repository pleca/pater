<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

function dump($array, $title = '') {
	echo '<pre style="background:#fff;color:red;">';
	if ($title) {
		echo '<h3 style="margin:0;">' . strtoupper($title) . '</h3>';
	}
	print_r($array);
	echo '</pre>';
}

function get_module() {
	$pattern = '(^' . CMS_URL . '/|\?.*|.php|.html|.htm|.xml)';
	$replacement = '';
	$subject = $_SERVER['REQUEST_URI'];
	$params = preg_replace($pattern, $replacement, $subject);
	$params = explode('/', $params);
	return $params;
}

function redirect_301($url = '') {
	if (isset($url)) {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $url);
		header("Connection: close");
		exit;
	}
}

function error_404() {
	header("HTTP/1.0 404 Not Found");
	echo Cms::$twig->render('templates/other/error-404.twig');
	exit;
}

function check_permission($moduleName = '', $scope = 'both') { //scopes: both, user, module
	if (!defined('NO_ACCESS')) {
		die('No access to files!');
	}
    
    if ($scope == 'both' || $scope == 'module') {
        if (Cms::$modules[$moduleName] != 1) {
            die('This module is disabled!');
        }
    }
    
    if ($scope == 'both' || $scope == 'user') {
        if ($_SESSION[USER_CODE]['privilege'][$moduleName] != 1) {
            die('No permission at this level!');
        }
    }
}

function check_level($level = null) {
	if (!defined('NO_ACCESS')) {
		die('No access to files!');
	}

    if (!$level) {
		die('No permission at this level!');
	}
	
	if ($_SESSION[USER_CODE]['level'] != $level && $_SESSION[USER_CODE]['level'] > $level) {
		die('No permission at this level!');
	}
}

function clearName($txt) {
	$txt = trim(strip_tags(stripslashes($txt)));
	$txt = addslashes(str_replace('"', '&quot;', $txt));
	return $txt;
}

function clearHtml($txt, $limit = 250, $end = '') {
	$txt = substr(strip_tags($txt), 0, $limit);
	$txt = str_replace('&oacute;', 'ó', $txt);
	$txt = str_replace('&Oacute', 'Ó', $txt);
	$txt = preg_replace('/(&[a-z]+[;]?)/', '', $txt);
	$txt = str_replace('&...', '...', $txt);
	$txt = str_replace('&', 'and', $txt);
	$txt = str_replace('
', '', $txt);
	$txt = $txt . $end;
	return $txt;
}

function makeUrl($txt) {
	$search = array('Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', ' ', '&', 'quot;');
	$replace = array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', '-', '', '');
	$txt = str_replace($search, $replace, strip_tags($txt));
	$txt = preg_replace('/[^A-Za-z0-9-]/', '', $txt);
	return rawurlencode(strtolower($txt));
}

function changeFileName($file, $end, $name_url = '') {
	$lastDot = strrpos($file, '.');
	$name = substr($file, 0, $lastDot);
	$ext = substr($file, $lastDot, strlen($file));
	if (!empty($name_url))
		$fileName = $name_url . $end . $ext;
	else
		$fileName = $name . $end . $ext;
	return strtolower($fileName);
}

function get_photo($dir, $url, $file) {
	$file_m = change_file_name($file, '_m');
	$file_s = change_file_name($file, '_s');
	$file_e = change_file_name($file, '_e');
	$row = '';
	if (!empty($file)) {
		if (file_exists($dir . '/' . $file)) {
			$row['normal'] = $url . '/' . $file;
		}
		if (file_exists($dir . '/' . $file_m)) {
			$row['medium'] = $url . '/' . $file_m;
		}
		if (file_exists($dir . '/' . $file_s)) {
			$row['small'] = $url . '/' . $file_s;
		}
		if (file_exists($dir . '/' . $file_e)) {
			$row['extra'] = $url . '/' . $file_e;
		}
	}
	return $row;
}

function change_file_name($file, $end, $name_url = '') {
	$lastDot = strrpos($file, '.');
	$name = substr($file, 0, $lastDot);
	$ext = substr($file, $lastDot, strlen($file));
	if (!empty($name_url)) {
		$fileName = $name_url . $end . $ext;
	} else {
		$fileName = $name . $end . $ext;
	}
	return strtolower($fileName);
}

function maddslashes($array) {
	if (!is_array($array)) {
		return addslashes($array);
	} else {
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$array[$key] = maddslashes($val);
			} else {
				$array[$key] = addslashes($val);
			}
		}
		return $array;
	}
}

function mstripslashes($array) {
	if (!is_array($array)) {
		return stripslashes($array);
	} else {
		foreach ($array as $key => $val) {
			$array[$key] = stripslashes($val);
		}
		return $array;
	}
}

function checkLogin($string) {
	return preg_match("/[a-z0-9_]{3,}/", $string);
}

function checkPassword($string) {
	return preg_match('/^(?=[a-z0-9_!@#$%^&\*-+]*?[A-Z])(?=[a-z0-9_!@#$%^&\*-+]*?[a-z])(?=[a-z0-9_!@#$%^&\*-+]*?[0-9])([a-z0-9_!@#$%^&\*-+]{8,})$/Diu', $string);
}

function checkEmail($string) {
	return preg_match("/^(.+?)@(([a-z0-9\.-]+?)\.[a-z]{2,5})$/i", $string);
}

function checkFormatDate($string) {
	return preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $string);
}

function checkPostcode(&$toCheck) {

	// Permitted letters depend upon their position in the postcode.
	$alpha1 = "[abcdefghijklmnoprstuwyz]";  // Character 1
	$alpha2 = "[abcdefghklmnopqrstuvwxy]";  // Character 2
	$alpha3 = "[abcdefghjkpmnrstuvwxy]"; // Character 3
	$alpha4 = "[abehmnprvwxy]"; // Character 4
	$alpha5 = "[abdefghjlnpqrstuwxyz]";  // Character 5
	// Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA with a space
	$pcexp[0] = '/^(' . $alpha1 . '{1}' . $alpha2 . '{0,1}[0-9]{1,2})([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})$/';

	// Expression for postcodes: ANA NAA
	$pcexp[1] = '/^(' . $alpha1 . '{1}[0-9]{1}' . $alpha3 . '{1})([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})$/';

	// Expression for postcodes: AANA NAA
	$pcexp[2] = '/^(' . $alpha1 . '{1}' . $alpha2 . '{1}[0-9]{1}' . $alpha4 . ')([[:space:]]{0,})([0-9]{1}' . $alpha5 . '{2})$/';

	// Exception for the special postcode GIR 0AA
	$pcexp[3] = '/^(gir)(0aa)$/';

	// Standard BFPO numbers
	$pcexp[4] = '/^(bfpo)([0-9]{1,4})$/';

	// c/o BFPO numbers
	$pcexp[5] = '/^(bfpo)(c\/o[0-9]{1,3})$/';

	// Overseas Territories
	$pcexp[6] = '/^([a-z]{4})(1zz)$/';

	// Load up the string to check, converting into lowercase
	$postcode = strtolower($toCheck);

	// Assume we are not going to find a valid postcode
	$valid = false;

	// Check the string against the six types of postcodes
	foreach ($pcexp as $regexp) {
		if (preg_match($regexp, $postcode, $matches)) {

			// Load new postcode back into the form element
			$postcode = strtoupper($matches[1] . ' ' . $matches [3]);

			// Take account of the special BFPO c/o format
			$postcode = str_replace('C\/O', 'c/o ', $postcode);

			// Remember that we have found that the code is valid and break from loop
			$valid = true;
			break;
		}
	}

	// Return with the reformatted valid postcode in uppercase if the postcode was
	// valid
	if ($valid) {
		$toCheck = $postcode;
		$data['post_code'] = $postcode;
		$_POST['post_code'] = $postcode;
		return true;
	} else
		return false;
}

function formatPrice($price = 0, $tax = 0) {
	if ($tax > 0) {
		$price = round($price + $price * $tax / 100, 2);
	}
	$price = round($price, 2);
	if (UNIX == 1) {
		$price = money_format("%i", $price);
	} else {
		$price = number_format($price, 2, '.', '');
	}
	return $price;
}

function setHash($input, $salt) {
	$salt = substr($salt, 0, 17);
	$output = hash('sha256', $input . SALT, false);
	$output = substr($output, 0, SALT_NUMBER) . $salt . substr($output, SALT_NUMBER);
	return $output;
}

function setHeader() {
	$params = get_module();

	if (isset($params) AND ( $params[0] == 'rss' OR $params[0] == 'sitemap')) {
		header("Content-type: text/xml; charset=utf-8");
	} elseif (isset($params[1]) AND ( $params[1] == 'rss' OR $params[1] == 'sitemap')) {
		header("Content-type: text/xml; charset=utf-8");
	} else {
		header("Content-type: text/html; charset=utf-8");
	}
}

function setConstsFromConfig() {
	define('EMAIL_OFFICE', Cms::$conf['email_office']);
	define('COMPANY_NAME', Cms::$conf['company_name']);
	define('EMAIL_ADMIN', Cms::$conf['email_admin']);
	define('SALT2', Cms::$conf['salt']);
	define('EPSILON', 0.1);
}

function arrayOrderByKey()
{
    $args = func_get_args();
	
	if (!$args[0]) {
		return false;
	}
	
    $data = array_shift($args);

    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
			
			if ($data) {
				foreach ($data as $key => $row) {
					$tmp[$key] = isset($row[$field]) ? $row[$field] : '';
				}
			}
			
            $args[$n] = $tmp;
		}
    }
    $args[] = &$data;

    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function redirect($url = '') {
	if (isset($url)) {
		header('Location: ' . $url);
		exit;
	}
}

/*
 * Get from array items started on letter, filtr array by letter
 */
function arrayItemsStartedWithLetter($items, $letter, $property = 'name') {
    
    $filteredItems = array_filter($items, function($item) use ($letter, $property) {
        return substr(strtolower($item[$property]), 0, 1) == $letter;
    });
    
    return $filteredItems;
}

function arrayItemsStartedWithNumber($items, $property = 'name') {
    
    $filteredItems = array_filter($items, function($item) use ($property) {        
        return is_numeric(substr(strtolower($item[$property]), 0, 1));
    });
    
    return $filteredItems;
}

//11.
//zamiana id na nazwę
function getFullCategoryName($categoryId, $categories) {
    $fullCategoryName = '';

    foreach ($categories as $category) {

        if (isset($category["id"]) && $categoryId == $category["id"]) {
            $fullCategoryName = $category['name'];
        }

		if (isset($category['subcategories'])) {
			foreach ($category['subcategories'] as $subcategory) {
				if (isset($category['name']) && isset($subcategory['name']) && $categoryId == $subcategory['id']) {
					$fullCategoryName = $category['name'] . ' -> ' . $subcategory['name'];
				}
			}
		}
    }
    
    return $fullCategoryName;
}

function getArrayByKey($array, $key) {
    if (!$array) {
        return false;
    }

    foreach ($array as $k => $item) {
        $newArray[$item[$key]] = $item;
    }
    
    return $newArray;
}

/*
 * Get file from url
 */
function getFileFormat($url) {
    $tempName = tempnam('/tmp', 'php_files');
    $originalName = basename(parse_url($url, PHP_URL_PATH));

    $imgRawData = file_get_contents($url);
    file_put_contents($tempName, $imgRawData);

    $file = array(
        'name' => $originalName,
        'type' => mime_content_type($tempName),
        'tmp_name' => $tempName,
        'error' => 0,
        'size' => strlen($imgRawData),
    );    

    return $file;
}
    
/**
 * Add to $_FILES from external url
 * sample usage: addToFiles('google_favicon', 'http://google.com/favicon.ico');
 * use addToFiles('image', 'http://someurl.ll/image.jpg');
 * @param string $key
 * @param string $url sample http://some.tld/path/to/file.ext
 */
function addToFiles($key = null, $url)
{
    $tempName = tempnam('/tmp', 'php_files');
    $originalName = basename(parse_url($url, PHP_URL_PATH));

    $imgRawData = file_get_contents($url);
    file_put_contents($tempName, $imgRawData);
    
    if ($key) {
        $_FILES[$key] = array(
            'name' => $originalName,
            'type' => mime_content_type($tempName),
            'tmp_name' => $tempName,
            'error' => 0,
            'size' => strlen($imgRawData),
        );
    } else {
        $_FILES[] = array(
            'name' => $originalName,
            'type' => mime_content_type($tempName),
            'tmp_name' => $tempName,
            'error' => 0,
            'size' => strlen($imgRawData),
        );        
    }
}
/*
 * comparing floats using epsilon method
 */
function compareFloats($float1, $float2) {
	
	if (abs($float1 - $float2) < EPSILON) {
		return true;
	}
	
	return false;
}

function str_replace_first($from, $to, $subject) {
    $from = '/'.preg_quote($from, '/').'/';

    return preg_replace($from, $to, $subject, 1);
}

function strpos_array($haystack, $needles) {
    if (is_array($needles) ) {
        foreach ($needles as $str) {
            if (is_array($str)) {
                $pos = strpos_array($haystack, $str);
            } else {
                $pos = strpos($haystack, $str);
            }
			
            if ($pos !== FALSE) {
                return $pos;
            }
        }
    } else {
        return strpos($haystack, $needles);
    }
    
    return false;
}
//echo strpos_array('This is a test', array('test', 'drive')); // Output is 10

function filterArrayKeyByPattern($array, $pattern) {
    if (!$array) {
        return false;
    }

    foreach ($array as $k => $item) {
		$pos = strpos($k, $pattern);
		
		if ($pos !== false) {
			$newArray[$k] = $item;
		}		        
    }
    
    return $newArray;
}


