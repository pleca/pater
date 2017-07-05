<?php

namespace Application\Classes\ImportExportCsv;
//require_once(MODEL_DIR . '/Status.php');
require_once(MODEL_DIR . '/shopProductsAdmin.php');
require_once(MODEL_DIR . '/shopProducersAdmin.php');
//require_once(MODEL_DIR . '/shopCategoriesAdmin.php');
require_once(MODEL_DIR . '/Category.php');
require_once(MODEL_DIR . '/ProductStatus.php');
require_once(MODEL_DIR . '/Feature.php');
require_once(CLASS_DIR . '/ImportExportCsv/Exception/WrongStatusException.php');
//require_once(MODEL_DIR . '/Category.php');
//require_once(MODEL_DIR . '/Product.php');
//require_once(MODEL_DIR . '/Variation.php');
//require_once(MODEL_DIR . '/../entity/Tax.php');
//require_once(CLASS_DIR . '/ImportExportCsv/CsvImporter.php');

use Product;
use ProductsAdmin;
use Application\Classes\ImportExportCsv\Exception\WrongStatusException;

class DBImport
{
    private $data;

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
//                if ($key > 2) {
                if ($key == 3) {
                    $product = $this->prepareData($row);
                    switch ($row['11']) {
                        case 'Parent':
                            //KATEGORIA:
                            //todo 1: NIE ZMIENIA KETEGORI JEŚLI DODAM ZUPEŁNIE NOWĄ
                            //todo 2: NIE ZMIENIA KETEGORI JEŚLI DODAM INNĄ
                            // nie działa bo nie może działać, nie mogę dla tej samej podkategori zmienić kategorię rodzica bo w wielu produktach
                            // użyana jest ta sama podkategoria i każda podkategoria wskazuje na tę samą kategorię rodzic.
                            // czyli muszę ... dodać nowy rekord dla podkategori (która już istnieje) z nowym parent_id.
                            //PODKATEGORIA
                            // (DZIAŁA GDY ZMIENIĘ PODKATEGORIĘ NA JUŻ ISTENIEJĄCĄ (INNĄ NIŻ BYŁA))
                            // (DZIAŁA GDY ZMIENIĘ PODKATEGORIĘ NA ZUPEŁNIE NOWĄ
                            // -ALE ON NIE MOŻE ZMIENIĆ SOBIE KATEGORI DLA PODKATEGORII DLA JEDNEGO PRODUKTU. TU JEST PROBLEM
                            //     TRZEBA ZABRONIĆ ZMIANY KATEGORII BO W DWÓCH PRODUKTACH Z JEDNAKOWYMI PODKATEGORIAMI MOZE SOBIE ZMIENIĆ NA RÓZNE KATEGORIE
                            //     ALBO WYWALIĆ BŁĄD.
                            //PRODUCENT:
                            // (DZIAŁA GDY ZMIENIĘ NA ISTENIEJĄCEGO PRODUCENTA.)
                            // (DZIAŁA JEŚLI ZMIENIĘ NA ZUPEŁNIE NOWY)
                            //STATUS:
                            // (DZIAŁA GDY ZMIENIĘ NA ISTENIEJĄCY STATUS)
                            // (DZIAŁA JEŚLI ZMIENIĘ NA ZUPEŁNIE NOWY, NIE DODAJE BO NIEMA DODOWAĆ)
                            //todo 3: JEŚLI ZMIENIĘ NA ZUPEŁNIE NOWY POWINIEN ZŁAPAĆ SPECYFICZNY WYJĄTEK, ALE NIE ŁAPIE BO TUTAJ JEST CATCH DLA OGÓLNEGO EXCEPTION.
                            //TAG:
                            // (DZIAŁA)
                            //FEATURE:
                            // (DZIAŁA GDY ZMIENIĘ NA ISTENIEJĄCY FEATURE w pierwszej kolumnie)
                            // (DZIAŁA JEŚLI ZMIENIĘ NA ZUPEŁNIE NOWY)

                            $categoryId = $this->addCategory($product, $categories, $categoryEntity);
                            $product['category_id'] = $this->addSubcategory($product, $categoryId, $categories, $categoryEntity);
                            $product['producer_id'] = $this->addProducer($product, $producers, $producersEntity);
                            $product['status_id'] = $this->addStatus($product, $productStatuses, $productStatusEntity);
                            $product['feature1_id'] = $this->addFeature($product['feature1_name'], $featureNames, $featureNameEntity);
                            $product['feature2_id'] = $this->addFeature($product['feature2_name'], $featureNames, $featureNameEntity);
                            $product['feature3_id'] = $this->addFeature($product['feature3_name'], $featureNames, $featureNameEntity);

                            if(is_null($product['id'])){
                                $this->addProduct($product, $productEntity);
                            }else{
                                $this->updateProduct($product, $productEntity);
                            }

                            break;
                        case 'Child':
                            // skąd mam wiedzieć jeśli wariacja została niezmieniona a zmieniono produkt? załóżmy że produkt ma teraz inne id, jak go przypisac do wariacji
                            //  - no ale to jest fikcyjne. Co to znaczy zmienić id produktu, jeśli modyfikuję produkt to id się nie zmienia, a jeśli dodaje nowy wiersz z
                            //    nowym produktem ...to ma nowe wariacje. Czy można mieć stare wariacje i nowy produkt? Co to jest nowy produkt?
                            //  - dobra chuj, traktuję że produkt id się nie zmienił,a ja zmieniam tylko pola wariacji...lub dodaję nową wariację.



                            $this->updateVariation();


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

    private function updateVariation()
    {

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
            $item[\Cms::$defaultLocale]['name'] = $product['category'];
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
            $item[\Cms::$defaultLocale]['name'] = $product['subcategory'];
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
            throw new WrongStatusException('Wrong CSV data. Status name must be among the following: '); //todo: dodać w jakiejść pętli dostępne nazwy statusów z uwzględnieniem locale
        } else {
            $status = $this->findStatusIdByName($product['status']);
            $statusId = $status['translatable_id'];
        }

        return $statusId;
    }

    private function addFeature($feat, $features, \Feature $featureNameEntity)
    {
        //tworzę tablicę nazw features ['Supplements']=>'Supplements'
        $featureNames = [];
        foreach ($features[\Cms::$defaultLocale] as $feature) {
            $featureNames[$feature['name']] = $feature['name'];
        }

        if(empty($feat)){
            return 0;
        }

        //TODO JEŚLI DODAŁEM NOWĄ NAZWĘ FEATURE chwilę wcześniej, np dla feature1, to jeśli feature2 ma tę samą nazwę (no ale nie może mieć przecież i tak!) to znowu będzie chciał ją wprowadzić bo nie widzi jej w bazie jeszcze.
        if (!in_array($feat, $featureNames)) {
            $item[\Cms::$defaultLocale]['name'] = $feat;
            $featureId = $featureNameEntity->add($item);
        } else {
            $featureId = $this->findFeatureIdByName($feat);
        }

        return $featureId;
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
        $tableName = 'product_status_translation';
        $productTable = 'product';
        $locale = empty(\Cms::$session->get('locale')) ? 'en' : (\Cms::$session->get('locale')); //todo: UWAGA na sztywno en jeśli brak sesji (potrzebne w trakcie testowania)

        $q = "SELECT pst.translatable_id "
            . "FROM `" . $tableName . "` pst "
            . "LEFT JOIN `" . $productTable . "` p ON p.status_Id = pst.translatable_Id ";
        $q .= " WHERE pst.locale='" . $locale . "' AND pst.name='" . $name . "' LIMIT 1";

        $result = \Cms::$db->getRow($q);

        return $result;
    }

    private function findFeatureIdByName($name)
    {
        $tableName = 'features_translation';

        $q = "SELECT ft.translatable_id "
            . "FROM `" . $tableName . "` ft "
            . "WHERE ft.name = '".$name."'";
        $result = \Cms::$db->getRow($q);

        return $result['translatable_id'];
    }

    private function addProduct($post, ProductsAdmin $entity)
    {
        $nieposzło = $entity->addAdmin($post);
        $entity->expandedAdmin($post);
        $poszło = 0;
    }

    private function updateProduct($post, ProductsAdmin $entity)
    {
        $nieposzło = $entity->editAdmin($post);
        $noico = $entity->expandedAdmin($post); //zwraca false jeśli nie było nic do zapdejtowania
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
        $product[\Cms::$defaultLocale]['name'] = $product['product_name'];
        $product[\Cms::$defaultLocale]['content'] = ''; //todo: dodac kolumnę, Omijam to bo chcę zrobić skutecznie update product a wymaga tych zmiennych.
        $product[\Cms::$defaultLocale]['seo_title'] = ''; //todo: dodac kolumnę, Omijam to bo chcę zrobić skutecznie update product a wymaga tych zmiennych.
        $product[\Cms::$defaultLocale]['content_short'] = ''; //todo: dodac kolumnę, Omijam to bo chcę zrobić skutecznie update product a wymaga tych zmiennych.

        return $product;
    }
}