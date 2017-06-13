<?php

/* 2015-10-14 | 4me.CMS 15.3 */

// main
define('CMS_DIR', dirname(dirname(dirname(__FILE__))));
define('CONTROL_DIR', CMS_DIR . '/application/controllers');
define('MODEL_DIR', CMS_DIR . '/application/models');
define('CLASS_DIR', CMS_DIR . '/application/classes');
define('LIB_DIR', CMS_DIR . '/application/libraries');
define('LOG_DIR', CMS_DIR . '/application/logs');
define('CONF_DIR', CMS_DIR . '/application/config');
define('SYS_DIR', CMS_DIR . '/system');
define('TPL_URL', CMS_URL . '/public');
define('VIEW_DIR', CMS_DIR . '/application/views');
define('ENTITY_DIR', CMS_DIR . '/application/entity');
define('EXP_DIR', CMS_DIR . '/application/export');

define('NO_ACCESS', 1); // dostep do plikow PHP
define('UNIX', 0); // wlancza funkcje dzialajace wylacznie na systmie UNIX

// security
define('SALT', 'ktY7LJTqdr1whm3Ej5Rf');
define('SALT_NUMBER', 14);
define('USER_CODE', 'userXb1zAauVZh7');
define('CUSTOMER_CODE', 'customerXgGvnAy43Uq');

$hide_left_menu = ['', 'basket', 'customer', 'order', 'payment-paypal', 'payment-creditcard', 'payment-banktransfer', 'producers'];

//producenci
define('PRODUCER_IMG_X1', 100); //medium - m
define('PRODUCER_IMG_Y1', 50);
define('PRODUCER_IMG_X2', 60); //small - s
define('PRODUCER_IMG_Y2', 20);
define('PRODUCER_IMG_X3', 0);//extra - e
define('PRODUCER_IMG_Y3', 0);
define('PRODUCER_IMG_RATIO', 'c'); // y - wysokosc auto, x - szerokosc auto, c - kadrowanie