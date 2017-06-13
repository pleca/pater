<?php
/* 2011-01-03 | creative.cms */

if(!defined('NO_ACCESS')) die('No access to files!');

require_once(CMS_DIR . '/application/models/imageUploader.php');

class Banner
{
   private $db;
   private $tpl;
   private $uploader;
   
   public function __construct($oCore)
   {
      $this -> db = $oCore -> db;
      $this -> tpl = $oCore -> tpl;
      $this -> module = 'banner';
      $this -> table = DB_PREFIX.'banner';
      $this -> dir = CMS_DIR.'/files/'.$this -> module;
		$this -> url = CMS_URL.'/files/'.$this -> module;
      $this -> uploader = new ImageUploader($this -> dir);
   }

   public function __destruct()
   {

   }
   
   public function addAdmin($post, $files)
   {
      if(!empty($files['file']['name']))
      {
         $post = maddslashes($post);
         $next_id = $this -> getNextId();
         $next_order = $this -> getNextOrder($post['type'], $post['lang']);
         $fileName = changeFileName($files['file']['name'], '_'.$next_id, makeUrl($post['alt']));
         $this -> uploader -> AddFile($files['file']);
         if(!$this -> uploader -> Upload($fileName, 0))
         {
            $this -> tpl -> setError($this -> uploader -> ErrorMsg());
            return false;
         }
         else
         {
            $q = "INSERT INTO `".$this -> table."` SET `lang`='".$post['lang']."', `type`='".$post['type']."', `file`='".$fileName."', `alt`='".$post['alt']."', ";
            $q.= "`url`='".$post['url']."', `title`='".$post['title']."', `target`='".$post['target']."', `order`='".$next_order."', `active`='".$post['active']."' ";
            $this -> db -> insert($q);
            $this -> tpl -> setInfo('Dodano nowy banner.');
            return true;
         }
      }
      $this -> tpl -> setError('Nie wybrano pliku.');
      return false;
   }
   
   public function editAdmin($post)
   {
      $post = maddslashes($post);
      $q = "UPDATE ".$this -> table." SET `alt`='".$post['alt']."', `url`='".$post['url']."', `title`='".$post['title']."', `target`='".$post['target']."', `active`='".$post['active']."' ";
      $q.= "WHERE `id`='".(int)$post['id']."' ";
      $this -> db -> update($q);
      $this -> tpl -> setInfo('Zapisano zmiany.');
      return true;
   }
   
   public function getNextId()
   {
      $q = "SELECT MAX(`id`) FROM `".$this -> table."` ";
      $t = $this -> db -> max($q);
      return $t[0] + 1;
   }
   
   public function getNextOrder($type = '', $lang = 1)
   {
      $q = "SELECT MAX(`order`) FROM `".$this -> table."` WHERE `type`='".$type."' AND `lang`='".$lang."' ";
      $t = $this -> db -> max($q);
      return $t[0] + 1;
   }

   public function loadAdmin($type = '', $lang = '')
   {
      $q = "SELECT * FROM `".$this -> table."` WHERE `type`='".$type."' AND `lang`='".$lang."' ORDER BY `order` ASC ";
      $array = $this -> db -> getAll($q);
      $items = array();
      foreach($array as $v)
      {
         $v['file'] = $this -> getFile($v['file']);
         $items[] = $v;
      }
      return $items;
   }
   
   function getFile($file)
	{
      $v = '';
		if(!empty($file))
		{
         if(file_exists($this -> dir.'/'.$file)) $v = $this -> url.'/'.$file;
		}
		return $v;
	}
   
   public function loadByIdAdmin($id)
   {
      $q = "SELECT * FROM `".$this -> table."` WHERE `id`='".(int)$id."' ";
      return $this -> db -> getRow($q);
   }

   public function moveDownAdmin($get)
   {
		if($item = $this -> loadByIdAdmin($get['id']))
		{
			$q = "UPDATE ".$this -> table." SET `order`=`order`-1 ";
			$q.= "WHERE `type`='".$item['type']."' AND `lang`='".$item['lang']."' AND `order`='".($item['order'] + 1)."'";
			if($this -> db -> update($q))
			{
            $q = "UPDATE ".$this -> table." SET `order`=`order`+1 WHERE `id`='".$item['id']."' ";
				if($this -> db -> update($q))
				{
					$this -> tpl -> setInfo('Przeniesiono element o jeden poziom niżej!');
					return true;
				}
			}
		}
		$this -> tpl -> setError('Zmiana nie powiodła się!');
		return false;
   }

   public function moveUpAdmin($get)
   {
		if($item = $this -> loadByIdAdmin($get['id']))
		{
         if($item['order'] > 1)
         {
            $q = "UPDATE ".$this -> table." SET `order`=`order`+1 ";
            $q.= "WHERE `type`='".$item['type']."' AND `lang`='".$item['lang']."' AND `order`='".($item['order'] - 1)."'";
            if($this -> db -> update($q))
            {
               $q = "UPDATE ".$this -> table." SET `order`=`order`-1 WHERE `id`='".$item['id']."' ";
               if($this -> db -> update($q))
               {
                  $this -> tpl -> setInfo('Przeniesiono element o jeden poziom wyżej!');
                  return true;
               }
            }
         }
		}
		$this -> tpl -> setError('Zmiana nie powiodła się!');
		return false;
   }

   function deleteAdmin($get)
	{
      if($get['id'] > 0)
		{
         if($item = $this -> loadByIdAdmin($get['id']))
         {
            if(!empty($item['file']) AND file_exists($this -> dir.'/'.$item['file'])) unlink($this -> dir.'/'.$item['file']);
            $q = "DELETE FROM ".$this -> table." WHERE `id`='".$item['id']."' ";
            $this -> db -> delete($q);
            $q = "UPDATE ".$this -> table." SET `order`=`order`-1 WHERE `order`>'".$item['order']."' AND `type`='".$item['type']."' AND `lang`='".$item['lang']."' ";
            $this -> db -> update($q);
            $this -> tpl -> setError('Wybrany element usunięto.');
            return true;
         }
      }
		$this -> tpl -> setError('Usuwanie elementu nie powiodło się!');
		return false;
	}
   
}
?>