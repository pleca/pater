<?php

/* 2015-10-14 | 4me.CMS 15.3 */

check_permission('unit_transport', 'module'); // sprawdzamy dostep dla podanego modulu

require_once(MODEL_DIR . '/UnitTransportGroup.php');
require_once(MODEL_DIR . '/UnitTransportUnit.php');

require_once(MODEL_DIR . '/UnitTransportGroupUnit.php');

$entity = new UnitTransportGroup();

$unitTransportGroupUnit = new UnitTransportGroupUnit();
$groupsUnits = $unitTransportGroupUnit->findAll();

if (isset($_POST['action']) AND $_POST['action'] == 'add') {
	$item = [];
	$item['name'] = $_POST['name'];
	$item['is_advertaising_material'] = isset($_POST['is_advertaising_material']) ? 1 : 0; 
	$item['is_excluded_from_free_delivery'] = isset($_POST['is_excluded_from_free_delivery']) ? 1 : 0; 
	
	if ($item['is_advertaising_material']) {
        $q = "UPDATE `" . $entity->table . "` SET `is_advertaising_material`= 0";
        Cms::$db->update($q);
	}
	
	if ($item['is_excluded_from_free_delivery']) {
        $q = "UPDATE `" . $entity->table . "` SET `is_excluded_from_free_delivery`= 0";
        Cms::$db->update($q);
	}
	
    if ($insertedId = $entity->set($item)) {
		
		if (isset($_POST['units'])) {
			foreach ($_POST['units'] as $unit) {
				$item2 = [];
				$item2['transport_group_id'] = $insertedId;
				$item2['unit_id'] = $unit;
				$unitTransportGroupUnit->set($item2);
			}
		}

		$params['info'] = $GLOBALS['LANG']['info_add'];
		$groupsUnits = $unitTransportGroupUnit->findAll();
    } else {
		$params['error'] = $GLOBALS['LANG']['error_add'];
    }	

} elseif (isset($_GET['action']) AND $_GET['action'] == 'edit') {
	$item = $entity->getById($_GET['id'])['0'];	
	
	$selectedUnits = [];
	if ($groupsUnits) {
		foreach ($groupsUnits as $groupsUnit) {
			if ($groupsUnit['transport_group_id'] == $_GET['id']) {
				$selectedUnits[] = $groupsUnit['unit_id'];
			}
		}
	}	

	$params['url_back'] = CMS_URL . '/admin/unit-transport-group.php';
	$params['selectedUnits'] = $selectedUnits;
	$params['item'] = $item;
	
} elseif (isset($_POST['action']) AND $_POST['action'] == 'save') {

    $item = [];
    $item['name'] = $_POST['name'];
	$item['is_advertaising_material'] = isset($_POST['is_advertaising_material']) ? 1 : 0; 
	$item['is_excluded_from_free_delivery'] = isset($_POST['is_excluded_from_free_delivery']) ? 1 : 0; 
		
	$oldEntity = $entity->getById($_POST['id'])[0];
	
	if ($item['is_advertaising_material']) {
        $q = "UPDATE `" . $entity->table . "` SET `is_advertaising_material`= 0";
        Cms::$db->update($q);
	}	
	
	if ($item['is_excluded_from_free_delivery']) {
        $q = "UPDATE `" . $entity->table . "` SET `is_excluded_from_free_delivery`= 0";
        Cms::$db->update($q);
	}	
	
	if ($oldEntity['name'] != $item['name'] || $oldEntity['is_advertaising_material'] != $item['is_advertaising_material']
			|| $oldEntity['is_excluded_from_free_delivery'] != $item['is_excluded_from_free_delivery']) {
		if ($entity->updateById($_POST['id'], $item)) {
			$params['info'] = $GLOBALS['LANG']['info_config'];
		} else {
			$params['error'] = $GLOBALS['LANG']['error_change'];
		}
	}

	$unitTransportGroupUnit->deleteById($_POST['id']);
	if (isset($_POST['units'])) {
		foreach ($_POST['units'] as $unit) {
			$item2 = [];
			$item2['transport_group_id'] = $_POST['id'];
			$item2['unit_id'] = $unit;
			$unitTransportGroupUnit->set($item2);
		}						
	}
	$groupsUnits = $unitTransportGroupUnit->findAll();
    
} elseif (isset($_GET['action']) AND $_GET['action'] == 'delete') {
	if ($entity->deleteById($_GET['id'])) {
		$params['info'] = $GLOBALS['LANG']['info_delete'];
	} else {
		$params['error'] = $GLOBALS['LANG']['error_delete'];
	}
}

$unitTransportUnit = new UnitTransportUnit();
$unitTransportUnits = $unitTransportUnit->getAll();
$unitTransportUnits = getArrayByKey($unitTransportUnits, 'id');

$data = array(
	'entities' => $entity->getAll(),
	'pageTitle' => $GLOBALS['LANG']['transport_groups'],
	'unitTransportUnits' => $unitTransportUnits,
	'groupsUnits'	=>	$groupsUnits
);

echo Cms::$twig->render('admin/unit_transport_group/list.twig', array_merge($data, $params));
