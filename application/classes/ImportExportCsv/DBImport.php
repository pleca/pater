<?php

namespace Application\Classes\ImportExportCsv;
//require_once(MODEL_DIR . '/Status.php');
require_once(MODEL_DIR . '/shopProductsAdmin.php');
require_once(MODEL_DIR . '/shopProducersAdmin.php');
//require_once(MODEL_DIR . '/shopCategoriesAdmin.php');
require_once(MODEL_DIR . '/Category.php');
require_once(MODEL_DIR . '/ProductStatus.php');
require_once(MODEL_DIR . '/Feature.php');
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
        $categoryEntity = new \Category();
        $categories = $categoryEntity->getAll();
        $producersEntity = new \ProducersAdmin();
        $producers = $producersEntity->loadProducersSelect();
        $productStatusEntity = new \ProductStatus();
        $productStatuses = $productStatusEntity->getAll();
        $featureNameEntity = new \Feature();
        $featureNames = $featureNameEntity->getAll();
        $productEntity = new ProductsAdmin();

        $m = 0;

        try {
            \Cms::$db->beginTransaction();
            foreach ($data as $key => $row) {
                if ($key == 3) {
//                if ($key > 2) {
                    $product = $this->prepareData($row);
                    switch ($row['8']) {
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

//                            $categoryId = $this->addCategory($product, $categories, $categoryEntity);                   //KATEGORIA. wstawiam nową kategorię (albo nie wstawiam) i zwracam id_category
                            $categoryId = 5;
//                            $subcategoryId = $this->addSubcategory($product, $categoryId, $categories, $categoryEntity); //PODKATEGORIA. wstawiam nową kategorię (albo nie wstawiam) i zwracam id_category
                            $subcategoryId = 5;
//                            $producerId = $this->addProducer($product, $producers, $producersEntity);                   //PRODUCENT. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            $producerId = 5;
//                            $statusId = $this->addStatus($product, $productStatuses, $productStatusEntity);                                          //STATUS. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            $statusId = 5;
//                            $featureNameIds = $this->addFeatureNames($product, $featureNames);                                          //STATUS. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            $featureNameIds = 5;

                            $product[\Cms::$defaultLocale]['name'] = $product['product_name'];
                            $product['category_id'] = $subcategoryId;
                            $product['producer_id'] = $producerId;
                            $product['status_id'] = $statusId;
//                            $product['feature1_id'] = $featureNameIds[0];
//                            $product['feature2_id'] = $featureNameIds[1];
//                            $product['feature3_id'] = $featureNameIds[2];
                            $product['type'] = 222; //todo: zaślepka chwilowa

                            $this->addProduct($product, $productEntity);

                            break;
                        case 'Child':

                            break;

                        default:
                            throw new \Exception('Wrong CSV data. Parantage name is set to: ' . $row['11'] . '. Should be "Parent" or "Child".');
                            break;
                    }
                }
            }
            \Cms::$db->commit();
        } catch (\Exception $e) {
            \Cms::$db->rollBack();
            echo $e->getMessage();
        }
    }

    private function addCategory($product, $categories, \Category $categoryEntity)
    {
        $categoryId = false;

        $categoryNames = [];
        foreach ($categories[\Cms::$defaultLocale] as $category) {
            if ($category['parent_id'] == 0) {
                $categoryNames[$category['name']] = $category['name'];
            }
        }

        if (!in_array($product['category'], $categoryNames)) {
            $item['parent_id'] = 0;
            $item['status_id'] = 1;
            $item['name'] = $product['category'];
            $categoryId = $categoryEntity->add($item);
        } else {
            $categoryId = $categoryEntity->getBy(['name'], $product['category']);//todo no ale to nie zwraca id raczej tylko tablicę (się zobaczy w debugowaniu)
        }

        return $categoryId;
    }

    private function addSubcategory($product, $parentId, $categories, \Category $categoryEntity)
    {
        $subcategoryId = false;

        $subcategoryNames = [];
        foreach ($categories[\Cms::$defaultLocale] as $category) {
            if ($category['parent_id'] !== 0) {
                $subcategoryNames[$category['name']] = $category['name'];
            }
        }

        if (!in_array($product['subcategory'], $subcategoryNames)) {
            $item['parent_id'] = $parentId;
            $item['status_id'] = 1;
            $item['name'] = $product['subcategory'];
            $subcategoryId = $categoryEntity->add($item);
        } else {
            $subcategoryId = $categoryEntity->getBy(['name'], $product['category']);//todo no ale to nie zwraca id raczej tylko tablicę (się zobaczy w debugowaniu)
        }

        return $subcategoryId;
    }

    private function addProducer($product, $producers, \ProducersAdmin $producersEntity)
    {
        $producerId = false;

        $producerNames = [];
        foreach ($producers as $producer) {
            $producerNames[$producer['name']] = $producer['name'];
        }

        if (!in_array($product['manufactured_name'], $producerNames)) {
            $item['status_id'] = 1;
            $item['name'] = $product['manufactured_name'];
            $producerId = $producersEntity->addAdmin($item);
        } else {
            $producerId = $producersEntity->getBy(['name'], $product['manufactured_name']);//todo no ale to nie zwraca id raczej tylko tablicę (się zobaczy w debugowaniu)
        }

        return $producerId;
    }

    private function addStatus($product, $statuses, \ProductStatus $statusEntity)
    {
        $statusId = false;

        $statusesNames = [];
        foreach ($statuses[\Cms::$defaultLocale] as $status) {
            $statusesNames[$status['name']] = $status['name'];
        }

        if (!in_array($product['status'], $statusesNames)) {
            throw new \Exception('Wrong CSV data. Status name must be among the following: '); //todo: dodać w jakiejść pętli dostępne nazwy statusów z uwzględnieniem locale
        } else {
            $statusId = $statusEntity->getBy(['name'], $product['status']);//todo no ale to nie zwraca id raczej tylko tablicę (się zobaczy w debugowaniu)
        }

        return $statusId;
    }

    private function addFeatureNames($product, $features, $featureNameEntity)
    {
        $featureNamesIds = false;

        //todo : nie zdąrzyłem
//        $featureNames = [];
//        foreach ($features[\Cms::$defaultLocale] as $feature) {
//            $featureNames[$feature['name']] = $feature['name'];
//        }
//
//        if (!in_array($product['feature1_name'], $featureNames)) {
//            $featureNameEntity->insert()
//        }


        return $featureNamesIds;
    }

    private function addProduct($post, ProductsAdmin $entity)
    {
        $nieposzło = $entity->addAdmin($post);
        $poszło = 0;
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
        $product['sku'] = $row[9];
        $product['ean'] = $row[10];
        $product['quantity'] = $row[11];
        $product['price'] = $row[12];
        $product['promotion'] = $row[13];
        $product['bestseller'] = $row[14];
        $product['recommended'] = $row[15];
        $product['main_page'] = $row[16];
        $product['feature1_value'] = $row[17];
        $product['feature2_value'] = $row[18];
        //todo tu coś nie halo, zgłasza undefinied offset, nie wma [19] kolumny
        $product['feature3_value'] = isset($row[19]) ? $row[19] : null;

        return $product;
    }
}