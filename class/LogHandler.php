<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/LogHandler.php
 *
 * Classes responsible for managing Downloads log objects
 *
 * @copyright	Copyright QM-B (Steffen Flohrer) 2011
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * ----------------------------------------------------------------------------------------------------------
 * 				Downloads
 * @since		1.00
 * @author		QM-B <qm-b@hotmail.de>
 * @version		$Id$
 * @package		downloads
 *
 */

defined('ICMS_ROOT_PATH') or die('ICMS root path not defined');

class DownloadsLogHandler extends icms_ipf_Handler {
	/**
	 * constructor
	 */
	public function __construct(&$db) {
		parent::__construct($db, "log", "log_id", "log_item_id", "log_date", "downloads");
	}

	public function clearLogDB($value='') {
		$time = ( time() - ( 86400 * (int)( icms::$module->config['downloads_daysnew'] ) ) );

	}

	protected function beforeInsert(&$obj){
		$ip = $obj->getVar("log_ip", "s");
		$ip = icms_core_DataFilter::checkVar($ip, "ip", "ipv4");
		return TRUE;
	}

	protected function beforeSafe(&$obj) {
		$item_id = $obj->getVar("log_item_id");
		$item = $obj->getVar("log_item");
		$case = $obj->getVar("log_case");

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item("log_item_id", $item_id));
		$criteria->add(new icms_db_criteria_Item("log_item", $item));
		$criteria->add(new icms_db_criteria_Item("log_case", $case));
		$log_total = $this->getCount($criteria);

	}



}
