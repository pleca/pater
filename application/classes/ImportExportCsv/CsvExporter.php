<?php

namespace Application\Classes\ImportExportCsv;


class CsvExporter
{
    private $fp;
    private $delimiter;

    function __construct(array $data, $file_name, $delimiter = ";", $chmod = true)
    {
        $this->data = $data;
        $this->file_name = $file_name;
        $this->fp = fopen($file_name, "w");
        $this->delimiter = $delimiter;

        //to easy remove created csv file in linux system
        if ($chmod){
            chmod($this->file_name, 0777);
        }
    }

    function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }

    function get()
    {
        if (!($this->fp)){
            throw new \Exception('Can\'t open file '. $this->file_name);
        }
       
        $pr = [];

        //set headers
        $pr = $this->setDbHeaders($pr);
        $pr = $this->setRows($pr);

        $this->data = $pr;

        foreach ($this->data as $fields){
            fputcsv($this->fp, $fields, ";");
        }

        return $this->fp;
    }

    private function setDbHeaders($pr)
    {
        foreach ($this->data[0] as $key => $val) {
            if (strcmp("variations", $key) !== 0) {
                $pr[0][$key] = $key;
            }
        }

        return $pr;
    }

//    private function setHumanHeaders()
//    {
//        $headers = ['SKU', 'PRODUCT_NAME', 'CATEGORY', 'MANUFACTURER', 'STATUS',
//            'PROMOTION', 'BESTSELLERS', 'RECOMMENDED', 'HOMEPAGE',
////            'TYPE', 'DESC', 'DESC_SHORT', 'TAG1', 'TAG2',
////            'TAG3', 'DATE_ADD', 'DATE_MOD', 'IMAGE1', 'IMAGE2',
////            'IMAGE3', 'FEATURE1_NAME', 'FEATURE2_NAME', 'FEATURE3_NAME', 'PARENTAGE',
////            'TAX', 'PRICE_PURCHASE', 'PRICE_RRP', 'PRICE', 'PRICE2',
////            'PRICE3', 'PRICE_PROMOTION', 'PROMOTION', 'BESTSELLER', 'RECOMMENDED',
////            'MAIN_PAGE', 'MEGA_OFFER', 'WEIGHT', 'QTY', 'DATE_PROMOTION',
////            'FEATURE1_VALUE', 'FEATURE2_VALUE', 'FEATURE3_VALUE', 'EXPORTED_DOMAIN'];
//
//        return $headers;
//    }

    private function setRows($pr)
    {
        $i = 1;

        foreach ($this->data as $product) {
            foreach ($product as $key => $val) {
                if (strcmp('variations', $key) !== 0) {
                    $pr[$i][$key] = $val;
                }
            }
            $i++;
        }

        return $pr;
    }
}