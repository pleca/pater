<?php

//namespace Models;


if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(CMS_DIR . '/application/models/uploadAdmin.php');

class Logotype extends BaseModel {

	public $table;
	public $imgDir;
	public $upload;    

	public function __construct() {
		$this->table = DB_PREFIX . 'logotypes';
        $this->imgDir = CMS_DIR . '/files/logotypes';
        $this->upload = new UploadAdmin(); 
	}

	public function findBy($params = [], $fields = []) {	
		if (!$params) {
			return false;
		}		
		
		$where = $this->where($params);

		return $this->select($this->table, $where, '', '', $fields);
	}	
    
	public function getAll(array $params) {
        $orderBy = isset($paramsp['orderBy']) ? $paramsp['orderBy'] : '';
        $dir = isset($paramsp['dir']) ? $paramsp['dir'] : '';        
                
		return $this->select($this->table, '1', $orderBy, $dir);
	}
    
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		return $this->insert($this->table, $item);
	}
    
	public function getMaxOrder() {
		$q = "SELECT MAX(`order`) FROM " . $this->table . " ";
		return Cms::$db->max($q);
	}
    
	public function editAdmin($post) {
		$post = maddslashes($post);
		$post['name'] = clearName($post['name']);
		$post['name_url'] = makeUrl($post['name']);

        if (isset($_FILES['file']) AND $_FILES['file']['error'] == 0) {      
            $ext = substr($_FILES['file']['name'], -4);
            $fileUrl = $post['name_url'];
            $file = $fileUrl . $ext;
            $fileName = $this->imgDir . '/' . $file;        
            
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            
            $uploadOk = 1;
            $imageFileType = pathinfo($fileName, PATHINFO_EXTENSION);
            // Check if image file is a actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["file"]["tmp_name"]);
                if($check !== false) {
                    $uploadOk = 1;
                } else {
                    $uploadOk = 0;
                }
            }

            if ($uploadOk == 0) {
                Cms::getFlashBag()->add('error', "Sorry, your file was not uploaded.");

            } else {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $fileName)) {
                } else {
                    Cms::getFlashBag()->add('error', "Sorry, there was an error uploading your file.");
                }
            }
			
        } else {
            $file = '';
        }
        
		$q = "UPDATE " . $this->table . " SET"
				. "`name`='" . $post['name'] . "', `name_url`='" . $post['name_url'] . "', `file`='" . $file . "', `url`='" . $post['url'] . "' "
				. "WHERE `id`='" . (int) $post['id'] . "' ";
		
		if (Cms::$db->update($q)) {
			return true;
		} else {
			return false;
		}
	}
	
    public function add($post) {
		$post = maddslashes($post);
		$post['name'] = clearName($post['name']);
        $post['name_url'] = makeUrl($post['name']);
		$orderMax = $this->getMaxOrder();
		$post['order'] = $orderMax['0'] + 1;

        if (isset($_FILES['file']) AND $_FILES['file']['error'] == 0) {      
            $ext = substr($_FILES['file']['name'], -4);
            $fileUrl = $post['name_url'];
            $file = $fileUrl . $ext;
            $fileName = $this->imgDir . '/' . $file;        
            
            if (file_exists($fileName)) {
                unlink($fileName);
            }

            $uploadOk = 1;
            $imageFileType = pathinfo($fileName, PATHINFO_EXTENSION);
            // Check if image file is a actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["file"]["tmp_name"]);
                if($check !== false) {
                    $uploadOk = 1;
                } else {
                    $uploadOk = 0;
                }
            }

            if ($uploadOk == 0) {
                Cms::getFlashBag()->add('error', "Sorry, your file was not uploaded.");

            } else {
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $fileName)) {
                } else {
                    Cms::getFlashBag()->add('error', "Sorry, there was an error uploading your file.");
                }
            }            
            
//            $imgName = $this->upload->add_image($_FILES['file'], $fileUrl, $this->imgDir, PRODUCER_IMG_RATIO, PRODUCER_IMG_X1, PRODUCER_IMG_Y1, PRODUCER_IMG_X2, PRODUCER_IMG_Y2, PRODUCER_IMG_X3, PRODUCER_IMG_Y3); 
        } else {
            $file = '';
        }
        
        $params = array(
            'name' => $post['name'],
            'name_url' => $post['name'],
            'file' => $file,
            'url' => $post['url'],
            'order' => $post['order']
        );
        
        $this->set($params);
        
        return true;
    }
    
	public function deleteById($id) {
		if ($id) {
			$id = addslashes($id);
			$item = $this->findBy(["id" => $id])[0];
            
			$q = "DELETE FROM " . $this->table . " "
					. "WHERE `id`='" . (int) $id . "' ";
			if (Cms::$db->delete($q)) {
				$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' ";
				Cms::$db->update($q);

                unlink($this->imgDir . $item['file']);
                
                Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
				return true;
			}
		}

        Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}    
	
	public function loadByIdAdmin($id = 0) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
                      
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
                
                if(isset($v['file'])) $v['photo'] = SERVER_URL . '/files/logotypes/' . $v['file'];

				return $v;
			}
            
		}
		return false;
	}
	
    public function deleteImageByName($filename) {

        if (file_exists($this->imgDir . '/' . $filename)) {
            unlink($this->imgDir . '/' . $filename);
		}

        $q = "UPDATE `" . $this->table . "` SET file=null WHERE file='" . $filename . "'";
		
		return Cms::$db->update($q);       
    }		

}
