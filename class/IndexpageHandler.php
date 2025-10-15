<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/IndexpageHandler.php
 * 
 * Classes responsible for managing Downloads indexpage objects
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
 * Indexpage Handler with Fluent Interface
 *
 * Manages CRUD operations for indexpage objects with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsIndexpageHandler extends icms_ipf_Handler
{
	public string $_moduleName;
	public string $_uploadPath;

	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db Database connection
	 */
	public function __construct(icms_db_legacy_Database &$db)
	{
		parent::__construct($db, "indexpage", "index_key", "index_header", "index_heading", "downloads");
		$mimetypes = ['image/jpeg', 'image/png', 'image/gif'];
		$this->enableUpload($mimetypes, 2000000, 500, 500);
	}

	/**
	 * Get list of available images
	 *
	 * @return array Image list
	 */
	public static function getImageList(): array
	{
		$indeximages = icms_core_Filesystem::getFileList(
			DOWNLOADS_UPLOAD_ROOT . 'indexpage/',
			'',
			['gif', 'jpg', 'png']
		);

		$ret = [0 => '-----------------------'];
		foreach (array_keys($indeximages) as $i) {
			$ret[$i] = $indeximages[$i];
		}
		return $ret;
	}

	/**
	 * Before insert hook
	 *
	 * @param DownloadsIndexpage $obj Indexpage object
	 * @return bool True on success
	 */
	protected function beforeInsert(DownloadsIndexpage &$obj): bool
	{
		$heading = $obj->getVar("index_heading", "s");
		$heading = icms_core_DataFilter::checkVar($heading, "html", "input");
		$obj->setVar("index_heading", $heading);

		$footer = $obj->getVar("index_footer", "s");
		$footer = icms_core_DataFilter::checkVar($footer, "html", "input");
		$obj->setVar("index_footer", $footer);

		if ($obj->getVar('index_img_upload') != '') {
			$obj->setVar('index_image', $obj->getVar('index_img_upload'));
		}

		return true;
	}
}
