<?php

if (!defined('NO_ACCESS')) {
    die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/Variation.php');

//todo: przenieś tę klasę/funkcję w normalne miejsce
class ProductsCsv
{
    public function getProducts()
    {
        $query = $this->getQuery();
        $array = Cms::$db->getAll($query);

        return $array;
    }

    protected function getQuery()
    {
        $locale  = Cms::$session->get('locale') ? Cms::$session->get('locale') : Cms::$defaultLocale;

        $q = "SELECT p.id as `Produkt ID`,v.id2 as `Variation ID`, c.id, ct.name as `subcategory`,";
        $q .= " (SELECT `name` FROM `categories_translation` WHERE `translatable_id`=c.parent_id LIMIT 1) as `parent`,";
        $q .= " pm.name, pst.name, v.promotion, v.bestseller, v.recommended, v.main_page, v.sku, v.ean, v.price,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature1_id AND locale='" . $locale . "' LIMIT 1) as `feature1_name`,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature2_id AND locale='" . $locale . "' LIMIT 1) as `feature2_name`,";
        $q .= " (SELECT `name` FROM `features_translation` WHERE `translatable_id`=p.feature3_id AND locale='" . $locale . "' LIMIT 1) as `feature3_name`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature1_value_id AND locale='" . $locale . "' LIMIT 1) as `feature1_value`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature2_value_id AND locale='" . $locale . "' LIMIT 1) as `feature2_value`,";
        $q .= " (SELECT `name` FROM `feature_values_translation` WHERE `translatable_id`=v.feature3_value_id AND locale='" . $locale . "' LIMIT 1) as `feature3_value`,";
        $q .= " v.qty";
        $q .= " FROM `product` p";
        $q .= " LEFT JOIN `categories` c ON p.category_id=c.id";
        $q .= " LEFT JOIN `categories_translation` ct ON ct.translatable_id=p.category_id";
        $q .= " LEFT JOIN `product_manufacturer` pm ON p.producer_id=pm.id";
        $q .= " LEFT JOIN `product_status_translation` pst ON p.status_id=pst.translatable_id";
        $q .= " LEFT JOIN `product_variation` v ON p.id=v.product_id";
        $q .= " WHERE pst.locale='" . $locale . "'";

        return $q;
    }

}