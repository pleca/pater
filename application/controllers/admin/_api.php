<?php

die;
// dodawanie kategori i producentow do sklepu online
$file1 = CMS_DIR . '/products_categories.csv';
$file2 = CMS_DIR . '/products_manufacturers.csv';
$i = 0;
$j = 0;

$q = "TRUNCATE `product_category` ";
Cms::$db -> exec($q);
$q = "TRUNCATE `product_manufacturer` ";
Cms::$db -> exec($q);

if(($handle = fopen($file1, "r")) !== FALSE) {
	while(($v = fgetcsv($handle, 1000, ",")) !== FALSE) {
		// id	parent_id	status_id	name	name_url	order
		$name_url = makeUrl($v[3]);
		$i++;
		$q = "INSERT INTO `" . DB_PREFIX . "product_category` SET `status_id`='2', `name`='" . addslashes($v[3]). "', `name_url`='" . addslashes($name_url). "', `order`='" . $i . "' ";
		Cms::$db->insert($q);
	}
}

if(($handle = fopen($file2, "r")) !== FALSE) {
	while(($v = fgetcsv($handle, 1000, ",")) !== FALSE) {
		// id	status_id	name	name_url	order
		$name_url = makeUrl($v[3]);
		$j++;
		$q = "INSERT INTO `" . DB_PREFIX . "product_manufacturer` SET `status_id`='2', `name`='" . addslashes($v[3]). "', `name_url`='" . addslashes($name_url). "', `order`='" . $j . "' ";
		Cms::$db->insert($q);
	}
}
dump($i);
dump($j);


die;
// przypisanie wszsytkich krajow to regionu w kurierze
$courier_id = 2;
$region_id = 5;

$q = "DELETE FROM `" . DB_PREFIX . "transport_region_country` WHERE `region_id`='" . $region_id . "' ";
Cms::$db->delete($q);

$q = "ALTER TABLE `" . DB_PREFIX . "transport_region_country` auto_increment = 1 ";
Cms::$db->exec($q);

$q = "SELECT * FROM `" . DB_PREFIX . "transport_country` ";
$array = Cms::$db->getAll($q);

foreach($array as $v) {
	$q = "INSERT INTO `" . DB_PREFIX . "transport_region_country` SET `region_id`='" . $region_id . "', `country_id`='" . $v['id']. "' ";
	Cms::$db->insert($q);
}
