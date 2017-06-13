<?php


$method = $_REQUEST['method'];
$id = isset($_POST['id']) ? $_POST['id'] : 0;

switch($method) {
   case 'getList':
       getList();
       break;
   case 'getProductVariations':
       $productId = $_POST['productId'];
       getProductVariations($productId);
       break;
   case 'getTransportGroupUnits':       
       getTransportGroupUnits($_POST['groupId'], $_POST['unitId']);
       break;
	default:
		break;   
}
die;

function getTransportGroupUnits($groupId = 0, $unitId = 0) {

	require_once(MODEL_DIR . '/UnitTransportGroupUnit.php');
	$unitTransportGroupUnit = new UnitTransportGroupUnit();
	$unitTransportGroupUnits = $unitTransportGroupUnit->findAll(['group_id' => $groupId]);		

	$data = array(
		'unitTransportGroupUnits' => $unitTransportGroupUnits,
		'unitId' => $unitId
	);

	echo Cms::$twig->render('admin/ajax/transport-group-units.twig', $data);     
}

function getProductVariations($productId) {
    require_once(MODEL_DIR . '/shopProductsAdmin.php');
    $oProduct = new ProductsAdmin();
    
    $product = $oProduct->loadByIdAdmin($productId);
    $variations = $oProduct->loadVariationsByProductId($productId);
    
	$data = array(
		'product' => $product,
		'variations' => $variations
	);

	echo Cms::$twig->render('admin/ajax/variations.twig', $data);
}

function getList() {
    error_reporting(E_ALL ^ E_NOTICE);
    /*
     * DataTables example server-side processing script.
     *
     * Please note that this script is intentionally extremely simply to show how
     * server-side processing can be implemented, and probably shouldn't be used as
     * the basis for a large complex system. It is suitable for simple use cases as
     * for learning.
     *
     * See http://datatables.net/usage/server-side for full details on the server-
     * side processing requirements of DataTables.
     *
     * @license MIT - http://datatables.net/license_mit
     */

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */

    // DB table to use
    $table = 'product';

    // Table's primary key
    $primaryKey = 'id';

    // Array of database columns which should be read and sent back to DataTables.
    // The `db` parameter represents the column name in the database, while the `dt`
    // parameter represents the DataTables column identifier. In this case simple
    // indexes

    //require(SYS_DIR . '/core/Cms.php');
    require_once(CMS_DIR . '/system/core/Functions.php');
    require_once(CMS_DIR . '/application/models/Category.php');
    require_once(CMS_DIR . '/application/models/shopProducersAdmin.php');
    require_once(CMS_DIR . '/application/models/ProductStatus.php');

    $oProducersAdmin = new ProducersAdmin();
	$category = new Category;
	$categories = $category->getAll(['locale' => Cms::$defaultLocale, 'parent_id' => 0]);

    $producers = $oProducersAdmin->loadProducersSelect();

    $productStatus = new ProductStatus();
    $statuses = $productStatus->getAll(['locale' => Cms::$session->get('locale_admin')]);
    $statuses = getArrayByKey($statuses, 'translatable_id');

    function setYesNo($val) {
        $result = '';

        if ($val == 1) {
            $result = '<i class="fa fa-check" title="' . $GLOBALS['LANG']['yes'] .'"></i>';
        } else {
            $result = '<i class="fa fa-minus" title="' . $GLOBALS['LANG']['no'] .'"></i>';
        }

        return $result;      
    }
    
    $columns = array(
        array( 'db' => '`p`.`id` as lp', 'dt' => 0, 'field' => 'lp' ),
        array( 'db' => '`p`.`id`', 'dt' => 1, 'field' => 'id' ),		
	
        array( 'db' => '`pt`.`name`',  'dt' => 2, 'field' => 'name',            
			'formatter' => function($d, $row) {        
                if (!$d) {
                    return '';
                }

                $productName = '<a title="' . $GLOBALS['LANG']['edit'] . '" href="?action=edit&amp;id=' . $row['id'] .'"> ' . $d .'</a>';				
				return $productName;
        }),	
		array( 'db' => '(SELECT GROUP_CONCAT(DISTINCT sku SEPARATOR "|") FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) as sku',  'dt' => 3, 'field' => 'sku', 
            'formatter' => function($d, $row) {        
                if (!$d) {
                    return '';
                }

				$sku_list = explode('|', $d);
				
				$content = '';		
				$content .= '<small><ul class="product-code" style="display:none;">';		
				foreach ($sku_list as $sku) {
					$content .= '<li>' . $sku . '</li>';
				}
				$content .= '</small></ul>';
						
				return $content;
        }),			
		array( 'db' => '(SELECT GROUP_CONCAT(DISTINCT ean SEPARATOR "|") FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) as ean',  'dt' => 4, 'field' => 'ean', 
            'formatter' => function($d, $row) {        
                if (!$d) {
                    return '';
                }

				$ean_list = explode('|', $d);
				
				$content = '';		
				$content .= '<small><ul class="product-code" style="display:none;">';		
				foreach ($ean_list as $ean) {
					if (!empty($ean)) {
						$content .= '<li>' . $ean . '</li>';
					}
				}
				$content .= '</small></ul>';
						
				return $content;
        }),			
//        array( 'db' => '`p`.`category_id`',   'dt' => 5, 'field' => 'category_id'),
        array( 'db' => '`p`.`category_id`',   'dt' => 5, 'field' => 'category_id', 
            'formatter' => function( $d, $row ) use ($categories) {
                if (!$d) {
                    return '';
                }
                return getFullCategoryName($d, $categories);                    
        }),
        array( 'db' => '`p`.`producer_id`',   'dt' => 6, 'field' => 'producer_id', 
            'formatter' => function( $d, $row ) use ($producers) {
                if (!$d) {
                    return '';
                }
				
				$producer = isset($producers[$d]) ? $producers[$d]['name'] : '';
                
                return $producer;                    
        }),
        array( 'db' => '`p`.`status_id`',   'dt' => 7, 'field' => 'status_id', 
            'formatter' => function( $d, $row ) use ($statuses) {
                if (!$d) {
                    return '';
                }
                return $statuses[$d]['name'];                    
        }),
        array( 'db' => '(SELECT max(promotion) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) as promotion', 'dt' => 8, 'field' => 'promotion',
            'formatter' => function( $d, $row ) {        
                return setYesNo($d);  
        }),
        array( 'db' => '(SELECT max(bestseller) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) as bestseller', 'dt' => 9, 'field' => 'bestseller',
            'formatter' => function( $d, $row ) {        
                return setYesNo($d);   
        }),
        array( 'db' => '(SELECT max(recommended) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) as recommended', 'dt' => 10, 'field' => 'recommended',
            'formatter' => function( $d, $row ) {
                return setYesNo($d);     
        }),
        array( 'db' => '(SELECT max(main_page) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) as main_page', 'dt' => 11, 'field' => 'main_page',
            'formatter' => function( $d, $row ) {
                return setYesNo($d);     
        }),             
        array( 'db' => '`p`.`date_add`', 'dt' => 12, 'field' => 'date_add' ),
        array( 'db' => '`p`.`date_mod`', 'dt' => 13, 'field' => 'date_mod' ),
        array( 'db' => '`p`.`id` as `actions`', 'dt' => 14, 'field' => 'actions',
            'formatter' => function( $d, $row ) {    
                $row = mstripslashes($row); 

				$url = '';
                if ($row['parent_url']) {
                    $url = URL . '/product/' . $row['parent_url'] . '/' . $row['category_url'] . '/' . $row['name_url'] . '.html';
                } else {
                    $url = URL . '/product/' . $row['category_url'] . '/' . $row['name_url'] . '.html';
                }   

                $actions = '';
                $actions .= '<a target="_blank" title="' . $GLOBALS['LANG']['view'] .'" href="' . $url .'">
                                <i class="fa fa-eye"></i>
                            </a>&nbsp;';  

//                $actions .= '<a title="Duplikuj" href="?action=duplicate&amp;id=' . $row['id'] .'">
//                                <i class="fa fa-copy"></i>
//                            </a>&nbsp;';

                $actions .= '<a title="' . $GLOBALS['LANG']['edit'] . '" href="?action=edit&amp;id=' . $row['id'] .'">
                                <i class="fa fa-edit"></i>
                            </a>';

                if ($_SESSION[USER_CODE]['available_actions']['product_delete']) {
                    $actions .= '&nbsp;<a href="#" data-href="?action=delete&amp;id=' . $row['id'] . '" data-toggle="modal" data-target="#confirm-delete"><i class="fa fa-remove"></i></a>';
                }

                return $actions;

        }),           
        array( 'db' => '(SELECT `slug` FROM `categories_translation` WHERE `translatable_id`=`c`.`parent_id` LIMIT 1) as `parent_url`', 'dt' => 15, 'field' => 'parent_url',
            'formatter' => function( $d, $row ) {
                
                return $d;   
        }),            
        array( 'db' => '`ct`.`slug` as `category_url`', 'dt' => 16, 'field' => 'category_url'),            
        array( 'db' => '`pt`.`slug` as `name_url`', 'dt' => 17, 'field' => 'name_url'),           
        array( 'db' => '`p`.`type`', 'dt' => 18, 'field' => 'type'),  

    //	array( 'db' => '`u`.`start_date`', 'dt' => 6, 'field' => 'start_date', 'formatter' => function( $d, $row ) {
    //																	return date( 'jS M y', strtotime($d));
    //																}),
    //	array('db'  => '`u`.`salary`',     'dt' => 7, 'field' => 'salary', 'formatter' => function( $d, $row ) {
    //																return '$'.number_format($d);
    //															})
    );

    // SQL server connection information                         
    $sql_details = array(
        'user' => DB_USER,
        'pass' => DB_PASSWORD,
        'db'   => DB_NAME,
        'host' => DB_SERVER,
        'port' => DB_PORT
    );

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    // require( 'ssp.class.php' );
    require(LIB_DIR . '/ssp.customized.class.php' );

    //$extraWhere = "`u`.`salary` >= 90000"; 

    $joinQuery = "FROM `product` AS `p` LEFT JOIN `categories` AS `c` ON (`c`.`id` = `p`.`category_id`)";
    $joinQuery .= "LEFT JOIN `categories_translation` AS `ct` ON (`ct`.`translatable_id` = `p`.`category_id` AND `ct`.locale = '" . LOCALE ."')";
    $joinQuery .= "LEFT JOIN `product_translation` AS `pt` ON (`pt`.`translatable_id` = `p`.`id` AND `pt`.locale = '" . LOCALE ."')";

//    $joinQuery = "FROM `product` AS `p` JOIN `product_category` AS `c` ON (`c`.`id` = `p`.`category_id`)";
	
	$joinQuery .= " LEFT JOIN `product_variation` AS `v` ON (`v`.`product_id` = `p`.`id` )";	//for sku searching needed
	
    $extraWhere = [];
	
    if (isset($_REQUEST['date_add_from']) && $_REQUEST['date_add_from']) {
        $extraWhere[] =  "`p`.`date_add` >= '" . $_REQUEST['date_add_from'] . "'";
    }

    if (isset($_REQUEST['date_add_to']) && $_REQUEST['date_add_to']) {
        $extraWhere[] = "`p`.`date_add` <= '" . $_REQUEST['date_add_to'] . "'";
    }

    if (isset($_REQUEST['date_mod_from']) && $_REQUEST['date_mod_from']) {
        $extraWhere[] =  "`p`.`date_mod` >= '" . $_REQUEST['date_mod_from'] . "'";
    }

    if (isset($_REQUEST['date_mod_to']) && $_REQUEST['date_mod_to']) {
        $extraWhere[] = "`p`.`date_mod` <= '" . $_REQUEST['date_mod_to'] . "'";
    }

    if (isset($_REQUEST['promotion']) && $_REQUEST['promotion'] !='') {
        $extraWhere[] = "(SELECT max(promotion) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) = '" . $_REQUEST['promotion'] ."'";
    }

    if (isset($_REQUEST['bestseller']) && $_REQUEST['bestseller'] !='') {
        $extraWhere[] = "(SELECT max(bestseller) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) = '" . $_REQUEST['bestseller'] ."'";
    }

    if (isset($_REQUEST['recommended']) && $_REQUEST['recommended'] !='') {
        $extraWhere[] = "(SELECT max(recommended) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) = '" . $_REQUEST['recommended'] ."'";
    }

    if (isset($_REQUEST['main_page']) && $_REQUEST['main_page'] !='') {
        $extraWhere[] = "(SELECT max(main_page) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) = '" . $_REQUEST['main_page'] ."'";
    }
	
	//category and subcategories
	$categoryId = isset($_REQUEST['columns'][5]['search']['value']) ? $_REQUEST['columns'][5]['search']['value'] : null;

	if ($categoryId) {
		$categoryId = $_REQUEST['columns'][5]['search']['value'];
		$entities = $category->getAll(['locale' => Cms::$defaultLocale, 'parent_id' => $categoryId]);
		$catIds = [];	
		$catIds[] = $categoryId;

		foreach ($entities as $row) {
			$catIds[] = $row['id'];
		}

		$extraWhere[] = "`c`.`id` IN (" .implode(',', $catIds) .")";	
	}
	
    if (isset($_REQUEST['sku']) && $_REQUEST['sku'] !='') {
//        $extraWhere[] = "(SELECT GROUP_CONCAT(sku) FROM `product_variation` v WHERE `v`.`product_id`=`p`.`id`) = '" . $_REQUEST['sku'] ."'";
//        $extraWhere[] = "`v`.`sku` LIKE '%tee%'";
    }

    $extraWhere = implode(' AND ', $extraWhere);

    if (!isset($_GET['draw'])) {
        $_GET['draw'] = 1;
    }
//    dump($_REQUEST);die;
    $result = SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere);
//    dump($result);

    // add index lp to first col
    $start=$_REQUEST['start'];
    $start++;
    foreach($result['data'] as &$res){
        $res[0]=(string)$start;
        $start++;
    }

    decorateProductName($result);    

    echo json_encode($result);
    die;    
}

//add variations to product name col
function decorateProductName(&$result) {   
//dump($result);

    foreach($result['data'] as &$res){
        if ($res[18] == 1) {    //product type 1 produkt z wariacjami
            $res[2]= $res[2] . '<div><a href="#" class="show-variations" data-readed="0" data-product-id="' . $res[1] .'">[' . $GLOBALS['LANG']['show_variations'] . ']</a><div class="variations" style="display:none;"></div>';
        }
    }    
}