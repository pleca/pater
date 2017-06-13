<?php
/* 2014-01-02 | creative.cms 14.1 */

if(!defined('NO_ACCESS')) die('No access to files!');

class Netto
{
   private $db;
   private $tpl;
   private $table;
   
   public function __construct($oCore)
   {      
      $this -> db = $oCore -> db;
      $this -> tpl = $oCore -> tpl;
      $this -> table = DB_PREFIX.'netto';
   }

   public function __destruct()
   {

   }
	
}
?>