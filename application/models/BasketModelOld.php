<?php

//namespace Models;


if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(CONTROL_DIR . '/classes/Transport.php');
require_once(MODEL_DIR . '/UnitTransportUnit.php');
//require_once(MODEL_DIR . '/ShoppingThresholds.php');
//require_once(CLASS_DIR . '/ShoppingThresholds.php');
//use System\Core\BaseModel;
//use Application\ShoppingThresholds;


class BasketModel extends BaseModel {

	public $table;
	public $customer_id;

	public function __construct() {
		$this->table = DB_PREFIX . 'basket';
		$this->customer_id = isset($_SESSION[CUSTOMER_CODE]['id']) ? $_SESSION[CUSTOMER_CODE]['id'] : 0;
	}

	public function __destruct() {
		
	}

	public function getAll() {
		return $this->select($this->table);
	}
	
	public function getById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["session_id" => session_id(), "id" => $id]);
		return $this->select($this->table, $where);
	}
	
	public function getBySessionCustomer() {
		$where = $this->where(["session_id" => session_id(), "customer_id" => $this->customer_id]);
		return $this->select($this->table, $where);
	}
	
	public function getBySession() {
		$where = $this->where(["session_id" => session_id()]);
		return $this->select($this->table, $where);
	}
	
	public function getByProductIdVariationId($product_id = '', $variation_id = '') {
		if(!$product_id OR !$variation_id) {
			return false;
		}
		$where = $this->where(["session_id" => session_id(), "customer_id" => $this->customer_id, "product_id" => $product_id, "variation_id" => $variation_id]);
		return $this->select($this->table, $where);
	}
	
	public function set($item = '') {		
		if(!$item) {
			return false;
		}
		$item['session_id'] = session_id();
		$item['customer_id'] = $this->customer_id;
		return $this->insert($this->table, $item);
	}
	
	public function updateById($id = '', $item = '') {
		if(!$id OR !$item) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->update($this->table, $where, $item);
	}
	
	public function deleteById($id = '') {
		if(!$id) {
			return false;
		}
		$where = $this->where(["id" => $id]);
		return $this->delete($this->table, $where);
	}
	
	public function decoratorItems($items = '') {
		if(!$items) {
			return false;
		}
        
		foreach ($items as &$v) {            
            $v['price_gross'] = formatPrice($v['price'], $v['tax']);		
			$v['sum'] = formatPrice($v['qty'] * $v['price_gross']);                   
		}
		return $items;
	}
	
	public function getSummary($items = '', $delivery = 0) {		
		$item = ["qty" => 0, "weight" => 0, "sum" => 0, "delivery" => 0, "total" => 0];		
		if($items) {
			foreach ($items as $v) {
				$item['qty'] += $v['qty'];
				$item['weight'] += $v['weight'] * $v['qty'];
				$item['sum'] += $v['sum'];
			}
		}
        
		$item['sum'] = formatPrice($item['sum']);
		$item['delivery'] = formatPrice($delivery);
		$item['total'] = formatPrice($item['sum'] + $delivery);
		return $item;
	}
    
    
	public function getBasketItems() {
        $productModel = new Products();
        
		if($items = $this->getBySessionCustomer()) {
			$items = $this->decoratorItems($items);
            
			foreach($items as &$v) {
				$product = $productModel->getById($v['product_id'], $v['variation_id']);
				$v['available'] = $product['qty'];
				$v['sku'] = $product['sku'];
				$v['price_purchase'] = $product['price_purchase'];
				$v['url'] = $product['url'];
				if(isset($product['photo']['small'])) $v['image'] = $product['photo']['small']; 
                $v['mega_offer'] = $product['mega_offer'];   
				
				if (Cms::$modules['unit_transport']) {
					$v['length'] = $product['length'];             
					$v['width'] = $product['width'];             
					$v['height'] = $product['height'];             
					$v['transport_group_id'] = $product['transport_group_id'];  			
					$v['transport_unit_id'] = $product['transport_unit_id'];  			
				}          
			}
			return $items;
		}
		return false;
	}

	public function getDelivery($optionId = 0) {
		$basket = $this->getBasketItems();				
		$summary = $this->getSummary($basket);
		
		$delivery = array(
			'price_gross' => 0
		);
		
		if (Cms::$conf['basket_free_delivery_from'] && $summary['sum'] > Cms::$conf['basket_free_delivery_from']) {
			return $delivery;
		}		
		
		if (Cms::$modules['unit_transport']) {			
			$deliveryPrice = $this->calculateUnitTransportDelivery($basket);
			
			$delivery = array(
				'price_gross' => $deliveryPrice
			);
		
			return $delivery;
		}
		
		$transport = new TransportController();		
		$delivery = $transport->getServiceOptionById($optionId);
		
		return $delivery;		
	}
	
	public function getDeliveryPrice($optionId = 0) {
		$basket = $this->getBasketItems();				
		$summary = $this->getSummary($basket);
				
		if (Cms::$conf['basket_free_delivery_from'] && $summary['sum'] > Cms::$conf['basket_free_delivery_from']) {
			return 0;
		}		
		
		if (Cms::$modules['unit_transport']) {			
			$deliveryPrice = $this->calculateUnitTransportDelivery($basket);
			return $deliveryPrice;
		}
		
		$transport = new TransportController();
		
		$deliveryOption = $transport->getServiceOptionById($optionId);
		$deliveryPrice = isset($deliveryOption['price_gross']) ? $deliveryOption['price_gross'] : 0;	
		
		return $deliveryPrice;		
	}
	
	protected function areAllProductsAdvertaising($basket, $advertisingGroupId = 0) {
		if (!$basket) {
			return false;
		}
		
		if (!$advertisingGroupId) {
			return false;
		}
		
		foreach ($basket as $entry) {
			if ($entry['transport_group_id'] != $advertisingGroupId) {
				return false;
			}
		}	
		
		return true;
	}
	
	public function calculateUnitTransportDelivery($basket) {	
		$unitTransportUnit = new UnitTransportUnit();
		$unitTransportUnits = $unitTransportUnit->getAll();
		$units = getArrayByKey($unitTransportUnits, 'id');
		
		$unitTransportGroup = new UnitTransportGroup();		
		$advertisingGroupId = $unitTransportGroup->getBy(['is_advertaising_material' => 1])[0]['id'];
		$areAllProductsAdvertaising = $this->areAllProductsAdvertaising($basket, $advertisingGroupId);
//        dump($units);
        
		if (!$basket) {
			return 0;
		}
		
//		dump($basket);
		
		$sortedBasket = [];		
		$advertaisingMaterials = [];
		foreach ($basket as $key => &$entry) {			
			if (!$areAllProductsAdvertaising && $entry['transport_group_id'] == $advertisingGroupId) {
				$advertaisingMaterials[] = $entry;
				continue;
			}
			
			$sortedBasket[$entry['transport_unit_id']][$key] = $entry;
		}
		
//		dump($sortedBasket, 'others');
//		dump($advertaisingMaterials, 'advert');
		
        $result = [];
		if ($sortedBasket) {
			foreach ($sortedBasket as $unitTransport => $items) {
				$result[$unitTransport] = $this->calculateUnitNumbers($unitTransport, $items, $units, $advertaisingMaterials);
			}
		}
		
		$deliveryPrice = 0;

		foreach ($result as $key => $num) {
			$deliveryPrice += $num * $units[$key]['price'];
		}

		return $deliveryPrice;
	}
    
	
    protected function calculateUnitNumbers($unitTransport, array $items, $units, &$advertaisingMaterials = []) {
        $unit = $units[$unitTransport];
        $unitVolume = $unit['length'] * $unit['width'] * $unit['height']; //objetosc jednostki
		$maxUnitWeight = 30000; // 30kg

		$flatItems = [];
		foreach ($items as $item) {
			if ($item['qty'] > 1) {
				for ($i = 1; $i <= $item['qty']; $i++) {
					$flatItems[] = $item;
				}
			} else {
				$flatItems[] = $item;
			}
		}
		
		foreach ($flatItems as &$item) {
			$item['qty'] = 1;
		}

		$usedUnits = [];
		
		foreach ($flatItems as $key => &$item) {
			$wasSpace = false;
			$itemVolume = $item['length'] * $item['width'] * $item['height'];   //objetosc produktu

//			echo '<br />item: ' . $item['desc'] . '<br />';
//			echo 'objetosc itemVolume: ' .$itemVolume . '<br />';

			$params = array(
				'volume' => $itemVolume,
				'weight' => $item['weight']
			);
			//jezeli nie ma jednostki to tworze
			if (!$usedUnits) {
				$usedUnits[] = $params;
				unset($flatItems[$key]);	
//				dump($usedUnits, 'tu');
				continue;
			}
			
			// try put in ealier created units
			if ($usedUnits) {
				foreach ($usedUnits as &$usedUnit) {
					if (($usedUnit['volume'] + $itemVolume) <= $unitVolume && ($usedUnit['weight'] + $item['weight']) <= $maxUnitWeight) {
						$usedUnit['volume'] = $usedUnit['volume'] + $itemVolume;
						$usedUnit['weight'] = $usedUnit['weight'] + $item['weight'];
						unset($flatItems[$key]);
						$wasSpace = true;
						break;
					}
				}
			}
			
			if (!$wasSpace) {
				$usedUnits[] = $params;
			}

//			dump($usedUnits);
		}	
		

//		dump($usedUnits, 'before materials');
		//try put in ealier created units advertising materials
		if ($advertaisingMaterials) {
			foreach ($advertaisingMaterials as $key2 => $adv) {
				$wasSpace2 = false;
				$advVolume = $adv['length'] * $adv['width'] * $adv['height'];
//				echo 'objetosc $advVolume: ' .$advVolume . '<br />';
				$params2 = array(
					'volume' => $advVolume,
					'weight' => $adv['weight']
				);				
				
				if ($usedUnits) {
					foreach ($usedUnits as &$usedUnit) {
						if ($usedUnit['volume'] + $advVolume <= $unitVolume && ($usedUnit['weight'] + $adv['weight']) <= $maxUnitWeight) {
							$usedUnit['volume'] = $usedUnit['volume'] + $advVolume;
							$usedUnit['weight'] = $usedUnit['weight'] + $adv['weight'];
							unset($advertaisingMaterials[$key2]);
							$wasSpace2 = true;
							break;							
						}
					}
				}					
			}
			
			if (!$wasSpace2) {
				$usedUnits[] = $params2;
			}			
		}

		dump($usedUnits);

		return count($usedUnits);		
    }

}
