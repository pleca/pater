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
        $pr = $this->setHeaders($pr);
        $pr = $this->setRows($pr);

        $this->data = $pr;

        foreach ($this->data as $fields){
            fputcsv($this->fp, $fields, ";");
        }

        return $this->fp;
    }

    private function setHeaders($pr)
    {
        foreach ($this->data[0] as $key => $val) {
            if (strcmp("variations", $key) !== 0) {
                $pr[0][$key] = $key;
            }
        }

        return $pr;
    }

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