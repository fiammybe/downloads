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

/**
 * Log Handler with Fluent Interface
 *
 * Manages CRUD operations for log objects with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsLogHandler extends icms_ipf_Handler
{
	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db Database connection
	 */
	public function __construct(icms_db_legacy_Database &$db)
	{
		parent::__construct($db, "log", "log_id", "log_item_id", "log_date", "downloads");
	}

	/**
	 * Clear old log entries from database
	 *
	 * @param string $value Unused parameter
	 * @return void
	 */
	public function clearLogDB(string $value = ''): void
	{
		$time = time() - (86400 * (int)icms::$module->config['downloads_daysnew']);
		// TODO: Implement log cleanup logic
	}

	/**
	 * Before insert hook
	 *
	 * @param DownloadsLog $obj Log object
	 * @return bool True on success
	 */
	protected function beforeInsert(DownloadsLog &$obj): bool
	{
		$ip = $obj->getVar("log_ip", "s");
		$ip = icms_core_DataFilter::checkVar($ip, "ip", "ipv4");
		$obj->setVar("log_ip", $ip);
		return true;
	}

	/**
	 * Before save hook
	 *
	 * @param DownloadsLog $obj Log object
	 * @return bool True on success
	 */
	protected function beforeSafe(DownloadsLog &$obj): bool
	{
		$item_id = $obj->getVar("log_item_id");
		$item = $obj->getVar("log_item");
		$case = $obj->getVar("log_case");

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item("log_item_id", $item_id));
		$criteria->add(new icms_db_criteria_Item("log_item", $item));
		$criteria->add(new icms_db_criteria_Item("log_case", $case));
		$log_total = $this->getCount($criteria);

		return true;
	}
}
