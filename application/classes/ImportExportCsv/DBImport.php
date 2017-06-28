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
        $productNames = $pcsv->getProductsNames();
        $statusesNames = $pcsv->getStatusesNamesWithIds();
$m=0;

        try {
            \Cms::$db->beginTransaction();
            foreach ($data as $key => $row) {
//                if ($key == 3) {
                if ($key > 2) {
                    $post = $this->prepareData($row);
                    switch ($row['11']){
                        case 'Parent':
                            //todo: STATUS
                            //1. muszę zamienić status name na status_id
                            //2. sprawdzić czy status_id danego produktu się zmienił
                            //2. albo nie sprawdzać i wstawiać do tabeli produkt to co user podał, byle było poprawne
                            //3. Czyli odbieram status_name zamieniam go na status_id i wstawiam do produktu
                            //4. Muszę też gdzieś w tej klasie mieć dostęp do nowego status_id bo będzie wykorzystywany jeszcze
                            // a czy na pewno będzie gdzieś jeszcze potrzebne??????
                            //i nic nie robię z tabelą Status!
                            if(!in_array($post['status'], $statusesNames)){
                                throw new \Exception('Wrong CSV data. Status name must be among the following: '); //todo: dodać w jakiejść pętli dostępne nazwy statusów z uwzględnieniem locale
                            };
                            //nie ja tylko potrzebuję wstawić nowy status w tabelę Products

                            // Jakich danych potrzebuję do aktualizacji tabeli produkt (i których nie mam bezpośrednio)??????
                            // - category_id
                            // - producer_id
                            // - status_id
                            // - type
                            // - feature1_id
                            // - feature2_id
                            // - feature3_id
                            // - tag1 //todo: to skąd?
                            // - tag2
                            // - tag3

                            //Czyli potrzebuję przed wstawieniem do tabeli Product mieć:
                            // - category_id - załatwić Kategorię (tabela: categories_translation)
                            //   - odczytać z excel Category i Subcategory
                            //   - zamienić nazwy na ID
                            //   - jeśli user wprowadził nową kategorię to zaktualizować tabelę Kategoria
                            //   - i upowszechnić nowy ID bo będę go potrzebował w Product
                            //   - a jeśli nie wprowadził to upowszechiam ID kategori bo będę go potrzebował w Product
                            //   - PODSUMOWUJĄC najpierw sprawdzam czy ID kategorii jest w tabeli Kategori, jeśli jest to zapisuję ID, jeśli nie ma to tworzę nową kategorię i zapisuję nowe ID
                            // - producer_id - załatwić Producenta (tabela product_manufacturer)
                            // -
                            // -
                            // -
                            // -
                            // -







//                            //todo: do testowania
//                            $post[\Cms::$defaultLocale]['name'] = $product_name;
//                            $post['category_id'] = 222;
//                            $post['producer_id'] = 222;
//                            $post['status_id'] = 222;
//                            $post['type'] = 222;
                            if(!in_array($post['product_name'], $productNames)){
                                $this->addProduct($post);
                            };
                            break;
                        case 'Child':

                            break;

                        default:
                            throw new \Exception('Wrong CSV data. Parantage name is set to: ' . $row['11']. '. Should be "Parent" or "Child".');
                            break;
                    }
                }
            }
            \Cms::$db->commit();
        }
        catch (\Exception $e) {
            \Cms::$db->rollBack();
            echo $e->getMessage();
        }
    }

    private function prepareData($row)
    {
        $post['product_name'] = $row[0];
        $post['category'] = $row[1];
        $post['subcategory'] = $row[2];
        $post['manufactured_name'] = $row[3];
        $post['status'] = $row[4];
        $post['feature1_name'] = $row[5];
        $post['feature2_name'] = $row[6];
        $post['feature3_name'] = $row[7];
        $post['feature1_value'] = $row[8];
        $post['feature2_value'] = $row[9];
        $post['feature3_value'] = $row[10];
        $post['ean'] = $row[13];
        $post['sku'] = $row[12];
        $post['quantity'] = $row[14];
        $post['price'] = $row[15];
        $post['promotion'] = $row[16];
        $post['bestseller'] = $row[17];
        $post['recommended'] = $row[18];
        $post['main_page'] = $row[19];

        return $post;
    }


    //todo: no będzie problem
    //todo ano taki, że tworząc produkt muszę podać np category_id, producer_id
    //todo: no i muszę wcześniej wiedzieć czy kategoria istnieje już
    //todo: no a jeśli istnieje to muszę podać ID tej kategorii
    //todo: a jeśli nie istenie to najpierw muszę ją uworzyć i podać tutaj nowe ID
    //todo: i tak kurwa z każdą rzeczą gdy tworzę tabelę używającą ID innych tabel.
    private function addProduct($post)
    {
        $entity = new ProductsAdmin();
        $nieposzło = $entity->addAdmin($post);
        $poszło=0;
    }



}