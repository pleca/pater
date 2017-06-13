<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
    die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/imageUploader.php');

class SliderModel extends BaseModel {

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

    public function getNextOrder($lang = 1) {
        $q = "SELECT MAX(`order`) FROM `" . $this->table . "` WHERE `lang`='" . $lang . "' ";
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

    public function add($post, $files) {		
		
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

    public function edit($post) {
        $post = maddslashes($post);
        $q = "UPDATE " . $this->table . " SET `alt`='" . $post['alt'] . "', `url`='" . $post['url'] . "', `title`='" . $post['title'] . "', `target`='" . $post['target'] . "', `active`='" . $post['active'] . "' ";
        $q.= "WHERE `id`='" . (int) $post['id'] . "' ";
        Cms::$db->update($q);
		Cms::getFlashBag()->add('info', 'Zapisano zmiany.');
        return true;
    }

    public function moveDown($get) {
        if ($item = $this->loadByIdAdmin($get['id'])) {
            $q = "UPDATE " . $this->table . " SET `order`=`order`-1 ";
            $q.= "WHERE `lang`='" . $item['lang'] . "' AND `order`='" . ($item['order'] + 1) . "'";
            if (Cms::$db->update($q)) {
                $q = "UPDATE " . $this->table . " SET `order`=`order`+1 WHERE `id`='" . $item['id'] . "' ";
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

    public function getAll() {

        $q = "SELECT * FROM `" . $this->table . "` ";
        $item = Cms::$db->getAll($q);

        if ($item) {
            $this->mss($item);
            return $item;
        }

        return false;
    }

    public function getById($id = 0) {

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
