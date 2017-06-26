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
        if ($chmod) {
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
        if (!($this->fp)) {
            throw new \Exception('Can\'t open file ' . $this->file_name);
        }

        $pr = [];

        //set headers
        $pr = $this->setHeaders($pr);
        $pr = $this->setRows($pr);

        $this->data = $pr;

        foreach ($this->data as $fields) {
            fputcsv($this->fp, $fields, ";");
        }

        return $this->fp;
    }

    private function setHeaders($pr)
    {
        $humanHeaders = $this->getHumanHeaders();
        $i = 0;

        foreach ($this->data[0] as $key => $val) {
            $pr[0][$key] = 'todo: opis ';
            $pr[1][$humanHeaders[$i]] = $humanHeaders[$i];
            $pr[2][$key] = $key;
            $pr[3][$key] = '';
            $i++;
        }

        return $pr;
    }

    private function getHumanHeaders()
    {
        $headers = ['PRODUCT ID', 'VARIATION ID', 'PARENTAGE', 'PRODUCT_NAME', 'SKU', 'EAN', 'QUANTITY', 'PRICE',
            'CATEGORY', 'SUBCATEGORY', 'MANUFACTURER', 'STATUS', 'PROMOTION', 'BESTSELLERS', 'RECOMMENDED', 'HOMEPAGE',

            'TYPE', 'DESC', 'DESC_SHORT', 'TAG1', 'TAG2',
            'TAG3', 'DATE_ADD', 'DATE_MOD', 'IMAGE1', 'IMAGE2',
            'IMAGE3', 'FEATURE1_NAME', 'FEATURE2_NAME', 'FEATURE3_NAME',
            'TAX', 'PRICE_PURCHASE', 'PRICE_RRP', 'PRICE', 'PRICE2',
            'PRICE3', 'PRICE_PROMOTION', 'PROMOTION', 'BESTSELLER', 'RECOMMENDED',
            'MAIN_PAGE', 'MEGA_OFFER', 'WEIGHT', 'QTY', 'DATE_PROMOTION',
            'FEATURE1_VALUE', 'FEATURE2_VALUE', 'FEATURE3_VALUE', 'EXPORTED_DOMAIN'];

        return $headers;
    }

    private function setRows($pr)
    {
        $i = 4;

        foreach ($this->data as $product) {
            foreach ($product as $key => $val) {
                $pr[$i][$key] = $val;
            }
            $i++;
        }

        return $pr;
    }
}