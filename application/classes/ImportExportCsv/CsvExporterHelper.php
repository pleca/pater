<?php

namespace Application\Classes\ImportExportCsv;


class CsvExporterHelper
{

    private $products;
    private $variations;

    /**
     * CsvExporterHelper constructor.
     */
    public function __construct($products, $variations)
    {
        $this->products = $products;
        $this->variations = $variations;
    }


    //ta metoda ma zwracać już przygotowaną tablicę danych taka jak jest w potrzebna w CSV
    public function get()
    {
        $data = [];

        $data[] = ['','','','',''];

        $array = [];
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

        return $array;
    }

    protected function prepareProductRow($product)
    {
        $row[0] = '';
        $row[1] = $product['product_name'];
        $row[2] = $product['category'];
        $row[3] = $product['subcategory'];
        $row[4] = $product['manufactured_name'];
        $row[5] = $product['status'];
        $row[6] = $product['feature1_name'];
        $row[7] = $product['feature2_name'];
        $row[8] = $product['feature3_name'];
        $row[9] = $product['feature1_value'];
        $row[10] = $product['feature2_value'];
        $row[11] = $product['feature3_value'];
        $row[12] = 'Parent';

        for( $i = 13; $i < 20; $i++) {
            $row[$i] = '';
        }

        return $row;
    }

    protected function prepareVariationRow($variation)
    {
        for( $i = 0; $i < 11; $i++) {
            $row[$i] = '';
        }

        $row[12] = 'Child';
        $row[13] = $variation['sku'];
        $row[14] = $variation['ean'];
        $row[15] = $variation['quantity'];
        $row[16] = $variation['price'];
        $row[17] = $variation['promotion'];
        $row[18] = $variation['bestseller'];
        $row[19] = $variation['recommended'];
        $row[20] = $variation['main_page'];

        return $row;

    }
}