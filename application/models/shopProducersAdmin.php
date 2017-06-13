<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/Producers.php');
require_once(CMS_DIR . '/application/models/uploadAdmin.php');

class ProducersAdmin extends Producers {

	public $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'product_manufacturer';
        $this->img_dir = CMS_DIR . '/files/producers';
        $this->img_url = CMS_URL . '/files/producers';
        $this->upload = new UploadAdmin();        
	}

	public function __destruct() {
		
	}

	public function loadAdmin() {
		$q = "SELECT * FROM `" . $this->table . "` ORDER BY `order` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$v['url'] = URL . '/producers/' . $v['name_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function loadByIdAdmin($id = 0) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
                      
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
                
                if(isset($v['file'])) $v['photo'] = get_photo($this->img_dir, SERVER_URL . $this->img_url, $v['file']);

				return $v;
			}
            
		}
		return false;
	}

	public function loadProducersSelect() {
		$q = "SELECT * FROM `" . $this->table . "` ORDER BY `order` ASC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[$v['id']] = $v;
		}
		return $items;
	}

	public function addAdmin($post) {
		$post = maddslashes($post);
		$post['name'] = clearName($post['name']);
		$post['name_url'] = makeUrl($post['name']);
		$orderMax = $this->getMaxOrder();
		$post['order'] = $orderMax['0'] + 1;

        if (isset($_FILES['file']) AND $_FILES['file']['error'] == 0) {      
            $ext = substr($_FILES['file']['name'], -4);
            $file_url = $post['name_url'];
            $file = $file_url . $ext;
            $file_name = $this->img_dir . '/' . $file;        
            
            if (file_exists($file_name)) {
                unlink($file_name);
            }
            
            $img_name = $this->upload->add_image($_FILES['file'], $file_url, $this->img_dir, PRODUCER_IMG_RATIO, PRODUCER_IMG_X1, PRODUCER_IMG_Y1, PRODUCER_IMG_X2, PRODUCER_IMG_Y2, PRODUCER_IMG_X3, PRODUCER_IMG_Y3); 
        } else {
            $file = '';
        }
        
		$q = "INSERT INTO " . $this->table . " SET"
				. "`status_id`='" . $post['status_id'] . "', `name`='" . $post['name'] . "', `name_url`='" . $post['name_url'] . "', `popular`='" . $post['popular'] . "', `file`='" . $file . "', `order`='" . $post['order'] . "' ";
		if ($id = Cms::$db->insert($q)) {			
			return $id;
		} else {
			
			return false;
		}
	}

	public function editAdmin($post) {
		$post = maddslashes($post);
		$post['name'] = clearName($post['name']);
		$post['name_url'] = makeUrl($post['name']);

        if (isset($_FILES['file']) AND $_FILES['file']['error'] == 0) {      
            $ext = substr($_FILES['file']['name'], -4);
            $file_url = $post['name_url'];
            $file = $file_url . $ext;
            $file_name = $this->img_dir . '/' . $file;        
            
            if (file_exists($file_name)) {
                unlink($file_name);
            }
            
            $img_name = $this->upload->add_image($_FILES['file'], $file_url, $this->img_dir, PRODUCER_IMG_RATIO, PRODUCER_IMG_X1, PRODUCER_IMG_Y1, PRODUCER_IMG_X2, PRODUCER_IMG_Y2, PRODUCER_IMG_X3, PRODUCER_IMG_Y3); 
        } else {
            $file = '';
        }
        
		$q = "UPDATE " . $this->table . " SET"
				. "`status_id`='" . $post['status_id'] . "', `name`='" . $post['name'] . "', `name_url`='" . $post['name_url'] . "', `popular`='" . $post['popular'] . "', `file`='" . $file . "'"
				. "WHERE `id`='" . (int) $post['id'] . "' ";
		if (Cms::$db->update($q)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getMaxOrder() {
		$q = "SELECT MAX(`order`) FROM " . $this->table . " ";
		return Cms::$db->max($q);
	}

	public function deleteAdmin($id) {
		if ($id) {
			$id = addslashes($id);
			$item = $this->loadByIdAdmin($id);
			$q = "DELETE FROM " . $this->table . " "
					. "WHERE `id`='" . (int) $id . "' ";
			if (Cms::$db->delete($q)) {
				$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' ";
				Cms::$db->update($q);

				return true;
			}
		}

		return false;
	}
    
    public function deleteImageByName($filename) {
        $ext = substr($filename, -4);
        $file_m = substr($filename, 0, -4) . '_m' . $ext;
        $file_s = substr($filename, 0, -4) . '_s' . $ext;
        if (file_exists($this->img_dir . '/' . $filename))
            unlink($this->img_dir . '/' . $filename);
        if (file_exists($this->img_dir . '/' . $file_m))
            unlink($this->img_dir . '/' . $file_m);
        if (file_exists($this->img_dir . '/' . $file_s))
            unlink($this->img_dir . '/' . $file_s);
        $q = "UPDATE `" . $this->table . "` SET file=null WHERE file='" . $filename . "'";
		
		return Cms::$db->update($q);       
    }    

}
