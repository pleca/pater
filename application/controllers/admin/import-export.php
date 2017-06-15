<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
    die('No access to files!');
if (Cms::$modules['shop'] != 1)
    die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
    die('No permission at this level!');

require_once(MODEL_DIR . '/Product.php');
require_once(CLASS_DIR . '/ImportExportCsv/CsvImporter.php');

$prod = new Product();
$params = ['locale' => Cms::$defaultLocale];


$products = $prod->getAll($params, ['name', 'tag1', 'tag2', 'tag3']);
//$products1 = array (
//    array('aaa', 'bbb', 'ccc', 'dddd'),
//    array('123', '456', '789'),
//    array('"aaa"', '"bbb"')
//);

$exporter = new \Application\Classes\ImportExportCsv\CsvExporter($products, EXP_DIR.'/products.csv');

$csv = $exporter->get();
$params['csv'] = $csv;
showList($params);



//CSV to Array
$importer = new \Application\Classes\ImportExportCsv\CsvImporter(EXP_DIR . '/products.csv');                    //nie widzi bez całej ścieżki, mimo że jest require.
$data = $importer->get();


function showList($params = [])
{
    $data = ['pageTitle' => $GLOBALS['LANG']['shop_tax_title']];

    echo Cms::$twig->render('admin/import_export/import-export.twig', array_merge($data, $params));
}


