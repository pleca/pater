<?php

//namespace Models;


if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(SYS_DIR . '/core/BaseModel.php');
require_once(MODEL_DIR . '/shopProducts.php');
require_once(CONTROL_DIR . '/classes/Transport.php');
require_once(MODEL_DIR . '/UnitTransportUnit.php');
require_once(MODEL_DIR . '/UnitTransportGroupUnit.php');
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
        $this->product = new Products();
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
//		$where = $this->where(["session_id" => session_id(), "id" => $id]);
		$where = $this->where(["id" => $id]);
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
    
	public function getByCustomer() {
		$where = $this->where(["customer_id" => $this->customer_id]);
		return $this->select($this->table, $where);
	}    
    
	public function getByCustomerOrSession() {

        if ($this->customer_id) {
            $where = "(`customer_id`='0' AND `session_id`='" . session_id() . "') OR `customer_id`='" . $this->customer_id . "'";
        } else {
            $where = "(`customer_id`='0' AND `session_id`='" . session_id() . "')";
        }

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
			$v['sum_netto'] = formatPrice($v['qty'] * $v['price']);                   
		}
		return $items;
	}
	
	public function getSummary($items = '', $delivery = 0) {		
		$item = ["qty" => 0, "weight" => 0, "sum" => 0, "sum_netto" => 0, "delivery" => 0, "total" => 0];		
		if($items) {
			foreach ($items as $v) {
				$item['qty'] += $v['qty'];
				$item['weight'] += $v['weight'] * $v['qty'];
				$item['sum'] += $v['sum'];
				$item['sum_netto'] += $v['sum_netto'];
			}
		}
        
		$item['sum'] = formatPrice($item['sum']);
		$item['sum_netto'] = formatPrice($item['sum_netto']);
		$item['delivery'] = formatPrice($delivery);
		$item['total'] = formatPrice($item['sum'] + $delivery);
		$item['total_netto'] = formatPrice($item['sum_netto'] + $delivery);
		return $item;
	}
    
    
	public function getBasketItems() {
        $productModel = new Products();
        
		if($items = $this->getByCustomerOrSession()) {
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

	public function getDelivery($optionId = 0, $paymentId = 0) 
    {
        $delivery = [];
        
        $priceGross = 0;

		$basket = $this->getBasketItems();				
		$summary = $this->getSummary($basket);		

		$transport = new TransportController();		
		$delivery = $transport->getServiceOptionById($optionId);        
        
		if ($this->isFreeDelivery($basket, $paymentId)) {
            $delivery['price_gross'] = $priceGross;
            $delivery['price'] = $priceGross;
			return $delivery;
		}	
		
		if (Cms::$modules['unit_transport']) {			
			$deliveryPrice = $this->calculateUnitTransportDelivery($basket);			
            $delivery['price_gross'] = $deliveryPrice;

			return $delivery;
		}

		return $delivery;		
	}
	
	protected function isFreeDelivery($basket, $paymentId = 0) {		
		
		if (Cms::$modules['unit_transport']) {
			$unitTransportGroup = new UnitTransportGroup();
			$unitTransportGroups = $unitTransportGroup->getBy(['is_excluded_from_free_delivery' => 1]);
			$unitTransportGroups = getArrayByKey($unitTransportGroups, 'id');
			
			if (is_array($unitTransportGroups)) {
				$excludedFromFreeDelivery = array_keys($unitTransportGroups);


				if ($basket) {
					foreach ($basket as $item) {
						if (in_array($item['transport_group_id'], $excludedFromFreeDelivery)) {
							return false;
						}
					}
				}
			}
		}

		$summary = $this->getSummary($basket);
		
		if (Cms::$conf['basket_free_delivery_from'] && $summary['sum'] > Cms::$conf['basket_free_delivery_from']
				|| $paymentId == 6) {
			return true;
		}		
		
		return false;
	}
	
	public function getDeliveryPrice($optionId = 0, $paymentId = 0) {
		$basket = $this->getBasketItems();				
		
		if ($this->isFreeDelivery($basket, $paymentId)) {
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
	
	protected function areAllProductsAdvertaising($basket) {
		if (!$basket) {
			return false;
		}
		
		$unitTransportGroup = new UnitTransportGroup();	
		$advertisingGroupId = $unitTransportGroup->getBy(['is_advertaising_material' => 1])[0]['id'];		
		
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
	
	public function sortBasketByGroupAndUnit($basket) {
		
		$sortedBasket = [];	
		
		$unitTransportGroup = new UnitTransportGroup();	
		$advertisingGroupId = $unitTransportGroup->getBy(['is_advertaising_material' => 1])[0]['id'];
		$areAllProductsAdvertaising = $this->areAllProductsAdvertaising($basket, $advertisingGroupId);
		
		foreach ($basket as $key => &$entry) {			
			if (!$areAllProductsAdvertaising && $entry['transport_group_id'] == $advertisingGroupId) {
				$advertaisingMaterials[] = $entry;
				continue;
			}

			$sortedBasket[$entry['transport_group_id']][$entry['transport_unit_id']][] = $entry;
		}

		return $sortedBasket;
	}
	
	public function getAdvertisingMaterials($basket) {
		
		$advertaisingMaterials = [];	
		
		$unitTransportGroup = new UnitTransportGroup();	
		$advertisingGroupId = $unitTransportGroup->getBy(['is_advertaising_material' => 1])[0]['id'];
		
		foreach ($basket as $key => &$entry) {			
			if ($entry['transport_group_id'] == $advertisingGroupId) {
				$advertaisingMaterials[] = $entry;
			}
		}
		
		$advertaisingMaterials = $this->flatedItems($advertaisingMaterials);

		return $advertaisingMaterials;
	}	
	
	/*
	 * if item in basket has qty > 1
	 * then create another record for it
	 */
	public function flatedItems($items) {
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
		
		return $flatItems;
	}
	
	protected function getBiggerThenUnit($unit, $usedUnits) {
		$unitTransportGroupUnit = new UnitTransportGroupUnit;	
		
		if ($usedUnits[$unit['transport_group_id']]) {
			foreach ($usedUnits[$unit['transport_group_id']] as $key => $value) {
				$acceptableUnits[] = $key;
			}
		}
		
		$units = $unitTransportGroupUnit->findAll(['group_id' => $unit['transport_group_id'], 'unit_id' => $acceptableUnits]);
		
		$currentUnitVolume = $unit['length'] * $unit['width'] * $unit['height'];
		
		$biggerUnits = [];
		
		foreach ($units as $row) {
			$unitVolume = $row['length'] * $row['width'] * $row['height'];
			if ($unitVolume > $currentUnitVolume) {
				$biggerUnits[] = $row;
			}
			
		}

		return $biggerUnits;
	}
	
	protected function putAllInBiggerUnit($unit, &$items, &$usedUnits) {
		echo 'sprawdzam wiele..<br />';
		dump($usedUnits);
		$biggerUnits = $this->getBiggerThenUnit($unit, $usedUnits);
		$biggerUnits = $this->getPartlyUsed($biggerUnits, $usedUnits);
		$maxUnitWeight = 30000; //30kg

		$itemsVolume = 0;
		$itemsWeight = 0;
		
		dump($items);
		foreach ($items as $item) {
			$itemsVolume += $item['length'] * $item['width'] * $item['height'];
			$itemsWeight += $item['weight'];
		}
		
		if ($biggerUnits) {
			foreach ($biggerUnits as $biggerUnit) {
				$unitVolume = $biggerUnit['length'] * $biggerUnit['width'] * $biggerUnit['height'];
				
				if ($usedUnits[$biggerUnit['transport_group_id']][$biggerUnit['unit_id']]) {
					echo 'sa rozpoczate...<br />';
					dump($biggerUnit['transport_group_id']);
					dump($biggerUnit['unit_id']);
					foreach ($usedUnits[$biggerUnit['transport_group_id']][$biggerUnit['unit_id']] as $key => $row) {
						
						echo 'unitVolume: ' . $unitVolume .' <br />';
						echo 'rowVolume: ' . $row['volume'] .' <br />';						
						echo 'itemsVolume: ' . $itemsVolume .' <br />';						
						
						if (($row['volume'] + $itemsVolume) <= $unitVolume && ($row['weight'] + $itemsWeight) <= $maxUnitWeight) {
							$params['volume'] = $row['volume'] + $itemsVolume;
//							$params['weight'] = $row['weight'] + $itemsWeight;
							foreach ($items as $key2 => $item) {
								unset($items[$key2]);
							}

							$usedUnits[$biggerUnit['transport_group_id']][$biggerUnit['unit_id']][$key] = $params;
							echo 'udalo sie spakowac do wiekszej jendostki<br />';
							return true;
						} else {

							echo 'nie udalo sie spakowac...';
						}
						
						
						
					}
				} else {
					echo 'brak rozpoaczetych...<br />';
				}
			}
		}

		return false;
	}		

	protected function getItemsByGroup($groupId, $basket) {
		$items = [];
		
		foreach ($basket as $item) {
			if ($item['transport_group_id'] == $groupId) {
				$items[] = $item;
			}
		}
		
		return $items;
	}

	
	
	
	protected function initUsedUnits($sortedBasket) {
		if (!$sortedBasket) {
			return false;
		}

		$usedUnits = [];
		foreach ($sortedBasket as $groupId => $units) {	
			foreach ($units as $unitId => $unit) {
				$usedUnits[$groupId][$unitId] = [];
			}
		}		
		
		return $usedUnits;
	}	
	
	protected function packItemsWithSameUnit($items, &$usedUnits) {
		$groupId = $items[0]['transport_group_id'];
		$unitId = $items[0]['transport_unit_id'];
		
		$unitTransportUnit = new UnitTransportUnit();
		$unitTransportUnits = $unitTransportUnit->getAll();
		$units = getArrayByKey($unitTransportUnits, 'id');
		$unit = $units[$unitId];
		$unit['transport_group_id'] = $groupId;
				
		$items = $this->flatedItems($items);
		$unitVolume = (float) $unit['length'] * $unit['width'] * $unit['height'];   //objetosc jednostki

		foreach ($items as $key => &$item) {			
			$itemVolume = (float) $item['length'] * $item['width'] * $item['height'];   //objetosc produktu

			$params = array(
				'volume' => $itemVolume,
				'weight' => $item['weight'],
				'is_full' => 0
			);

			$unitPackages = &$usedUnits[$groupId][$unitId];

			end($unitPackages);
			$lastUnitPackage = &$unitPackages[key($unitPackages)];

			if ($lastUnitPackage) {
				$expectedVolume = (float) $lastUnitPackage['volume'] + $itemVolume;	
				
				if (($expectedVolume < $unitVolume || compareFloats($expectedVolume, $unitVolume)) && ($lastUnitPackage['weight'] + $item['weight']) <= UnitTransportUnit::MAX_WEIGHT) {
					$lastUnitPackage['volume'] = $expectedVolume;
					$lastUnitPackage['weight'] = $lastUnitPackage['weight'] + $item['weight'];
					$lastUnitPackage['items'][] = $item;
					
					if (($lastUnitPackage['volume'] + $itemVolume < $unitVolume || compareFloats($lastUnitPackage['volume'] + $itemVolume, $unitVolume))  && ($lastUnitPackage['weight'] + $item['weight']) <= UnitTransportUnit::MAX_WEIGHT) {
					} else {
						$lastUnitPackage['is_full'] = 1;
					}
					
				} else {

					$lastUnitPackage['is_full'] = 1;
					$params['items'][] = $item;
					$unitPackages[] = $params;					
				}			
			} else {
				$params['items'][] = $item;
				$unitPackages[] = $params;
			}
//			dump($usedUnits, 'used');
		}
//dump($usedUnits, 'used');
	}

	
	protected function getBiggestUnitForItems($items) {
		if (!$items) {
			return false;
		}
		
		$unitIds = [];
		
		foreach ($items as $item) {
			$unitIds[] = $item['transport_unit_id'];
		}		
		
		$unitTransportUnit = new UnitTransportUnit();
		$unitTransportUnits = $unitTransportUnit->getAll();
		$units = getArrayByKey($unitTransportUnits, 'id');

		$calculatedUnits = [];
		foreach ($unitIds as $unitId) {
			$unit = $units[$unitId];
			$calculatedUnits[$unitId] = $unit;
			$calculatedUnits[$unitId]['volume'] = (float) $unit['length'] * $unit['width'] * $unit['height'];  
		}
		
		$orderedCalculatedUnits = arrayOrderByKey($calculatedUnits, 'volume', SORT_DESC); 

		return reset($orderedCalculatedUnits);		
	}
	
	protected function getRemains($usedUnits, $currentGroupId) {
		$items = [];
		$remainsToRemove = [];
				
		foreach ($usedUnits as $groupId => $usedUnit) {
			
			if ($groupId == $currentGroupId) {
				foreach ($usedUnit as $unitId => $units) {
					$units = array_filter($units);
					foreach ($units as $key => $unit) {
						if (isset($unit['is_full']) && !$unit['is_full']) {
							
							$unitItems = $unit['items'];
							foreach ($unitItems as $key2 => &$item) {
								$item['pack_key'] = $key;
								$item['item_key'] = $key2;
							}
							
							$items = array_merge($items, $unitItems);

							$remainsToRemove[] = array(
								'groupId' => $groupId,
								'unitId' => $unitId,
								'key' => $key,
							);

						}					
					}
				}
			}
			
		}
		
		$result = array(
			'items' => $items,
			'remainsToRemove' => $remainsToRemove
		);
		
		return $result;
	}	
			
	protected function putRemains(&$usedUnits, $currentGroupId) {
		$remains = $this->getRemains($usedUnits, $currentGroupId);

		$items = $remains['items'];
		$biggestUnit = $this->getBiggestUnitForItems($items);
		$biggestUnitVolume = $biggestUnit['volume'];
		$maxItemVolume = 0;
		$maxItemWeight = 0;		
		$packedItems = [];

		$numItems = count($items);
		$i = 1;
		
		foreach ($items as $key => $item) {
			
			$maxItemVolume += (float) $item['length'] * $item['width'] * $item['height'];
			$maxItemWeight += $item['weight'];	
				
			if (($maxItemVolume < $biggestUnitVolume || compareFloats($maxItemVolume, $biggestUnitVolume)) && $maxItemWeight <= UnitTransportUnit::MAX_WEIGHT) {
				$packedItems[] = $item;
				
				if ($i == $numItems) {
					if (count($packedItems) > 1) {
						$maxItemVolume -= (float) $item['length'] * $item['width'] * $item['height'];
						$maxItemWeight -= $item['weight'];					

						$newEntry = array(
							'volume' => $maxItemVolume,
							'weight' => $maxItemWeight,
							'items' => $packedItems,
						);

						$usedUnits[$currentGroupId][$biggestUnit['id']][] = $newEntry;

						foreach ($packedItems as $row) {
							unset($usedUnits[$row['transport_group_id']][$row['transport_unit_id']][$row['pack_key']]['items'][$row['item_key']]);
							if (!$usedUnits[$row['transport_group_id']][$row['transport_unit_id']][$row['pack_key']]['items']) {
								unset($usedUnits[$row['transport_group_id']][$row['transport_unit_id']][$row['pack_key']]);
							}							
						}
						
						$packedItems = [];
					}
				}
				
			} else {

				if (count($packedItems) > 1) {

					$maxItemVolume -= (float) $item['length'] * $item['width'] * $item['height'];
					$maxItemWeight -= $item['weight'];					

					$newEntry = array(
						'volume' => $maxItemVolume,
						'weight' => $maxItemWeight,
						'items' => $packedItems,
					);

					$usedUnits[$currentGroupId][$biggestUnit['id']][] = $newEntry;

					foreach ($packedItems as $row) {
//						echo 'group'.  $row['transport_group_id'] .' unit: ' . $row['transport_unit_id'] . 'pack_key: ' .$row['pack_key'] .'item key: ' .$row['item_key'] .'<br />';												
						unset($usedUnits[$row['transport_group_id']][$row['transport_unit_id']][$row['pack_key']]['items'][$row['item_key']]);												
						if (!$usedUnits[$row['transport_group_id']][$row['transport_unit_id']][$row['pack_key']]['items']) {
							unset($usedUnits[$row['transport_group_id']][$row['transport_unit_id']][$row['pack_key']]);
						}
					}

					$packedItems = [];
				}

				
			}
			
			$i++;
		}

	}
	
	/*
	 * it has to be at least 2 elements with remains to process packing
	 * otherwise element are already packed correctly
	 */
	protected function putRemainsOld(&$usedUnits, $currentGroupId) {
		$items = [];
		$remainsToRemove = [];
		foreach ($usedUnits as $groupId => $usedUnit) {
			
			if ($groupId == $currentGroupId) {
				foreach ($usedUnit as $unitId => $units) {
					$units = array_filter($units);
					foreach ($units as $key => $unit) {
						if (!$unit['is_full']) {
							$items = array_merge($items, $unit['items']);

							$remainsToRemove[] = array(
								'groupId' => $groupId,
								'unitId' => $unitId,
								'key' => $key,
							);

						}					
					}
				}
			}
			
		}

		if (count($items) <= 1) {
			return false;
		}

		$biggestUnit = $this->getBiggestUnitForItems($items);
		$biggestUnitVolume = (float) $biggestUnit['length'] * $biggestUnit['width'] * $biggestUnit['height'];

		$maxItemVolume = 0;
		$maxItemWeight = 0;
		foreach ($items as $item) {
			$maxItemVolume += (float) $item['length'] * $item['width'] * $item['height'];
			$maxItemWeight += $item['weight'];
		}
						
		if (($maxItemVolume < $biggestUnitVolume || compareFloats($maxItemVolume, $biggestUnitVolume)) && $maxItemWeight <= UnitTransportUnit::MAX_WEIGHT) {
			$newEntry = array(
				'volume' => $maxItemVolume,
				'weight' => $maxItemWeight,
				'items' => $items,
			);
			
			$usedUnits[$currentGroupId][$biggestUnit['id']][] = $newEntry;

			foreach ($remainsToRemove as $row) {
				unset($usedUnits[$row['groupId']][$row['unitId']][$row['key']]);
			}
		}	
	}
	
	protected function removeAdvertaisingMaterialsFromBasket(&$basket) {		
		$advertaisingMaterials = [];	
		
		$unitTransportGroup = new UnitTransportGroup();	
		$advertisingGroupId = $unitTransportGroup->getBy(['is_advertaising_material' => 1])[0]['id'];
		
		foreach ($basket as $key => $entry) {			
			if ($entry['transport_group_id'] == $advertisingGroupId) {
				unset($basket[$key]);
			}
		}

	}
	
	protected function putAdvertisingMaterials($advertaisingMaterials, &$usedUnits) {

		$unitTransportUnit = new UnitTransportUnit();
		$unitTransportUnits = $unitTransportUnit->getAll();
		$allUnits = getArrayByKey($unitTransportUnits, 'id');

		foreach ($advertaisingMaterials as $advKey => $advertaisingMaterial) {			
			foreach ($usedUnits as $groupId => $usedUnit) {

				foreach ($usedUnit as $unitId => $units) {
					$units = array_filter($units);

					foreach ($units as $key => $unit) {

						$currentUnit = $allUnits[$unitId];
						$unitVolume = $currentUnit['length'] * $currentUnit['width'] * $currentUnit['height'];	
						$expectedVolume = (float) $unit['volume'] + $itemVolume;	

						$itemVolume = (float) $advertaisingMaterial['length'] * $advertaisingMaterial['width'] * $advertaisingMaterial['height'];

						if (($expectedVolume < $unitVolume || compareFloats($expectedVolume, $unitVolume)) && ($unit['weight'] + $advertaisingMaterial['weight']) <= UnitTransportUnit::MAX_WEIGHT) {
//							echo 'mozna pakowac...';
							$items = array_merge($unit['items'], array($advertaisingMaterial));
//
							$params = array(
								'volume' => $unit['volume'] + $itemVolume,
								'weight' => $unit['weight'] + $advertaisingMaterial['weight'],
								'items' => $items
							);
//
							$usedUnits[$groupId][$unitId][$key] = $params;
							unset($advertaisingMaterials[$advKey]);
						}


					}	
				}
			}
		}
		
//		dump($advertaisingMaterials, 'pozostalo');
		
		if ($advertaisingMaterials) {
			foreach ($advertaisingMaterials as $adv) {
				if (!isset($usedUnits[$adv['transport_group_id']][$adv['transport_unit_id']])) {
					$usedUnits[$adv['transport_group_id']][$adv['transport_unit_id']] = [];
				}
			}
		}
		
		$sortedAdv = $this->sortBasketByGroupAndUnit($advertaisingMaterials);
		foreach ($sortedAdv as $groupId => $units) {
			foreach ($units as $unitId => $items) {
				$this->packItemsWithSameUnit($items, $usedUnits);				
			}
		}
					
	}
	
	public function calculateUnitTransportDelivery($basket) {	
		if (!$basket) {
			return 0;
		}
		
		$orginalBasket = $basket;

		$areAllProductsAdvertaising = $this->areAllProductsAdvertaising($basket);
		$advertaisingMaterials = $this->getAdvertisingMaterials($basket);
		$this->removeAdvertaisingMaterialsFromBasket($basket);
		
		$sortedBasket = $this->sortBasketByGroupAndUnit($basket);			
		$usedUnits = $this->initUsedUnits($sortedBasket);

		if ($areAllProductsAdvertaising === false) {			
			
			foreach ($sortedBasket as $groupId => $units) {
//				dump($usedUnits, 'przed');
				foreach ($units as $unitId => $items) {
					$this->packItemsWithSameUnit($items, $usedUnits);				
				}

//				dump($usedUnits, 'po same unit');
				$this->putRemains($usedUnits, $groupId);
				
				//czasami jeszcze cos zostaje
				$this->putRemains($usedUnits, $groupId);
//				dump($usedUnits, 'po resztkach');
			}
						
			
			if ($advertaisingMaterials) {
//				echo 'zwykle pakowanie  + reklamy...';
				$this->putAdvertisingMaterials($advertaisingMaterials, $usedUnits);
			}
			
		} else {
//			echo 'tylko reklamy pakuje...';
			
			if ($advertaisingMaterials) {
				foreach ($advertaisingMaterials as $adv) {
					if (!isset($usedUnits[$adv['transport_group_id']][$adv['transport_unit_id']])) {
						$usedUnits[$adv['transport_group_id']][$adv['transport_unit_id']] = [];
					}
				}
			}					
			
			$sortedAdv = $this->sortBasketByGroupAndUnit($advertaisingMaterials);

			foreach ($sortedAdv as $groupId => $units) {
				foreach ($units as $unitId => $items) {
					$this->packItemsWithSameUnit($items, $usedUnits);				
				}
			}
			
		}

//		dump($usedUnits, 'used');
//		dump($orginalBasket, '$orginalBasket');
        $result = [];
		if ($orginalBasket) {
			
			foreach ($usedUnits as $groupId => $units) {
				foreach ($units as $unitId => $unit) {
					$unit = array_filter($unit);		
					$result[$groupId][$unitId] = count($unit);
				}
			}			
		}
		
//		dump($result);
		
		$unitTransportUnit = new UnitTransportUnit();
		$unitTransportUnits = $unitTransportUnit->getAll();
		$allUnits = getArrayByKey($unitTransportUnits, 'id');
		
		$deliveryPrice = 0;
		foreach ($result as $groupId => $units) {
			foreach ($units as $unitId => $num) {				
				$deliveryPrice += $num * $allUnits[$unitId]['price'];
			}			
		}

		return $deliveryPrice;
	}    
    
    public function processClearBasket() {

		if (!$basket = $this->getByCustomerOrSession()) {		
			return false;
		}
		
		if (Cms::$conf['basket_clear_products']) {
			
			$hours = (int) Cms::$conf['basket_clear_products'];
			$time_ago = date("Y-m-d H:i:s", strtotime("-" . $hours ." hour"));

			foreach ($basket as $v) {
				if($v['time_add'] < $time_ago) {
					$product = $this->product->getById($v['product_id'], $v['variation_id']);
					$aFields['qty'] = $product['qty'] + $v['qty'];
					$this->product->updateVariationById($product['id2'], $aFields);
					$this->deleteById($v['id']);
				}
			}
			return true;			
		}
		
		return false;        
        
    }

}
