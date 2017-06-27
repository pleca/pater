<?php

namespace Application\Classes\ImportExportCsv;
//require_once(MODEL_DIR . '/Status.php');
require_once(MODEL_DIR . '/shopProductsAdmin.php');
//require_once(MODEL_DIR . '/Category.php');
//require_once(MODEL_DIR . '/Product.php');
//require_once(MODEL_DIR . '/Variation.php');
//require_once(MODEL_DIR . '/../entity/Tax.php');
//require_once(CLASS_DIR . '/ImportExportCsv/CsvImporter.php');

use Product;
use ProductsAdmin;


class DBImport
{

    private $data;

    /**
     * DBImport constructor.
     */
    public function __construct($data)
    {
        $this->data = $data;


    }


    public function run()
    {
        $this->transact($this->data);
    }

    private function transact($data)
    {
        $pcsv = new \ProductsCsv();
        $names = $pcsv->getProductsNames();
$m=0;



        try {
            \Cms::$db->beginTransaction();
            foreach ($data as $key => $row) {
                if ($key > 2) {
                    switch ($row['11']){ //todo OK, tutaj $data gdy nie modyfikuję pliku ma postać tablicy [0], [1], ... . w każdej jest tablica 19 elementami będącymi kolumnami. A po edycji excel bedzie w jednej lini
                        case 'Parent':
                            $product_name = $row[0];
                            $category = $row[1];
                            $subcategory = $row[2];
                            $manufactured_name = $row[3];
                            $status = $row[4];
                            $feature1_name = $row[5];
                            $feature2_name = $row[6];
                            $feature3_name = $row[7];
                            $feature1_value = $row[8];
                            $feature2_value = $row[9];
                            $feature3_value = $row[10];

                            $post['name'] = $product_name;
                            $post['category_id'] = 666;
                            $post['producer_id'] = 666;
                            $post['status_id'] = 666;
                            $post['type'] = 666;

                            if(!in_array($product_name, $names)){
                                $this->addProduct($post);
                            };
                            break;


                        case 'Child':
                            $ean = $row[13];
                            $sku = $row[12];
                            $quantity = $row[14];
                            $price = $row[15];
                            $promotion = $row[16];
                            $bestseller = $row[17];
                            $recommended = $row[18];
                            $main_page = $row[19];
                            break;

                        default:
                            throw new \Exception('Parantage name is set to: ' . $row['11']. '. Should be "Parent" or "Child".');
                            break;
                    }


                }
            }
        }
        catch (\Exception $e) {
//            \Cms::$db->rollBack();
//            echo $e->getMessage();
        }
    }


    //todo: no będzie problem
    //todo ano taki, że tworząc produkt muszę podać np category_id, producer_id
    //todo: no i muszę wcześniej wiedzieć czy kategoria istnieje już
    //todo: no a jeśli istnieje to muszę podać ID tej kategorii
    //todo: a jeśli nie istenie to najpierw muszę ją uworzyć i podać tutaj nowe ID
    //todo: i tak kurwa z każdą rzeczą gdy tworzę tabelę używającą ID innych tabel.
    private function addProduct($post)
    {
        //todo OK 15:22 27-06-17
        // nie zapisuje do bazy. Zatrzymuje się na lini 183(185 po zmianie) shopProductsAdmin.php
        // bo spełnia ten warunek poniżej
        // if (empty($post[Cms::$defaultLocale]['name'])) {
        //todo zapytaj Krzyśka czego on oczekuje w 185 shopProductsAdmin
        // bo on szuka jakiejś wartości w
        // $post[Cms::$defaultLocale]['name'] czyli $post['en']['name']
        //zakomentowałem tam i...i dalej
        $entity = new ProductsAdmin();
        $nieposzło = $entity->addAdmin($post);
        //todo zapytaj Krzyska dlaczego on nie dodaje do bazy w tabeli products
        //nie widać tam Id=615 mimo ze w trakcie debugowania pokazuje ze dodał.
// A zapytanie z debugera INSERT INTO product SET `category_id`='666', `producer_id`='666', `status_id`='666', `type`='666', `date_add`=NOW(), `date_mod`=NOW()
// jest poprawne bo wpisane w phpmyadmin dodaje wiersznowy
//mowa o zapytaniu które jest wygenerowane w liniach 191-193 application/models/shopProductsAdmin.php
        $poszło=0;
    }



}