<?php

if (!defined('NO_ACCESS')) {
    die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/Variation.php');

//todo: przenieś tę klasę/funkcję w normalne miejsce
class ProductsCsv
{
    public $table;
    public $locale;

    public function __construct()
    {
        $this->table = DB_PREFIX . 'product';
        $this->locale  = Cms::$session->get('locale') ? Cms::$session->get('locale') : Cms::$defaultLocale;
    }


    public function getVariations()
    {
        $query = $this->getVariationsQuery();
        $array = Cms::$db->getAll($query);

        return $array;
    }

    public function getProducts()
    {
        $query = $this->getProductsQuery();
        $array = Cms::$db->getAll($query);

        return $array;
    }

    public function getProductsNames()
    {
        $query = $this->getProductsNamesQuery();
        $array = Cms::$db->getAll($query);
        $newArray=[];
        foreach ($array as $k => $v) {
            $newArray[$v['product_name']] = $v['product_name'];
        }

        return $newArray;
    }

    public function getStatusesNamesWithIds()
    {
        $query = $this->getStatusesNamesQuery();
        $array = Cms::$db->getAll($query);
        $newArray=[];
        foreach ($array as $k => $v) {
            $newArray[$v['product_name']] = $v['product_name'];
        }

        return $newArray;
    }

    public function getProducersNames()
    {
        $query = $this->getProductsNamesQuery();
        $array = Cms::$db->getAll($query);
        $newArray=[];
        foreach ($array as $k => $v) {
            $newArray[$v['product_name']] = $v['product_name'];
        }

        return $newArray;
    }

    public function getCategoriesNames()
    {
        $query = $this->getProductsNamesQuery();
        $array = Cms::$db->getAll($query);
        $newArray=[];
        foreach ($array as $k => $v) {
            $newArray[$v['product_name']] = $v['product_name'];
        }

        return $newArray;
    }

    public function getTaxesNames()
    {
        $query = $this->getProductsNamesQuery();
        $array = Cms::$db->getAll($query);
        $newArray=[];
        foreach ($array as $k => $v) {
            $newArray[$v['product_name']] = $v['product_name'];
        }

        return $newArray;
    }




    protected function getVariationsQuery()
    {
        $q = "SELECT p.id as `product_id`, v.id2 as `variation_id`, pt.name as `product_name`, ";
        $q .= " v.sku, v.ean, v.qty as `quantity`, v.price,";
        $q .= " (SELECT `name` FROM `categories_translation` WHERE `translatable_id`=c.parent_id LIMIT 1) as `category`,";
        $q .= " ct.name as `subcategory`,";
        $q .= " pm.name as `manufactured_name`, pst.name as `status`, v.promotion, v.bestseller, v.recommended, v.main_page, ";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature1_id AND locale='" . $this->locale . "' LIMIT 1) as `feature1_name`,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature2_id AND locale='" . $this->locale . "' LIMIT 1) as `feature2_name`,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature3_id AND locale='" . $this->locale . "' LIMIT 1) as `feature3_name`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='" . $this->locale . "' LIMIT 1) as `feature1_value`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='" . $this->locale . "' LIMIT 1) as `feature2_value`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='" . $this->locale . "' LIMIT 1) as `feature3_value`";
        $q .= " FROM `product` p";
        $q .= " LEFT JOIN `categories` c ON p.category_id=c.id";
        $q .= " LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id";
        $q .= " LEFT JOIN `categories_translation` ct ON ct.translatable_id=p.category_id";
        $q .= " LEFT JOIN `product_manufacturer` pm ON p.producer_id=pm.id";
        $q .= " LEFT JOIN `product_status_translation` pst ON p.status_id=pst.translatable_id";
        $q .= " LEFT JOIN `product_variation` v ON p.id=v.product_id";
        $q .= " WHERE pst.locale='" . $this->locale . "'order BY p.id";

        return $q;
    }

    protected function getProductsQuery()
    {
        $q = "SELECT p.id as `product_id`, pt.name as `product_name`, v.id2 as `variation_id`, ";
        $q .= " v.sku, v.ean, v.qty as `quantity`, v.price,";
        $q .= " (SELECT `name` FROM `categories_translation` WHERE `translatable_id`=c.parent_id LIMIT 1) as `category`,";
        $q .= " ct.name as `subcategory`,";
        $q .= " pm.name as `manufactured_name`, pst.name as `status`, v.promotion, v.bestseller, v.recommended, v.main_page, ";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature1_id AND locale='" . $this->locale . "' LIMIT 1) as `feature1_name`,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature2_id AND locale='" . $this->locale . "' LIMIT 1) as `feature2_name`,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature3_id AND locale='" . $this->locale . "' LIMIT 1) as `feature3_name`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='" . $this->locale . "' LIMIT 1) as `feature1_value`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='" . $this->locale . "' LIMIT 1) as `feature2_value`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='" . $this->locale . "' LIMIT 1) as `feature3_value`";
        $q .= " FROM `product` p";
        $q .= " LEFT JOIN `categories` c ON p.category_id=c.id";
        $q .= " LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id";
        $q .= " LEFT JOIN `categories_translation` ct ON ct.translatable_id=p.category_id";
        $q .= " LEFT JOIN `product_manufacturer` pm ON p.producer_id=pm.id";
        $q .= " LEFT JOIN `product_status_translation` pst ON p.status_id=pst.translatable_id";
        $q .= " LEFT JOIN `product_variation` v ON p.id=v.product_id";
        $q .= " WHERE pst.locale='" . $this->locale . "' GROUP BY p.id order BY p.id ";

        return $q;
    }

    protected function getProductsNamesQuery()
    {
        $q = "SELECT pt.name as `product_name`";
        $q .= " FROM `product` p";
        $q .= " LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id";
        $q .= " LEFT JOIN `product_status_translation` pst ON p.status_id=pst.translatable_id";
        $q .= " WHERE pst.locale='" . $this->locale . "'order BY pt.name ";

        return $q;
    }

    protected function getStatusesNamesQuery()
    {
        $q = "SELECT pt.name as `product_name`";
        $q .= " FROM `product` p";
        $q .= " LEFT JOIN `product_translation` pt ON p.id=pt.translatable_id";
        $q .= " LEFT JOIN `product_status_translation` pst ON p.status_id=pst.translatable_id";
        $q .= " WHERE pst.locale='" . $this->locale . "'order BY pt.name ";

        return $q;

    }
}