<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
    die('No access to files!');
if (Cms::$modules['shop'] != 1)
    die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
    die('No permission at this level!');

require_once(MODEL_DIR . '/Product.php');
require_once(MODEL_DIR . '/ProductsCsv.php');
require_once(CLASS_DIR . '/ImportExportCsv/CsvImporter.php');
require_once(CLASS_DIR . '/ImportExportCsv/CsvExporter.php');
require_once(CLASS_DIR . '/ImportExportCsv/CsvExporterHelper.php');
require_once(CLASS_DIR . '/ImportExportCsv/DBImport.php');

$pc = new ProductsCsv();
//$variations = $pc->getVariations();     // SELECT, tablica danych
//$products = $pc->getProducts();         // SELECT, tablica danych
//$helper = new \Application\Classes\ImportExportCsv\CsvExporterHelper($products,$variations);
//$data = $helper->get();
//
////todo: eksport z DB do CSV działa jak ta lala!
////DB to array to CSV
//$exporter = new \Application\Classes\ImportExportCsv\CsvExporter($data, EXP_DIR . '/products.csv', false);
//$csv = $exporter->get();
//$params['csv'] = $csv;

//todo: import z CSV do DB. z CSV do array, nie zapisuje w bazie
//CSV to Array
$importer = new \Application\Classes\ImportExportCsv\CsvImporter(EXP_DIR . '/products.csv');                    //nie widzi bez całej ścieżki, mimo że jest require.
$data = $importer->get();
showList($params);

//todo: import z CSV do DB. z array do bazy.
//Array to DB
$dbImporter = new \Application\Classes\ImportExportCsv\DBImport($data);
$dbImporter->run();


function showList($params = [])
{
    $data = ['pageTitle' => $GLOBALS['LANG']['shop_tax_title']];
    echo Cms::$twig->render('admin/import_export/import-export.twig', array_merge($data, $params));
}


