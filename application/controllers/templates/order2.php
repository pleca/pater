<?php
/* 2011-01-03 | creative.cms */

/* 2012-08-03 | integracja platnosci PAYPAL
   modyfikowane pliki:
      \application\controllers\templates\order.php
      \application\languages\enSite.php
      \application\languages\plSite.php
      \application\views\templates\header.tpl
      \application\views\templates\shop\finish.tpl
   
   dodane pliki:
      \library\cardinal\
      \library\paypal\
      \library\simple_html_dom.php
      \application\controllers\templates\mcs_learn_more.php
      \application\controllers\templates\vbv_learn_more.php
      \application\views\templates\paypal\
      \public\scripts\jquery.tipTip.js
      \public\styles\tipTip.css
 
   zmiany w bazie zrzuty zapytan
      ALTER TABLE `shop_orders` ADD `paypal_respone` TEXT NOT NULL ;
      INSERT INTO `magicsupplements`.`shop_payment` (`id`, `name`, `order`, `active`) VALUES ('5', 'PayPal', '4', '0'), ('6', 'CreditCard 	', '3', '0');

   ustawienia:
      \library\paypal\constants.php
      \library\cardinal\lib\curl\CentinelConfig.php
      https://paypal.cardinalcommerce.com/MerchAdmin/viewprofile.asp - tutaj ustawiamy haslo
      https://www.paypal.com/uk/cgi-bin/webscr?cmd=_profile-api-signature - api access
*/

if(!defined('NO_ACCESS')) die('No access to files!');

if($aModules['orders'] != 1) die;

require_once(CMS_DIR . '/application/models/shopOrders.php');
require_once(CMS_DIR . '/application/models/shopLojal.php');
// < PAYPAL
require_once(CMS_DIR . '/application/libraries/paypal/CallerService.php');
// > PAYPAL
// < CARDINAL
require_once(CMS_DIR . '/application/libraries/cardinal/lib/curl/CentinelConfig.php');
require_once(CMS_DIR . '/application/libraries/cardinal/lib/curl/CentinelClient.php');
require_once(CMS_DIR . '/application/libraries/cardinal/lib/curl/CentinelErrors.php');
require_once(CMS_DIR . '/application/libraries/cardinal/lib/curl/CentinelUtility.php');
require_once(CMS_DIR . '/application/libraries/cardinal/lib/curl/XMLParser.php');
// > CARDINAL
$oOrders = new Orders($oCore);
$oLojal = new Lojal($oCore);

function check_cc($cc, $extra_check = false){
    $cards = array(
        "visa" => "(4\d{12}(?:\d{3})?)",
        "amex" => "(3[47]\d{13})",
        "jcb" => "(35[2-8][89]\d\d\d{10})",
        "maestro" => "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
        "solo" => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
        "mastercard" => "(5[1-5]\d{14})",
        "switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
    );
    $names = array("Visa", "American Express", "JCB", "Maestro", "Solo", "Mastercard", "Switch");
    $matches = array();
    $pattern = "#^(?:".implode("|", $cards).")$#";
    $result = preg_match($pattern, str_replace(" ", "", $cc), $matches);
    if($extra_check && $result > 0){
        $result = (validatecard($cc))?1:0;
    }
    return ($result>0)?$names[sizeof($matches)-2]:false;
}

if(!isset($_SESSION['customer']['access']) AND  !$oCustomer -> logged()) // formularz z danymi jesli nie zalogowani lub nie wprowadzilismy formularza
{
	if(isset($_SERVER['HTTP_USER_AGENT']) AND strpos($_SERVER['HTTP_USER_AGENT'], "Safari") == true AND strpos($_SERVER['HTTP_USER_AGENT'], "Chrome") === false)
	{
		$oTpl -> assign('redirect', true);
	}
	else
	{
		redirect301(SERVER_URLS.CMS_URL.LANG_URL.'/customer/login2.html');
		die;
	}
}
if(isset($_SESSION['customer']['access']) AND $_SESSION['customer']['access'] == 'without_login') // jesli mam dane z formualrza to wrzucamy je do szablonu
{
	$oTpl -> assign('customer', $_SESSION['customer']);
}
if(isset($_POST['action']) AND $_POST['action'] == 'promotion_code')
{
	require_once(CMS_DIR . '/application/models/phrase.php');
	$oPhrase = new Phrase($oCore);
	 
	if($discount = $oPhrase -> getDiscount($_POST['promotion_code']))
	{
		$_SESSION['customer']['discount'] = $discount;
		$_SESSION['customer']['promotion_code'] = $_POST['promotion_code'];
	}
	else
	{
		$customer = $oCustomer -> loadById($_SESSION['customer']['id']);
		$_SESSION['customer']['discount'] = $customer['discount'];
		$_SESSION['customer']['promotion_code'] = '';
	}
	
	$_SESSION['ppCustomerDiscount'] = $_SESSION['customer']['discount'];
	
	showOrder();
	die();
}
elseif(isset($_POST['action']) AND $_POST['action'] == 'save_order')
{
	$oTpl -> assign('customer', $_POST);   // dane klienta z formualrza
	 
	$aBasket = $oBasket -> loadBasketOrder();
	$basketInfo = $oBasket -> getInfo();
	$basketInfo['mnoznik'] = $oLojal -> getSklepMnoznik('online');
	$basketInfo['razemPkt'] = $basketInfo['userPoints'] = 0;
	$basketInfo['dzielnik'] = 0.01;
	 
	if(isset($_POST['transport']) AND $_POST['transport'])
	{
		$transport = $oOrders -> getTransport($_POST['transport'], $basketInfo['sum'], $basketInfo['prize']);
		if(LOGGED == 1)
		{
			$basketInfo['razemPkt'] = $basketInfo['sum_points'];   // ilosc punktow potrzebna do zaplaty
			$basketInfo['userPoints'] = $oLojal -> getUserPoints($_SESSION['customer']['id']);
			$basketInfo['dzielnik'] = $oLojal -> lojal_config['punkt_wartosc'];
		}
		if($data = $oOrders -> add($_POST, $aBasket, $basketInfo, $transport))  // dodano zamowenienie do systmeu
		{
			if($aModules['tracking'] == 1)   // zapisywanie danych z porownywarek i wyszukiwarek
			{
				require_once(CMS_DIR . '/application/models/tracking.php');
				$oTracking = new Tracking($oCore);
				$oTracking -> setClick();
			}
			 
			// dopisuje do bazy użycie frazy promocyjnej, o ile ta fraza istnieje
			if(isset($_SESSION['customer']['promotion_code']))
			{
				require_once(CMS_DIR . '/application/models/phrase.php');
				$oPhrase = new Phrase($oCore);
				$customer_id = isset($_SESSION['customer']['id']) ? $_SESSION['customer']['id'] : '';
				$oPhrase -> usePhrase($_SESSION['customer']['promotion_code'], $data['id'], $customer_id);
				$customer = $oCustomer -> loadById($customer_id);
				$_SESSION['customer']['discount'] = $customer['discount'];
				$_SESSION['customer']['promotion_code'] = '';
			}

			$oBasket -> updateProducts($aBasket);
			//$oBasket -> deleteBasketAfterOrder();
			
			$_SESSION['ppAmount'] = $data['total'];
			$_SESSION['ppData'] =	$data;
			$_SESSION['ppTransport'] = $transport;
			
			showFinish($data, $transport);
			die();
		}
		else
		{
			showOrder();
			die();
		}
	}
	else
	{
		$oTpl -> setError($GLOBALS['LANG']['order_deliver2']);
		showOrder();
		die();
	}
}
elseif((isset($_REQUEST['action']) && $_REQUEST['action'] == 'centinel')) {
	
	if(isset($_REQUEST['action_type']) && $_REQUEST['action_type'] == "ACSForm") {
		$oTpl -> assign('acs_url',$_SESSION["Centinel_ACSUrl"]);
		$oTpl -> assign('pa_req',$_SESSION["Centinel_Payload"]);
		$oTpl -> assign('term_url',CENTINEL_TERM_URL);
		$oTpl -> assign('md','');
		$oTpl -> showPage('shop/centinel_acs.tpl');
	}
	elseif(isset($_REQUEST['action_type']) && $_REQUEST['action_type'] == "ACSResp") {
	    
		$pares = $_POST['PaRes'];
		$sessParams = $_POST['MD'];
		
	    if (strcasecmp('', $pares )!= 0 && $pares != null) {	
	    	$centinelClient = new CentinelClient;
	    	$centinelClient->add('MsgType', 'cmpi_authenticate');
	    	$centinelClient->add('Version', CENTINEL_MSG_VERSION);
	    	$centinelClient->add('MerchantId', CENTINEL_MERCHANT_ID);
	    	$centinelClient->add('ProcessorId', CENTINEL_PROCESSOR_ID);
	    	$centinelClient->add('TransactionPwd', CENTINEL_TRANSACTION_PWD);
	    	$centinelClient->add('TransactionType', $_SESSION['Centinel_TransactionType']);
	    	$centinelClient->add('OrderId', $_SESSION['Centinel_OrderId']);
	    	$centinelClient->add('TransactionId', $_SESSION["Centinel_TransactionId"]);
	    	$centinelClient->add('PAResPayload', $pares);

			$centinelClient->sendHttp(CENTINEL_MAPS_URL, CENTINEL_TIMEOUT_CONNECT, CENTINEL_TIMEOUT_READ);
         $string = $centinelClient->getResponseACS();
         saveHistory('o', 'Centinel', $_SESSION['ppData']['id'], '', $string);
		
	    	$_SESSION["Centinel_cmpiMessageResp"]       = $centinelClient->response;
	    	$_SESSION["Centinel_PAResStatus"]           = $centinelClient->getValue("PAResStatus");
	    	$_SESSION["Centinel_SignatureVerification"] = $centinelClient->getValue("SignatureVerification");
	    	$_SESSION["Centinel_ErrorNo"]               = $centinelClient->getValue("ErrorNo");
	    	$_SESSION["Centinel_ErrorDesc"]             = $centinelClient->getValue("ErrorDesc");
	    	$_SESSION["Centinel_Cavv"]            		= $centinelClient->getValue("Cavv");
	    	$_SESSION["Centinel_EciFlag"] 				= $centinelClient->getValue("EciFlag");
	    	$_SESSION["Centinel_Xid"] 					= $centinelClient->getValue("Xid");
	    	
	    	$oTpl -> showPage('shop/centinel_complete.tpl');
	    	
	    } else {
	    	
	    }
	}
	else {
		
		$_SESSION['pp_maestro'] = false;
		
		if(!isset($_SESSION['ppData']) || !isset($_SESSION['ppTransport'])) {
			redirectBrowser('basket.html');
			die();
		}
		
		$doQuery = FALSE;
		
		if(strlen(trim($_POST['expDateYear'])) == 2) {
		
			$_POST['expDateYear'] = "20" . $_POST['expDateYear'];
		}
		
		$pp_creditCardNumber = 	urlencode($_POST['creditCardNumber']);
		$pp_expDateMonth =		urlencode($_POST['expDateMonth']);	
		$pp_padDateMonth = 		str_pad($pp_expDateMonth, 2, '0', STR_PAD_LEFT);		
		$pp_expDateYear =		urlencode($_POST['expDateYear']);
		$pp_amount = 			 $_SESSION['ppData']['total'];
		$pp_countryCode =		urlencode($_POST['country_code']);
		$pp_currencyCode =		"GBP";
		
		if(is_numeric($pp_creditCardNumber) && is_numeric($pp_padDateMonth) && is_numeric($pp_expDateYear)) {
			$doQuery = TRUE;
		}
		else {
         $login = isset($_SESSION['customer']['login']) ? $_SESSION['customer']['login'] : 'gość';
         saveHistory('o', $login, $_SESSION['ppData']['id'], 36);
			$oTpl -> assign('error', $GLOBALS['LANG']['contact_fill2']);
		}
		
		$_SESSION['pp_postData'] = serialize($_POST);
		
		if($_POST['creditCardType'] == "Maestro") {
			$doQuery = false;
			$_SESSION['pp_maestro'] = true;
			redirectBrowser('order.html?action=website_payments_direct');
			die();
		}
		
		if(check_cc($pp_creditCardNumber) == "Maestro") {
			$doQuery = false;
			$_SESSION['pp_maestro'] = true;
			redirectBrowser('order.html?action=website_payments_direct');
			die();
		}
		
		/*
		 * CARDINAL AUTH BEGIN
		 */
			
		clearCentinelSession();		
		$centinelClient = new CentinelClient;
		$centinelClient->add("MsgType", "cmpi_lookup");
		$centinelClient->add("Version", CENTINEL_MSG_VERSION);
		$centinelClient->add("ProcessorId", CENTINEL_PROCESSOR_ID);
		$centinelClient->add("MerchantId", CENTINEL_MERCHANT_ID);
		$centinelClient->add("TransactionPwd", CENTINEL_TRANSACTION_PWD);
		$centinelClient->add("UserAgent", $_SERVER["HTTP_USER_AGENT"]);
		$centinelClient->add("BrowserHeader", $_SERVER["HTTP_ACCEPT"]);
		$centinelClient->add("TransactionType", "C");
		$centinelClient->add('IPAddress', CLIENT_IP);

		// Standard cmpi_lookup fields
		$centinelClient->add('OrderNumber', $_SESSION['ppData']['id']);
		$centinelClient->add('Amount', ceil($pp_amount * 100)); // Converted from pounds to cents
		$centinelClient->add('CurrencyCode', "826"); // 826 == GBP
		$centinelClient->add('TransactionMode', "S");

		// Payer Authentication specific fields
		$centinelClient->add('CardNumber', $pp_creditCardNumber);
		$centinelClient->add('CardExpMonth', $pp_padDateMonth);
		$centinelClient->add('CardExpYear', $pp_expDateYear);
		
		if($doQuery) {
		
			$centinelClient->sendHttp(CENTINEL_MAPS_URL, CENTINEL_TIMEOUT_CONNECT, CENTINEL_TIMEOUT_READ);
         
         $string = $centinelClient->getResponse();
         saveHistory('o', 'Centinel', $_SESSION['ppData']['id'], '', $string);
			
			if($centinelClient->getValue("ErrorNo") == 0) {
				// Save response in sessiond
				$_SESSION["Centinel_cmpiMessageResp"]   = $centinelClient->response; // Save lookup response in session
				$_SESSION["Centinel_Enrolled"]          = $centinelClient->getValue("Enrolled");
				$_SESSION["Centinel_TransactionId"]     = $centinelClient->getValue("TransactionId");
				$_SESSION["Centinel_OrderId"]           = $centinelClient->getValue("OrderId");
				$_SESSION["Centinel_ACSUrl"]            = $centinelClient->getValue("ACSUrl");
				$_SESSION["Centinel_Payload"]           = $centinelClient->getValue("Payload");
				$_SESSION["Centinel_ErrorNo"]           = $centinelClient->getValue("ErrorNo");
				$_SESSION["Centinel_ErrorDesc"]         = $centinelClient->getValue("ErrorDesc");
				$_SESSION["Centinel_EciFlag_lookup"]	= $centinelClient->getValue("EciFlag");
				// Needed for the cmpi_authenticate message
				$_SESSION["Centinel_TransactionType"] = "C";
		
				// Add TermUrl to session
				$_SESSION["Centinel_TermUrl"] = CENTINEL_TERM_URL;
		
				if( (strcasecmp('Y', $_SESSION['Centinel_Enrolled']) == 0) && (strcasecmp('0', $_SESSION['Centinel_ErrorNo']) == 0) ) {
					redirectBrowser('order.html?action=centinel&action_type=ACSForm');
		
				} 
				else {

					redirectBrowser('order.html?action=website_payments_direct');		
				}
			}
			else {
				
				$error = "There was a problem verifying your card. Please choose another payment method or try again.";
				$doOrder = FALSE;
				
				require_once(CMS_DIR . '/application/models/mailer.php');				
				$oMailer = new Mailer($oCore);				
				$oMailer -> Subject = "Błąd w płatności kartą";
				$oMailer -> Body = "ErrorNo: " . $centinelClient->getValue("ErrorNo");
				$oMailer -> CharSet = 'utf-8';
				$oMailer -> isHTML(true);
				$oMailer -> AddAddress("test@vitamin-shop.co.uk", "test@vitamin-shop.co.uk");
				$oMailer -> Send();
				
            saveHistory('o', 'Centinel', $_SESSION['ppData']['id'], '', 'ErrorNo: '.$centinelClient->getValue("ErrorNo"));
            
				showFinish($_SESSION['ppData'],$_SESSION['ppTransport'],$error);
				die();
			}
		}
		else {
			showFinish($_SESSION['ppData'],$_SESSION['ppTransport']);
			die();
		}		
	}
	
	/*
	 * CARDINAL AUTH END
	 */	
}
elseif(isset($_REQUEST['action']) && isset($_SESSION['pp_postData']) && $_REQUEST['action'] == 'website_payments_direct') {
	
	$doOrder = FALSE;
	
	require_once(CMS_DIR . '/application/models/mailer.php');
	
	if($_SESSION['pp_maestro'])
		$doOrder = TRUE;
	else {
		if((strcasecmp('N', $_SESSION['Centinel_Enrolled']) == 0) && (strcasecmp('0', $_SESSION['Centinel_ErrorNo']) == 0)) {
			$doOrder = TRUE;
		}
		if( (strcasecmp('Y', $_SESSION['Centinel_Enrolled']) == 0) && (strcasecmp('0', $_SESSION['Centinel_ErrorNo']) == 0) ) {
			$doOrder = TRUE;
		}
		if( (strcasecmp('U', $_SESSION['Centinel_Enrolled']) == 0) && (strcasecmp('0', $_SESSION['Centinel_ErrorNo']) == 0) ) {
			$doOrder = TRUE;
		}
		if(isset($_SESSION["Centinel_SignatureVerification"]) && $_SESSION["Centinel_SignatureVerification"] == 'N') {
			$error = "There was a problem verifying your card. Please choose another payment method or try again.";
			$doOrder = FALSE;
			
			$oMailer = new Mailer($oCore);
			$oMailer -> Subject = "Błąd w płatności kartą";
			$oMailer -> Body = "SignatureVerification: N";
			$oMailer -> CharSet = 'utf-8';
			$oMailer -> isHTML(true);
			$oMailer -> AddAddress("test@vitamin-shop.co.uk", "test@vitamin-shop.co.uk");
			$oMailer -> Send();
         
         saveHistory('o', 'PayPal', $_SESSION['ppData']['id'], 35);
			
			showFinish($_SESSION['ppData'],$_SESSION['ppTransport'],$error);
			die();
		}
		if(isset($_SESSION["Centinel_PAResStatus"]) && $_SESSION["Centinel_PAResStatus"] == 'N') {
			$error = "There was a problem verifying your card. Please choose another payment method or try again.";
			$doOrder = FALSE;
			
			$oMailer = new Mailer($oCore);
			$oMailer -> Subject = "Błąd w płatności kartą";
			$oMailer -> Body = "PAResStatus: N";
			$oMailer -> CharSet = 'utf-8';
			$oMailer -> isHTML(true);
			$oMailer -> AddAddress("test@vitamin-shop.co.uk", "test@vitamin-shop.co.uk");
			$oMailer -> Send();
         
         saveHistory('o', 'PayPal', $_SESSION['ppData']['id'], 34);
			
			showFinish($_SESSION['ppData'],$_SESSION['ppTransport'],$error);
			die();
		}
	}
	
	if($doOrder) {
		$ppSessData = unserialize($_SESSION['pp_postData']);		
		$pp_firstName =			urlencode($ppSessData['firstName']);
		$pp_lastName =			urlencode($ppSessData['lastName']);
		$pp_creditCardType =	urlencode($ppSessData['creditCardType']);
		$pp_creditCardNumber = 	urlencode($ppSessData['creditCardNumber']);
		$pp_expDateMonth =		urlencode($ppSessData['expDateMonth']);	
		$pp_padDateMonth = 		str_pad($pp_expDateMonth, 2, '0', STR_PAD_LEFT);		
		$pp_expDateYear =		urlencode($ppSessData['expDateYear']);		
		$pp_startDateMonth =		urlencode($ppSessData['startDateMonth']);	
		$pp_pad2DateMonth = 		str_pad($pp_expDateMonth, 2, '0', STR_PAD_LEFT);		
		$pp_startDateYear =		urlencode($ppSessData['startDateYear']);	
		$pp_cvv2Number = 		urlencode($ppSessData['cvv2Number']);
		$pp_address1 = 			urlencode($ppSessData['address1']);
		$pp_address2 = 			urlencode($ppSessData['address2']);
		$pp_city = 				urlencode($ppSessData['city']);
		$pp_state =				urlencode($ppSessData['state']);
		$pp_zip = 				urlencode($ppSessData['zip']);
		$pp_amount = 			$_SESSION['ppAmount'];
		$pp_countryCode =		urlencode($ppSessData['country_code']);
		$pp_currencyCode =		"GBP";
		$pp_paymentType	=		"Sale";
		$pp_ipAddress =			CLIENT_IP;
		
		$nvpstr = 	"&PAYMENTACTION=".$pp_paymentType.
					"&AMT=".$pp_amount.
					"&CREDITCARDTYPE=".$pp_creditCardType.
					"&ACCT=".$pp_creditCardNumber.
					"&EXPDATE=".$pp_padDateMonth.$pp_expDateYear.
					"&CVV2=".$pp_cvv2Number.
					"&FIRSTNAME=".$pp_firstName.
					"&LASTNAME=".$pp_lastName.
					"&STREET=".$pp_address1. " " .$pp_address2.
					"&CITY=".$pp_city.
					"&STATE=".$pp_state.
					"&ZIP=".$pp_zip.
					"&IPADDRESS=".$pp_ipAddress.
					"&COUNTRYCODE=".$pp_countryCode.
					"&CURRENCYCODE=".$pp_currencyCode;
		
		if(is_numeric($pp_startDateMonth) && is_numeric($pp_startDateYear)) {
		
			$nvpstr .= "&STARTDATE=" .$pp_pad2DateMonth.$pp_startDateYear;
		}
		
		if(is_numeric($ppSessData['issueNumber'])) {
		
			$nvpstr .= "&ISSUENUMBER=" .$ppSessData['issueNumber'];
		}
		
		/*
		 * CARDINAL DATA
		 */
		
		$nvpstr .= "&VERSION=59.0";
		if(isset($_SESSION["Centinel_Enrolled"]))
			$nvpstr .= "&MPIVENDOR3DS=" . $_SESSION["Centinel_Enrolled"];
			
		if(isset($_SESSION["Centinel_EciFlag"]))
			$nvpstr .= "&ECI3DS=" . $_SESSION["Centinel_EciFlag"];
		elseif(isset($_SESSION["Centinel_EciFlag_lookup"]))
			$nvpstr .= "&ECI3DS=" . $_SESSION["Centinel_EciFlag_lookup"];
			
		if(isset($_SESSION["Centinel_Cavv"]))
			$nvpstr .= "&CAVV=" . $_SESSION["Centinel_Cavv"];
		else 
			$nvpstr .= "&CAVV=" . '';
		
		if(isset($_SESSION["Centinel_PAResStatus"]))
			$nvpstr .= "&AUTHSTATUS3DS=" . $_SESSION["Centinel_PAResStatus"];
		else 
			$nvpstr .= "&AUTHSTATUS3DS=" . '';
			
		if(isset($_SESSION["Centinel_Xid"]))
			$nvpstr .= "&XID=" . $_SESSION["Centinel_Xid"];
		else 
			$nvpstr .= "&XID=" . '';
		
		/*
		 * CARDINAL DATA END
		 */
			
		$resArray=hash_call("doDirectPayment",$nvpstr);
      
      $string = '';
      foreach($resArray as $k => $v) {
         $string.= "\n";
         $string.= $k.": ".$v;
      }
      saveHistory('o', 'PayPal', $_SESSION['ppData']['id'], '', $string);   
		
		$ack = strtoupper($resArray["ACK"]);		
		$oTpl -> assign('aPP', $_POST);
		
		if($ack!="SUCCESS")  {
		    $oTpl -> assign('error', $resArray['L_LONGMESSAGE0']);
			showFinish($_SESSION['ppData'],$_SESSION['ppTransport']);
			die();
		}
		
		else { // Zamowienie zostalo oplacone
			 $id_zam = $_SESSION['ppData']['id'];
	
			 // pobieramy zamowienie
			 $q = "SELECT * FROM `".DB_PREFIX."shop_orders` WHERE `id`='".$id_zam."' ";
			 $zam = $oCore -> db -> getRow($q);
			 $sum = round(($zam['price'] - $zam['price'] * $zam['discount'] / 100 + $zam['transport_price']), 2);
			 if($sum == $resArray['AMT'])
			 {
			 	$transactionID = urlencode($resArray['TRANSACTIONID']);

   // sprawdzamy czy w zamowieniu sa produkty ameryki, jesli tak to status zmianiamy na 8 (order in progress) a nie 2 (payment accepted)
$ameryka = $oOrders -> exist_ameryka_product($id_zam);
if($ameryka == 1) $status = 8;
else $status = 2;
			 	
			 	// płatność przyjęta
			 	$q = "UPDATE `".DB_PREFIX."shop_orders` SET `status_id`='".$status."', `paypal_respone`='".addslashes($transactionID)."', `date_payment`=NOW() WHERE `id`='".$id_zam."' LIMIT 1 ";
			 	$oCore -> db -> update($q);
			 	saveHistory('o', 'CreditCard', $id_zam, 16);
	
			 	// naliczenie punktów z programu lojalnościowego
	
			 	$data['id_user'] = $zam['customer_id'];
			 	$data['login'] = 'online';
			 	$data['date'] = date("Y-m-d H:i:s");
			 	$data['source']  = 'o-'.$zam['id'];
			 	$data['info']  = 'Order '.$zam['id'];
			 	$data['amount'] = $zam['price'];   // bez transportu
			 	$data['barcode'] = '';
			 	$data['hash'] = '';
			 	// obliczanie hash'a niepotrzebne
			 	$oLojal -> naliczPunkty($data, true);
	
			 	//mail
			 	require_once(CMS_DIR . '/application/models/mailer.php');
			 	$oMailer = new Mailer($oCore);
	
			 	$oMailer -> Subject = "CreditCard payment";
			 	$oMailer -> Body = "CreditCard payment for ".$id_zam." received ";
			 	$oMailer -> CharSet = 'utf-8';
			 	$oMailer -> isHTML(true);
			 	$oMailer -> AddAddress(EMAIL_ADMIN, EMAIL_ADMIN);
			 	$oMailer -> Send();
			 	$info = "Your payment has been successful.";
			 	
				// BUILD GOOGLE ANALYTICS CODE				
				
				$aBasket = $oBasket -> loadBasketOrder();
				
				$gaCode =
				"pageTracker._addTrans(
					'" . $zam['id'] . "',
					'',
					'" . $_SESSION['ppData']['total'] . "',
					'',
					'" . $_SESSION['ppTransport']['price'] . "',
					'" . $_SESSION['ppData']['city'] . "',
					'',
					'" . $_SESSION['ppData']['country'] . "'
				); ";
					
				$inc = 0;
				foreach($aBasket as $item) {
				
					$gaCode .= "pageTracker._addItem(
						'" . $zam['id'] . "',
						'" . $item['product_code'] . "',
						'" . $item['title'] . "',
						'" . $item['category'] . "',
						'" . $item['price'] . "',
						'" . $item['amount'] . "'
					); ";
				}
            
				$gaCode .= " pageTracker._trackTrans();";
				
				$_SESSION['gaTrack'] = true;
				$_SESSION['gaCode'] = $gaCode;
				$_SESSION['gaCodeConv'] = <<<EOL
<img height="1" width="1" alt="" src="http://www.googleadservices.com/pagead/conversion/1036483880/imp.gif?value=0&amp;label=zi4vCOCcpAMQqPqd7gM&amp;guid=ON&amp;script=0"/>
EOL;
				// END OF BUILD GOOGLE ANALYTICS CODE
			 	
			 	unset($_SESSION['ppData']);
			 	unset($_SESSION['ppAmout']);
			 	unset($_SESSION['ppTransport']);
			 	unset($_SESSION['pp_postData']);
			 	clearCentinelSession();
			 	$oBasket -> deleteBasketAfterOrder();
				if(LOGGED == 1)
					redirectBrowser('customer/order-redirect/' . md5($zam['id'] . $zam['date_add']));
				else {
					showSuccess($zam['id']);
				}
			 }
		}
	}
	else {
		$error = "An error has occured. Please choose another payment method or try again.";
		
		require_once(CMS_DIR . '/application/models/mailer.php');
		$oMailer = new Mailer($oCore);
		$oMailer -> Subject = "Błąd w płatności kartą";
		$oMailer -> Body = "Błąd nie uwzględniony";
		$oMailer -> CharSet = 'utf-8';
		$oMailer -> isHTML(true);
		$oMailer -> AddAddress("test@vitamin-shop.co.uk", "test@vitamin-shop.co.uk");
		$oMailer -> Send();
      
      saveHistory('o', 'PayPal', $_SESSION['ppData']['id'], 33);
		
		showFinish($_SESSION['ppData'],$_SESSION['ppTransport'],$error);
		die();
	}
}
elseif(isset($_REQUEST['action']) AND $_REQUEST['action'] == 'website_payments_express') {
	
	$aBasket = $oBasket -> loadBasketOrder();
	$basketInfo = $oBasket -> getInfo();

	if(!isset($_REQUEST['token'])) {

		$serverName = $_SERVER['SERVER_NAME'];
		$serverPort = $_SERVER['SERVER_PORT'];
		$pp_url = dirname('https://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);
		$pp_currencyCodeType	= 'GBP';
		$pp_paymentType			= 'Sale';
		$pp_AMT					= $_SESSION['ppData']['total'];
		$pp_ITEMAMT				= (float)$pp_AMT - (float)$_SESSION['ppTransport']['price'];
		
		// discount
		$ppDiscount = $_SESSION['ppData']['sum'] - ($_SESSION['ppData']['total'] - $_SESSION['ppTransport']['price']) ;
		
		$_SESSION['pp_currencyCodeType'] = $pp_currencyCodeType;
		$_SESSION['pp_paymentType'] = $pp_paymentType;
		$_SESSION['pp_AMT'] = $pp_AMT;
		
		$pp_returnURL = urlencode($pp_url . '/order.html?action=website_payments_express');
		$pp_cancelURL = urlencode($pp_url . '/order.html' );
		
		$nvpstr = "&LOCALECODE=en_GB";
		
		//if(_ID == 1)
			//$nvpstr = "&LOCALECODE=en_GB";
		//else
			//$nvpstr = "&LOCALECODE=pl_PL";
		
		$nvpstr .= "&HDRIMG=https://www.vitamin-shop.co.uk/public/img/logo_pp.png";
		
		if(isset($_SESSION['pec_payment_id'])) {
			 if($_SESSION['pec_payment_id'] == 7)
				$nvpstr .= "&SOLUTIONTYPE=Sole&LANDINGPAGE=Billing";
		}
		
		$nvpstr	.=	"&PAYMENTREQUEST_0_AMT=" . $pp_AMT .
					"&PAYMENTREQUEST_0_ITEMAMT=" . $pp_ITEMAMT .
					"&RETURNURL=" . $pp_returnURL . 
					"&CANCELURL=" . $pp_cancelURL . 
					"&PAYMENTREQUEST_0_CURRENCYCODE=" . $pp_currencyCodeType . 
					"&PAYMENTREQUEST_0_PAYMENTACTION=" . $pp_paymentType;
		
		$inc = 0;
		foreach($aBasket as $item) {
			$nvpstr .= "&L_PAYMENTREQUEST_0_NAME" . $inc . "=" . urlencode($item['title']) . "&L_PAYMENTREQUEST_0_QTY" . $inc . "=" . $item['amount'];
			
			if(!isset($item['price_promotion_gross']))
				$nvpstr .= "&L_PAYMENTREQUEST_0_AMT" . $inc . "=" . $item['price_gross'];
			else
				$nvpstr .= "&L_PAYMENTREQUEST_0_AMT" . $inc . "=" . $item['price_promotion_gross'];
				
			$inc++;
		}
		
		if($ppDiscount > 0) {
			$nvpstr .= "&L_PAYMENTREQUEST_0_NAME" . $inc . "=" . urlencode("Your Discount") . "&L_PAYMENTREQUEST_0_QTY" . $inc . "=1";
			$nvpstr .= "&L_PAYMENTREQUEST_0_AMT" . $inc . "=-" . $ppDiscount;
		}
		
		$nvpstr .= "&PAYMENTREQUEST_0_SHIPPINGAMT=" . $_SESSION['ppTransport']['price'];
		
		$resArray=hash_call("SetExpressCheckout",$nvpstr);
		$_SESSION['reshash'] = $resArray;

		$ack = strtoupper($resArray["ACK"]);

		if($ack=="SUCCESS") {
			$token = urlencode($resArray["TOKEN"]);
			$payPalURL = $aPayPal_CONF["PAYPAL_URL"] . $token;
			header("Location: ".$payPalURL);
		} else  {
			$oTpl -> assign('error', $resArray['L_LONGMESSAGE0']);
			showFinish($_SESSION['ppData'],$_SESSION['ppTransport']);
			die();
		}
		
	} else {

		$pp_token = urlencode($_REQUEST['token']);

		$nvpstr="&TOKEN=" . $pp_token;

		$resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
		$_SESSION['reshash']=$resArray;
		$ack = strtoupper($resArray["ACK"]);

		if(($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') && isset($_SESSION['pp_AMT'])) {
			$pp_token =				urlencode($resArray['TOKEN']);
			$pp_paymentAmount =		urlencode($_SESSION['pp_AMT']);
			$pp_paymentType = 		urlencode($_SESSION['pp_paymentType']);
			$pp_currCodeType = 		urlencode($_SESSION['pp_currencyCodeType']);
			$pp_payerID = 			urlencode($resArray['PAYERID']);
			$pp_ipAddress = 		CLIENT_IP;

			$nvpstr= '&TOKEN=' . $pp_token . 
					 '&PAYERID=' . $pp_payerID . 
					 '&PAYMENTACTION=' . $pp_paymentType . 
					 '&AMT=' . $pp_paymentAmount . 
					 '&CURRENCYCODE=' . $pp_currCodeType . 
					 '&IPADDRESS=' . $pp_ipAddress ;

			$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);

			$ack = strtoupper($resArray["ACK"]);

			if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING'){
				$oTpl -> assign('error', $resArray['L_LONGMESSAGE0']);
				showFinish($_SESSION['ppData'],$_SESSION['ppTransport']);
				die();
			}
			else { // Zamowienie zostalo oplacone
				$id_zam = $_SESSION['ppData']['id'];

				 // pobieramy zamowienie
				 $q = "SELECT * FROM `".DB_PREFIX."shop_orders` WHERE `id`='".$id_zam."' ";
				 $zam = $oCore -> db -> getRow($q);
				 $sum = round(($zam['price'] - $zam['price'] * $zam['discount'] / 100 + $zam['transport_price']), 2);
				 if($sum == $resArray['AMT'])
				 {				 	
				 	$transactionID = urlencode($resArray['TRANSACTIONID']);
				 	
   // sprawdzamy czy w zamowieniu sa produkty ameryki, jesli tak to status zmianiamy na 8 (order in progress) a nie 2 (payment accepted)
$ameryka = $oOrders -> exist_ameryka_product($id_zam);
if($ameryka == 1) $status = 8;
else $status = 2;           
               
				 	// płatność przyjęta
				 	$q = "UPDATE `".DB_PREFIX."shop_orders` SET `status_id`='".$status."', `paypal_respone`='".addslashes($transactionID)."', `date_payment`=NOW() WHERE `id`='".$id_zam."' LIMIT 1 ";
				 	$oCore -> db -> update($q);
				 	saveHistory('o', 'PayPal', $id_zam, 16);
	
				 	// naliczenie punktów z programu lojalnościowego	
				 	$data['id_user'] = $zam['customer_id'];
				 	$data['login'] = 'online';
				 	$data['date'] = date("Y-m-d H:i:s");
				 	$data['source']  = 'o-'.$zam['id'];
				 	$data['info']  = 'Order '.$zam['id'];
				 	$data['amount'] = $zam['price'];   // bez transportu
				 	$data['barcode'] = '';
				 	$data['hash'] = '';
				 	// obliczanie hash'a niepotrzebne
				 	$oLojal -> naliczPunkty($data, true);
	
				 	//mail
				 	require_once(CMS_DIR . '/application/models/mailer.php');
				 	$oMailer = new Mailer($oCore);	
				 	$oMailer -> Subject = "PayPal payment";
				 	$oMailer -> Body = "Pay Pal payment for ".$id_zam." received ";
				 	$oMailer -> CharSet = 'utf-8';
				 	$oMailer -> isHTML(true);
				 	$oMailer -> AddAddress(EMAIL_ADMIN, EMAIL_ADMIN);
				 	$oMailer -> Send();
				 	$info = "Your payment has been successful.";
               
					// BUILD GOOGLE ANALYTICS CODE
				
					$aBasket = $oBasket -> loadBasketOrder();
					
					$gaCode =
					"pageTracker._addTrans(
						'" . $zam['id'] . "',
						'',
						'" . $_SESSION['ppData']['total'] . "',
						'',
						'" . $_SESSION['ppTransport']['price'] . "',
						'" . $_SESSION['ppData']['city'] . "',
						'',
						'" . $_SESSION['ppData']['country'] . "'
					);";
						
					$inc = 0;
					foreach($aBasket as $item) {
					
						$gaCode .= "pageTracker._addItem(
							'" . $zam['id'] . "',
							'" . $item['product_code'] . "',
							'" . $item['title'] . "',
							'" . $item['category'] . "',
							'" . $item['price'] . "',
							'" . $item['amount'] . "'
						); ";
					}					
					// " . $item['cat_name'] . "
					
					$gaCode .= " pageTracker._trackTrans();";
					
					$_SESSION['gaTrack'] = true;
					$_SESSION['gaCode'] = $gaCode;
					$_SESSION['gaCodeConv'] = <<<EOL
<img height="1" width="1" alt="" src="http://www.googleadservices.com/pagead/conversion/1036483880/imp.gif?value=0&amp;label=zi4vCOCcpAMQqPqd7gM&amp;guid=ON&amp;script=0"/>
EOL;
					// END OF BUILD GOOGLE ANALYTICS CODE               
				 	
				 	unset($_SESSION['ppData']);
				 	unset($_SESSION['ppAmout']);
				 	unset($_SESSION['ppTransport']);
				 	$oBasket -> deleteBasketAfterOrder();
				 	
					if(LOGGED == 1)
					redirectBrowser('customer/order-redirect/' . md5($zam['id'] . $zam['date_add']));
					else {
						showSuccess($zam['id']);
					}
				 }
			}
			
		} else  {
			if(isset($resArray['L_LONGMESSAGE0']))
			$oTpl -> assign('error', $resArray['L_LONGMESSAGE0']);
			showFinish($_SESSION['ppData'],$_SESSION['ppTransport']);
			die();
		}
	}
}

else
{
	showOrder();
	die();
}

function showOrder($showError = NULL)
{
	global $oBasket, $oOrders, $oLojal, $oCustomer, $oTpl;

	if(!$aBasket = $oBasket -> loadBasket())  //sprawdzamy czy koszyk nie jest pusty
	{
		$oTpl -> showInfo($GLOBALS['LANG']['basket_empty']);
	}
	$basketInfo = $oBasket -> getInfo();
	$aPayment = $oOrders -> loadPayment();
	$country = $oCustomer -> loadCountry();

	// jesli mamy rabat uzytkowanika lub frazy promocyjnej
	if(isset($_SESSION['customer']['discount']) AND $_SESSION['customer']['discount'] > 0)
	{
		$sum = $basketInfo['sum'];
		$sum2 = $basketInfo['sum_free'];
		$discount['discount'] = $_SESSION['customer']['discount'];
		$discount['sum'] = formatPrice($sum - $sum * $discount['discount'] / 100);
		$discount['sum_free'] = formatPrice($sum2 - $sum2 * $discount['discount'] / 100);
		$discount['saving'] = formatPrice($sum - $discount['sum']);
		$discount['total'] = formatPrice($discount['sum']);
		$oTpl -> assign('discount', $discount);
	}
	
	// obsluga naliczania punktow za zamowienie
	$points['moznik'] = $oLojal -> getSklepMnoznik('online');
	$points['dzielnik'] = $oLojal -> lojal_config['punkt_wartosc'];
	$points['dzielnik'] = $points['dzielnik'] == 0 ? 0.01 : $points['dzielnik'];  // aby nie dzielic przez 0
	$points['nalicz'] = floor(($basketInfo['sum']) * $points['moznik']); // ilosc naliczanych punktow
	$points['razem'] = $basketInfo['sum_points'];   // ilosc punktow potrzebna do zaplaty
	if(isset($_SESSION['customer']['id'])) $points['sum'] = $oLojal -> getUserPoints($_SESSION['customer']['id']);
	$oTpl -> assign('points', $points);
	
	$rtbhouse = '<iframe src="//creativecdn.com/tags?id=pr_bSN5TtJTUePBvLqF3m1C_startorder" width="1" height="1" scrolling="no" frameBorder="0"></iframe>';	// tutaj ustawiamy kod rtbhouse, zamowienie, dane: aBasket

	if($rtbhouse)
	{
		$oTpl -> assign('rtbhouse', $rtbhouse);
	}

	if($showError) $oTpl -> assign('error', $showError);
	$oTpl -> assign('aBasket', $aBasket);
	$oTpl -> assign('aPayment', $aPayment);
	$oTpl -> assign('country', $country);
	if(isset($_SESSION['customer']['id']))
	$oTpl -> assign('customer_id',$_SESSION['customer']['id']);
	$oTpl -> assign('pageTitle', $GLOBALS['LANG']['order_title'].' - '.PAGE_TITLE);
	$oTpl -> assign('pageKeywords', PAGE_KEYWORDS);
	$oTpl -> assign('pageDescription', PAGE_DESCRIPTION);
	$oTpl -> showPage('shop/order.tpl');
}

function showFinish($data, $transport, $showError = '')
{
	global $oTpl,$aPayPal_CONF;
	 
	 if(isset($data['payment']['id'])) {
		$_SESSION['pec_payment_id'] = $data['payment']['id'];
	 }
	 
	if($data['payment']['id'] == 1)
	{
		$CS_CONF['PreSharedKey'] = 'BaQ9H1zni2pA52H03jr/2ndJ9VeLwVUj2LyN+/yetwu0ZvqAxv3Rt2U8tA==';
		$CS_CONF['MerchantID'] = 'Vitami-2828902';
		$CS_CONF['Password'] = 'J17C3P81N6';
		$CS_CONF['CallbackURL'] = 'http://www.vitamin-shop.co.uk/cardsave.html';

		$PreSharedKey = $CS_CONF['PreSharedKey'];
		$CS['MerchantID'] = $CS_CONF['MerchantID'];
		$Password = $CS_CONF['Password'];
		$CS['Amount']= (int)($data['total'] * 100);
		$CS['CurrencyCode']= '826'; //GBP
		$CS['OrderID']= $data['id'];
		$CS['TransactionType']='SALE';
		$CS['TransactionDateTime']=date('Y-m-d H:i:s P');
		$CS['CallbackURL']= $CS_CONF['CallbackURL'];
		$CS['OrderDescription']='Payment for order '.$data['id'];
		$CS['CustomerName'] = ''; //$data['first_name'].' '.$data['last_name'];
		$CS['Address1'] = '';
		$CS['Address2'] = '';
		$CS['Address2'] = '';
		$CS['Address3'] = '';
		$CS['Address4'] = '';
		$CS['State'] = '';
		$CS['City'] = ''; //$data['city'];
		$CS['PostCode'] = ''; //$data['post_code'];
		$CS['CountryCode'] = '826';

		$HashDigestStr =
      	'PreSharedKey='.$PreSharedKey
		.'&MerchantID='.$CS['MerchantID']
		.'&Password='.$Password
		.'&Amount='.$CS['Amount']
		.'&CurrencyCode='.$CS['CurrencyCode']
		.'&OrderID='.$CS['OrderID']
		.'&TransactionType='.$CS['TransactionType']
		.'&TransactionDateTime='.$CS['TransactionDateTime']
		.'&CallbackURL='.$CS['CallbackURL']
		.'&OrderDescription='.$CS['OrderDescription']
		.'&CustomerName='.$CS['CustomerName']
		.'&Address1='.$CS['Address1']
		.'&Address2='.$CS['Address2']
		.'&Address3='.$CS['Address3']
		.'&Address4='.$CS['Address4']
		.'&City='.$CS['City']
		.'&State='.$CS['State']
		.'&PostCode='.$CS['PostCode']
		.'&CountryCode='.$CS['CountryCode'];
		//  obliczenie Hash Digest
		$HashDigest = sha1($HashDigestStr);

		$oTpl -> assign('HashDigest', $HashDigest);
		$oTpl -> assign('CS', $CS);
	}
	elseif ($data['payment']['id'] == 6 && isset($aPayPal_CONF)) {
		
		$aPP = array();
		
		$aPP['firstName'] =				"";//$data['first_name'];
		$aPP['lastName'] =				"";//$data['last_name'];
		$aPP['creditCardType'] =		'';
		$aPP['creditCardNumber'] = 		'';
		$aPP['expDateMonth'] =			'';
		$aPP['expDateYear'] =			'';
		$aPP['cvv2Number'] = 			'';
		$aPP['address1'] = 				"";//$data['address1'];
		$aPP['address2'] = 				"";//$data['address2'];
		$aPP['city'] = 					"";//$data['city'];
		$aPP['state'] =					'';
		$aPP['zip'] = 					"";//trim($data['post_code1']) . trim($data['post_code2']);	
		$oTpl -> assign('aPP', $aPP);
	}

   $login = isset($_SESSION['customer']['login']) ? $_SESSION['customer']['login'] : 'gość';
   if($data['payment']['id'] == 1 OR $data['payment']['id'] == 6) saveHistory('o', $login, $data['id'], 10);
   elseif($data['payment']['id'] == 2 OR $data['payment']['id'] == 5) saveHistory('o', $login, $data['id'], 11);
   elseif($data['payment']['id'] == 3) saveHistory('o', $login, $data['id'], 12);
 	
	if($showError) $oTpl -> assign('error', $showError);
	$oTpl -> assign('basketInfo', false);
	$oTpl -> assign('data', $data);
	$oTpl -> assign('transport', $transport);
	$oTpl -> assign('pageTitle', $GLOBALS['LANG']['order_title'].' - '.PAGE_TITLE);
	$oTpl -> assign('pageKeywords', PAGE_KEYWORDS);
	$oTpl -> assign('pageDescription', PAGE_DESCRIPTION);
	$oTpl -> showPage('shop/finish.tpl');
}

function showSuccess($id = 0)
{
	global $oOrders, $oTpl;
	
	if($aItem = $oOrders -> loadById($id))
	{	
		$login = isset($_SESSION['customer']['login']) ? $_SESSION['customer']['login'] : 'gość';
		
// tutaj ustawiamy kod rtbhouse, po oplaceniu zamowienia (ostatni kod). w aItem beda wszsytkie potrzebne dane
		$rtbhouse = '<iframe src="//creativecdn.com/tags?id=pr_bSN5TtJTUePBvLqF3m1C_orderstatus2_';
    $rtbhouse.= $aItem['total'].'_'.$aItem['id'].'_';
    $ids = '';
    foreach($aItem['products'] as $v)
    {
      $ids.= $v['product_id'].',';
    }
    $ids = substr($ids, 0, -1);
    $rtbhouse.= $ids.'" width="1" height="1" scrolling="no" frameBorder="0"></iframe>'; 

		if($rtbhouse)
		{			
			saveHistory('o', $login, $aItem['id'], 49);	
			$oTpl -> assign('rtbhouse', $rtbhouse);
		}
		else
		{
			saveHistory('o', $login, $aItem['id'], 48);
		}
	}
	
	$oTpl -> showPage('shop/success.tpl');
}

?>
