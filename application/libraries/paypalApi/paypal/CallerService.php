<?php

/* * **************************************************
  CallerService.php

  This file uses the constants.php to get parameters needed
  to make an API call and calls the server.if you want use your
  own credentials, you have to change the constants.php

  Called by TransactionDetails.php, ReviewOrder.php,
  DoDirectPaymentReceipt.php and DoExpressCheckoutPayment.php.

 * ************************************************** */

function nvpHeader() {
	
	global $nvp_Header;

	$aPayPal_CONF = include (dirname(__FILE__) . '/constants.php');
    $aPayPal_CONF['API_USERNAME'] = Cms::$conf['paypal_api_username'];
    $aPayPal_CONF['API_PASSWORD'] = Cms::$conf['paypal_api_password'];
    $aPayPal_CONF['API_SIGNATURE'] = Cms::$conf['paypal_api_signature'];
    $aPayPal_CONF['SHOP_LOGO_URL'] = CMS_DIR . '/files/graphics/logo.png';
        
	if (defined('AUTH_MODE')) {
		//$AuthMode = "3TOKEN"; //Merchant's API 3-TOKEN Credential is required to make API Call.
		//$AuthMode = "FIRSTPARTY"; //Only merchant Email is required to make EC Calls.
		//$AuthMode = "THIRDPARTY";Partner's API Credential and Merchant Email as Subject are required.
		$AuthMode = "AUTH_MODE";
	} else {

		if ((!empty($aPayPal_CONF["API_USERNAME"])) && (!empty($aPayPal_CONF["API_PASSWORD"])) && (!empty($aPayPal_CONF["API_SIGNATURE"])) && (!empty($aPayPal_CONF["SUBJECT"]))) {
			$AuthMode = "THIRDPARTY";
		} else if ((!empty($aPayPal_CONF["API_USERNAME"])) && (!empty($aPayPal_CONF["API_PASSWORD"])) && (!empty($aPayPal_CONF["API_SIGNATURE"]))) {
			$AuthMode = "3TOKEN";
		} elseif (!empty($aPayPal_CONF["AUTH_TOKEN"]) && !empty($aPayPal_CONF["AUTH_SIGNATURE"]) && !empty($aPayPal_CONF["AUTH_TIMESTAMP"])) {
			$AuthMode = "PERMISSION";
		} elseif (!empty($aPayPal_CONF["SUBJECT"])) {
			$AuthMode = "FIRSTPARTY";
		}
	}
	switch ($AuthMode) {

		case "3TOKEN" :
			$nvpHeaderStr = "&PWD=" . urlencode($aPayPal_CONF["API_PASSWORD"]) . "&USER=" . urlencode($aPayPal_CONF["API_USERNAME"]) . "&SIGNATURE=" . urlencode($aPayPal_CONF["API_SIGNATURE"]);
			break;
		case "FIRSTPARTY" :
			$nvpHeaderStr = "&SUBJECT=" . urlencode($aPayPal_CONF["SUBJECT"]);
			break;
		case "THIRDPARTY" :
			$nvpHeaderStr = "&PWD=" . urlencode($aPayPal_CONF["API_PASSWORD"]) . "&USER=" . urlencode($aPayPal_CONF["API_USERNAME"]) . "&SIGNATURE=" . urlencode($aPayPal_CONF["API_SIGNATURE"]) . "&SUBJECT=" . urlencode($aPayPal_CONF["SUBJECT"]);
			break;
		case "PERMISSION" :
			$nvpHeaderStr = formAutorization($aPayPal_CONF["AUTH_TOKEN"], $aPayPal_CONF["AUTH_SIGNATURE"], $aPayPal_CONF["AUTH_TIMESTAMP"]);
			break;
	}
	return $nvpHeaderStr;
}

/**
 * hash_call: Function to perform the API call to PayPal using API signature
 * @methodName is name of API  method.
 * @nvpStr is nvp string.
 * returns an associtive array containing the response from the server.
 */
function hash_call($methodName, $nvpStr) {
	
	$aPayPal_CONF = include (dirname(__FILE__) . '/constants.php');
    $aPayPal_CONF['API_USERNAME'] = Cms::$conf['paypal_api_username'];
    $aPayPal_CONF['API_PASSWORD'] = Cms::$conf['paypal_api_password'];
    $aPayPal_CONF['API_SIGNATURE'] = Cms::$conf['paypal_api_signature'];
    $aPayPal_CONF['SHOP_LOGO_URL'] = CMS_DIR . '/files/graphics/logo.png';
    
	//declaring of global variables
	global $nvp_Header;
	
	// form header string
	$nvpheader = nvpHeader();
	//setting the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $aPayPal_CONF["API_ENDPOINT"]);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	//in case of permission APIs send headers as HTTPheders
	if (!empty($aPayPal_CONF["AUTH_TOKEN"]) && !empty($aPayPal_CONF["AUTH_SIGNATURE"]) && !empty($aPayPal_CONF["AUTH_TIMESTAMP"])) {
		$headers_array[] = "X-PP-AUTHORIZATION: " . $nvpheader;

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
		curl_setopt($ch, CURLOPT_HEADER, false);
	} else {
		$nvpStr = $nvpheader . $nvpStr;
	}
	//if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	//Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
	if ($aPayPal_CONF['USE_PROXY'])
		curl_setopt($ch, CURLOPT_PROXY, $aPayPal_CONF['PROXY_HOST'] . ":" . $aPayPal_CONF['PROXY_PORT']);

	//check if version is included in $nvpStr else include the version.
	if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
		$nvpStr = "&VERSION=" . urlencode($aPayPal_CONF["VERSION"]) . $nvpStr;
	}

	$nvpreq = "METHOD=" . urlencode($methodName) . $nvpStr;

	//setting the nvpreq as POST FIELD to curl
	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	//getting response from server
	$response = curl_exec($ch);

	if (curl_errno($ch) == 60) {

		curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
		$response = curl_exec($ch);
	}

	//convrting NVPResponse to an Associative Array
	$nvpResArray = deformatNVP($response);
	$nvpReqArray = deformatNVP($nvpreq);
	$_SESSION['nvpReqArray'] = $nvpReqArray;

	if (curl_errno($ch)) {
		// moving to display page to display curl errors
		$_SESSION['curl_error_no'] = curl_errno($ch);
		$_SESSION['curl_error_msg'] = curl_error($ch);
		//$location = "APIError.php";
		//header("Location: $location");
	} else {
		//closing the curl
		curl_close($ch);
	}

	return $nvpResArray;
}

/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
 * It is usefull to search for a particular key and displaying arrays.
 * @nvpstr is NVPString.
 * @nvpArray is Associative Array.
 */
function deformatNVP($nvpstr) {

	$intial = 0;
	$nvpArray = array();


	while (strlen($nvpstr)) {
		//postion of Key
		$keypos = strpos($nvpstr, '=');
		//position of value
		$valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

		/* getting the Key and Value values and storing in a Associative Array */
		$keyval = substr($nvpstr, $intial, $keypos);
		$valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
		//decoding the respose
		$nvpArray[urldecode($keyval)] = urldecode($valval);
		$nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
	}
	return $nvpArray;
}

function formAutorization($auth_token, $auth_sig, $auth_stamp) {
	$authString = "token=" . $auth_token . ",signature=" . $auth_sig . ",timestamp=" . $auth_stamp;
	return $authString;
}

?>
