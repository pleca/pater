<?php

class PayPalApi {

    const EXCEPTION_ERROR = 'There was problem with our system. Please contact with us in order to resolve it.';

    private $_aData;
    private $_aExpressProducts;
    private $_aConfPayPal;
    private $_aConfCentinel;
    private $_oCentinelClient;
    private $_aError;
    private $_aInfo;
    private $_sEncryptionKey;
    private $_bEncrypt;
    private $expressCreditCard;
    private $_sIpAddress;

    public function __construct()
    {

        require_once(dirname(__FILE__) . '/paypal/CallerService.php');
        require_once(dirname(__FILE__) . '/cardinal/lib/curl/CentinelClient.php');

        $this->_aConfCentinel = include (CONF_DIR . '/payment_centinel.php');
        $this->_aConfPayPal = include (CONF_DIR . '/payment_paypal.php');
        $this->_aConfPayPal['API_USERNAME'] = Cms::$conf['paypal_api_username'];
        $this->_aConfPayPal['API_PASSWORD'] = Cms::$conf['paypal_api_password'];
        $this->_aConfPayPal['API_SIGNATURE'] = Cms::$conf['paypal_api_signature'];

        $this->_aError = [];
        $this->_aInfo = [];
        $this->_aExpressProducts = [];
        $this->_sEncryptionKey = '28a018f0127c99b6e3';
        $this->_bEncrypt = true;
        $this->_sIpAddress = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $this->expressCreditCard = false;
        $this->_loadPaymentOrderSession();
        $this->_loadCreditCardSession();
        $this->_loadCentinelSession();
        $this->_loadPayPalSession();
    }

    public function __destruct()
    {
        ;
    }

    private function _addInfo($i)
    {

        if (!empty($i))
        {
            if (!is_array($i))
            {
                array_push($this->_aInfo, $i);
                return true;
            } else
            {

                $this->_aInfo = array_merge($this->_aInfo, $i);
                return true;
            }
        }

        return false;
    }

    private function _addError($e)
    {

        if (!empty($e))
        {
            if (!is_array($e))
            {
                array_push($this->_aError, $e);
                return true;
            } else
            {

                $this->_aError = array_merge($this->_aError, $e);
                return true;
            }
        }

        return false;
    }

    public function getErrors()
    {

        if (count($this->_aError) > 0)
        {
            return $this->_aError;
        } else
        {
            return false;
        }
    }

    public function getInfos()
    {

        if (count($this->_aInfo) > 0)
        {
            return $this->_aInfo;
        } else
        {
            return false;
        }
    }

    public function isError()
    {

        if (count($this->_aError) > 0)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isInfo()
    {

        if (count($this->_aInfo) > 0)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function setOrder(array $order)
    {
        $this->setOrderId(SYS_ID . '_' . $order['id']);
        $this->setOrderPriceTotal($order['total']); // cena WRAZ z cena transportu i MINUS ewentualna znizka (jesli jest)
        $this->setOrderTransportPrice($order['transport_price']);
        $this->setOrderTax($order['tax_val']);

        foreach ($order['products'] as $item)
        {
            $this->setExpressProduct($item['id'], $item['name'], $item['price_gross'], $item['qty'], $item['desc']);
        }

        if ($order['discount'] > 0)
        { // Jesli klient ma jakas znizke ustawiamy jej sume. Jesli nie pomijamy ten kod calkowicie
            $discountSum = formatPrice($order['price_gross'] - $order['price_gross'] * $order['discount'] / 100);
            $discount = formatPrice($order['price_gross'] - $discountSum);

            $this->setOrderDiscountPrice($discount);
        }
    }

    public function setOrderId($n)
    {

        if (is_numeric($n) || is_string($n))
        {
            $this->_aData['PaymentOrder_orderId'] = $n;
            $this->_savePaymentOrderSession();
        }
    }

    public function setOrderTax($tax)
    {
        if (is_numeric($tax))
        {
            $this->_aData['PaymentOrder_orderTax'] = $tax;
            $this->_savePaymentOrderSession();
        }
    }

    public function getOrderId()
    {

        if (isset($this->_aData['PaymentOrder_orderId']) && (is_numeric($this->_aData['PaymentOrder_orderId']) || is_string($this->_aData['PaymentOrder_orderId'])))
        {

            return $this->_aData['PaymentOrder_orderId'];
        } else
        {

            return 0;
        }
    }

    // with transport price, without discount
//	public function setOrderPriceSum($n) {
//		
//		if(is_numeric($n)) {
//			$this->_aData['PaymentOrder_priceSum'] = $n;
//			$this->_savePaymentOrderSession();
//		}
//		
//	}
    // with transport price, with discount
    public function setOrderPriceTotal($n)
    {

        if (is_numeric($n))
        {
            $this->_aData['PaymentOrder_priceTotal'] = $n;
            $this->_savePaymentOrderSession();
        }
    }

    public function setOrderTransportPrice($n)
    {

        if (is_numeric($n))
        {
            $this->_aData['PaymentOrder_priceTransport'] = $n;
            $this->_savePaymentOrderSession();
        }
    }

    public function setOrderDiscountPrice($n)
    {

        if (is_numeric($n))
        {
            $this->_aData['PaymentOrder_priceDiscount'] = $n;
            $this->_savePaymentOrderSession();
        }
    }

    private function _setCreditCardPost($postData)
    {

        foreach ($postData as $key => $data)
        {
            $this->_aData['CreditCard_' . $key] = $data;
        }
    }

    public function getCreditCardPost($postData)
    {

        $aRet = [];

        foreach ($this->_aData as $key => $value)
        {
            if (preg_match("/^CreditCard_.*/", $key) > 0)
            {
                $newKey = str_replace("CreditCard_", "", $key);
                $aRet[$newKey] = $value;
            }
        }

        return $aRet;
    }

    public function processCentinel($aPost)
    {

        if (isset($aPost) && is_array($aPost) && count($aPost))
        {

            $this->_setCreditCardPost($aPost);
        } else
        {

            throw new Exception('_aData[\'CreditCard_\'] from POST isn\'t set');
        }

        if (strlen(trim($this->_aData['CreditCard_expDateYear'])) == 2)
        {

            $this->_aData['CreditCard_expDateYear'] = "20" . trim($this->_aData['CreditCard_expDateYear']);
        }

        $this->_aData['CreditCard_padDateMonth'] = str_pad($this->_aData['CreditCard_expDateMonth'], 2, '0', STR_PAD_LEFT);

        $this->_saveCreditCardSession();

        if ($this->_aData['CreditCard_creditCardType'] == "Maestro" || $this->_checkCC($this->_aData['CreditCard_creditCardNumber']) == "Maestro")
        {

            $this->_aData['CreditCard_creditCardType'] = "Maestro";
            $this->_saveCreditCardSession();

            return 'redirectDirect';
        }

        if (is_numeric($this->_aData['CreditCard_creditCardNumber']) && is_numeric($this->_aData['CreditCard_padDateMonth']) && is_numeric($this->_aData['CreditCard_expDateYear']))
        {

            return $this->_sendCentinel();
        } else
        {

            $this->_addError('Please fill in all fields.');
            return false;
        }
    }

    public function getCurrencyNum($currency)
    {

        $numbers = array(
            'GBP' => '826',
            'EUR' => '978'
        );

        return isset($numbers[$currency]) ? $numbers[$currency] : false;
    }

    private function _sendCentinel()
    {

        $this->_clearCentinelSession();

        $this->_oCentinelClient = new CentinelClient();

        $this->_oCentinelClient
                ->add("MsgType", "cmpi_lookup");
        $this->_oCentinelClient
                ->add("Version", $this->_aConfCentinel['CENTINEL_MSG_VERSION']);
        $this->_oCentinelClient
                ->add("ProcessorId", $this->_aConfCentinel['CENTINEL_PROCESSOR_ID']);
        $this->_oCentinelClient
                ->add("MerchantId", $this->_aConfCentinel['CENTINEL_MERCHANT_ID']);
        $this->_oCentinelClient
                ->add("TransactionPwd", $this->_aConfCentinel['CENTINEL_TRANSACTION_PWD']);
        $this->_oCentinelClient
                ->add("UserAgent", $_SERVER["HTTP_USER_AGENT"]);
        $this->_oCentinelClient
                ->add("BrowserHeader", $_SERVER["HTTP_ACCEPT"]);
        $this->_oCentinelClient
                ->add("TransactionType", "C");
        $this->_oCentinelClient
                ->add('IPAddress', $this->_sIpAddress);

        // Standard cmpi_lookup fields
        $this->_oCentinelClient
                ->add('OrderNumber', urlencode($this->_aData['PaymentOrder_orderId']));
        $this->_oCentinelClient
                ->add('Amount', ceil($this->_aData['PaymentOrder_priceTotal'] * 100)); // Converted from pounds to cents
//		$this->_oCentinelClient
//				->add('CurrencyCode', "826"); // 826 == GBP
        $this->_oCentinelClient
                ->add('CurrencyCode', $this->getCurrencyNum(Cms::$conf['currency'])); // 826 == GBP
        $this->_oCentinelClient
                ->add('TransactionMode', "S");

        // Payer Authentication specific fields
        $this->_oCentinelClient
                ->add('CardNumber', urlencode($this->_aData['CreditCard_creditCardNumber']));
        $this->_oCentinelClient
                ->add('CardExpMonth', $this->_aData['CreditCard_padDateMonth']);
        $this->_oCentinelClient
                ->add('CardExpYear', urlencode($this->_aData['CreditCard_expDateYear']));

        $this->_oCentinelClient
                ->sendHttp($this->_aConfCentinel['CENTINEL_MAPS_URL'], $this->_aConfCentinel['CENTINEL_TIMEOUT_CONNECT'], $this->_aConfCentinel['CENTINEL_TIMEOUT_READ']);

        $sCentinelResponse = $this->_oCentinelClient->getResponse();

        if ($this->_oCentinelClient->getValue("ErrorNo") != 0)
        {

            $this->_addError("There was a problem verifying your card. Please choose another payment method or try again.");
            return false;
        }

        // Save response in sessiond
        $this->_aData["Centinel_cmpiMessageResp"] = $this->_oCentinelClient->response; // Save lookup response in session
        $this->_aData["Centinel_Enrolled"] = $this->_oCentinelClient->getValue("Enrolled");
        $this->_aData["Centinel_TransactionId"] = $this->_oCentinelClient->getValue("TransactionId");
        $this->_aData["Centinel_OrderId"] = $this->_oCentinelClient->getValue("OrderId");
        $this->_aData["Centinel_ACSUrl"] = $this->_oCentinelClient->getValue("ACSUrl");
        $this->_aData["Centinel_Payload"] = $this->_oCentinelClient->getValue("Payload");
        $this->_aData["Centinel_ErrorNo"] = $this->_oCentinelClient->getValue("ErrorNo");
        $this->_aData["Centinel_ErrorDesc"] = $this->_oCentinelClient->getValue("ErrorDesc");
        $this->_aData["Centinel_EciFlag_lookup"] = $this->_oCentinelClient->getValue("EciFlag");
        // Needed for the cmpi_authenticate message
        $this->_aData["Centinel_TransactionType"] = "C";

        // Add TermUrl to session
        $this->_aData["Centinel_TermUrl"] = $this->_aConfCentinel['CENTINEL_TERM_URL'];

        $this->_saveCentinelSession();

        if ((strcasecmp('Y', $this->_aData['Centinel_Enrolled']) == 0) && (strcasecmp('0', $this->_aData['Centinel_ErrorNo']) == 0))
        {

            return 'redirectACSForm';
        } else
        {

            return 'redirectDirect';
        }
    }

    public function processCentinelACSResponse()
    {

        if (!isset($_POST['PaRes']))
        {

            throw new Exception('PaRes from Centinel ACSResponse isn\'t set in POST!.');
        }

        $sPaResPayload = $_POST['PaRes'];
        $sMd = isset($_POST['MD']) ? $_POST['MD'] : ''; // wysylamy to do centinela i on nam to zwraca z powrotem. Mozna tego uzyc do zachowania danych.

        if (strcasecmp('', $sPaResPayload) == 0 || $sPaResPayload == null)
        {

            throw new Exception('PaRes from Centinel ACSResponse is wrong!.');
        }

        $this->_oCentinelClient = new CentinelClient;
        $this->_oCentinelClient->add('MsgType', 'cmpi_authenticate');
        $this->_oCentinelClient->add('Version', $this->_aConfCentinel['CENTINEL_MSG_VERSION']);
        $this->_oCentinelClient->add('MerchantId', $this->_aConfCentinel['CENTINEL_MERCHANT_ID']);
        $this->_oCentinelClient->add('ProcessorId', $this->_aConfCentinel['CENTINEL_PROCESSOR_ID']);
        $this->_oCentinelClient->add('TransactionPwd', $this->_aConfCentinel['CENTINEL_TRANSACTION_PWD']);
        $this->_oCentinelClient->add('TransactionType', $this->_aData['Centinel_TransactionType']);
        $this->_oCentinelClient->add('OrderId', $this->_aData['Centinel_OrderId']);
        $this->_oCentinelClient->add('TransactionId', $this->_aData["Centinel_TransactionId"]);
        $this->_oCentinelClient->add('PAResPayload', $sPaResPayload);

        $this->_oCentinelClient->sendHttp($this->_aConfCentinel['CENTINEL_MAPS_URL'], $this->_aConfCentinel['CENTINEL_TIMEOUT_CONNECT'], $this->_aConfCentinel['CENTINEL_TIMEOUT_READ']);
        $sResponse = $this->_oCentinelClient->getResponseACS();

        $this->_aData["Centinel_cmpiMessageResp"] = $this->_oCentinelClient->response;
        $this->_aData["Centinel_PAResStatus"] = $this->_oCentinelClient->getValue("PAResStatus");
        $this->_aData["Centinel_SignatureVerification"] = $this->_oCentinelClient->getValue("SignatureVerification");
        $this->_aData["Centinel_ErrorNo"] = $this->_oCentinelClient->getValue("ErrorNo");
        $this->_aData["Centinel_ErrorDesc"] = $this->_oCentinelClient->getValue("ErrorDesc");
        $this->_aData["Centinel_Cavv"] = $this->_oCentinelClient->getValue("Cavv");
        $this->_aData["Centinel_EciFlag"] = $this->_oCentinelClient->getValue("EciFlag");
        $this->_aData["Centinel_Xid"] = $this->_oCentinelClient->getValue("Xid");

        $this->_saveCentinelSession();

        return $this->processDirect();
    }

    public function processDirect()
    {

        if ($this->_aData['CreditCard_creditCardType'] == 'Maestro')
        {
            return $this->_sendDirect();
        }
        if ((strcasecmp('N', $this->_aData["Centinel_Enrolled"]) == 0) && (strcasecmp('0', $this->_aData["Centinel_ErrorNo"]) == 0))
        {
            return $this->_sendDirect();
        }
        if ((strcasecmp('Y', $this->_aData["Centinel_Enrolled"]) == 0) && (strcasecmp('0', $this->_aData["Centinel_ErrorNo"]) == 0))
        {
            return $this->_sendDirect();
        }
        if ((strcasecmp('U', $this->_aData["Centinel_Enrolled"]) == 0) && (strcasecmp('0', $this->_aData["Centinel_ErrorNo"]) == 0))
        {
            return $this->_sendDirect();
        }
        if (isset($_SESSION["Centinel_SignatureVerification"]) && $_SESSION["Centinel_SignatureVerification"] == 'N')
        {

            $this->_addError("There was a problem verifying your card. Please choose another payment method or try again.");
            return false;
        }
        if (isset($this->_aData["Centinel_PAResStatus"]) && $this->_aData["Centinel_PAResStatus"] == 'N')
        {
            $this->_addError("There was a problem verifying your card. Please choose another payment method or try again.");
            return false;
        }

        $this->_addError("There was a problem verifying your card. Please choose another payment method or try again.");
        return false;
    }

    /*
     * @return transactionId if success, false otherwise
     */

    private function _sendDirect()
    {

        $sFirstName = urlencode($this->_aData['CreditCard_firstName']);
        $sLastName = urlencode($this->_aData['CreditCard_lastName']);
        $sCreditCardType = urlencode($this->_aData['CreditCard_creditCardType']);
        $sCreditCardNumber = urlencode($this->_aData['CreditCard_creditCardNumber']);
        $sExpDateMonth = urlencode($this->_aData['CreditCard_expDateMonth']);
        $sPadDateMonth = str_pad($sExpDateMonth, 2, '0', STR_PAD_LEFT);
        $sExpDateYear = urlencode($this->_aData['CreditCard_expDateYear']);
        $sStartDateMonth = urlencode($this->_aData['CreditCard_startDateMonth']);
        $sPad2DateMonth = str_pad($sExpDateMonth, 2, '0', STR_PAD_LEFT);
        $sStartDateYear = urlencode($this->_aData['CreditCard_startDateYear']);
        $sCvv2Number = urlencode($this->_aData['CreditCard_cvv2Number']);
        $sAddress1 = urlencode($this->_aData['CreditCard_address1']);
        $sAddress2 = urlencode($this->_aData['CreditCard_address2']);
        $sCity = urlencode($this->_aData['CreditCard_city']);
        $sState = urlencode($this->_aData['CreditCard_state']);
        $sZip = urlencode($this->_aData['CreditCard_zip']);
        $sAmount = $this->_aData['PaymentOrder_priceTotal'];
        $sCountryCode = urlencode($this->_aData['CreditCard_country_code']);
        $sCurrencyCode = Cms::$conf['currency']; //"GBP";
        $sPaymentType = "Sale";
        $sIpAddress = $this->_sIpAddress;

        $sNvp = "&PAYMENTACTION=" . $sPaymentType .
                "&AMT=" . $sAmount .
                "&CREDITCARDTYPE=" . $sCreditCardType .
                "&ACCT=" . $sCreditCardNumber .
                "&EXPDATE=" . $sPadDateMonth . $sExpDateYear .
                "&CVV2=" . $sCvv2Number .
                "&FIRSTNAME=" . $sFirstName .
                "&LASTNAME=" . $sLastName .
                "&STREET=" . $sAddress1 . " " . $sAddress2 .
                "&CITY=" . $sCity .
                "&STATE=" . $sState .
                "&ZIP=" . $sZip .
                "&IPADDRESS=" . $sIpAddress .
                "&COUNTRYCODE=" . $sCountryCode .
                "&CURRENCYCODE=" . $sCurrencyCode;

        if (is_numeric($sStartDateMonth) && is_numeric($sStartDateYear))
        {

            $sNvp .= "&STARTDATE=" . $sPad2DateMonth . $sStartDateYear;
        }

        if (is_numeric($this->_aData['CreditCard_issueNumber']))
        {

            $sNvp .= "&ISSUENUMBER=" . $this->_aData['CreditCard_issueNumber'];
        }

        /*
         * CARDINAL DATA
         */

        $sNvp .= "&VERSION=59.0";
        if (isset($this->_aData["Centinel_Enrolled"]))
            $sNvp .= "&MPIVENDOR3DS=" . $this->_aData["Centinel_Enrolled"];

        if (isset($this->_aData["Centinel_EciFlag"]))
            $sNvp .= "&ECI3DS=" . $this->_aData["Centinel_EciFlag"];
        elseif (isset($this->_aData["Centinel_EciFlag_lookup"]))
            $sNvp .= "&ECI3DS=" . $this->_aData["Centinel_EciFlag_lookup"];

        if (isset($this->_aData["Centinel_Cavv"]))
            $sNvp .= "&CAVV=" . $this->_aData["Centinel_Cavv"];
        else
            $sNvp .= "&CAVV=" . '';

        if (isset($this->_aData["Centinel_PAResStatus"]))
            $sNvp .= "&AUTHSTATUS3DS=" . $this->_aData["Centinel_PAResStatus"];
        else
            $sNvp .= "&AUTHSTATUS3DS=" . '';

        if (isset($this->_aData["Centinel_Xid"]))
            $sNvp .= "&XID=" . $this->_aData["Centinel_Xid"];
        else
            $sNvp .= "&XID=" . '';

        /*
         * CARDINAL DATA END
         */

        $aResponse = hash_call("doDirectPayment", $sNvp);

        if (strtoupper($aResponse["ACK"]) != "SUCCESS")
        {

            $this->_addError($aResponse['L_LONGMESSAGE0']);
            return false;
        }

        if ($aResponse['AMT'] != round($this->_aData['PaymentOrder_priceTotal'], 2))
        {

            $this->_addError('Paid amount doesn\'t match order amount! Please contact us immediately.');
            return false;
        }

        $this->_addInfo('Your payment was successful. Your order was placed. Thank you!');

        $this->_clearCentinelSession();
        $this->_clearCreditCardSession();
        $this->_clearPayPalSession();

        return $aResponse['TRANSACTIONID'];
    }

    public function setExpressProduct($id, $name, $price, $qty, $desc = '')
    {

        $this->_aExpressProducts[$id] = ['name' => $name, 'price' => $price, 'qty' => $qty, 'desc' => $desc];
    }

    /*
     * @return transactionId if success, false otherwise
     */

    public function setExpressCreditCard($value = false)
    {
        $this->expressCreditCard = $value;
    }

    public function processExpress($sReturnUrl, $sCancelUrl)
    {
        if (!count($this->_aExpressProducts))
        {
            throw new Exception('_aExpressProducts isn\'t set!');
        }

        if (isset($_REQUEST['token']))
        {
            return $this->_processExpressToken();
        }

        $sCurrencyCodeType = Cms::$conf['currency']; //'GBP';
        $sPaymentType = 'Sale';

        // cena tylko za produkty, bez transportu
        $sItemAmt = (float) $this->_aData['PaymentOrder_priceTotal'] - (float) $this->_aData['PaymentOrder_priceTransport'];

        $this->_aData['PayPal_currencyCodeType'] = $sCurrencyCodeType;
        $this->_aData['PayPal_paymentType'] = $sPaymentType;
        $this->_aData['PayPal_AMT'] = $this->_aData['PaymentOrder_priceTotal'];

        $this->_savePayPalSession();

        $sReturnUrl = urlencode($sReturnUrl);
        $sCancelUrl = urlencode($sCancelUrl);

        $sNvp = '&LOCALECODE=' . $this->_aConfPayPal['LOCALECODE'];

        // ustawiamy to jesli chcemy aby PayPal wyswietlil formularz wprowadzania karty kredytowej zamiast logowania
        // pokaze sie tylko gdy ciastko wyczyszczone z wczesniejszego logowania (paypal login_email)
        if ($this->expressCreditCard)
        {
            $sNvp .= "&SOLUTIONTYPE=Sole&LANDINGPAGE=Billing";
        }

        $sNvp .= "&PAYMENTREQUEST_0_AMT=" . $this->_aData['PaymentOrder_priceTotal'] .
                "&PAYMENTREQUEST_0_ITEMAMT=" . $sItemAmt .
                "&RETURNURL=" . $sReturnUrl .
                "&CANCELURL=" . $sCancelUrl .
                "&PAYMENTREQUEST_0_CURRENCYCODE=" . $sCurrencyCodeType .
                "&PAYMENTREQUEST_0_PAYMENTACTION=" . $sPaymentType;

        $i = 0;

        foreach ($this->_aExpressProducts as $item)
        {

            $sNvp .= "&L_PAYMENTREQUEST_0_NAME" . $i . "=" . urlencode($item['name']);
            $sNvp .= "&L_PAYMENTREQUEST_0_QTY" . $i . "=" . $item['qty'];
            $sNvp .= "&L_PAYMENTREQUEST_0_AMT" . $i . "=" . $item['price'];
            $sNvp .= "&L_PAYMENTREQUEST_0_DESC" . $i . "=" . $item['desc'];
//                $sNvp .= "&L_PAYMENTREQUEST_0_TAXAMT" . $i . "=0";
            $i++;
        }

        // znizka

        if (isset($this->_aData['PaymentOrder_priceDiscount']))
        {
            if ($this->_aData['PaymentOrder_priceDiscount'] > 0)
            {
                $sNvp .= "&L_PAYMENTREQUEST_0_NAME" . $i . "=" . urlencode("Your Discount") . "&L_PAYMENTREQUEST_0_QTY" . $i . "=1";
                $sNvp .= "&L_PAYMENTREQUEST_0_AMT" . $i . "=-" . $this->_aData['PaymentOrder_priceDiscount'];
            } else if ($this->_aData['PaymentOrder_priceDiscount'] < 0)
            {
                $sNvp .= "&L_PAYMENTREQUEST_0_NAME" . $i . "=" . urlencode("Your Discount") . "&L_PAYMENTREQUEST_0_QTY" . $i . "=1";
                $sNvp .= "&L_PAYMENTREQUEST_0_AMT" . $i . "=" . $this->_aData['PaymentOrder_priceDiscount'];
            }
        }

        $sNvp .= "&PAYMENTREQUEST_0_SHIPPINGAMT=" . $this->_aData['PaymentOrder_priceTransport'];
        $sNvp .= "&PAYMENTREQUEST_0_INVNUM=" . $this->_aData['PaymentOrder_orderId'];

        // opis zamowienia, wylaczone bo na PP tego nie widac
//        $sNvp .= "&PAYMENTREQUEST_0_DESC=Description: " . SERVER_URL;

        //toDo
//            $sNvp .="&PAYMENTREQUEST_0_TAXAMT=" . $this->_aData['PaymentOrder_orderTax'];

//        Cms::$log->LogInfo('SetExpressCheckout: ' . $sNvp);

        $aResponse = hash_call("SetExpressCheckout", $sNvp);

//        Cms::$log->LogInfo('aResponse 1: ' . print_r($aResponse, true));

        if (strtoupper($aResponse["ACK"]) == "SUCCESS")
        {

            $sToken = urlencode($aResponse["TOKEN"]);
            $sRedirect = $this->_aConfPayPal["PAYPAL_URL"] . $sToken;
            header("Location: " . $sRedirect);
            exit();
        } else
        {

            $this->_addError($aResponse['L_LONGMESSAGE0']);
            return false;
        }
    }

    private function _processExpressToken()
    {

        if (!isset($_REQUEST['token']))
        {
            throw new Exception('PayPal token isn\'t set!');
        }

        $sToken = urlencode($_REQUEST['token']);

        $sNvp = "&TOKEN=" . $sToken;

//        Cms::$log->LogInfo('GetExpressCheckoutDetails: ' . $sNvp);

        $aResponse = hash_call("GetExpressCheckoutDetails", $sNvp);

//        Cms::$log->LogInfo('aResponse 2: ' . print_r($aResponse, true));

        $sAck = strtoupper($aResponse["ACK"]);

        if (($sAck == 'SUCCESS' || $sAck == 'SUCCESSWITHWARNING'))
        {

            $sToken = urlencode($aResponse['TOKEN']);
            $sPaymentAmount = urlencode($this->_aData['PaymentOrder_priceTotal']);
            $sPaymentType = urlencode($this->_aData['PayPal_paymentType']);
            $sCurrCodeType = urlencode($this->_aData['PayPal_currencyCodeType']);
            $sPayerID = urlencode($aResponse['PAYERID']);
            $sIpAddress = $this->_sIpAddress;

            // cena tylko za produkty, bez transportu
            $sItemAmt = (float) $this->_aData['PaymentOrder_priceTotal'] - (float) $this->_aData['PaymentOrder_priceTransport'];

            $sNvp = '&TOKEN=' . $sToken .
                    '&PAYERID=' . $sPayerID .
//					 '&PAYMENTACTION=' . $sPaymentType . 
//					 '&AMT=' . $sPaymentAmount . 
//					 '&CURRENCYCODE=' . $sCurrCodeType . 
                    '&IPADDRESS=' . $sIpAddress;

            // ponownie wysylamy koszyk aby miec w szczegolach na PayPal                        
            $sNvp .= "&PAYMENTREQUEST_0_AMT=" . $sPaymentAmount .
                    "&PAYMENTREQUEST_0_ITEMAMT=" . $sItemAmt .
                    "&PAYMENTREQUEST_0_CURRENCYCODE=" . $sCurrCodeType .
                    "&PAYMENTREQUEST_0_PAYMENTACTION=" . $sPaymentType;

            $i = 0;
            foreach ($this->_aExpressProducts as $item)
            {

                $sNvp .= "&L_PAYMENTREQUEST_0_NAME" . $i . "=" . urlencode($item['name']);
                $sNvp .= "&L_PAYMENTREQUEST_0_QTY" . $i . "=" . $item['qty'];
                $sNvp .= "&L_PAYMENTREQUEST_0_AMT" . $i . "=" . $item['price'];
                $sNvp .= "&L_PAYMENTREQUEST_0_DESC" . $i . "=" . $item['desc'];
                //                $sNvp .= "&L_PAYMENTREQUEST_0_TAXAMT" . $i . "=0";
                $i++;
            }

            // znizka
            if (isset($this->_aData['PaymentOrder_priceDiscount']))
            {
                if ($this->_aData['PaymentOrder_priceDiscount'] > 0)
                {
                    $sNvp .= "&L_PAYMENTREQUEST_0_NAME" . $i . "=" . urlencode("Your Discount") . "&L_PAYMENTREQUEST_0_QTY" . $i . "=1";
                    $sNvp .= "&L_PAYMENTREQUEST_0_AMT" . $i . "=-" . $this->_aData['PaymentOrder_priceDiscount'];
                } else if ($this->_aData['PaymentOrder_priceDiscount'] < 0)
                {
                    $sNvp .= "&L_PAYMENTREQUEST_0_NAME" . $i . "=" . urlencode("Your Discount") . "&L_PAYMENTREQUEST_0_QTY" . $i . "=1";
                    $sNvp .= "&L_PAYMENTREQUEST_0_AMT" . $i . "=" . $this->_aData['PaymentOrder_priceDiscount'];
                }
            }

            $sNvp .= "&PAYMENTREQUEST_0_SHIPPINGAMT=" . $this->_aData['PaymentOrder_priceTransport'];
            $sNvp .= "&PAYMENTREQUEST_0_INVNUM=" . $this->_aData['PaymentOrder_orderId'];

            // opis zamowienia, wylaczone bo na PP tego nie widac
//            $sNvp .= "&PAYMENTREQUEST_0_DESC=Description: " . SERVER_URL;

            Cms::$log->LogInfo('DoExpressCheckoutPayment: ' . $sNvp);

            $aResponse = hash_call("DoExpressCheckoutPayment", $sNvp);

//            Cms::$log->LogInfo('aResponse 3: ' . print_r($aResponse, true));

            $sAck = strtoupper($aResponse["PAYMENTINFO_0_ACK"]);

            if ($sAck != 'SUCCESS' && $sAck != 'SUCCESSWITHWARNING')
            {

                $this->_addError($aResponse['L_LONGMESSAGE0']);
                return false;
            }

            if ($aResponse['PAYMENTINFO_0_AMT'] != round($this->_aData['PaymentOrder_priceTotal'], 2))
            {

                $this->_addError('Paid amount doesn\'t match order amount! Please contact us immediately.');
                return false;
            }

            $this->_clearCentinelSession();
            $this->_clearCreditCardSession();
            $this->_clearPayPalSession();

            return $aResponse;
//			return $aResponse['TRANSACTIONID'];
        } else
        {

            if (isset($aResponse['L_LONGMESSAGE0']))
                $this->_addError($aResponse['L_LONGMESSAGE0']);
            else
                $this->_addError('There was a problem with PayPal communication or with verifying your card. Please choose another payment method or try again.');

            return false;
        }
    }

    public function getTransactionDetailsAsString($sTransactionId)
    {

        $aResponse = $this->getTransactionDetailsAsArray($sTransactionId);

        if (!$aResponse)
            return false;

        if (strtoupper($aResponse["ACK"]) == "SUCCESS")
        {

            $string = '';
            foreach ($aResponse as $k => $v)
            {
                $string .= $k . ": " . $v;
                $string .= "\n";
            }

            return $string;
        }

        return false;
    }

    public function getTransactionDetailsAsArray($sTransactionId)
    {

        $sNvp = "&TRANSACTIONID=" . $sTransactionId;

        $aResponse = hash_call("getTransactionDetails", $sNvp);

        return $aResponse;
    }

    public function getACSForm($sLanguage = 'en')
    {

        if (!isset($this->_aData['Centinel_ACSUrl']))
            throw new Exception('Centinel_ACSUrl isn\'t set!');

        if (!isset($this->_aData['Centinel_Payload']))
            throw new Exception('Centinel_Payload isn\'t set!');

        $sForm = <<<FORM
<script type="text/javascript">
   $(document).ready(function() {
		document.frmLaunchACS.submit();
		$("#loadingPayment").fadeIn();
   });
</script>
	<form name="frmLaunchACS" method="Post" action="CENTINEL_ACS_URL">
        <input type="hidden" name="PaReq" value="CENTINEL_PAYLOAD">
        <input type="hidden" name="TermUrl" value="CENTINEL_TERM_URL">
        <input type="hidden" name="MD" value="CENTINEL_MD">
        <noscript> 
        <div class="center"> 
            <div class="red"> 
                <h2>Processing your Payer Authentication Transaction</h2> 
                <h3>JavaScript is currently disabled or is not supported by your browser.<br></h3> 
                <h4>Please click Submit to continue the processing of your transaction.</h4> 
            </div> 
        </div>
        <input type="submit" value="Proceed to bank website" style="float:right;" class="inpSubmit" /> 
        </noscript> 
    </form>
    <div style="display:block;width:42px;height:42px;margin:0 auto;"><img id="loadingPayment" style="display:block;display:none;" src="data:image/gif;base64,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"/></div>
FORM;

        $sForm = str_replace('CENTINEL_ACS_URL', $this->_aData['Centinel_ACSUrl'], $sForm);
        $sForm = str_replace('CENTINEL_PAYLOAD', $this->_aData['Centinel_Payload'], $sForm);
        $sForm = str_replace('CENTINEL_TERM_URL', $this->_aConfCentinel['CENTINEL_TERM_URL'], $sForm);
        $sForm = str_replace('CENTINEL_MD', '', $sForm);

        return $sForm;
    }

    public function getExpressForm($sFormActionUrl = '', $sLanguage = 'en')
    {

        $sForm = <<<FORM
<script type="text/javascript">
$(document).ready(function() {
    $("#submitPaymentPE").click(function () {
		$("#submitPaymentPE").addClass('disabled').attr('disabled','disabled').attr('style','cursor:default;opacity:0.6;filter:alpha(opacity=60);');
		$("#loadingPayment").fadeIn();
		$("#formPaymentPE").submit();
  	});	
});
</script>
<div class="center bold" style="clear:left;">
<form id="formPaymentPE" class="center" action="REPLACE_FORM_ACTION" method="post" style="margin-top:35px;">
  <input type="hidden" name="action" value="website_payments_express" />
  <input id="submitPaymentPED" type="submit" value="Pay" style="background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAVCAYAAACpF6WWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkU0MkJGMDM3MUE5RTExRTE5NTQ4OTdCNDQ2RUYzNTBCIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkU0MkJGMDM4MUE5RTExRTE5NTQ4OTdCNDQ2RUYzNTBCIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6RTQyQkYwMzUxQTlFMTFFMTk1NDg5N0I0NDZFRjM1MEIiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6RTQyQkYwMzYxQTlFMTFFMTk1NDg5N0I0NDZFRjM1MEIiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5MBRmeAAAD0klEQVR42nSVa2gUVxTHz52d3Wg20RgSRO0Dqi6KwUcgxNRaao0VjSWgthEiPkEEPyiIGmj9opZSKbT0Qz9X21K0BQnEWEks+sEQRaFgkUiiJiaal7uzu5N5z53ruTN3ltmtWfjv7M7M/c05/3PuGQJv/xBULCJJnPdQNCI22+LS/xxQhioXKhPnOMBGmShDHB1xns0G5b9lVBJV1fTpe8s2tSxdv3tX2/ms/Qgcpri6pj9/PZ2/Nzas3O7/59Wdm1cmxwTYi4JJCbASVXPym49bjx05e5GSDNhUBdOZAtUehZwxCJTZwBgFTTV6hgaUH88det6La1wBLoLGRIS1535uPnC4/ezX/KTujEPOHALVGgXDmQSL5sB2c+B6mr/UY447Pqp+ebp1qjNqAxHivlWf+u7DXSeOfvsTv5A1B+Hh2AVwEEA9jA7rQoTlEomjZJ9BmeVmpsymMzvMB6WFqdi4bcnKS5d/6S9LJHzgC6UbUrXt8EH1Tv/GyZl70D3Q6tvB4YQEDUGpC6YBV0yN7e/43LBAALnkTTsW13MPecoT6l0YzfVC79ABeJr+y1+8sKIRtq/ohESsEuPzMHWKcsFFN12btcWlynVhpFJowbvL5zbyKLiHGf0/fwFD3Xp6EJ5lrpWA5/mpM3SQYXlcrL+huS0hL2xqVlFNG/wqY1EUY6DQIbzSvUP7isAtK7oEGEuOUIpQXbe3lUKpJJsp3ja8yox5RTuCg59M/1r4X5NcDbXJev+5HmXg4JawdLpGdJEP5QTHsLTHvA9527CS3ffO/M2wZfnvQWTYpz2D7fAyf9tPn6duGeiyk3j8P6iuz/Sk0Uveh6XAramrIEtzC8Bh5XoBaOOGNbFt42RBXzgjwj1N8znthuvpvp+MBUVYMu+TWYEeDSLU8oFqq1J3Q7fkcPpMvdT7kwukP3HBFzx+CVUeXwx9w6f8/uAPm1D7CkBTZ6BmGGSnGUhe1Z3Guj0Pw11VNKW+/3vOKuzlf3nfynH8ShCIycEDOJjD8Dqmi0CFQXqcQT4NXkPdzkPH9/7RhXep3ErCU41+LnYu+mxGU64TDkYoh0ux4FrgIYMZtD2f9o9e3dItHV8d7foNL+fFSPRKof5G+OFa/YbJ1yMdpqVtD1Ph/Yg7B9MOIk1INX1rVzZfOr7vcrcA6uHgJkEfFoF5srGb9y/UPBq8tXFieqQ5m1U2GKaekknyWWX5wgfvL6rr/6ih7X7Tmt0jeK8mBrYbnVIwGxiFyUNCHMNXCxMRWZHpXxjShJDi18lbrJAiDwjHpBd5VxVPfBLg3ggwANZ0H3ipedtnAAAAAElFTkSuQmCC') no-repeat scroll 4px 9px #F6F6F6;border: 1px solid #98C809;border-radius: 11px 11px 11px 11px;color: #666666;cursor: pointer;font-size: 20px;font-weight: bold;height: 40px;line-height: 23px;padding: 0 20px 1px 30px;width: auto;">
  <div style="display:block;width:42px;height:42px;margin:0 auto;">
	  <img id="loadingPayment" src="data:image/gif;base64,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" style="display:block;margin:0 auto;display:none;" />
  </div>
</form>
</div>
	  <ul style="text-align: left;margin: 0px 75px;">
			<li>For our customers safety credit card payments are handled by PayPal service.</li>
			<li>You will be redirected to PayPal secure payments website.</li>
			<li>After successful payment you will be redirected back to our website.</li>
			<li style="font-weight:bold;">You don't need to have PayPal registered account. PayPal supports guest payments for debit or credit cards.</li>
	  </ul>
FORM;

        $sForm = str_replace('REPLACE_FORM_ACTION', $sFormActionUrl, $sForm);

        return $sForm;
    }

    public function getCreditCardForm($sFormActionUrl = '', $sLanguage = 'en')
    {

        $sForm = <<<FORM
<script type="text/javascript">
var eleVisible = 0;
$(document).ready(function(){
	 $(function() {
       $('select[name=country_code]').change(function(){
          var country = $(this).val();
          if(country != 'GB')
          {
             $('#stateBox').hide();
          }
          else
          {
             $('#stateBox').show();
          }
       });
    });
	$("#submitPaymentPD").click(function () {
		$("#submitPaymentPD").addClass('disabled').attr('disabled','disabled').attr('style','cursor:default;opacity:0.6;filter:alpha(opacity=60);');
		$("#loadingPayment").fadeIn();
		$('#formPaymentPD').submit();
  	});
		$("#issueNumberBox").hide();
    $('#startDateBox').hide();
    $('select[name|="creditCardType"]').change(function () {
          var str = "";
          $('select[name|="creditCardType"] option:selected').each(function () {
              	str = $(this).text();
          });
          if( str == "Visa/Delta/Electron" || str == "MasterCard/Eurocard") {
        	$("#issueNumberBox").hide();
        	$('#startDateBox').hide();
          }
          else  if( str == "Maestro") {
        	$("#issueNumberBox").show();
        	$('#startDateBox').show();
          }
		  else {
			$("#issueNumberBox").hide();
			$('#startDateBox').hide();
		  
		  }
        }).change();
});
</script> 
    <form id="formPaymentPD" class="form-horizontal" action="REPLACE_FORM_ACTION" method="post" style="margin-top:35px;">                
        <div class="form-group">
            <label class="col-sm-2 control-label">Country<span class="red">*</span></label>
            <div class="col-sm-3">
                <select name="country_code" id="country_code" class="form-control"><option value="">...</option><option value="GB">United Kingdom</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AG">Antigua and Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan Republic</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BB">Barbados</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BA">Bosnia and Herzegovina</option><option value="BW">Botswana</option><option value="BR">Brazil</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="KY">Cayman Islands</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="C2">China</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="CD">Democratic Republic of the Congo</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="SV">El Salvador</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="GA">Gabon Republic</option><option value="GM">Gambia</option><option value="DE">Germany</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GT">Guatemala</option><option value="GN">Guinea</option><option value="GW">Guinea Bissau</option><option value="GY">Guyana</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IE">Ireland</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LS">Lesotho</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="FM">Micronesia</option><option value="MN">Mongolia</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="AN">Netherlands Antilles</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NU">Niue</option><option value="NF">Norfolk Island</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PW">Palau</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn Islands</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="QA">Qatar</option><option value="CG">Republic of the Congo</option><option value="RE">Reunion</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="KN">Saint Kitts and Nevis Anguilla</option><option value="PM">Saint Pierre and Miquelon</option><option value="VC">Saint Vincent and Grenadines</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">São Tomé and Príncipe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="KR">South Korea</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="SH">St. Helena</option><option value="LC">St. Lucia</option><option value="SR">Suriname</option><option value="SJ">Svalbard and Jan Mayen Islands</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TG">Togo</option><option value="TO">Tonga</option><option value="TT">Trinidad and Tobago</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks and Caicos Islands</option><option value="TV">Tuvalu</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="US">United States</option><option value="UY">Uruguay</option><option value="VU">Vanuatu</option><option value="VA">Vatican City State</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="VG">Virgin Islands (British)</option><option value="WF">Wallis and Futuna Islands</option><option value="YE">Yemen</option><option value="ZM">Zambia</option></select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">First Name<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" name="firstName" value="REPLACE_FIRSTNAME" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">First Name<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" name="lastName" value="REPLACE_LASTNAME" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Card Type<span class="red">*</span></label>
            <div class="col-sm-3">
                <select class="form-control" name="creditCardType">
                        <option value="">...</option>
                        <option value="MasterCard">MasterCard/Eurocard</option>
                        <option value="Visa">Visa/Delta/Electron</option>
                        <option value="Maestro">Maestro</option>
                 </select> 
            </div>
        </div>                
        <div class="form-group">
            <label class="col-sm-2 control-label">Card Number<span class="red">*</span></label>
            <div class="col-sm-3">
                <input maxlength="19" type="text" name="creditCardNumber" id="creditCardNumber" value="" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Expiry Date&nbsp;<span style="color:#bbb">mm | yyyy</span><span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" style="width:48%;display:inline-block;" maxlength="2" type="text" name="expDateMonth" value="" placeholder="mm" />
            	<input class="form-control" style="width:48%;display:inline-block;" maxlength="4" type="text" name="expDateYear" value="" placeholder="yyyy" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CVV<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" name="cvv2Number" value="" />
            </div>
        </div>            
        <div class="form-group" id="startDateBox">
            <label class="col-sm-2 control-label">Start Date&nbsp;<span style="color:#bbb">mm | yyyy</span></label>
            <div class="col-sm-3">
                <input class="form-control" style="width:48%;display:inline-block;" maxlength="2" type="text" name="startDateMonth" value="" placeholder="mm" />
            	<input class="form-control" style="width:48%;display:inline-block;" maxlength="4" type="text" name="startDateYear" value="" placeholder="yyyy" />
                <span style="display:block;color:#999">fill only if it's shown on card</span>
            </div>
        </div>
        <div class="form-group" id="issueNumberBox">
            <label class="col-sm-2 control-label">Issue Number<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" maxlength="2" name="issueNumber" value="" />
                <span style="display:block;color:#999">fill only if it's shown on card</span>
            </div>
        </div>             
        <div class="form-group">
            <label class="col-sm-2 control-label">Address Line 1<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" name="address1" value="REPLACE_ADDRESS1" />
            </div>
        </div>                  
        <div class="form-group">
            <label class="col-sm-2 control-label">Address Line 2</label>
            <div class="col-sm-3">
                <input class="form-control" type="text" name="address2" value="REPLACE_ADDRESS2" />
            </div>
        </div>                  
        <div class="form-group">
            <label class="col-sm-2 control-label">Post Code<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" id="postcodeVal" name="zip" value="REPLACE_POSTCODE" maxlength="20" />
            </div>
        </div>                 
        <div class="form-group">
            <label class="col-sm-2 control-label">Town/City<span class="red">*</span></label>
            <div class="col-sm-3">
                <input class="form-control" type="text" id="postcodeVal" name="city" value="REPLACE_CITY" />
            </div>
        </div>
        <div class="form-group" id="stateBox">
            <label class="col-sm-2 control-label">County<span class="red">*</span></label>
            <div class="col-sm-3">
                <select name="state" id="state" class="form-control"><option value=""></option><optgroup label="England"><option value="Avon">Avon</option><option value="Bedfordshire">Bedfordshire</option><option value="Berkshire">Berkshire</option><option value="Bristol">Bristol</option><option value="Buckinghamshire">Buckinghamshire</option><option value="Cambridgeshire">Cambridgeshire</option><option value="Cheshire">Cheshire</option><option value="Cleveland">Cleveland</option><option value="Cornwall">Cornwall</option><option value="Cumbria">Cumbria</option><option value="Derbyshire">Derbyshire</option><option value="Devon">Devon</option><option value="Dorset">Dorset</option><option value="Durham">Durham</option><option value="East Riding of Yorkshire">East Riding of Yorkshire</option><option value="East Sussex">East Sussex</option><option value="Essex">Essex</option><option value="Gloucestershire">Gloucestershire</option><option value="Greater Manchester">Greater Manchester</option><option value="Hampshire">Hampshire</option><option value="Herefordshire">Herefordshire</option><option value="Hertfordshire">Hertfordshire</option><option value="Humberside">Humberside</option><option value="Isle of Wight">Isle of Wight</option><option value="Isles of Scilly">Isles of Scilly</option><option value="Kent">Kent</option><option value="Lancashire">Lancashire</option><option value="Leicestershire">Leicestershire</option><option value="Lincolnshire">Lincolnshire</option><option value="London">London</option><option value="Merseyside">Merseyside</option><option value="Middlesex">Middlesex</option><option value="Norfolk">Norfolk</option><option value="North Yorkshire">North Yorkshire</option><option value="North East Lincolnshire">North East Lincolnshire</option><option value="Northamptonshire">Northamptonshire</option><option value="Northumberland">Northumberland</option><option value="Nottinghamshire">Nottinghamshire</option><option value="Oxfordshire">Oxfordshire</option><option value="Rutland">Rutland</option><option value="Shropshire">Shropshire</option><option value="Somerset">Somerset</option><option value="South Yorkshire">South Yorkshire</option><option value="Staffordshire">Staffordshire</option><option value="Suffolk">Suffolk</option><option value="Surrey">Surrey</option><option value="Tyne and Wear">Tyne and Wear</option><option value="Warwickshire">Warwickshire</option><option value="West Midlands">West Midlands</option><option value="West Sussex">West Sussex</option><option value="West Yorkshire">West Yorkshire</option><option value="Wiltshire">Wiltshire</option><option value="Worcestershire">Worcestershire</option></optgroup><optgroup label="Northern Ireland"><option value="Antrim">Antrim</option><option value="Armagh">Armagh</option><option value="Down">Down</option><option value="Fermanagh">Fermanagh</option><option value="Londonderry">Londonderry</option><option value="Tyrone">Tyrone</option></optgroup><optgroup label="Scotland"><option value="Aberdeen City">Aberdeen City</option><option value="Aberdeenshire">Aberdeenshire</option><option value="Angus">Angus</option><option value="Argyll and Bute">Argyll and Bute</option><option value="Banffshire">Banffshire</option><option value="Borders">Borders</option><option value="Clackmannan">Clackmannan</option><option value="Dumfries and Galloway">Dumfries and Galloway</option><option value="East Ayrshire">East Ayrshire</option><option value="East Dunbartonshire">East Dunbartonshire</option><option value="East Lothian">East Lothian</option><option value="East Renfrewshire">East Renfrewshire</option><option value="Edinburgh City">Edinburgh City</option><option value="Falkirk">Falkirk</option><option value="Fife">Fife</option><option value="Glasgow">Glasgow (City of)</option><option value="Highland">Highland</option><option value="Inverclyde">Inverclyde</option><option value="Midlothian">Midlothian</option><option value="Moray">Moray</option><option value="North Ayrshire">North Ayrshire</option><option value="North Lanarkshire">North Lanarkshire</option><option value="Orkney">Orkney</option><option value="Perthshire and Kinross">Perthshire and Kinross</option><option value="Renfrewshire">Renfrewshire</option><option value="Roxburghshire">Roxburghshire</option><option value="Shetland">Shetland</option><option value="South Ayrshire">South Ayrshire</option><option value="South Lanarkshire">South Lanarkshire</option><option value="Stirling">Stirling</option><option value="West Dunbartonshire">West Dunbartonshire</option><option value="West Lothian">West Lothian</option><option value="Western Isles">Western Isles</option></optgroup><optgroup label="Unitary Authorities of Wales"><option value="Blaenau Gwent">Blaenau Gwent</option><option value="Bridgend">Bridgend</option><option value="Caerphilly">Caerphilly</option><option value="Cardiff">Cardiff</option><option value="Carmarthenshire">Carmarthenshire</option><option value="Ceredigion">Ceredigion</option><option value="Conwy">Conwy</option><option value="Denbighshire">Denbighshire</option><option value="Flintshire">Flintshire</option><option value="Gwynedd">Gwynedd</option><option value="Isle of Anglesey">Isle of Anglesey</option><option value="Merthyr Tydfil">Merthyr Tydfil</option><option value="Monmouthshire">Monmouthshire</option><option value="Neath Port Talbot">Neath Port Talbot</option><option value="Newport">Newport</option><option value="Pembrokeshire">Pembrokeshire</option><option value="Powys">Powys</option><option value="Rhondda Cynon Taff">Rhondda Cynon Taff</option><option value="Swansea">Swansea</option><option value="Torfaen">Torfaen</option><option value="The Vale of Glamorgan">The Vale of Glamorgan</option><option value="Wrexham">Wrexham</option></optgroup><optgroup label="UK Offshore Dependencies"><option value="Channel Islands">Channel Islands</option><option value="Isle of Man">Isle of Man</option></optgroup></select>
            </div>
        </div>   
        <div class="form-group" id="stateBox">
            <label class="col-sm-2 control-label"></label>
            <div class="col-sm-3">
                <span class="red">*</span> The fields marked are mandatory.
            </div>
        </div>               
        <input type="hidden" name="action" value="centinel" />
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-default" id="submitPaymentPD" value="Pay">Pay</button>
            </div>
        </div>                  
        <div style="display:block;"><img id="loadingPayment" src="data:image/gif;base64,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" style="display:block;margin:0 auto;display:none;" /></div>
   </form>
FORM;

        $sForm = str_replace('REPLACE_FORM_ACTION', $sFormActionUrl, $sForm);
        $sForm = isset($this->_aData['CreditCard_firstName']) ? str_replace('REPLACE_FIRSTNAME', $this->_aData['CreditCard_firstName'], $sForm) : str_replace('REPLACE_FIRSTNAME', '', $sForm);
        $sForm = isset($this->_aData['CreditCard_lastName']) ? str_replace('REPLACE_LASTNAME', $this->_aData['CreditCard_lastName'], $sForm) : str_replace('REPLACE_LASTNAME', '', $sForm);
        $sForm = isset($this->_aData['CreditCard_address1']) ? str_replace('REPLACE_ADDRESS1', $this->_aData['CreditCard_address1'], $sForm) : str_replace('REPLACE_ADDRESS1', '', $sForm);
        $sForm = isset($this->_aData['CreditCard_address2']) ? str_replace('REPLACE_ADDRESS2', $this->_aData['CreditCard_address2'], $sForm) : str_replace('REPLACE_ADDRESS2', '', $sForm);
        $sForm = isset($this->_aData['CreditCard_zip']) ? str_replace('REPLACE_POSTCODE', $this->_aData['CreditCard_zip'], $sForm) : str_replace('REPLACE_POSTCODE', '', $sForm);
        $sForm = isset($this->_aData['CreditCard_city']) ? str_replace('REPLACE_CITY', $this->_aData['CreditCard_city'], $sForm) : str_replace('REPLACE_CITY', '', $sForm);

        if (isset($this->_aData['CreditCard_creditCardType']) && strpos($sForm, "value=\"{$this->_aData['CreditCard_creditCardType']}\"") !== false)
        {

            $sForm = str_replace("value=\"{$this->_aData['CreditCard_creditCardType']}\"", "value=\"{$this->_aData['CreditCard_creditCardType']}\" selected=\"selected\"", $sForm);
        }

        if (isset($this->_aData['CreditCard_country_code']) && strpos($sForm, "value=\"{$this->_aData['CreditCard_country_code']}\"") !== false)
        {

            $sForm = str_replace("value=\"{$this->_aData['CreditCard_country_code']}\"", "value=\"{$this->_aData['CreditCard_country_code']}\" selected=\"selected\"", $sForm);
        }

        if (isset($this->_aData['CreditCard_state']) && strpos($sForm, "value=\"{$this->_aData['CreditCard_state']}\"") !== false)
        {

            $sForm = str_replace("value=\"{$this->_aData['CreditCard_state']}\"", "value=\"{$this->_aData['CreditCard_state']}\" selected=\"selected\"", $sForm);
        }

        return $sForm;
    }

    private function _clearCentinelSession()
    {

        unset($_SESSION['Message']);
        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^Centinel_.*/", $key) > 0)
            {
                unset($_SESSION[$key]);
                unset($this->_aData[$key]);
            }
        }
    }

    private function _loadCentinelSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^Centinel_.*/", $key) > 0)
            {
                $this->_aData[$key] = $this->_decryptVar($value);
            }
        }
    }

    private function _saveCentinelSession()
    {

        foreach ($this->_aData as $key => $value)
        {
            if (preg_match("/^Centinel_.*/", $key) > 0)
            {
                $_SESSION[$key] = $this->_encryptVar($value);
            }
        }
    }

    private function _clearPayPalSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^PayPal_.*/", $key) > 0)
            {
                unset($_SESSION[$key]);
                unset($this->_aData[$key]);
            }
        }
    }

    private function _loadPayPalSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^PayPal_.*/", $key) > 0)
            {
                $this->_aData[$key] = $this->_decryptVar($value);
            }
        }
    }

    private function _savePayPalSession()
    {

        foreach ($this->_aData as $key => $value)
        {
            if (preg_match("/^PayPal_.*/", $key) > 0)
            {
                $_SESSION[$key] = $this->_encryptVar($value);
            }
        }
    }

    private function _clearCreditCardSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^CreditCard_.*/", $key) > 0)
            {
                unset($_SESSION[$key]);
                unset($this->_aData[$key]);
            }
        }
    }

    private function _loadCreditCardSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^CreditCard_.*/", $key) > 0)
            {
                $this->_aData[$key] = $this->_decryptVar($value);
            }
        }
    }

    private function _saveCreditCardSession()
    {

        foreach ($this->_aData as $key => $value)
        {
            if (preg_match("/^CreditCard_.*/", $key) > 0)
            {
                $_SESSION[$key] = $this->_encryptVar($value);
            }
        }
    }

    private function _clearPaymentOrderSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^PaymentOrder_.*/", $key) > 0)
            {
                unset($_SESSION[$key]);
                unset($this->_aData[$key]);
            }
        }
    }

    private function _loadPaymentOrderSession()
    {

        foreach ($_SESSION as $key => $value)
        {
            if (preg_match("/^PaymentOrder_.*/", $key) > 0)
            {
                $this->_aData[$key] = $this->_decryptVar($value);
            }
        }
    }

    private function _savePaymentOrderSession()
    {

        foreach ($this->_aData as $key => $value)
        {
            if (preg_match("/^PaymentOrder_.*/", $key) > 0)
            {
                $_SESSION[$key] = $this->_encryptVar($value);
            }
        }
    }

    private function _encryptVar($s)
    {

        if (!$this->_bEncrypt)
            return $s;

        if (is_array($s))
            return $s;

        if (is_object($s))
            return $s;

        $variab = base64_encode(gzcompress(mcrypt_encrypt(
                                MCRYPT_RIJNDAEL_128, md5($this->_sEncryptionKey), $s, MCRYPT_MODE_CBC, substr(md5(sha1($this->_sEncryptionKey)), 0, 16)), 1));

        return $variab;
    }

    private function _decryptVar($s)
    {
        if (!$this->_bEncrypt)
            return $s;

        if (is_array($s))
            return $s;

        if (is_object($s))
            return $s;

        $variab = rtrim(mcrypt_decrypt(
                        MCRYPT_RIJNDAEL_128, md5($this->_sEncryptionKey), gzuncompress(base64_decode($s)), MCRYPT_MODE_CBC, substr(md5(sha1($this->_sEncryptionKey)), 0, 16)), "\0");

        return $variab;
    }

    private function _checkCC($cc, $extra_check = false)
    {

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
        $pattern = "#^(?:" . implode("|", $cards) . ")$#";
        $result = preg_match($pattern, str_replace(" ", "", $cc), $matches);
        if ($extra_check && $result > 0)
        {
            $result = (validatecard($cc)) ? 1 : 0;
        }
        return ($result > 0) ? $names[sizeof($matches) - 2] : false;
    }

}
