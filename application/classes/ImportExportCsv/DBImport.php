<?php

namespace Application\Classes\ImportExportCsv;
//require_once(MODEL_DIR . '/Status.php');
require_once(MODEL_DIR . '/shopProductsAdmin.php');
require_once(MODEL_DIR . '/shopCategoriesAdmin.php');
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
                    $product = $this->prepareData($row);
                    switch ($row['11']){
                        case 'Parent':
                            // Jakich danych potrzebuję do aktualizacji tabeli produkt (i których nie mam bezpośrednio)??????
                            // - category_id
                            // - producer_id
                            // - status_id
                            // - type
                            // - feature1_id
                            // - feature2_id
                            // - feature3_id
                            // - tag1 //todo: to skąd? z excela, to też powinno tam być ( jest w danych rozszerzonych /shop-products)
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
                            //   - to co z kategorią
                            // - status_id - załatwić Status (tabela product_status_translation)
                            //   - tylko odnajduję status_id po nazwie statusu
                            // - (type)
                            //   - czy z wariacjami (1) czy bez (2). To polę wstawię gdy zajmę się wariacjami)
                            // - (tag)
                            //   - narazie pomijam

                            //todo: KATEGORIA. wstawiam nową kategorię (albo nie wstawiam) i zwracam id_category
                            $categoryId = $this->addCategory();

                            //todo: PRODUCENT. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            $producerId = $this->addProducer();



                            //todo: STATUS
                            //1. muszę zamienić status name na status_id
                            //2. sprawdzić czy status_id danego produktu się zmienił
                            //2. albo nie sprawdzać i wstawiać do tabeli produkt to co user podał, byle było poprawne
                            //3. Czyli odbieram status_name zamieniam go na status_id i wstawiam do produktu
                            //4. Muszę też gdzieś w tej klasie mieć dostęp do nowego status_id bo będzie wykorzystywany jeszcze
                            // a czy na pewno będzie gdzieś jeszcze potrzebne??????
                            //i nic nie robię z tabelą Status!
                            if(!in_array($product['status'], $statusesNames)){
                                throw new \Exception('Wrong CSV data. Status name must be among the following: '); //todo: dodać w jakiejść pętli dostępne nazwy statusów z uwzględnieniem locale
                            };





//                            //todo: do testowania
//                            $product[\Cms::$defaultLocale]['name'] = $product_name;
//                            $product['category_id'] = 222;
//                            $product['producer_id'] = 222;
//                            $product['status_id'] = 222;
//                            $product['type'] = 222;
                            if(!in_array($product['product_name'], $productNames)){
                                $this->addProduct($product);
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
        $product['product_name'] = $row[0];
        $product['category'] = $row[1];
        $product['subcategory'] = $row[2];
        $product['manufactured_name'] = $row[3];
        $product['status'] = $row[4];
        $product['feature1_name'] = $row[5];
        $product['feature2_name'] = $row[6];
        $product['feature3_name'] = $row[7];
        $product['feature1_value'] = $row[8];
        $product['feature2_value'] = $row[9];
        $product['feature3_value'] = $row[10];
        $product['ean'] = $row[13];
        $product['sku'] = $row[12];
        $product['quantity'] = $row[14];
        $product['price'] = $row[15];
        $product['promotion'] = $row[16];
        $product['bestseller'] = $row[17];
        $product['recommended'] = $row[18];
        $product['main_page'] = $row[19];

        return $product;
    }

    private function addCategory($post)
    {
        //todo:1) zwróć uwagę że dodając nową kategorię w shop-categories.php?action=addForm&parent_id=1
        //todo:... też wybierasz status
        //todo:... Zwróć też uwagę że tworząc nową podkategorię w models/Category.php::add() on tworząc nową kategorię
        //todo:...chce różne parametry i daje domyślne jeśli puste.

        //todo:2) user może dać kategorię bez podkategori, może dać podkategorię bez kategorii(to THROW)

        //todo: WNIOSKI:
        // - wykorzystujesz models/Category.php::add()
        // - tę metodę wykorzystujesz i do kategori i do podkategori
        // - kategorię tworzysz podając parametr parent_id==0 !!!
        // - podkategorię tworzysz podając id parenta
        // - czyli najpierw musisz stworzyć parent id (lub go odebrać) i tworząc podkategorię to podać.
        $entity = new CategoriesAdmin();

        return $categoryId;
    }

    private function addProducer($post)
    {

        return $data;
    }

    private function addFeature($post)
    {

        return $data;
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