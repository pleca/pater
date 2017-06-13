<?php

/* 2015-10-14 | 4me.CMS 15.3 */

if (!defined('NO_ACCESS')) {
	die('No access to files!');
}

require_once(MODEL_DIR . '/transportCountry.php');
require_once(MODEL_DIR . '/transportCourier.php');
require_once(MODEL_DIR . '/transportRegion.php');
require_once(MODEL_DIR . '/transportRegionCountry.php');
require_once(MODEL_DIR . '/transportRegionPostcode.php');
require_once(MODEL_DIR . '/transportRegionService.php');
require_once(MODEL_DIR . '/transportService.php');
require_once(MODEL_DIR . '/transportServiceOption.php');
require_once(ENTITY_DIR . '/Tax.php');

class TransportController {

	private $country;
	private $courier;
	private $region;
	private $regionCountry;
	private $regionPostcode;
	private $regionService;
	private $service;
	private $serviceOption;

	public function __construct() {
		$this->country = new TransportCountryModel();
		$this->courier = new TransportCourierModel();
		$this->region = new TransportRegionModel();
		$this->regionCountry = new TransportRegionCountryModel();
		$this->regionPostcode = new TransportRegionPostcodeModel();
		$this->regionService = new TransportRegionServiceModel();
		$this->service = new TransportServiceModel();
		$this->serviceOption = new TransportServiceOptionModel();
	}
	
	public function getCountryById($id = 0) {
		if($item = $this->country->getById($id)[0]) {
			return $item;
		}
		return false;
	}

	public function getAllCountry() {
		if($country = $this->country->getAll()) {
			foreach ($country as $v) {
				if ($v['status_id'] != 1 AND $v['status_id'] != 2) {	// nowy, aktywny
					unset($v);
				}
			}
			return $country;
		}
		return false;
	}
	
	public function getAllDeliveryCountry() {
		if ($courier = $this->courier->getAll()) {		
			$courier_ids = [];
			foreach ($courier as $v) {
				if ($v['status_id'] != 1 AND $v['status_id'] != 2) {	// nowy, aktywny
					continue;
				}
				$courier_ids[] = $v['id'];
			}
			$country = $this->region->getCountryByCourierId($courier_ids);
			return $country;
		}		
		return false;
	}
	
	public function getServiceOptionById($id = 0) {
		if($option = $this->serviceOption->getById($id)[0]) {
			
			$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
			$tax = $taxRepository->find($option['tax_id']);

			$service = $this->service->getById($option['service_id'])[0];
			$courier = $this->courier->getById($service['courier_id'])[0];
			$region = $this->region->getById($option['region_id'])[0];
			$option['service_name'] = $service['name'];
			$option['courier_id'] = $courier['id'];
			$option['courier_name'] = $courier['name'];
			$option['region_name'] = $region['name'];
			$option['tax'] = $tax->getValue();
			$option['price_net'] = formatPrice($option['price']);
			$option['price_gross'] = formatPrice($option['price'], $option['tax']);										
			return $option;
		}		
		return false;
	}
		
	public function getAllDeliveryService ($country_id = 0, $postcode = '', $weight = 0) {

		$taxRepository = CMS::$entityManager->getRepository('Application\Entity\Tax');
					
		// postcode: caly kod, rozdzielamy po spacji
		// weight: g
		$post1 = explode(" ", $postcode); // tylko pierwszy czlon kodu, znak rozdzielajacy to spacja
		$transport = array();

		if ($country_id == Cms::$conf['country_id']) { // okreslamy typ przesylki:
			$type_id = 1; // krajowy
		} else {
			$type_id = 2; // zagraniczny
		}

		if($courier = $this->courier->getAll()) { // dostepni Kurierzy
			foreach ($courier as $v) {
				if ($v['status_id'] != 1 AND $v['status_id'] != 2) {	// nowy, aktywny
					continue;
				}
				$courierTmp = $v;

				$aRegion = array();
				if($aRegions = $this->region->getAllByCourierIdTypeId($v['id'], $type_id)) { // dostepne regiony kuriera
//dump($aRegions, 'aRegions');

					foreach ($aRegions as $k2 => $v2) {
						if ($v2['status_id'] != 1 AND $v2['status_id'] != 2) { // srawdzamy status
							continue;
						}

						if ($aRegionCountry = $this->regionCountry->getByRegionIdCountryId($v2['id'], $country_id)) { // powiazanie regionu z krajami
//dump($aRegionCountry, 'regionCountry');
							foreach ($aRegionCountry as $v3) {
								if ($v3['region_id'] != $v2['id']) {
									unset($aRegions[$k2]); // usuwamy niezgodne regiony
								}
							}
						} else {
							unset($aRegions[$k2]); // usuwamy niezgodne regiony
						}
					}

					foreach ($aRegions as $v2) { // zerowanie kluczy
						$aRegion[] = $v2;
					}
				}				
				unset($aRegions);
//dump($aRegion, 'aRegion');

				if(!$aRegion) {
					continue;
				}
				
				$countRegion = count($aRegion);
				if ($countRegion > 1) { // region posiada wiecej stref, musimy sprawdzic kody pocztowe
					if ($type_id == 1) { // krajowy
						for ($i = $countRegion; $i > 0; $i--) {
							if ($i > 1) {

								if ($psotcode = $this->regionPostcode->getByRegionIdPost1($aRegion[$i - 1]['id'], $post1[0])[0]) { // sprawdzamy kod pocztowy
									if ($psotcode['status_id'] != 1 AND $psotcode['status_id'] != 2) { // srawdzamy status
										continue;
									}

									$courierTmp['region'] = $aRegion[$i - 1];
									break;
								}
							} else {
								$courierTmp['region'] = $aRegion[$i - 1];
							}
						}
					} else { // zagraniczny: jedne kraj w wielu strefach, ustawiamy z najnizszej strefy
						$courierTmp['region'] = $aRegion[0];
					}
				} else {
					$courierTmp['region'] = $aRegion[0];
				}
//dump($courierTmp, 'courierTmp');

				if ($aRegionService = $this->regionService->getAllByRegionId($courierTmp['region']['id'])) { // sprawdzamy serwisy dla regionu
//dump($aRegionService, 'aRegionService');
					$courierTmp['services'] = array();
					foreach ($aRegionService as $v2) {

						if ($aOptions = $this->serviceOption->getAllByRegionIdServiceId($v2['region_id'], $v2['service_id'])) { // przedzialy dla regionu i serwisu
//dump($aOptions, 'aOptions');
							foreach ($aOptions as $v3) {
//dump($weight, 'weight');
								if ($weight >= $v3['weight_from'] AND $weight <= $v3['weight_to']) {
                                    
									if ($aService = $this->service->getById($v2['service_id'])[0]) {  // informacje o serwisie
//dump($aService, 'aService');				
//										$tax = $this->tax->getById($v3['tax_id'])[0];
										$tax = $taxRepository->find($v3['tax_id']);
										$v3['tax'] = $tax->getValue();
										$v3['price_net'] = formatPrice($v3['price']);
										$v3['price_gross'] = formatPrice($v3['price'], $v3['tax']);

										$aService['option'] = $v3;
										$courierTmp['services'][] = $aService;
									}
								}
							}
						}
					}

					if ($courierTmp['services']) {
						$aCourier[] = $courierTmp;
					}
				}
			}
//dump($aCourier);	// wszystkie dostepne uslugi

			if (isset($aCourier)) {
				foreach ($aCourier as $v) {
					if ($v['services']) {
						foreach ($v['services'] as $v2) {
							$v2['courier_name'] = $v['name'];
							$v2['region_name'] = $v['region']['name'];
							$v2['service_name'] = $v2['name'];
							$services[] = $v2;
						}
					}
				}
			}

			if (isset($services)) {
				foreach ($services as $s) {
					$tmp['courier_id'] = $s['courier_id'];
					$tmp['courier_name'] = $s['courier_name'];
					$tmp['service_id'] = $s['id'];
					$tmp['service_name'] = $s['service_name'];
					$tmp['region_id'] = $s['option']['region_id'];
					$tmp['region_name'] = $s['region_name'];
					$tmp['option_id'] = $s['option']['id'];
					$tmp['delivery_time'] = $s['option']['delivery_time'];
					$tmp['price'] = $s['option']['price'];
					$tmp['tax'] = $s['option']['tax'];
					$tmp['price_gross'] = $s['option']['price_gross'];
					$transport[] = $tmp;
				}
			}
		}

		return $transport;
	}

}
