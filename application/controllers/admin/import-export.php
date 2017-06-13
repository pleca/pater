<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
    die('No access to files!');
if (Cms::$modules['shop'] != 1)
    die('This module is disabled!');
if ($_SESSION[USER_CODE]['privilege']['shop'] != 1)
    die('No permission at this level!');

require_once(MODEL_DIR . '/Product.php');

$prod = new Product();
$params = ['locale' => Cms::$defaultLocale];

$m=EXP_DIR . '/products.csv';

$products = $prod->getAll($params, ['name', 'tag1', 'tag2', 'tag3']);
//$products1 = array (
//    array('aaa', 'bbb', 'ccc', 'dddd'),
//    array('123', '456', '789'),
//    array('"aaa"', '"bbb"')
//);

$csv = prepareCsv($products);
$params['csv'] = $csv;
showList($params);

function showList($params = [])
{
    $data = ['pageTitle' => $GLOBALS['LANG']['shop_tax_title']];

    echo Cms::$twig->render('admin/import_export/import-export.twig', array_merge($data, $params));
}

function prepareCsv(array $products)
{
    $csvFile = fopen(EXP_DIR . '/products.csv', 'w');
    if (!($csvFile)){
        throw new \Exception('Can\'t open file '. EXP_DIR . '/products.csv');
    }
    chmod(EXP_DIR . '/products.csv', 0777);

    $pr = [];
    $pr[0]['1'] = 'jedyn';
    $pr[0]['2'] = 'dwa';
    $pr[0]['3'] = 'czy';
    $pr[0]['4'] = 'tery';
    $pr[0]['5'] = 'ęć';
    $pr[0]['6'] = 'szejść';
    $i=1;





    foreach ($products as $product) {
        foreach ($product as $key => $val) {
            if (strcmp('variations', $key) !== 0) {
                $pr[$i][$key] = $val;
            }
        }
        $i++;
    }

    $products = $pr;

    foreach ($products as $fields){
        fputcsv($csvFile, $fields, ";");//NOTICE: [8] Array to string conversion (jeśli repozytorium zwraca wariacje z Product, bo robi się 3 wymiarowa tablica)
    }



    if (!fclose($csvFile)){
        throw new \Exception('CSV file not written properly.');
    }



    return $csvFile;
}
