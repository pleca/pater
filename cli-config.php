<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
//require_once 'bootstrap.php';
$GLOBALS['counter_db'] = 0;
$GLOBALS['counter_q'] = 0;
require('application/config/config.php'); // ustawienia indywidualne
require('application/config/config_common.php'); // ustawienia wspolne
require(SYS_DIR . '/core/Cms.php');
require(SYS_DIR . '/core/ErrorHandling.php');
require(SYS_DIR . '/core/Functions.php');
require(SYS_DIR . '/database/Database.php');


Cms::initialize();
// replace with mechanism to retrieve EntityManager in your app
$entityManager = Cms::$entityManager;

return ConsoleRunner::createHelperSet($entityManager);
