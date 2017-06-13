<?php

/* 2013-11-21 | central 01 */

if (!defined('NO_ACCESS'))
	die('No access to files!');

require_once(SYS_DIR . '/core/BaseModel.php');

class OrderLog extends BaseModel {

    const ACTION_ORDER_UPDATE_STATUS_BY_GA = 'order_update_status_by_ga';
    const ACTION_ORDER_UPDATE_TRACKING_BY_GA = 'order_update_tracking_by_ga';
	private $table;

	public function __construct() {
		$this->table = DB_PREFIX . 'order_logs';
	}

}
