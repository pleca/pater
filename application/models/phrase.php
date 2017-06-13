<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');

class Phrase extends BaseModel {

	public function __construct() {
		$this->table = DB_PREFIX . 'frazy_promocyjne';
		$this->tableUzycia = DB_PREFIX . 'frazy_promocyjne_uzycia';
	}

	public function __destruct() {
		
	}

	public function getAll() {
		return $this->select($this->table);
	} 
    
	public function loadAdmin() {
		$q = "SELECT * FROM `" . $this->table . "` ORDER BY `fraza` ";
		$array = Cms::$db->getAll($q);
		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			$items[] = $v;
		}
		return $items;
	}

    public function existsPhrase($phrase) {
        $q = "SELECT `id` FROM `" . $this->table . "` WHERE `fraza` = '" . $phrase . "' ";

        return Cms::$db->getAll($q);
    }

    public function loadUzycia() {   

		$q = "SELECT u.*, f.fraza, f.data_od, f.data_do, o.discount, o.price as sum, (SELECT `login` FROM `" . DB_PREFIX . "customer` WHERE `id`=u.id_user LIMIT 1) as login, ";
//		$q.= "(SELECT `name` FROM `" . DB_PREFIX . "order_status` WHERE `id`=o.status_id AND `lang_id`=" . _ID . " LIMIT 1) as status ";
		$q.= "(SELECT `name` FROM `" . DB_PREFIX . "order_status_translation` WHERE `translatable_id` = o.status_id AND `locale` = '" . LOCALE . "' LIMIT 1) as status ";
		$q.= "FROM `" . $this->tableUzycia . "` u LEFT JOIN `" . $this->table . "` f ON f.id=u.id_frazy ";
		$q.= "LEFT JOIN `" . DB_PREFIX . "order` o ON o.id=u.id_zam ORDER BY u.id DESC ";

		$array = Cms::$db->getAll($q);

		$items = array();
		foreach ($array as $v) {
			$v = mstripslashes($v);
			if ($v['sum']) {
				$v['discount_val'] = formatPrice($v['sum'] * $v['discount'] / 100);
				$v['sum'] = formatPrice($v['sum']);
			}
			$items[] = $v;
		}
		return $items;
	}

	function add($post) {
		$post = maddslashes($post);
        
        if ($this->existsPhrase($post['fraza'])) {
            Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_phrase_exists']);
            return false;
        }
        
		$q = "INSERT INTO `" . $this->table . "` SET `fraza`='" . $post['fraza'] . "', `wartosc`='" . $post['wartosc'] . "', `data_od`='" . $post['data_od'] . "', `data_do`='" . $post['data_do'] . "', ";
		$q.= "`max_uzyc`='" . $post['max_uzyc'] . "', `klient_uzyc`='" . $post['klient_uzyc'] . "' ";
        
        try {
            Cms::$db->insert($q);
        } catch (Exception $e) {
//            echo 'Caught exception: '.  $e->getMessage(). "\n";
            return false;
        }
        
		return true;
	}

    public function getById($id) {
        $q = "SELECT * FROM `" . $this->table . "` WHERE `id` = '" . $id . "' ";
        
        return Cms::$db->getRow($q);
    }
    
	function edit($post) {
		$post = maddslashes($post);
        //hack for form inside table
        $post['klient_uzyc'] = $post['klient_uzyc_h'];
        
        $entity = $this->getById($post['id']);
                
        if ($entity['fraza'] != $post['fraza'] && $this->existsPhrase($post['fraza'])) {
            Cms::getFlashBag()->add('error', $GLOBALS['LANG']['error_phrase_exists']);
            return false;
        }
        
		$q = "UPDATE `" . $this->table . "` SET `fraza`='" . $post['fraza'] . "', `wartosc`='" . $post['wartosc'] . "', `data_od`='" . $post['data_od'] . "', `data_do`='" . $post['data_do'] . "', ";
		$q.= "`max_uzyc`='" . $post['max_uzyc'] . "', `klient_uzyc`='" . $post['klient_uzyc'] . "' WHERE `id`='" . (int) $post['id'] . "' ";
		Cms::$db->update($q);
		return true;
	}

	function deleteOld($id) {
		$q = "DELETE FROM `" . $this->table . "` WHERE `id`='" . (int) $id . "' ";
		Cms::$db->delete($q);	
		return true;
	}

	function getDiscount($promotion_code) {       
		$now = date('Y-m-d');
		$q = "SELECT `id`, `wartosc`, `klient_uzyc` FROM `" . $this->table . "` WHERE `fraza`='" . addslashes($promotion_code) . "' AND `max_uzyc`>`uzyto` AND `data_od`<='" . $now . "' AND `data_do`>='" . $now . "' ";                    
        
		if ($item = Cms::$db->getRow($q)) {                        
        
            if (!isset($_SESSION[CUSTOMER_CODE]['id']) && $item['klient_uzyc'] == 1) {
                Cms::getFlashBag()->add('error', $GLOBALS['LANG']['order_phrase3']);
                return 0;                
            }
        
			if ($item['klient_uzyc'] == 1 && isset($_SESSION[CUSTOMER_CODE]['id'])) {
				$q = "SELECT p.id_frazy FROM `" . $this->tableUzycia . "` p LEFT JOIN `" . DB_PREFIX . "order` o ON p.id_zam=o.id ";				                
                $q.= "WHERE p.id_frazy='" . $item['id'] . "' AND p.id_user='" . $_SESSION[CUSTOMER_CODE]['id'] . "' AND (o.status_id='2' OR o.status_id='3') ";

				if (Cms::$db->getRow($q)) {
					Cms::getFlashBag()->add('error', $GLOBALS['LANG']['order_phrase']);
					return false;
				}
			}
            
//            if ($item['klient_uzyc'] == 1 && !isset($_SESSION[CUSTOMER_CODE]['id'])) {
//				Cms::getFlashBag()->add('error', $GLOBALS['LANG']['order_phrase2']);
//                return 0;
//            }
            
			return $item['wartosc'];
		}               
        
		Cms::getFlashBag()->add('error', $GLOBALS['LANG']['order_phrase2']);
		return false;
	}

	function usePhrase($promotion_code, $order_id, $customer_id = 0) {
		$now = date('Y-m-d');
		$q = "SELECT `id` FROM `" . $this->table . "` WHERE `fraza`='" . addslashes($promotion_code) . "' ";        
        
		if ($item = Cms::$db->getRow($q)) {
			$q = "INSERT INTO " . $this->tableUzycia . " SET `id_frazy`='" . (int) $item['id'] . "', `id_zam`='" . (int) $order_id . "', `id_user`='" . $customer_id . "', `data`='" . $now . "' ";
			Cms::$db->insert($q);
			$q = "UPDATE " . $this->table . " SET `uzyto`=`uzyto`+1 WHERE `id`='" . (int) $item['id'] . "' LIMIT 1 ";
			return Cms::$db->update($q);
		}
		return false;
	}

}
