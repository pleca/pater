<?php

/* 2015-10-14 | 4me.CMS 15.3 */

// main
define('SERVER_URL', 'http://pattern.dev');
define('CMS_URL', '');

define('ERROR', 1);  // wlancza raportowanie błędów
define('LOGGER_PRIORITY', 1);  // poziom logów: 1-DEBUG 2-INFO 3-WARN 4-ERROR 5-FATAL 6-OFF
define('TEST', 1);  // system testowy
define('PHP_SELF_OTHER', 0);  // na niektorych serwerach nie ma zmiennej REDIRECT_URL, wlanczyamy SCRIPT_URL

define('SYS_ID', 'pattern.idea4me.pl'); // identyfikator systemu
//define('SYS_ID', 'pattern'); // tak było tydzien temu

//date_default_timezone_set('Europe/Warsaw');
date_default_timezone_set('Europe/London');

// database
define('DB_SERVER', 'localhost');
define('DB_USER', 'phpmyadmin');
define('DB_PASSWORD', 'hinolp');
define('DB_NAME', 'sh_shopvitaminsh');
define('DB_PREFIX', '');
define('DB_PORT', '3306');

// API GA
define('GA_ACC_NAME', '');
define('GA_ACC_ID', '');
define('GA_ACC_KEY', '');

//paypal
define('RETURN_URL', 'https://pattern.idea4me.pl/payment-paypal.html');
define('CANCEL_URL', 'https://pattern.idea4me.pl/basket.html');
define('CREDIT_CARD_RETURN_URL', 'https://pattern.idea4me.pl/payment-creditcard.html');
