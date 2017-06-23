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

$pc = new ProductsCsv();
$products = $pc->getProducts();

//todo: eksport z DB do CSV działa. Popraw nazwy kolumn "as"
//DB to array to CSV
$exporter = new \Application\Classes\ImportExportCsv\CsvExporter($products, EXP_DIR . '/products.csv', false);
$csv = $exporter->get();
$params['csv'] = $csv;
//showList($params);

//todo: import z CSV do DB. Eksportuje do array, nie zapisuje w bazie
//CSV to Array
$importer = new \Application\Classes\ImportExportCsv\CsvImporter(EXP_DIR . '/products.csv');                    //nie widzi bez całej ścieżki, mimo że jest require.
$data = $importer->get();
$mam=0;
showList($params);

function showList($params = [])
{
    $data = ['pageTitle' => $GLOBALS['LANG']['shop_tax_title']];
    echo Cms::$twig->render('admin/import_export/import-export.twig', array_merge($data, $params));
}


