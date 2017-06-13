<?php

return [
	"API_ENDPOINT"			=> 'https://api-3t.paypal.com/nvp',
	"SUBJECT"				=> '',
	"USE_PROXY"				=> FALSE,
	"PROXY_HOST"			=> '127.0.0.1',
	"PROXY_PORT"			=> '8080',
/*
	Sandbox:
	https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
	Live:
	https://www.paypal.com/webscr&cmd=_express-checkout&token=
*/
	"PAYPAL_URL"			=> 'https://www.paypal.com/webscr&cmd=_express-checkout&token=',
	"VERSION"				=> '87.0',
	"ACK_SUCCESS"			=> 'SUCCESS',
	"ACK_SUCCESS_WITH_WARNING" => 'SUCCESSWITHWARNING',
    "LOCALECODE"            => 'en_GB'
];
