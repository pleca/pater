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
                if ($key > 2) {
                    $product = $this->prepareData($row);
                    switch ($row['11']) {
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

                            $categoryId = $this->addCategory($product, $categories, $categoryEntity);                   //KATEGORIA. wstawiam nową kategorię (albo nie wstawiam) i zwracam id_category
                            $product['category_id'] = $this->addSubcategory($product, $categoryId, $categories, $categoryEntity); //PODKATEGORIA. wstawiam nową kategorię (albo nie wstawiam) i zwracam id_category
                            $product['producer_id'] = $this->addProducer($product, $producers, $producersEntity);                   //PRODUCENT. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            $product['status_id'] = $this->addStatus($product, $productStatuses, $productStatusEntity);                                          //STATUS. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            list($product['feature1_id'],
                                $product['feature2_id'],
                                $product['feature3_id']) = $this->addFeatureNames($product, $featureNames, $featureNameEntity);                                          //STATUS. wstawiam nowego producenta (albo nie wstawiam) i zwracam id_producer
                            $product[\Cms::$defaultLocale]['name'] = $product['product_name'];

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

        // jeśli pusty rekord "". todo: pytanie czy produkt może być bez przypisanej kategori.
        if (empty($product['category'])) {
            return $categoryId = null;
        }

        $categoryNames = [];
        //tworzę tablicę kategorii (bez podkategorii) z nazwami ['Supplements']=>'Supplements'
        foreach ($categories[\Cms::$defaultLocale] as $category) {
            if ($category['parent_id'] == 0) {                              // "0" czyli jeśli kategoria nie ma rodzica
                $categoryNames[$category['name']] = $category['name'];
            }
        }

        //jeśli nie ma w bazie kategori o nazwie z badanego wiersza excel to dodaj. Ustaw niezbędne do dodania kategori wartości.
        if (!in_array($product['category'], $categoryNames)) {
            $item['parent_id'] = 0;
            $item['status_id'] = 1;
            $item['name'] = $product['category'];
            $categoryId = $categoryEntity->add($item);
        } else {
            //a jeśli kategoria istnieje w bazie to zwróć jej ID (bo będzie potrzebne do dodania produktu w tabeli 'product')
            $category = $this->findCategoryIdByName($product['category'], null, 'en');//todo na sztywno 'en', bo bez sesji nie widzi \Cms::$session->get('locale')
            $categoryId = $category['id'];
        }

        return $categoryId;
    }

    private function addSubcategory($product, $parentId, $categories, \Category $categoryEntity)
    {
        $subcategoryId = false;

        if (empty($product['subcategory'])) {
            return $subcategoryId = null;
        }

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
            $category = $this->findCategoryIdByName($product['subcategory'], null, 'en');//todo na sztywno 'en', bo bez sesji nie widzi \Cms::$session->get('locale')
            $subcategoryId = $category['id'];
        }

        return $subcategoryId;
    }

    private function addProducer($product, $producers, \ProducersAdmin $producersEntity)
    {
        $producerId = false;

        if (empty($product['manufactured_name'])) {
            return $producerId = null;
        }

        $producerNames = [];
        foreach ($producers as $producer) {
            $producerNames[$producer['name']] = $producer['name'];
        }

        if (!in_array($product['manufactured_name'], $producerNames)) {
            $item['status_id'] = 1;
            $item['name'] = $product['manufactured_name'];
            $producerId = $producersEntity->addAdmin($item);
        } else {
            $producer = $this->findProducerIdByName($product['manufactured_name']);//todo no ale to nie zwraca id raczej tylko tablicę (się zobaczy w debugowaniu)
            $producerId = $producer['Id'];
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
            $statusId = $this->findStatusIdByName($product['status']);//todo no ale to nie zwraca id raczej tylko tablicę (się zobaczy w debugowaniu)
        }

        return $statusId;
    }

    private function addFeatureNames($product, $features, $featureNameEntity)
    {
        $featureNamesIds = false;

        $featuresRow[] = $product['feature1_name'];
        $featuresRow[] = $product['feature2_name'];
        $featuresRow[] = $product['feature3_name'];

        foreach ($featuresRow as $feature) {

        }

        $featureNames = [];
        foreach ($features[\Cms::$defaultLocale] as $feature) {
            $featureNames[$feature['name']] = $feature['name'];
        }

//        if (!in_array($product['feature1_name'], $featureNames)) {
//            $featureNameEntity->insert()
//        }


        return $featureNamesIds;
    }

    private function addProduct($post, ProductsAdmin $entity)
    {
        $nieposzło = $entity->addAdmin($post);
        $entity->expandedAdmin($post);
        $poszło = 0;
    }

    private function findCategoryIdByName($name = null, $mainCategory = null, $locale = null)
    {
        //TODO: UWAGA TU WSTAWIAM NAZWĘ TABELI NA SZTYWNO W ZAPYTANIA, A KRZYSIEK TERAZ ZMIENIA NAZWY TABEL NA LICZBĘ POJEDYNCZĄ
        $tableName = 'categories';

        if (!$name) {
            return false;
        }

        if (!$locale) {
            $locale = \Cms::$session->get('locale');
        }

        $q = "SELECT c.*,t.translatable_id, t.name, t.slug, t.seo_title, t.meta_description, t.accordion_header1, t.accordion_content1, t.accordion_header2, t.accordion_content2, t.accordion_header3, t.accordion_content3, t.locale, (SELECT `slug` FROM `" . $tableName . "` WHERE `id`=c.parent_id LIMIT 1) as `parent_url` "
            . "FROM `" . $tableName . "` c "
            . "LEFT JOIN `" . $tableName . "_translation` t ON c.id = t.translatable_id "
            . "WHERE t.name = '" . $name . "' AND t.locale = '" . $locale . "' ";

        if ($mainCategory) {
            $q .= "AND c.parent_id = '" . (int)$mainCategory['id'] . "' ";
        }

        $result = \Cms::$db->getRow($q);

        return $result;
    }


    private function findProducerIdByName($name)
    {
        $tableName = 'product_manufacturer';
        $q = "SELECT Id FROM `" . $tableName . "` WHERE `name`='" . $name . "' ";
        $result = \Cms::$db->getRow($q);

        return $result;
    }

    private function findStatusIdByName($name)
    {
        //TODO JOJ TU PRZERWAŁEM KONSTRUUJĄC ZAPYTANIE ZWRACAJĄCE


        $tableName = 'product_status_translation';
        $productTable = 'product';

        $locale = (\Cms::$session->get('locale')) ?? 'en'; //todo: UWAGA na sztywno en jeśli brak sesji (potrzebne w trakcie testowania)


        $q = "SELECT pst.translatable_id, pst.name, pst.locale "
            . "FROM `" . $tableName . "` pst "
            . "LEFT JOIN `" . $productTable . "` p ON p.status_Id = pst.translatable_Id ";

        $q .= " WHERE pst.locale='" . $locale . "' GROUP BY p.id order BY p.id ";




        $result = \Cms::$db->getRow($q);

        return $result;
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
        $product['tag1'] = isset($row['8']) ? $row['8'] : '';
        $product['tag2'] = isset($row['9']) ? $row['9'] : '';
        $product['tag3'] = isset($row['10']) ? $row['10'] : '';
        $product['sku'] = $row[12];
        $product['ean'] = $row[13];
        $product['quantity'] = $row[14];
        $product['price'] = $row[15];
        $product['promotion'] = $row[16];
        $product['bestseller'] = $row[17];
        $product['recommended'] = $row[18];
        $product['main_page'] = $row[19];
        $product['feature1_value'] = $row[20];
        $product['feature2_value'] = $row[21];
        $product['feature3_value'] = isset($row[22]) ? $row[22] : null; //todo tu coś nie halo, zgłasza undefinied offset, nie ma [19] kolumny
        $product['id'] = $row[23];
        //todo: zaślepka chwilowa. Coś tu trzeba wymyślić. (1/2 -wariacje/bez). Trzeba w kolejnych krokach wpisac tu wartość na podstawie wyglądu excela.
        //todo: trzeba dac domyślnie '2' na etapie dodawania produktu, i zmienić na '1' na etapie dodawania wariacji i zaaktualizować tabelę.
        $product['type'] = 1;

        return $product;
    }
}