<?php

namespace Application\Classes\ImportExportCsv;


class CsvExporterHelper
{

    private $products;
    private $variations;

    public function __construct($products, $variations)
    {
        $this->products = $products;
        $this->variations = $variations;
    }

    //ta metoda ma zwracać już przygotowaną tablicę danych taka jak jest w potrzebna w CSV
    public function get()
    {
        $data = [];

        foreach ($this->products as $product){
            $row = $this->prepareProductRow($product);
            $data[] = $row;
            foreach ($this->variations as $variation){
                if($product['product_id']==$variation['product_id']) {
                    $row = $this->prepareVariationRow($variation);
                    $data[] = $row;
                }elseif ($product['product_id'] < $variation['product_id']){
                    continue; //nie wiem czy continue zadziała tak jak chcę
                }
            }
        }

        return $data;
    }

    protected function prepareProductRow($product)
    {
        $row[0] = $product['product_name'];
        $row[1] = $product['category'];
        $row[2] = $product['subcategory'];
        $row[3] = $product['manufactured_name'];
        $row[4] = $product['status'];
        $row[5] = $product['feature1_name'];
        $row[6] = $product['feature2_name'];
        $row[7] = $product['feature3_name'];
        $row[8] = 'Parent';

        for( $i = 9; $i < 19; $i++) {
            $row[$i] = '';
        }

        return $row;
    }

    protected function prepareVariationRow($variation)
    {
        for( $i = 0; $i < 8; $i++) {
            $row[$i] = '';
        }

        $row[8] = 'Child';
        $row[9] = $variation['sku'];
        $row[10] = $variation['ean'];
        $row[11] = $variation['quantity'];
        $row[12] = $variation['price'];
        $row[13] = $variation['promotion'];
        $row[14] = $variation['bestseller'];
        $row[15] = $variation['recommended'];
        $row[16] = $variation['main_page'];
        $row[17] = $variation['feature1_value'];
        $row[18] = $variation['feature2_value'];
        $row[19] = $variation['feature3_value'];

        return $row;
    }
}