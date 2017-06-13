<?php

//czysc wpisy starsze niz 3 miesiace
$q = "DELETE FROM `" . DB_PREFIX . "api_shop_log` WHERE `date_add`< DATE_SUB(NOW(), INTERVAL 3 MONTH)";

if (Cms::$db->delete($q)) {
	echo 'Old api_shop_log entries has been deleted';
} else {
	echo 'There was mothing to clear...';
}