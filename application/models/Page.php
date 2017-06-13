<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(CMS_DIR . '/application/models/mailer.php');

class Page extends BaseModel {

	public $mailer;
	public $module;
	public $table;
	public $tableDesc;

	public function __construct() {
		$this->mailer = new Mailer();
		$this->module = 'pages';
		$this->table = DB_PREFIX . 'pages';		
		$this->tableMenu = DB_PREFIX . 'menu';
	}

	public function getAll() {
		$q = "SELECT p.*,t.translatable_id, t.title, t.slug, t.content, t.seo_title, t.content_short, t.tag1, t.tag2, t.tag3, t.locale, CONCAT('" . URL ."', '/',slug, '.html') as url "
				. "FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "ORDER BY p.`id` ";

		$array = Cms::$db->getAll($q);
	
		$grouped = $this->groupByTranslation($array, 'id');

		return $grouped;
	}
	
	public function getPage($id) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT * FROM `" . $this->table ."` "
				. "WHERE `id` = '" . (int) $id . "' ";
		
		$result = Cms::$db->getRow($q);	

//		$result = mstripslashes($result);

//		if ($locale) {
//			if ($lang['default'] != 1)
//				$langUrl = '/' . $lang['code'];
//			else
//				$langUrl = '';
//			$result['url'] = SERVER_URL . CMS_URL . $langUrl . '/' . $v['title_url'] . '.html';
//		}		

		return $result;
	}	
	
	public function getTranslation($id) {
		$q = "SELECT p.*,t.translatable_id, t.title, t.slug, t.content, t.seo_title, t.content_short, t.tag1, t.tag2, t.tag3, t.locale FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "WHERE p.id = '" . (int) $id . "' ";

		$result = Cms::$db->getAll($q);			
		$result = getArrayByKey($result, 'locale');

		if ($result) {
			foreach ($result as &$entity) {
				$entity = mstripslashes($entity);
			}
		}
		
		return $result;
	}
	
	public function getById($id) {
		
		$page = $this->getPage($id);

		$result = [];		
		$result = $page;
		$result['trans'] = $this->getTranslation($id);
		
		return $result;
//		$translation = $this->getPageTranslation($id);
//		
//		$q = "SELECT p.*,t.translatable_id, t.title, t.title_url,t.content, t.content_short, t.tag1, t.tag2, t.tag3, t.locale FROM `" . $this->table . "` p "
//				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
//				. "WHERE p.id = '" . (int) $id . "' ";
//
//		$array = Cms::$db->getAll($q);
//		dump($array);
//		
//		
//		
//		$grouped = $this->groupByTranslation($array, 'id');
//
//		return $grouped;
	}	
	
	public function getMenuPages() {
		$items = array();
		
		foreach (Cms::$langs as $v) {
			$q = "SELECT p.id, t.title, t.slug FROM `" . $this->table . "` p LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id ";
			$q.= "WHERE p.menu = 1 AND t.locale='" . $v['code'] . "' ORDER BY t.title ASC ";
			$array = Cms::$db->getAll($q);
			foreach ($array as $v2) {
				$v2['title'] = stripslashes($v2['title']);
				$items[$v['code']][] = $v2;
			}
		}
		
		return $items;
	}		
	
	
	
	
	
	
	public function getById2($id) {
		$q = "SELECT p.*,t.translatable_id, t.title, t.title_url,t.content, t.content_short, t.tag1, t.tag2, t.tag3, t.locale FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "WHERE p.id = '" . (int) $id . "' ";

		$array = Cms::$db->getAll($q);
		$grouped = $this->groupByTranslation($array, 'id');

		return $grouped;
	}	
	
	public function generateSlug($titleUrl, $translatableId = 0, $locale = '') {
		if (empty($titleUrl)) {
			$titleUrl = '-';
		}
			
		$tempUrl = $titleUrl;
		$i = 0;
		
		do {
			$q = "SELECT `slug` FROM `" . $this->table . "_translation` WHERE `slug`='" . $tempUrl . "' AND `locale`='" . $locale . "' ";
			if ($translatableId) {
				$q.= "AND `translatable_id`!='" . (int) $translatableId . "' ";
			}
			
			if ($row = Cms::$db->getRow($q)) {
				$i++;
				$tempUrl = $titleUrl . '_' . $i;
			} else {
				return $tempUrl;
			}
		} while ($titleUrl != $tempUrl);
		
		return $tempUrl;
	}
	
	public function updateById($id = '', $item = '') {
		if(!$id) {
			return false;
		}
		if(!$item) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
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
	
	public function add($post) {
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;
		
		$item = array(						
			'gallery_id' => $post['gallery_id'],
			'menu'	=> '1',
			'date_add' => date('Y-m-d'),
			'active' => $post['active'],			
		);

		$id = $this->insert($this->table, $item);		
		$this->convertToTranslationData($post);				
		
		if ($id) {
			foreach ($post as $locale => $trans) {
				$title = clearName($trans['title']);
				$titleUrl = makeUrl($title);
				$content = addslashes($trans['content']);
				$contentShort = addslashes($trans['content_short']);			
				
				if (empty($contentShort)) {
					$contentShort = substr(strip_tags($content), 0, 160);	//old 250
				}

				$slug = $this->generateSlug($titleUrl, $id, $locale);

				$item = array(
					'translatable_id' => $id,					
					'title' => $title,					
					'slug' => $slug,					
					'content' => $content,
					'seo_title' => addslashes($trans['seo_title']),
					'content_short' => $contentShort,
					'tag1' => $trans['tag1'],
					'tag2' => $trans['tag2'],
					'tag3' => $trans['tag3'],
					'locale' => $locale,
				);
				
				$this->insert($this->table . '_translation', $item);
			}
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_add']);
		} else {
			Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_add']);
		}

		return $id;
	}
	
	public function edit($post) {

		$entitBeforeChanged = $this->getById($post['id']);

		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;

		$item = array(
			'active' => $post['active'],
			'gallery_id' => $post['gallery_id'],
			'date_mod' => date("Y-m-d"),
		);
		
		$id = $post['id'];
				
		$this->updateById($id, $item);
		$this->convertToTranslationData($post);

		$entities = $this->getAll();

		if ($id) {
			foreach ($post as $locale => $trans) {
				$title = clearName($trans['title']);
				$titleUrl = makeUrl($title);
				$titleUrlOld = $entitBeforeChanged['trans'][$locale]['slug'];
				$content = addslashes($trans['content']);
				$contentShort = addslashes($trans['content_short']);			

				if (empty($contentShort)) {
					$contentShort = substr(strip_tags($content), 0, 160);	//old 250
				}

				$slug = $this->generateSlug($titleUrl, $id, $locale);

				$item = array(
					'translatable_id' => $id,		
					'title' => $title,					
					'slug' => $slug,					
					'content' => $content,
					'seo_title' => addslashes($trans['seo_title']),
					'content_short' => $contentShort,
					'tag1' => $trans['tag1'],
					'tag2' => $trans['tag2'],
					'tag3' => $trans['tag3'],
					'locale' => $locale,
				);

				if ($this->existsTranslation($id, $locale, $entities)) {
					$this->updateTranslation($id, $locale, $item);					
					$q = "UPDATE `" . $this->tableMenu . "`, `" . $this->tableMenu . "_translation` SET `url`='" . $slug . "' ";
					$q.= "WHERE `" . $this->tableMenu . "`.`type`='page' AND `" . $this->tableMenu . "_translation`.`url`='" . $titleUrlOld . "'";
					Cms::$db->update($q);						
				} else {					
					$this->insert($this->table . '_translation', $item);
				}

			}
		}

		return true;
	}	
	
	public function deleteById($id) {
		if ($id) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->table . "_translation` WHERE `translatable_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$params['info'] = $GLOBALS['LANG']['info_delete'];
			
			Cms::getFlashBag()->add('info', $GLOBALS['LANG']['info_delete']);
			return true;
		}

		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_delete']);
		return false;
	}
	
	public function getBySlug($slug) {				

//		$locale = Cms::$session->get('locale') ? Cms::$session->get('locale') : LOCALE;
		$locale = Cms::$session->get('locale');

		if ($slug) {			
			$q = "SELECT p.*, t.* FROM `" . $this->table . "` p LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id ";
			$q.= "WHERE t.slug='" . $slug . "' AND t.locale = '" . $locale . "' ";
			
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['content_short'] = clearHTML($v['content_short'], 200, '');
				$v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
				$v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
				$v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
				$v['module'] = $this->module;
				$v['url'] = URL . '/' . $v['slug'] . '.html';
				
//				dump($v);
				return $v;
			}
		}
		
		return false;
	}

	public function getCurrentLocaleSlug($previousLocaleSlug) {
		
		if ($previousLocaleSlug) {			
			$q = "SELECT p.*, t.* FROM `" . $this->table . "` p LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id ";
			$q.= "WHERE t.slug='" . $previousLocaleSlug . "' ";
			
			if ($v = Cms::$db->getRow($q)) {						
				$q = "SELECT p.*, t.* FROM `" . $this->table . "` p LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id ";
				$q.= "WHERE t.translatable_id='" . $v['translatable_id'] . "' AND t.locale = '" . Cms::$session->get('locale') . "' ";				
				
				$newPage = Cms::$db->getRow($q);
				return $newPage['slug'];
			}

		}
		
		return false;
	}	
	
	
	
	
	

	public function load($url = '') {
		if ($url) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			$q.= "WHERE d.title_url='" . $url . "' AND d.lang_id='" . _ID . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['desc_short'] = clearHTML($v['desc_short'], 200, '');
				$v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
				$v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
				$v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
				$v['module'] = $this->module;
				$v['url'] = URL . '/' . $v['title_url'] . '.html';
				return $v;
			}
		}
		return false;
	}

	public function loadType($type = '') {
		if ($type) {
			$q = "SELECT a.*, d.* FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
			$q.= "WHERE a.type IN (" . $type . ") AND d.lang_id='" . _ID . "' ";
			$array = Cms::$db->getAll($q);
			$items = array();
			foreach ($array as $v) {
				$v = mstripslashes($v);
				$v['desc_short'] = substr($v['desc_short'], 0, 150);
				$v['url'] = URL . '/' . $v['title_url'] . '.html';
				$items[] = $v;
			}
			return $items;
		}
		return false;
	}

	public function loadRss() {
		$q = "SELECT d.title, d.title_url, d.desc_short, a.date_add, a.active FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.active=1 AND d.lang_id='" . _ID . "' ORDER BY a.date_add DESC ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['desc_short'] = clearHtml(stripslashes($v['desc_short']));
			$v['url'] = URL . '/' . $v['title_url'] . '.html';
			$v['category'] = $this->module;
			$items[] = $v;
		}
		return $items;
	}

	public function loadSearch($keyword) {
		$keyword = addslashes($keyword);
		$keyword2 = str_replace('ó', '&oacute;', $keyword);
		$keyword2 = str_replace('Ó', '&Oacute;', $keyword2);
		$q = "SELECT d.title, d.title_url, d.desc_short FROM `" . $this->table . "` a LEFT JOIN `" . $this->tableDesc . "` d ON a.id=d.parent_id ";
		$q.= "WHERE a.active=1 AND d.lang_id='" . _ID . "' AND ";
		$q.= "(d.title LIKE '%" . $keyword . "%' OR d.desc LIKE '%" . $keyword2 . "%' OR d.desc_short LIKE '%" . $keyword . "%') ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v['title'] = stripslashes($v['title']);
			$v['desc_short'] = clearHTML($v['desc_short'], 350, '...');
			$v['desc_short'] = str_replace($keyword, '<strong>' . $keyword . '</strong>', $v['desc_short']);
			$v['url'] = URL . '/' . $v['title_url'] . '.html';
			$items[] = $v;
		}
		return $items;
	}
    
	public function getByTitle($title = '') {
		$locale = Cms::$session->get('locale');
		
		if ($title) {
			$q = "SELECT p.*, t.* FROM `" . $this->table . "` p LEFT JOIN `" . $this->table . "_translation` t ON p.id=t.translatable_id ";
			$q.= "WHERE t.title='" . $title . "' AND t.locale='" . $locale . "' ";
			if ($v = Cms::$db->getRow($q)) {
				$v = mstripslashes($v);
				$v['content_short'] = clearHTML($v['content_short'], 200, '');
				$v['tag1_url'] = str_replace(' ', '-', $v['tag1']);
				$v['tag2_url'] = str_replace(' ', '-', $v['tag2']);
				$v['tag3_url'] = str_replace(' ', '-', $v['tag3']);
				$v['module'] = $this->module;
				$v['url'] = URL . '/' . $v['slug'] . '.html';
                
				return $v;
			}
            
            
		}
		return false;
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
			$v['url'] = URL . '/' . $v['title_url'] . '.html';
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
				$v['url'] = SERVER_URL . CMS_URL . $langUrl . '/' . $v['title_url'] . '.html';
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
			if ($v = Cms::$db->getRow($q)) {

				$v = mstripslashes($v);
			} else {

				$v['title'] = '';
				$v['desc_short'] = '';
				$v['desc'] = '';
				$v['tag1'] = '';
				$v['tag2'] = '';
				$v['tag3'] = '';
			}

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

		$q = "INSERT INTO " . $this->table . " SET `active`='" . $post['active'] . "', `gallery_id`='" . $post['gallery_id'] . "', `menu`='1', `date_add`=NOW() ";
		$id = Cms::$db->insert($q);

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
		return $id;
	}

	public function editAdmin($post, $langs, $ping = 0) {
		$i = 0;
		$post['active'] = isset($post['active']) ? $post['active'] : 0;
		$post['gallery_id'] = isset($post['gallery_id']) ? $post['gallery_id'] : 0;

		$q = "UPDATE " . $this->table . " SET `active`='" . $post['active'] . "', `gallery_id`='" . $post['gallery_id'] . "', `date_mod`=NOW() ";
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

		$q = "UPDATE `" . $this->tableMenu . "`, `" . $this->tableMenu . "_desc` SET `url`='" . $title_url . "' ";
		$q.= "WHERE `" . $this->tableMenu . "`.`type`='page' AND `" . $this->tableMenu . "_desc`.`url`='" . $post['title_url_old'] . "'";
		Cms::$db->update($q);

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

	function deleteAdmin($id) {
		if ($id) {
			$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			$q = "DELETE FROM `" . $this->tableDesc . "` WHERE `parent_id`='" . (int) $id . "' ";
			Cms::$db->delete($q);
			return true;
		}

		return false;
	}	
	
	public function getPage2($id, $locale = LOCALE) {
		if (!$id) {
			return false;
		}
		
		$q = "SELECT p.*,t.translatable_id, t.title, t.title_url,t.content, t.content_short, t.tag1, t.tag2, t.tag3, t.locale FROM `" . $this->table . "` p "
				. "LEFT JOIN `" . $this->table . "_translation` t ON p.id = t.translatable_id "
				. "WHERE p.id = '" . (int) $id . "' AND t.locale='" . $locale . "' ";

		$result = Cms::$db->getRow($q);	
		$result = mstripslashes($result);

//		if ($locale) {
//			if ($lang['default'] != 1)
//				$langUrl = '/' . $lang['code'];
//			else
//				$langUrl = '';
//			$result['url'] = SERVER_URL . CMS_URL . $langUrl . '/' . $v['title_url'] . '.html';
//		}		

		return $result;
	}	

}
