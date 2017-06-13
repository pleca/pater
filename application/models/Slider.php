<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
    die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/imageUploader.php');

class Slider extends BaseModel {

    private $table;
    private $uploader;
    private $dir;
    private $url;

    public function __construct() {
        $this->table = DB_PREFIX . 'slider';
        $this->dir = CMS_DIR . '/files/slider';
        $this->url = CMS_URL . '/files/slider';
        $this->uploader = new ImageUploader($this->dir);
    }

    public function __destruct() {
        
    }

    public function getNextId() {
        $q = "SELECT MAX(`id`) FROM `" . $this->table . "` ";
        $t = Cms::$db->max($q);
        return $t[0] + 1;
    }
	
	public function getAutoIncrement() {
		$q = "SELECT Auto_increment FROM information_schema.tables WHERE table_name='" . $this->table . "_translation' AND table_schema='" . DB_NAME . "' ";
		$result = Cms::$db->getRow($q);
		
		return $result['Auto_increment'];
	}
	
    public function getNextTranslationId() {
        $q = "SELECT MAX(`id`) FROM `" . $this->table . "_translation` ";
        $t = Cms::$db->max($q);
		
		if (!$t[0]) {
			return $this->getAutoIncrement();
		}
		
        return $t[0] + 1;
    }
	
    public function getNextTranslationIdOld() {
        $q = "SELECT MAX(`id`) FROM `" . $this->table . "_translation` ";
        $t = Cms::$db->max($q);
        return $t[0] + 1;
    }

    public function getNextOrder() {
        $q = "SELECT MAX(`order`) FROM `" . $this->table . "` ";
        $t = Cms::$db->max($q);
        return $t[0] + 1;
    }

    public function loadAdmin($lang = '') {
        $q = "SELECT * FROM `" . $this->table . "` WHERE `lang`='" . $lang . "' ORDER BY `order` ASC ";
        $array = CMS::$db->getAll($q);

        return $array;
    }

    function getFile($file) {
        $v = '';
        if (!empty($file)) {
            if (file_exists($this->dir . '/' . $file))
                $v = $this->url . '/' . $file;
        }
        return $v;
    }
		
	protected function areFiles($files) {
		if ($files) {
			foreach ($files as $locale => $file) {
				if ($file['name']) {
					return true;
				}
				
			}
		}
		
		return false;
	}
	
	public function add($post, $files) {
		
		if ($this->areFiles($files)) {
			$post = maddslashes($post);						
            $nextOrder = $this->getNextOrder();

			$item = array(						
				'target' => $post['target'],
				'order' => $nextOrder,
				'active' => $post['active']
			);	
			
			$id = $this->insert($this->table, $item);	
			$this->convertToTranslationData($post);

			if ($id) {
				foreach ($post as $locale => $trans) {
					$title = clearName($trans['title']);	
					$nextTranslationId = $this->getNextTranslationId();
					$file = '';
					
					if ($files[$locale]['name']) {
                        $newFileName = str_replace(" ", "_", $files[$locale]['name']);
						$file = changeFileName($newFileName, '_' . $nextTranslationId, makeUrl($trans['title']));
					}					

					$item = array(
						'translatable_id' => $id,					
						'title' => $title,					
						'file' => $file,				
						'url' => $trans['url'],
						'locale' => $locale,
					);

					$translationId = $this->insert($this->table . '_translation', $item);
					
					if ($translationId && $files[$locale]['name']) {
						$this->uploader->AddFile($files[$locale]);
						if (!$this->uploader->Upload($file, 0)) {
							Cms::getFlashBag()->add('error', $this->uploader->ErrorMsg());							
						}
					}
				}

				Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
				return true;
			} else {
				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
				return false;
			}			
			
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_no_file']);
		return false;
	}
	
//        $q = "UPDATE " . $this->table . " SET `alt`='" . $post['alt'] . "', `url`='" . $post['url'] . "', `title`='" . $post['title'] . "', `target`='" . $post['target'] . "', `active`='" . $post['active'] . "' ";
//        $q.= "WHERE `id`='" . (int) $post['id'] . "' ";
//        Cms::$db->update($q);
		
	public function edit($post) {
		$entitBeforeChanged = $this->getById($post['id']);
		$post['active'] = isset($post['active']) ? $post['active'] : 0;

		$item = array(
			'active' => $post['active'],
			'target' => $post['target'],
		);
		
		$id = $post['id'];
	
		$this->updateById($id, $item);
		$this->convertToTranslationData($post);

		$entities = $this->getAll();

		if ($id) {
			foreach ($post as $locale => $trans) {
				$title = clearName($trans['title']);
				$oldFile = $entitBeforeChanged['trans'][$locale]['file'];
				$newFile = isset($_FILES[$locale]['name']) ? $_FILES[$locale]['name'] : null;
				$file = $oldFile;
				
				if ($title != $entitBeforeChanged['trans'][$locale]['title'] && $entitBeforeChanged['trans'][$locale]['file'] && !$newFile) {
					$file = changeFileName($oldFile, '_' . $entitBeforeChanged['trans'][$locale]['id'], makeUrl($trans['title']));
					rename($this->dir . '/' . $oldFile, $this->dir . '/' . $file);
				}								
				
				if ($newFile) {
					$file = changeFileName($newFile, '_' . $entitBeforeChanged['trans'][$locale]['id'], makeUrl($trans['title']));
					unlink($this->dir . '/' . $oldFile);
					
					$this->uploader->AddFile($_FILES[$locale]);
					if (!$this->uploader->Upload($file, 0)) {
						Cms::getFlashBag()->add('error', $this->uploader->ErrorMsg());							
					}
				}
				
				$item = array(
					'translatable_id' => $id,		
					'title' => $title,					
					'file' => $file,					
					'url' => $trans['url'],
					'locale' => $locale,
				);

				if ($this->existsTranslation($id, $locale, $entities)) {
					$this->updateTranslation($id, $locale, $item);					
				} else {					
					$this->insert($this->table . '_translation', $item);
				}								
			}
		}

		Cms::getFlashBag()->add('info', 'Zapisano zmiany.');
		return true;
	}
	
	public function updateTranslation($id, $locale, $item) {
		if(!$id) {
			return false;
		}
		if(!$item) {
			return false;
		}
		$where = $this->where(["translatable_id" => $id, 'locale' => $locale]);
		return $this->update($this->table . '_translation', $where, $item);
	}	
	
	public function deleteById($id) {
		
		if ($entity = $this->getById($id)) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $entity['order'] . "' ";
            Cms::$db->update($q);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);		
			
			foreach ($entity['trans'] as $locale => $row) {
				if (!empty($row['file']) && file_exists($this->dir . '/' . $row['file'])) {
					unlink($this->dir . '/' . $row['file']);
				}
			}
   
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			return true;
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}
	
    public function deleteImageByName($filename) {

        if (file_exists($this->dir . '/' . $filename)) {
            unlink($this->dir . '/' . $filename);
		}

        $q = "UPDATE `" . $this->table . "_translation` SET file=null WHERE file='" . $filename . "'";		
		
		return Cms::$db->update($q);       
    }	
	
	public function getSlider($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT * FROM `" . $this->table ."` "
				. "WHERE `id` = '" . (int) $id . "' ";
		
		$result = Cms::$db->getRow($q);	

		return $result;
	}
	
	public function getById($id) {
		
		$slider = $this->getSlider($id);

		$result = [];		
		$result = $slider;
		$result['trans'] = $this->getTranslation($id);
		
		return $result;
	}	
	
	public function getTranslation($id) {
		$q = "SELECT t.id, t.translatable_id, t.title, t.file, t.url, t.locale FROM `" . $this->table . "` s "
				. "LEFT JOIN `" . $this->table . "_translation` t ON s.id = t.translatable_id "
				. "WHERE s.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		if ($result) {
			foreach ($result as &$entity) {
				$entity = mstripslashes($entity);
			}
		}
		
		return $result;
	}	
	
//    function delete($get) {
//        if ($get['id'] > 0) {
//            if ($item = $this->loadByIdAdmin($get['id'])) {
//                if (!empty($item['photo']) AND file_exists($this->dir . '/' . $item['photo']))
//                    unlink($this->dir . '/' . $item['photo']);
//                $q = "DELETE FROM " . $this->table . " WHERE `id`='" . $item['id'] . "' ";
//                Cms::$db->delete($q);
//                $q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' AND `lang`='" . $item['lang'] . "' ";
//                Cms::$db->update($q);
//				Cms::getFlashBag()->add('error', 'Wybrany element usunięto.');
//                return true;
//            }
//        }
//		Cms::getFlashBag()->add('error', 'Usuwanie elementu nie powiodło się!');
//        return false;
//    }	
	
    public function addOLD($post, $files) {		
		
        if (!empty($files['file']['name'])) {
            $post = maddslashes($post);
            $next_id = $this->getNextId();
            $next_order = $this->getNextOrder($post['lang']);
            $fileName = changeFileName($files['file']['name'], '_' . $next_id, makeUrl($post['alt']));
            $this->uploader->AddFile($files['file']);
            if (!$this->uploader->Upload($fileName, 0)) {
				return $this->uploader->ErrorMsg();
            } else {
                $q = "INSERT INTO `" . $this->table . "` SET `lang`='" . $post['lang'] . "', `photo`='" . $fileName . "', `alt`='" . $post['alt'] . "', ";
                $q.= "`url`='" . $post['url'] . "', `title`='" . $post['title'] . "', `target`='" . $post['target'] . "', `order`='" . $next_order . "', `active`='" . $post['active'] . "' ";
                Cms::$db->insert($q);
                return true;
            }
        }
        
		return 'Nie wybrano pliku.';
    }

    public function editOld($post) {
        $post = maddslashes($post);
        $q = "UPDATE " . $this->table . " SET `alt`='" . $post['alt'] . "', `url`='" . $post['url'] . "', `title`='" . $post['title'] . "', `target`='" . $post['target'] . "', `active`='" . $post['active'] . "' ";
        $q.= "WHERE `id`='" . (int) $post['id'] . "' ";
        Cms::$db->update($q);
		Cms::getFlashBag()->add('info', 'Zapisano zmiany.');
        return true;
    }

    public function moveDown($get) {
        if ($entity = $this->getById($get['id'])) {
            $q = "UPDATE " . $this->table . " SET `order`=`order`-1 ";
            $q.= "WHERE `order`='" . ($entity['order'] + 1) . "'";
            if (Cms::$db->update($q)) {
                $q = "UPDATE " . $this->table . " SET `order`=`order`+1 WHERE `id`='" . $get['id'] . "' ";
                if (Cms::$db->update($q)) {
					Cms::getFlashBag()->add('info', 'Przeniesiono element o jeden poziom niżej!');
                    return true;
                }
            }
        }
		Cms::getFlashBag()->add('error', 'Zmiana nie powiodła się!');
        return false;
    }

    public function moveUp($get) {
        if ($entity = $this->getById($get['id'])) {

            if ($entity['order'] > 1) {
                $q = "UPDATE " . $this->table . " SET `order`=`order`+1 ";
                $q.= "WHERE `order`='" . ($entity['order'] - 1) . "'";
                if (Cms::$db->update($q)) {
                    $q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `id`='" . $get['id'] . "' ";
                    if (Cms::$db->update($q)) {
						Cms::getFlashBag()->add('info', 'Przeniesiono element o jeden poziom wyżej!');
                        return true;
                    }
                }
            }
        }
        Cms::getFlashBag()->add('error', 'Zmiana nie powiodła się!');
        return false;
    }
	
    public function moveUpOld($get) {
        if ($item = $this->loadByIdAdmin($get['id'])) {
            if ($item['order'] > 1) {
                $q = "UPDATE " . $this->table . " SET `order`=`order`+1 ";
                $q.= "WHERE `lang`='" . $item['lang'] . "' AND `order`='" . ($item['order'] - 1) . "'";
                if (Cms::$db->update($q)) {
                    $q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `id`='" . $item['id'] . "' ";
                    if (Cms::$db->update($q)) {
						Cms::getFlashBag()->add('info', 'Przeniesiono element o jeden poziom wyżej!');
                        return true;
                    }
                }
            }
        }
        Cms::$tpl->setError('Zmiana nie powiodła się!');
        return false;
    }

    public function loadByIdAdmin($id) {
        $q = "SELECT * FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
        return Cms::$db->getRow($q);
    }

    function deleteAdmin($get) {
        if ($get['id'] > 0) {
            if ($item = $this->loadByIdAdmin($get['id'])) {
                if (!empty($item['photo']) AND file_exists($this->dir . '/' . $item['photo']))
                    unlink($this->dir . '/' . $item['photo']);
                $q = "DELETE FROM " . $this->table . " WHERE `id`='" . $item['id'] . "' ";
                Cms::$db->delete($q);
                $q = "UPDATE " . $this->table . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' AND `lang`='" . $item['lang'] . "' ";
                Cms::$db->update($q);
				Cms::getFlashBag()->add('error', 'Wybrany element usunięto.');
                return true;
            }
        }
		Cms::getFlashBag()->add('error', 'Usuwanie elementu nie powiodło się!');
        return false;
    }

	public function getAll(array $params = []) {
//		$q = "SELECT NOW()";
//		dump(Cms::$db->getRow($q));
		
		$q = "SELECT s.*,t.translatable_id, t.title, t.file, t.url, t.locale "
				. "FROM `" . $this->table . "` s "
				. "LEFT JOIN `" . $this->table . "_translation` t ON s.id = t.translatable_id ";

		$lastElement = end($params);
		if ($params) {
			$q .= 'WHERE ';
			
			foreach ($params as $key => $value) {
				
				$prefix = 's';
				
				if ($key == 'locale') {
					$prefix = 't';
				}
				
				if (is_array($value)) {
					$prefix = 's';
					$q .= "$prefix.$key IN (" .implode(',', $value) .") ";
				} else {
					$q .= "$prefix.$key = '" .$value ."' ";
				}								
				
				if ($value !== $lastElement) {
					$q .= " AND ";
				}
			}
			
		}
		
		$q .= " ORDER BY s.`order` ASC";

		$entities = Cms::$db->getAll($q);

		if (!isset($params['locale'])) {
			$entities = $this->groupByTranslation($entities, 'id');
		}
		
		return $entities;
	}	
	
    public function getAllOld() {

        $q = "SELECT * FROM `" . $this->table . "` ";
        $item = Cms::$db->getAll($q);

        if ($item) {
            $this->mss($item);
            return $item;
        }

        return false;
    }

    public function getByIdOld($id = 0) {

        $this->mas($id);

        $q = "SELECT * FROM `" . $this->table . "` " .
                "WHERE `id` = '" . $id . "' ";

        $item = Cms::$db->getAll($q);

        if ($item) {

            $this->mss($item);
            return $item;
        }
        return false;
    }

    public function set($item = '') {

        $tableFields = $this->getTableFields($this->table);

        $fields = $this->getFields($item, $tableFields);

        $this->mas($fields);

        $q = "INSERT INTO `" . $this->table . "` " .
                $this->createInsertFields($fields) .
                $this->createInsertValues($fields);

        return Cms::$db->insert($q);
    }

    public function updateById($id = 0, $item = '') {

        unset($item['id']);
        $this->mas($id);
        $this->mas($item);

        $tableFields = $this->getTableFields($this->table);

        $fields = $this->getFields($item, $tableFields);
        $this->mas($fields);

        $q = "UPDATE `" . $this->table . "` " .
                $this->createUpdate($fields) .
                "WHERE `id`='" . $id . "' ";

        return Cms::$db->update($q);
    }
    
	public function getSrc($file = '') {
		$item = '';
		if (!empty($file) and file_exists($this->dir . '/' . $file)) {
			$item = $this->url . '/' . $file;
		}
		return $item;
	}    

}
