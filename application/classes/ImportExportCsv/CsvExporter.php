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
        //wrzucam dane
        $pr = $this->setRows($pr);

        $this->data = $pr;
        //array to csv
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
            $pr[2][$key] = '';
            $i++;
        }

        return $pr;
    }

    private function getHumanHeaders()
    {
            $headers = ['PRODUCT NAME','CATEGORY', 'SUBCATEGORY', 'MANUFACTURER', 'STATUS', 'FEATURE1_NAME',
                'FEATURE2_NAME', 'FEATURE3_NAME', 'TAG1', 'TAG2', 'TAG3', 'PARENTAGE', 'SKU', 'EAN', 'QUANTITY', 'PRICE', 'TAX', 'PROMOTION',
                'BESTSELLERS', 'RECOMMENDED', 'HOMEPAGE', 'FEATURE1_VALUE', 'FEATURE2_VALUE', 'FEATURE3_VALUE','PRODUCT_ID'];

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