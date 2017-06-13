<?php
define('VER', '');
error_reporting(E_ALL);
ini_set('display_errors', 'on');

echo '<h1>Runnig crons...</h1>';

require_once('application/config/config.php'); // ustawienia indywidualne
require_once('application/config/config_common.php'); // ustawienia wspolne

require(SYS_DIR . '/core/Cms.php');
require(SYS_DIR . '/core/ErrorHandling.php');
require(SYS_DIR . '/core/Functions.php');
//require(SYS_DIR . '/core/Logger.php');
require(SYS_DIR . '/database/Database.php');

require_once(ENTITY_DIR . '/Cron.php');

$GLOBALS['counter_db'] = 0;
$GLOBALS['counter_q'] = 0;

Cms::initialize();
setConstsFromConfig();

Cms::$twig->addGlobal('conf', Cms::$conf);
require(CMS_DIR . '/application/languages/admin-en.php');
Cms::$twig->addGlobal('lang', $GLOBALS['LANG']);
foreach (Cms::$langs as $v) { // domyslny jezyk serwisu
	if ($v['default'] == 1) {
		define('_ID', $v['id']);		
		define('LOCALE', $v['code']);		
	}
}

$cronRepository = CMS::$entityManager->getRepository('Application\Entity\Cron');
$entities = $cronRepository->findBy(['active' => 1]);

//$now = new \DateTime('2017-01-15 10:00:05');
$now = new \DateTime();

$params = array(
	'now'		=> $now,
	'minute'	=> $now->format('i'),
	'hour'	=> $now->format('H'),
	'dayOfMonth'	=> $now->format('d'),
	'month'	=> $now->format('n'),
	'dayOfWeek'	=> $now->format('w'),
);
		
if ($entities) {
	foreach ($entities as $entity) {

		if (!meetConditions($entity, $params)) {
			continue;
		}
		
		echo 'Running cron <strong>' . $entity->getName() .'</strong> <br />';
		if (file_exists(CMS_DIR . '/application/crons/' . $entity->getName() . '.php')) {
			echo 'Result:<br />';
			require_once(CMS_DIR . '/application/crons/' . $entity->getName() . '.php');			
			$entity->setLastRun($now);
			echo '<br /><br />';
		}				
	}
	
	CMS::$entityManager->flush();
}

function meetConditions($entity, $params) {
	
	if ($entity->getStartDate() && $params['now'] >= $entity->getStartDate()) {
		return false;
	}

	if ($entity->getEndDate() && $params['now'] >= $entity->getEndDate()) {
		return false;
	}

	if ($entity->getDayOfWeek() != '*' && $entity->getDayOfWeek() != $params['dayOfWeek']) {
		return false;
	}		

	if ($entity->getMonth() != '*' && $entity->getMonth() != $params['month']) {
		return false;
	}		

	if ($entity->getDayOfMonth() != '*' && $entity->getDayOfMonth() != $params['dayOfMonth']) {
		return false;
	}

	if ($entity->getHour() != '*' && $entity->getHour() != $params['hour']) {
		return false;
	}

	if ($entity->getMinute() != '*' && $entity->getMinute() != $params['minute']) {
		return false;
	}

	return true;
}

//dump($entities);
die;

?>