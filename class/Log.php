<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/Log.php
 *
 * Class representing Download log objects
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
 * Log entity class
 *
 * Represents a download log entry with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsLog extends icms_ipf_Object
{
	/**
	 * Constructor
	 *
	 * @param icms_ipf_Handler $handler Handler instance
	 */
	public function __construct(icms_ipf_Handler &$handler)
	{
		icms_ipf_object::__construct($handler);

		$this->quickInitVar('log_id', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('log_ip', XOBJ_DTYPE_OTHER);
		$this->quickInitVar('log_uid', XOBJ_DTYPE_INT);
		$this->quickInitVar('log_item_id', XOBJ_DTYPE_INT);
		$this->quickInitVar('log_date', XOBJ_DTYPE_LTIME);
		$this->quickInitVar('log_item', XOBJ_DTYPE_INT); // file = 0; category = 1
		$this->quickInitVar('log_case', XOBJ_DTYPE_INT); // download = 0; upload/create = 1; delete = 2; updated= 3; download from mirror = 4, vote up = 5, vote down = 6, report broken = 7

	}

	/**
	 * Get log IP address
	 *
	 * @return string Filtered IP address
	 */
	public function getLogIP(): string
	{
		$ip = $this->getVar("log_ip", "s");
		return icms_core_DataFilter::checkVar($ip, "ip", "ipv4");
	}

	/**
	 * Get formatted log date
	 *
	 * @return string Formatted date
	 */
	public function getLogDate(): string
	{
		$date = $this->getVar('log_date', 'e');
		return date(icms::$module->config['downloads_dateformat'], $date);
	}

	/**
	 * Get log item ID with link
	 *
	 * @return string HTML link to item
	 */
	public function getLogItemId(): string
	{
		$item_id = $this->getVar("log_item_id", "e");
		$item = $this->getVar("log_item", "e");
		$downloads_download_handler = icms_getModuleHandler("download", basename(dirname(__DIR__)), "downloads");
		$downloads_category_handler = icms_getModuleHandler("category", basename(dirname(__DIR__)), "downloads");

		if ($item == 0) {
			$file = $downloads_download_handler->get($item_id);
			$filename = $file->getVar("download_title", "s");
			$url = DOWNLOADS_URL . 'singledownload.php?download_id=' . $item_id;
			return sprintf('<a href="%s" title="%s">%s</a>', $url, $filename, $filename);
		} elseif ($item == 1) {
			$cat = $downloads_category_handler->get($item_id);
			$catname = $cat->getVar("category_title", "s");
			$url = DOWNLOADS_URL . 'index.php?category_id=' . $item_id;
			return sprintf('<a href="%s" title="%s">%s</a>', $url, $catname, $catname);
		}

		return '';
	}

	/**
	 * Get log item type
	 *
	 * @return string Item type label
	 */
	public function getLogItem(): string
	{
		$item = $this->getVar("log_item", "e");
		return match ($item) {
			'0' => 'file',
			'1' => 'category',
			default => '',
		};
	}

	/**
	 * Get log case/action type
	 *
	 * @return string Case label
	 */
	public function getLogCase(): string
	{
		$item = $this->getVar("log_case", "e");
		return match ($item) {
			'0' => 'download',
			'1' => 'create',
			'2' => 'delete',
			'3' => 'updated',
			'4' => 'download mirror',
			'5' => 'vote up',
			'6' => 'vote down',
			'7' => 'report broken',
			default => '',
		};
	}

	/**
	 * Convert object to array
	 *
	 * @return array Object data as array
	 */
	public function toArray(): array
	{
		$ret = parent::toArray();
		$ret['date'] = $this->getLogDate();
		$ret['item_id'] = $this->getLogItemId();
		$ret['item'] = $this->getLogItem();
		$ret['case'] = $this->getLogCase();
		return $ret;
	}
}
