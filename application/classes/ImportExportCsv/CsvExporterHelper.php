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
        $row[8] = $product['tag1'];
        $row[9] = $product['tag2'];
        $row[10] = $product['tag3'];
        $row[11] = 'Parent';

        for( $i = 12; $i < 24; $i++) {
            $row[$i] = '';
        }

        $row[24] = $product['product_id'];

        return $row;
    }

    protected function prepareVariationRow($variation)
    {
        for( $i = 0; $i < 11; $i++) {
            $row[$i] = '';
        }

        $row[11] = 'Child';
        $row[12] = $variation['sku'];
        $row[13] = $variation['ean'];
        $row[14] = $variation['quantity'];
        $row[15] = $variation['price'];
        $row[16] = $variation['tax_value'];
        $row[17] = $variation['promotion'];
        $row[18] = $variation['bestseller'];
        $row[19] = $variation['recommended'];
        $row[20] = $variation['main_page'];
        $row[21] = $variation['feature1_value'];
        $row[22] = $variation['feature2_value'];
        $row[23] = $variation['feature3_value'];

        return $row;
    }
}