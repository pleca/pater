<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(CMS_DIR . '/application/models/gallery.php');

class GalleryAdmin extends Gallery {

	public $module;
	public $table;
	public $tableDesc;
	public $dir;
	public $url;

	public function __construct() {
		$this->module = 'gallery';
		$this->table = DB_PREFIX . 'gallery';
		$this->tableDesc = DB_PREFIX . 'gallery_desc';
		$this->tablePhotos = DB_PREFIX . 'gallery_photos';
		$this->dir = CMS_DIR . '/files/' . $this->module;
		$this->url = CMS_URL . '/files/' . $this->module;
		$this->widthS = 135;
		$this->heightS = 95;
	}

	public function __destruct() {
		
	}

	public function loadAdmin($limitStart = 0, $limit = 25) {
		$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE d.lang_id='" . _ID . "' ";
		$q.= "ORDER BY a.date_add DESC, a.id DESC ";
		$q.= "LIMIT " . $limitStart . ", " . $limit;
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['url'] = URL . '/' . $this->module . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}

	public function getPagesAdmin($limit = 25) {
		$q = "SELECT COUNT(`id`) FROM `" . $this->table . "` ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}

	public function loadByIdAdmin($id = 0, $lang = '') {
		if ($id > 0) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			if ($lang)
				$q.= "WHERE d.lang_id='" . $lang['id'] . "' AND a.id='" . (int) $id . "' ";
			else
				$q.= "WHERE d.lang_id='" . _ID . "' AND a.id='" . (int) $id . "' ";
			$v = Cms::$db->getRow($q);
			$v = mstripslashes($v);
			if ($lang) {
				if ($lang['default'] != 1)
					$langUrl = '/' . $lang['code'];
				else
					$langUrl = '';
				$v['url'] = SERVER_URL . CMS_URL . $langUrl . '/' . $this->module . '/' . $v['title_url'] . '.html';
			}
			return $v;
		}
		return false;
	}

	public function loadDescAdmin($id = 0, $langs = '') {
		$items = array();
		foreach ($langs as $l) {
			$q = "SELECT * FROM `" . $this->tableDesc . "` WHERE `parent_id`='" . (int) $id . "' AND `lang_id`='" . (int) $l['id'] . "' ";
			$v = Cms::$db->getRow($q);
			$v = mstripslashes($v);
			$v['lang_id'] = $l['id'];
			$v['lang_code'] = $l['code'];
			$v['lang_name'] = $l['name'];
			$items[$l['id']] = $v;
		}
		return $items;
	}

	public function addAdmin($post, $langs, $ping = 0) {
		$i = 0;
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;

		$q = "INSERT INTO " . $this->table . " SET `active`='" . $post['active'] . "', `view`='" . $post['view'] . "', `signature`='" . $post['signature'] . "', `date_add`=NOW() ";
		$id = Cms::$db->insert($q);
		mkdir($this->dir . '/' . $id, 0777);

		foreach ($langs as $v) {
			$title = clearName($post['title'][$v['id']]);
			$title_url = makeUrl($title);
			if ($i != 1) {
				$title_img = $title_url;
				$i = 1;
			}
			$desc = addslashes($post['desc'][$v['id']]);
			$desc_short = addslashes($post['desc_short'][$v['id']]);
			if (empty($desc_short))
				$desc_short = substr(strip_tags($desc), 0, 250);
			$title_url = $this->titleExists($title_url, $id, $v['id']);

			$q = "INSERT INTO " . $this->tableDesc . " SET `title`='" . $title . "', `title_url`='" . $title_url . "', `desc`='" . $desc . "', `desc_short`='" . $desc_short . "', ";
			$q.= "`tag1`='" . $post['tag1'][$v['id']] . "', `tag2`='" . $post['tag2'][$v['id']] . "', `tag3`='" . $post['tag3'][$v['id']] . "', ";
			$q.= "`parent_id`='" . $id . "', `lang_id`='" . $v['id'] . "' ";
			Cms::$db->insert($q);
		}

		Cms::$tpl->setInfo('Dodano nowy artykuł.');
		return true;
	}

	public function editAdmin($post, $langs, $ping = 0) {
		$i = 0;
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;

		$q = "UPDATE " . $this->table . " SET `active`='" . $post['active'] . "', `view`='" . $post['view'] . "', `signature`='" . $post['signature'] . "', `date_mod`=NOW() ";
		$q.= "WHERE `id`='" . (int) $post['id'] . "'";
		Cms::$db->update($q);

		$id = $post['id'];
		foreach ($langs as $v) {
			$title = clearName($post['title'][$v['id']]);
			$title_url = makeUrl($title);
			if ($i != 1) {
				$title_img = $title_url;
				$i = 1;
			}
			$desc = addslashes($post['desc'][$v['id']]);
			$desc_short = addslashes($post['desc_short'][$v['id']]);
			if (empty($desc_short))
				$desc_short = substr(strip_tags($desc), 0, 250);
			$title_url = $this->titleExists($title_url, $id, $v['id']);

			if ($this->idExists($id, $v['id'])) {
				$q = "UPDATE " . $this->tableDesc . " SET `title`='" . $title . "', `title_url`='" . $title_url . "', `desc`='" . $desc . "', `desc_short`='" . $desc_short . "', ";
				$q.= "`tag1`='" . $post['tag1'][$v['id']] . "', `tag2`='" . $post['tag2'][$v['id']] . "', `tag3`='" . $post['tag3'][$v['id']] . "' ";
				$q.= "WHERE `parent_id`='" . (int) $id . "' AND `lang_id`='" . (int) $v['id'] . "' ";
				Cms::$db->update($q);
			} else {
				$q = "INSERT INTO " . $this->tableDesc . " SET `title`='" . $title . "', `title_url`='" . $title_url . "', `desc`='" . $desc . "', `desc_short`='" . $desc_short . "', ";
				$q.= "`tag1`='" . $post['tag1'][$v['id']] . "', `tag2`='" . $post['tag2'][$v['id']] . "', `tag3`='" . $post['tag3'][$v['id']] . "', ";
				$q.= "`parent_id`='" . $id . "', `lang_id`='" . $v['id'] . "' ";
				Cms::$db->insert($q);
			}
		}

		Cms::$tpl->setInfo('Zapisano zmiany dla artykułu.');
		return true;
	}

	public function titleExists($title_url, $parent_id = 0, $lang_id = '') {
		if (empty($title_url))
			$title_url = '-';
		$temp_url = $title_url;
		$i = 0;
		do {
			$q = "SELECT `title_url` FROM `" . $this->tableDesc . "` WHERE `title_url`='" . $temp_url . "' AND `lang_id`='" . (int) $lang_id . "' ";
			if ($parent_id > 0)
				$q.= "AND `parent_id`!='" . (int) $parent_id . "' ";
			if ($row = Cms::$db->getRow($q)) {
				$i++;
				$temp_url = $title_url . '_' . $i;
			} else {
				return $temp_url;
			}
		} while ($title_url != $temp_url);
		return $temp_url;
	}

	public function idExists($parent_id = 0, $lang_id = '') {
		$q = "SELECT `parent_id` FROM `" . $this->tableDesc . "` WHERE `parent_id`='" . (int) $parent_id . "' AND `lang_id`='" . (int) $lang_id . "' ";
		if (Cms::$db->getRow($q)) {
			return true;
		}
		return false;
	}

	public function deleteAdmin($id) {
		if ($id) {
			$q = "DELETE FROM " . $this->table . " WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM " . $this->tableDesc . " WHERE `parent_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			rmdir($this->dir . '/' . $id);
			Cms::$tpl->setInfo('Wybrany element usunięto.');
			return true;
		}
		Cms::$tpl->setError('Usuwanie elementu nie powiodło się!');
		return false;
	}

	public function listFilesDir($id) {
		$dir = $this->dir . '/' . $id . '/';
		$galleryPhotos = $this->loadPhotos($id);
		$aPhotos = array();
		foreach ($galleryPhotos as $v) {
			if (is_array($v['photo'])) {
				$aPhotos[] = $v['photo']['normal'];
				$aPhotos[] = $v['photo']['small'];
			}
		}
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				$items = array();
				while (($file = readdir($dh)) !== false) {
					if (($file <> ".") && ($file <> "..")) {
						$src = $this->url . '/' . $id . '/' . $file;
						if (!in_array($src, $aPhotos)) {
							$item['id'] = md5($file);
							$item['name'] = $file;
							$item['src'] = $src;
							$item['file'] = $this->dir . '/' . $id . '/' . $file;
							$items[] = $item;
						}
					}
				}
				closedir($dh);
				return $items;
			}
		}
	}

	public function addPhotoAdmin($post) {
		$i = 0;
		$id = $post['id'];
		$gallery = $this->loadByIdAdmin($id);
		$files = $this->listFilesDir($id);
		foreach ($files as $v) {
			if (isset($post[$v['id']]) AND $post[$v['id']] == 1) {
				$idMax = $this->getMaxId();
				$idMax = $idMax['0'] + 1;
				$file = $v['file'];
				$fileName = changeFileName($v['name'], '-' . $idMax, substr($gallery['title_url'], 0, 55));
				$file_new = $this->dir . '/' . $id . '/' . $fileName;
				rename($file, $file_new);
				$orderMax = $this->getMaxOrder($id);
				$orderMax = $orderMax['0'] + 1;

				if ($this->createThumb($file_new, $this->widthS, $this->heightS)) {
					$q = "INSERT INTO " . $this->tablePhotos . " SET `parent_id`='" . $id . "', `file`='" . $fileName . "', `order`='" . $orderMax . "' ";
					Cms::$db->insert($q);
					$i++;
				}
			}
		}
		if ($i > 0) {
			Cms::$tpl->setInfo('Dodano zdjęcia do galerii.');
			return true;
		}
		Cms::$tpl->setError('Błąd dodawania zdjęć.');
		return false;
	}

	public function createThumb($filename, $width = 0, $height = 0, $append = '_s') {
		$new_image = changeFileName($filename, $append);
		list($width_orig, $height_orig, $type) = getimagesize($filename);
		$ratio_orig = $width_orig / $height_orig;
		if ($width / $height > $ratio_orig)
			$width = $height * $ratio_orig;
		else
			$height = $width / $ratio_orig;
		$image_p = imagecreatetruecolor($width, $height);
		// tutaj nalezy dorobic funkcje kadrujaca fotki

		switch ($type) {
			case '1' :  // GIF
				$image = imagecreatefromgif($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagegif($image_p, $new_image);
				imagedestroy($image_p);
				break;
			case '2' : // JPEG
				$image = imagecreatefromjpeg($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagejpeg($image_p, $new_image, 100);
				imagedestroy($image_p);
				break;
			case '3' : // PNG
				$image = imagecreatefrompng($filename);
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				imagepng($image_p, $new_image);
				imagedestroy($image_p);
				break;
			default :
				return false;
		}
		return true;
	}

	public function getMaxId() {
		$q = "SELECT MAX(`id`) FROM " . $this->tablePhotos . " ";
		return Cms::$db->max($q);
	}

	public function getMaxOrder($parent_id) {
		$q = "SELECT MAX(`order`) FROM " . $this->tablePhotos . " WHERE `parent_id`='" . $parent_id . "' ";
		return Cms::$db->max($q);
	}

	public function loadPhotoById($id) {
		if ($id > 0) {
			$q = "SELECT * FROM `" . $this->tablePhotos . "` WHERE `id`='" . (int) $id . "' ";
			return Cms::$db->getRow($q);
		}
		return false;
	}

	public function moveDownPhotoAdmin($id) {
		if ($item = $this->loadPhotoById($id)) {
			$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`-1 ";
			$q.= "WHERE `parent_id`='" . $item['parent_id'] . "' AND `order`='" . ($item['order'] + 1) . "'";
			if (Cms::$db->update($q)) {
				$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`+1 WHERE `id`='" . (int) $id . "'";
				if (Cms::$db->update($q)) {
					Cms::$tpl->setInfo('Przeniesiono element o jeden poziom niżej!');
					return true;
				}
			}
		}
		Cms::$tpl->setError('Zmiana nie powiodła się!');
		return false;
	}

	public function moveUpPhotoAdmin($id) {
		if ($item = $this->loadPhotoById($id)) {
			if ($item['order'] > 1) {
				$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`+1 ";
				$q.= "WHERE `parent_id`='" . $item['parent_id'] . "' AND `order`='" . ($item['order'] - 1) . "'";
				if (Cms::$db->update($q)) {
					$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`-1 WHERE `id`='" . (int) $id . "'";
					if (Cms::$db->update($q)) {
						Cms::$tpl->setInfo('Przeniesiono element o jeden poziom wyżej!');
						return true;
					}
				}
			}
		}
		Cms::$tpl->setError('Zmiana nie powiodła się!');
		return false;
	}

	public function editDescAdmin($post) {
		$q = "UPDATE " . $this->tablePhotos . " SET `desc`='" . $post['desc'] . "', `alt`='" . $post['alt'] . "' ";
		$q.= "WHERE `id`='" . (int) $post['photo_id'] . "'";
		if (Cms::$db->update($q)) {
			Cms::$tpl->setInfo('Zapisano zmiany dla zdjęcia.');
			return true;
		}
		Cms::$tpl->setError('Zmiana opisu nie powiodła się!');
		return false;
	}

	public function deletePhotoAdmin($id) {
		if ($item = $this->loadPhotoById($id)) {
			$file = $this->dir . '/' . $item['parent_id'] . '/' . changeFileName($item['file'], '_s');
			if (file_exists($file))
				unlink($file);
			$q = "DELETE FROM " . $this->tablePhotos . " WHERE `id`='" . (int) $item['id'] . "' ";
			Cms::$db->delete($q);
			$q = "UPDATE " . $this->tablePhotos . " SET `order`=`order`-1 WHERE `order`>'" . $item['order'] . "' AND `parent_id`='" . $item['parent_id'] . "' ";
			Cms::$db->update($q);
			// $this -> tpl -> setError('Wybrany zdjęcie usunięto.');
			return true;
		}
		Cms::$tpl->setError('Usuwanie elementu nie powiodło się!');
		return false;
	}

	public function deleteFileAdmin($file, $id) {
		if ($file AND $id > 0) {
			$file2 = $this->dir . '/' . $id . '/' . $file;
			if (file_exists($file2))
				unlink($file2);
			Cms::$tpl->setInfo('Wybrane zdjęcie usunięto.');
			return true;
		}
		Cms::$tpl->setError('Usuwanie elementu nie powiodło się!');
		return false;
	}

}
