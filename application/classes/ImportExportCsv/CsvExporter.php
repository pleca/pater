<?php

namespace Application\Classes\ImportExportCsv;


class CsvExporter
{
    private $fp;
    private $parse_header;
    private $header;
    private $delimiter;
    private $length;
    private $chmod;

    function __construct(array $data, $file_name, $parse_header = false, $delimiter = ";", $length = 8000, $chmod = true)
    {
        $this->data = $data;
        $this->file_name = $file_name;
        $this->fp = fopen($file_name, "w");
        $this->parse_header = $parse_header;
        $this->delimiter = $delimiter;
        $this->length = $length;
        $this->chmod = $chmod;

        if ($this->parse_header) {
            $this->header = fgetcsv($this->fp, $this->length, $this->delimiter);
        }
        
        //to easy remove created csv file in linux system
        if ($this->chmod){
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

        $pr[0]['1'] = 'jedyn';
        $pr[0]['2'] = 'dwa';
        $pr[0]['3'] = 'czy';
        $pr[0]['4'] = 'tery';
        $pr[0]['5'] = 'ęć';
        $pr[0]['6'] = 'szejść';

        $i=1;

        foreach ($this->data as $product) {
            foreach ($product as $key => $val) {
                if (strcmp('variations', $key) !== 0) {
                    $pr[$i][$key] = $val;
                }
            }
            $i++;
        }

        $this->data = $pr;

        foreach ($this->data as $fields){
            fputcsv($this->fp, $fields, ";");
        }

        return $this->fp;
    }

    public function addHeaders()
    {
        
    }
}