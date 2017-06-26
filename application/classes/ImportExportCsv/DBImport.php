<?php

namespace Application\Classes\ImportExportCsv;

class DBImport
{

    private $data;

    /**
     * DBImport constructor.
     */
    public function __construct($data)
    {
        $this->data = $data;


    }


    public function run()
    {
        $this->transact($this->data);
    }

    private function transact($data)
    {
        try {
            \Cms::$db->beginTransaction();
            foreach ($data as $key => $row) {
                if ($key > 3) {
                }
            }
        } catch (\Exception $e) {
            \Cms::$db->rollBack();
            echo $e->getMessage();
        }


    }
}