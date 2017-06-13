<?php

return [
	"CENTINEL_MSG_VERSION"		=> "1.7",
	"CENTINEL_PROCESSOR_ID"		=> "134-01",
	"CENTINEL_MERCHANT_ID"		=> "payments@vitamin-shop.co.uk",
	"CENTINEL_TRANSACTION_PWD"  => "9kjySMeXMBXD412r",
	"CENTINEL_MAPS_URL"			=> "https://paypal.cardinalcommerce.com/MAPS/txns.asp",
	"CENTINEL_TERM_URL"			=> "https://{$_SERVER['SERVER_NAME']}/order.html?action=centinel&action_type=ACSResp",
	"CENTINEL_NOTIFY_URL"		=> "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}",
	"CENTINEL_TIMEOUT_CONNECT"	=> "5000",
	"CENTINEL_TIMEOUT_READ"		=> "15000",
	"CENTINEL_AUTHENTICATION_MESSAGING" => "For your security, please fill out the form below to complete your order.</b><br/>"
			. "Do not click the refresh or back button or this transaction may be interrupted or cancelled.",
	"CENTINEL_MERCHANT_LOGO"	=>  $_SERVER['DOCUMENT_ROOT'] . "/files/cardinal/logo.png",
	"CENTINEL_ERROR_CODE_8000"  => "8000",
	"CENTINEL_ERROR_CODE_8000_DESC" => "Protocol Not Recogonized, must be http:// or https://",
	"CENTINEL_ERROR_CODE_8010"	=> "8010",
	"CENTINEL_ERROR_CODE_8010_DESC" => "Unable to Communicate with MAPS Server",
	"CENTINEL_ERROR_CODE_8020"	=> "8020",
	"CENTINEL_ERROR_CODE_8020_DESC" => "Error Parsing XML Response",
	"CENTINEL_ERROR_CODE_8030"	=> "8030",
	"CENTINEL_ERROR_CODE_8030_DESC" => "Communication Timeout Encountered",

	"CENTINEL_ERROR_CODE_8090"	=> "8090",
	"CENTINEL_ERROR_CODE_8090_DESC" => "Error Parsing Payload",
	"CENTINEL_ERROR_CODE_8091"	=> "8091",
	"CENTINEL_ERROR_CODE_8091_DESC" => "Invalid Payload Hash",
];
