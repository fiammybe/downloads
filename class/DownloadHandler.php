<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/DownloadHandler.php
 *
 * Classes responsible for managing Downloads download objects
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

class DownloadsDownloadHandler extends icms_ipf_Handler {

	private $_download_limitations = array();

	private $_download_related = array();

	private $_download_platform = array();

	private $_download_version_status = array();

	private $_download_license = array();

	private $_download_cid = array();

	public $_moduleName;

	public function __construct(&$db) {
		parent::__construct($db, "download", "download_id", "download_title", "download_description", "downloads");
		$this->addPermission('download_grpperm', _CO_DOWNLOADS_DOWNLOAD_DOWNLOAD_GRPPERM, _CO_DOWNLOADS_DOWNLOAD_DOWNLOAD_GRPPERM_DSC);
		$mimetypes = array('image/jpeg', 'image/png', 'image/gif');
		$this->enableUpload($allowedMimeTypes = TRUE, icms::$module->config['image_file_size'], icms::$module->config['image_upload_width'], icms::$module->config['image_upload_height']);

		$mimetypes = $this->checkMimeType();
		$filesize = icms::$module->config['downloads_file_size'];
		$this->enableUpload($mimetypes,	$filesize);
	}

	public function checkMimeType() {
		global $icmsModule;
		$mimetypeHandler = icms_getModulehandler('mimetype', 'system');
		$modulename = basename(dirname(dirname(__FILE__)));
		if (empty($this->mediaRealType) && empty($this->allowUnknownTypes)) {
			//icms_file_MediaUploadHandler::setErrors(_ER_UP_UNKNOWNFILETYPEREJECTED);
			return FALSE;
		}
		$AllowedMimeTypes = $mimetypeHandler->AllowedModules($this->mediaRealType, $modulename);
		if ((!empty($this->allowedMimeTypes) && !in_array($this->mediaRealType, $this->allowedMimeTypes))
				|| (!empty($this->deniedMimeTypes) && in_array($this->mediaRealType, $this->deniedMimeTypes))
				|| (empty($this->allowedMimeTypes) && !$AllowedMimeTypes))
			{
			icms_file_MediaUploadHandler::setErrors(sprintf(_ER_UP_MIMETYPENOTALLOWED, $this->mediaType));
			return FALSE;
		}
		return TRUE;
	}

	public function getList($criteria = null, $limit = 0, $start = 0, $debug = false) {
		//$criteria = new icms_db_criteria_Compo();
		if (isset($download_active)) {
			$criteria->add(new icms_db_criteria_Item('download_active', TRUE));
		}
		$this->setGrantedObjectsCriteria($criteria, "download_grpperm");
		$downloads = $this->getObjects($criteria, TRUE);
		$ret[0] = '-----------';
		foreach(array_keys($downloads) as $i) {
			$ret[$i] = $downloads[$i]->getVar('download_title');
		}
		return $ret;
	}

	// some criterias used by other requests
	public function getDownloadsCriteria($start = 0, $limit = 0, $download_publisher = FALSE, $download_id = FALSE,$download_cid = FALSE, $order = 'download_published_date', $sort = 'DESC') {
		$criteria = new icms_db_criteria_Compo();
		if ($start) $criteria->setStart($start);
		if ($limit) $criteria->setLimit((int)$limit);
		if ($order) $criteria->setSort($order);
		if ($sort) $criteria->setOrder($sort);
		if ($download_publisher) $criteria->add(new icms_db_criteria_Item('download_publisher', $download_publisher));
		if ($download_id) {
			$crit = new icms_db_criteria_Compo(new icms_db_criteria_Item('short_url', $download_id,'LIKE'));
			$alt_download_id = str_replace('-',' ',$download_id);
			//Added for backward compatiblity in case short_url contains spaces instead of dashes.
			$crit->add(new icms_db_criteria_Item('short_url', $alt_download_id),'OR');
			$crit->add(new icms_db_criteria_Item('download_id', $download_id),'OR');
			$criteria->add($crit);
		}
		if ($download_cid != FALSE)	{
			$critTray = new icms_db_criteria_Compo();
			$critTray->add(new icms_db_criteria_Item("download_cid", '%:"' . $download_cid . '";%', "LIKE"));
			$criteria->add($critTray);
		}
		return $criteria;
	}

	public function getDownloads($start = 0, $limit = 0,$tag_id = FALSE, $download_publisher = FALSE, $download_id = FALSE,  $download_cid = FALSE, $order = 'weight', $sort = 'ASC') {
		$criteria = $this->getDownloadsCriteria($start, $limit, $download_publisher, $download_id,  $download_cid, $order, $sort);
		if($tag_id) {
			$critTray = new icms_db_criteria_Compo();
			$critTray->add(new icms_db_criteria_Item("download_tags", '%:"' . $tag_id . '";%', "LIKE"));
			$criteria->add($critTray);
		}
		$criteria->add(new icms_db_criteria_Item('download_active', TRUE));
		$criteria->add(new icms_db_criteria_Item('download_approve', TRUE));
		$this->setGrantedObjectsCriteria($criteria, "download_grpperm");
		$downloads = $this->getObjects($criteria, TRUE, FALSE);
		$ret = array();
		foreach ($downloads as $download){
			$ret[$download['download_id']] = $download;
		}
		return $ret;
	}

	public function getDownloadsForBlocks($start = 0, $limit = 0,$updated = FALSE,$popular = FALSE, $order = 'download_published_date', $sort = 'DESC', $cid = FALSE, $img_req = FALSE) {
		$criteria = new icms_db_criteria_Compo();
		if ($start) $criteria->setStart($start);
		if ($limit) $criteria->setLimit($limit);
		$criteria->add(new icms_db_criteria_Item('download_active', TRUE));
		$criteria->add(new icms_db_criteria_Item('download_inblocks', TRUE));
		$criteria->add(new icms_db_criteria_Item('download_approve', TRUE));
		if($updated == TRUE) $criteria->add(new icms_db_criteria_Item('download_updated', TRUE));
		if($popular == TRUE) {
			$pop = icms::$module->config['downloads_popular'];
			$critTray = new icms_db_criteria_Compo();
			$critTray->add(new icms_db_criteria_Item('counter', $pop, ">="));
			$criteria->add($critTray);
		}
		if ($cid != FALSE)	{
			$critTray = new icms_db_criteria_Compo();
			$critTray->add(new icms_db_criteria_Item("download_cid", '%:"' . $cid . '";%', "LIKE"));
			$criteria->add($critTray);
		}
		if($img_req != FALSE) {
			$criteria->add(new icms_db_criteria_Item("download_img", "0", "!="));
		}
		$this->setGrantedObjectsCriteria($criteria, "download_grpperm");
		if ($order) $criteria->setSort($order);
		if ($sort) $criteria->setOrder($sort);
		$downloads = $this->getObjects($criteria, TRUE, FALSE);
		$ret = array();
		foreach ($downloads as $download){
			$ret[$download['download_id']] = $download;
		}
		return $ret;
	}

	public function getCatalogueItems() {
		$catalogueModule = icms_getModuleInfo('catalogue');
		if(icms_get_module_status("catalogue")) {
			$catalogue_item_handler = icms_getModuleHandler ('item', $catalogueModule->getVar('dirname'), 'catalogue');
			$criteria = new icms_db_criteria_Compo();
			$criteria->add (new icms_db_criteria_Item('online_status', TRUE));
			$catalogueObjects = $catalogue_item_handler->getObjects($criteria, TRUE, FALSE);
			$ret = array();
			$ret[0] = '--None--';
			foreach(array_keys($catalogueObjects) as $i) {
				$ret[$catalogueObjects[$i]['item_id']] = $catalogueObjects[$i]['title'];
			}
			return $ret;
		}
	}

	public function getAlbumList() {
		$albumModule = icms_getModuleInfo('album');
		if(icms_get_module_status("album")) {
			$album_album_handler = icms_getModuleHandler ('album', $albumModule->getVar('dirname'), 'album');
			$albumObjects = $album_album_handler->getAlbumListForPid();
			return $albumObjects;
		}
	}

	/**
	 * handling some functions to easily switch some fields
	 */
	public function changeVisible($download_id) {
		$visibility = '';
		$downloadObj = $this->get($download_id);
		if ($downloadObj->getVar('download_active', 'e') == TRUE) {
			$downloadObj->setVar('download_active', 0);
			$visibility = 0;
		} else {
			$downloadObj->setVar('download_active', 1);
			$visibility = 1;
		}
		$this->insert($downloadObj, TRUE);
		return $visibility;
	}

	public function changeShow($download_id) {
		$show = '';
		$downloadObj = $this->get($download_id);
		if ($downloadObj->getVar('download_inblocks', 'e') == TRUE) {
			$downloadObj->setVar('download_inblocks', 0);
			$show = 0;
		} else {
			$downloadObj->setVar('download_inblocks', 1);
			$show = 1;
		}
		$this->insert($downloadObj, TRUE);
		return $show;
	}

	public function changeApprove($download_id) {
		$approve = '';
		$downloadObj = $this->get($download_id);
		if ($downloadObj->getVar('download_approve', 'e') == TRUE) {
			$downloadObj->setVar('download_approve', 0);
			$approve = 0;
		} else {
			$downloadObj->setVar('download_approve', 1);
			$approve = 1;
		}
		$this->insert($downloadObj, TRUE);
		return $approve;
	}

	public function changeMirrorApprove($download_id) {
		$mirror_approve = '';
		$downloadObj = $this->get($download_id);
		if ($downloadObj->getVar('download_mirror_approve', 'e') == TRUE) {
			$downloadObj->setVar('download_mirror_approve', 0);
			$mirror_approve = 0;
		} else {
			$downloadObj->setVar('download_mirror_approve', 1);
			$mirror_approve = 1;
		}
		$this->insert($downloadObj, TRUE);
		return $mirror_approve;
	}

	public function changeBroken($download_id) {
		$broken = '';
		$downloadObj = $this->get($download_id);
		if ($downloadObj->getVar('download_broken', 'e') == TRUE) {
			$downloadObj->setVar('download_broken', 0);
			$broken = 0;
		} else {
			$downloadObj->setVar('download_broken', 1);
			$broken = 1;
		}
		$this->insert($downloadObj, TRUE);
		return $broken;
	}

	/**
	 * Adding some filters for object table in ACP
	 */
	public function download_active_filter() {
		return array(0 => 'Offline', 1 => 'Online');
	}

	public function download_inblocks_filter() {
		return array(0 => 'Hidden', 1 => 'Visible');
	}

	public function download_approve_filter() {
		return array(0 => 'Denied', 1 => 'Approved');
	}

	public function download_broken_filter() {
		return array(0 => 'Online', 1 => 'Broken');
	}

	public function download_has_mirror_filter() {
		return array(0 => 'No Mirror', 1 => 'Has Mirror');
	}

	public function getDownloadVersionStatus() {
		if (!$this->_download_version_status) {
			$this->_download_version_status = array(1 => _CO_DOWNLOADS_DOWNLOAD_VERSION_STATUS_FINAL, 2 => _CO_DOWNLOADS_DOWNLOAD_VERSION_STATUS_ALPHA, 3 => _CO_DOWNLOADS_DOWNLOAD_VERSION_STATUS_BETA, 4 => _CO_DOWNLOADS_DOWNLOAD_VERSION_STATUS_RC, 5 => _CO_DOWNLOADS_DOWNLOAD_VERSION_STATUS_NONE);
		}
		return $this->_download_version_status;
	}

	public function getDownloadLimitations() {
		if (!$this->_download_limitations) {
			$limitations_array = explode(",", icms::$module->config['downloads_limitations']);
			foreach (array_keys($limitations_array) as $i) {
				$this->_download_limitations[$limitations_array[$i]] = $limitations_array[$i];
			}
		}
		return $this->_download_limitations;
	}

	public function getDownloadLicense() {
		if (!$this->_download_license) {
			$license_array = explode(",", icms::$module->config['downloads_license']);
			$license = array();
			foreach (array_keys($license_array) as $i) {
				$this->_download_license[$license_array[$i]] = $license_array[$i];
			}
		}
		return $this->_download_license;
	}

	public function getDownloadPlatform() {
		if (!$this->_download_platform) {
			$platform_array = explode(",", icms::$module->config['downloads_platform']);
			foreach (array_keys($platform_array) as $i) {
				$this->_download_platform[$platform_array[$i]] = $platform_array[$i];
			}
		}
		return $this->_download_platform;
	}

	public function getLink($download_id = NULL) {
		$file = $this->get($download_id);
		$link = $file->getItemLink(FALSE);
		return $link;
	}

	public function getRelated() {
		if (!$this->_download_related) {
			$this->_download_related = $this->getList(TRUE);
		}
		return $this->_download_related;
	}

	public function getDownloadTags() {
		$sprocketsModule = icms::handler('icms_module')->getByDirname("sprockets");
		if($sprocketsModule && icms_get_module_status("sprockets")) {
			$sprockets_tag_handler = icms_getModuleHandler("tag", $sprocketsModule->getVar("dirname") , "sprockets");
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item("label_type", 0));
			$criteria->add(new icms_db_criteria_Item("navigation_element", 0));

			$tags = $sprockets_tag_handler->getObjects($criteria, TRUE, FALSE);
			$ret[] = '------------';
			foreach(array_keys($tags) as $i) {
				$ret[$tags[$i]['tag_id']] = $tags[$i]['title'];
			}
			return $ret;
		}
	}

	public function getPostersArray() {
		return icms::handler('icms_member')->getUserList();
	}

	public function makeLink($download) {
		$seo = str_replace(" ", "-", $download->getVar('short_url'));
		return $seo;
	}

	public function getCountCriteria ($active = FALSE, $approve = FALSE, $groups = array(), $perm = 'download_grpperm', $download_publisher = FALSE, $download_id = FALSE, $download_cid = FALSE, $tag_id = FALSE) {
		$criteria = new icms_db_criteria_Compo();
		if ($active) $criteria->add(new icms_db_criteria_Item('download_active', TRUE));
		if ($approve) $criteria->add(new icms_db_criteria_Item('download_approve', TRUE));
		if ($download_cid != FALSE)	{
			$critTray = new icms_db_criteria_Compo();
			$critTray->add(new icms_db_criteria_Item("download_cid", '%:"' . $download_cid . '";%', "LIKE"));
			$criteria->add($critTray);
		}
		if($tag_id) {
			$critTray = new icms_db_criteria_Compo();
			$critTray->add(new icms_db_criteria_Item("download_tags", '%:"' . $tag_id . '";%', "LIKE"));
			$criteria->add($critTray);
		}
		$this->setGrantedObjectsCriteria($criteria, "download_grpperm");
		return $this->getCount($criteria);
	}

	//update hit-counter
	public function updateCounter($download_id) {
		global $download_isAdmin;
		$downloadObj = $this->get($download_id);
		if (!is_object($downloadObj)) return FALSE;

		if (isset($downloadObj->vars['counter']) && !is_object(icms::$user) || (!$download_isAdmin && $downloadObj->getVar('download_publisher', 'e') != icms::$user->getVar("uid")) ) {
			$new_counter = $downloadObj->getVar('counter') + 1;
			$sql = 'UPDATE ' . $this->table . ' SET counter=' . $new_counter
				. ' WHERE ' . $this->keyName . '=' . $downloadObj->id();
			$this->query($sql, NULL, TRUE);
		}
		return TRUE;
	}

	// some fuctions related to icms core functions
	public function getDownloadsForSearch($queryarray, $andor, $limit, $offset, $userid) {
		$criteria = new icms_db_criteria_Compo();
		$criteria->setStart($offset);
		$criteria->setLimit($limit);
		if ($userid != 0) $criteria->add(new icms_db_criteria_Item('download_publisher', $userid));

		if ($queryarray) {
			$criteriaKeywords = new icms_db_criteria_Compo();
			for($i = 0; $i < count($queryarray); $i ++) {
				$criteriaKeyword = new icms_db_criteria_Compo();
				$criteriaKeyword->add(new icms_db_criteria_Item('download_title', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('download_description', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
				$criteriaKeywords->add($criteriaKeyword, $andor);
				unset($criteriaKeyword);
			}
			$criteria->add($criteriaKeywords);
		}
		$this->setGrantedObjectsCriteria($criteria, "download_grpperm");
		$criteria->add(new icms_db_criteria_Item('download_active', TRUE));
		$criteria->add(new icms_db_criteria_Item('download_approve', TRUE));
		return $this->getObjects($criteria, TRUE, FALSE);
	}

	public function updateComments($download_id, $total_num) {
		$downloadObj = $this->get($download_id);
		if ($downloadObj && !$downloadObj->isNew()) {
			$downloadObj->setVar('download_comments', $total_num);
			$this->insert($downloadObj, TRUE);
		}
	}

	// some related functions for storing
	protected function beforeInsert(&$obj) {
		$mirror_url = $obj->getVar('download_mirror_url');
		if(!$mirror_url == "") {
			$obj->setVar('download_has_mirror', 1);
		} else {
			$obj->setVar('download_has_mirror', 0);
		}
		$history = $obj->getVar("download_history", "s");
		$history = icms_core_DataFilter::checkVar($history, "html", "input");
		$obj->setVar("download_history", $history);

		$teaser = $obj->getVar("download_teaser", "s");
		$teaser = icms_core_DataFilter::checkVar($teaser, "html", "input");
		$obj->setVar("download_teaser", $teaser);

		return TRUE;
	}

	protected function afterSave(&$obj) {
		if ($obj->updating_counter)
		return TRUE;
		if (!$obj->getVar('download_notification_sent') && $obj->getVar('download_active', 'e') == TRUE && $obj->getVar('download_approve', 'e') == TRUE) {
			$obj->sendDownloadNotification('new_file');
			$obj->setVar('download_notification_sent', TRUE);
			$this->insert($obj);
		}
		if (!$obj->isNew() && ($obj->getVar('download_active', 'e') == TRUE) && ($obj->getVar('download_approve', 'e') == TRUE) && ($obj->getVar('download_notification_sent') == 1)) {
			$obj->sendDownloadNotification('file_modified');
		}
		return TRUE;
	}

	protected function afterDelete(& $obj) {
		$notification_handler = icms::handler( 'icms_data_notification' );
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname( icms::$module -> getVar( 'dirname' ) );
		$module_id = icms::$module->getVar('mid');
		$category = 'global';
		$download_id = $obj->id();
		// delete global notifications
		$notification_handler->unsubscribeByItem($module_id, $category, $download_id);

		$downloads_log_handler = icms_getModuleHandler("log", basename(dirname(__DIR__)), "downloads");
		if (!is_object(icms::$user)) {
			$log_uid = 0;
		} else {
			$log_uid = icms::$user->getVar("uid");
		}
		$logObj = $downloads_log_handler->create();
		$logObj->setVar('log_item_id', $obj->id() );
		$logObj->setVar('log_date', (time()-200) );
		$logObj->setVar('download_publisher', $log_uid);
		$logObj->setVar('log_item', 0 );
		$logObj->setVar('log_case', 2	 );
		$logObj->setVar('log_ip', xoops_getenv('REMOTE_ADDR') );
		$logObj->store(TRUE);
		return TRUE;
	}

}
