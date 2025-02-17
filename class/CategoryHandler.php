<?php
/**
 * 'Downloads' is a light weight category handling module for ImpressCMS
 *
 * File: /class/CategoryHandler.php
 *
 * Classes responsible for managing Downloads category objects
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

icms_loadLanguageFile('downloads', 'common');

class DownloadsCategoryHandler extends icms_ipf_Handler {

	public $_moduleName;

	public $_uploadPath;

	public $identifierName;

	public function __construct($db) {
		parent::__construct($db, 'category', 'category_id', 'category_title', 'category_description', 'downloads');
		$this->addPermission('category_grpperm', _CO_DOWNLOADS_CATEGORY_CATEGORY_GRPPERM, _CO_DOWNLOADS_CATEGORY_CATEGORY_GRPPERM_DSC);
		$this->addPermission('category_uplperm', _CO_DOWNLOADS_CATEGORY_CATEGORY_UPLPERM, _CO_DOWNLOADS_CATEGORY_CATEGORY_UPLPERM_DSC);
		$mimetypes = array('image/jpeg', 'image/png', 'image/gif');
		$this->enableUpload($mimetypes, 2000000, 500, 500);
	}

	// some criterias used by other requests
	public function getCategoryCriteria($start = 0, $limit = 0, $category_publisher = FALSE, $category_id = FALSE,  $category_pid = FALSE, $order = 'category_published_date', $sort = 'DESC') {
		$criteria = new icms_db_criteria_Compo();
		if ($start) $criteria->setStart($start);
		if ($limit) $criteria->setLimit((int)$limit);
		$criteria->setSort($order);
		$criteria->setOrder($sort);
		if ($category_publisher) $criteria->add(new icms_db_criteria_Item('category_publisher', $category_publisher));
		if ($category_id) {
			$crit = new icms_db_criteria_Compo(new icms_db_criteria_Item('short_url', $category_id,'LIKE'));
			$alt_category_id = str_replace('-',' ',$category_id);
			//Added for backward compatiblity in case short_url contains spaces instead of dashes.
			$crit->add(new icms_db_criteria_Item('short_url', $alt_category_id),'OR');
			$crit->add(new icms_db_criteria_Item('category_id', $category_id),'OR');
			$criteria->add($crit);
		}
		if ($category_pid !== FALSE)	$criteria->add(new icms_db_criteria_Item('category_pid', $category_pid));
		return $criteria;
	}

	public function getDownloadCategories($start = 0, $limit = 0, $category_publisher = FALSE, $category_id = FALSE,  $category_pid = FALSE, $order = 'weight', $sort = 'ASC', $approved= null, $active = null) {
		$criteria = $this->getCategoryCriteria($start, $limit, $category_publisher, $category_id,  $category_pid, $order, $sort);
		if($approved) $criteria->add(new icms_db_criteria_Item("category_approve", TRUE));
		if ($active) $criteria->add(new icms_db_criteria_Item("category_active", TRUE));
		$this->setGrantedObjectsCriteria($criteria, "category_grpperm");
		$categories = $this->getObjects($criteria, TRUE, FALSE);
		$ret = array();
		foreach ($categories as $category){
			$ret[$category['category_id']] = $category;
		}
		return $ret;
	}

	public function getCategoryListForPid($groups = array(), $perm = 'category_grpperm', $status = null,$approved = null, $category_id = null, $showNull = TRUE) {

		$criteria = new icms_db_criteria_Compo();
		$this->setGrantedObjectsCriteria($criteria, "category_grpperm");
		if (isset($status)) {
			$criteria->add(new icms_db_criteria_Item('category_active', TRUE));
		}
		if (isset($approved)) {
			$criteria->add(new icms_db_criteria_Item('category_approve', TRUE));
		}
		if (is_null($category_id)) $category_id = 0;
		$criteria->add(new icms_db_criteria_Item('category_pid', $category_id));
		$this->setGrantedObjectsCriteria($criteria, "category_grpperm");
		$categories = & $this->getObjects($criteria, TRUE);
		$ret = array();
		if ($showNull) {
			$ret[0] = '-----------------------';
		}
		foreach(array_keys($categories) as $i) {
			$ret[$i] = $categories[$i]->getVar('category_title');
			$subcategories = $this->getCategoryListForPid($groups, $perm, $status, $approved, $categories[$i]->getVar('category_id'), $showNull);
			foreach(array_keys($subcategories) as $j) {
				$ret[$j] = '-' . $subcategories[$j];
			}
		}
		return $ret;
	}

	public function makeLink($category) {
		$seo = str_replace(" ", "-", $category->getVar('short_url'));
		return $seo;
	}

	//set category online/offline
	public function changeVisible($category_id) {
		$visibility = '';
		$categoryObj = $this->get($category_id);
		if ($categoryObj->getVar('category_active', 'e') == TRUE) {
			$categoryObj->setVar('category_active', 0);
			$visibility = 0;
		} else {
			$categoryObj->setVar('category_active', 1);
			$visibility = 1;
		}
		$this->insert($categoryObj, TRUE);
		return $visibility;
	}

	// show/hide category in Block
	public function changeShow($category_id) {
		$show = '';
		$categoryObj = $this->get($category_id);
		if ($categoryObj->getVar('category_inblocks', 'e') == TRUE) {
			$categoryObj->setVar('category_inblocks', 0);
			$show = 0;
		} else {
			$categoryObj->setVar('category_inblocks', 1);
			$show = 1;
		}
		$this->insert($categoryObj, TRUE);
		return $show;
	}

	// approve/deny categories created on userside
	public function changeApprove($category_id) {
		$approve = '';
		$categoryObj = $this->get($category_id);
		if ($categoryObj->getVar('category_approve', 'e') == TRUE) {
			$categoryObj->setVar('category_approve', 0);
			$approve = 0;
		} else {
			$categoryObj->setVar('category_approve', 1);
			$approve = 1;
		}
		$this->insert($categoryObj, TRUE);
		return $approve;
	}

	public function category_active_filter() {
		return array(0 => 'Offline', 1 => 'Online');
	}

	public function category_inblocks_filter() {
		return array(0 => 'Hidden', 1 => 'Visible');
	}

	public function category_approve_filter() {
		return array(0 => 'Denied', 1 => 'Approved');
	}

	static public function getImageList() {
		$categoryimages = array();
		$categoryimages = icms_core_Filesystem::getFileList(ICMS_ROOT_PATH . '/uploads/' . icms::$module -> getVar( 'dirname' ) . '/category/', '', array('gif', 'jpg', 'png'));
		$ret = array();
		$ret[0] = '-----------------------';
		foreach(array_keys($categoryimages) as $i ) {
			$ret[$i] = $categoryimages[$i];
		}
		return $ret;
	}

	public function getCategoriesCount ( $groups, $perm = 'category_grpperm', $category_publisher = FALSE, $category_id = null, $category_pid = null,$active = null, $approve = null) {
		$criteria = new icms_db_criteria_Compo();

		if (isset($active)) {
			$criteria->add(new icms_db_criteria_Item('category_active', TRUE));
		}
		if (isset($approve)) {
			$criteria->add(new icms_db_criteria_Item('category_approve', TRUE));
		}
		if($category_id) $criteria->add(new icms_db_criteria_Item('category_id', $category_id));
		if (is_null($category_pid)) $category_pid == 0;
		$criteria->add(new icms_db_criteria_Item('category_pid', $category_pid));
		$this->setGrantedObjectsCriteria($criteria, "category_grpperm");
		return $this->getCount($criteria);
	}

	public function userCanSubmit() {
		global $downloads_isAdmin;
		if (!is_object(icms::$user)) return FALSE;
		if ($downloads_isAdmin) return TRUE;
		$user_groups = icms::$user->getGroups();
		$module = icms::handler("icms_module")->getByDirname(basename(dirname(__DIR__)), TRUE);
		return count(array_intersect_key($module->config['downloads_allowed_groups'], $user_groups)) > 0;
	}

	// get breadcrumb
	public function getBreadcrumbForPid($category_id, $userside=FALSE){
		$ret = FALSE;
		if ($category_id == FALSE) {
			return $ret;
		} else {
			if ($category_id > 0) {
				$category = $this->get($category_id);
				if ($category->getVar('category_id', 'e') > 0) {
					if (!$userside) {
						$ret = "<a href='" . DOWNLOADS_URL . "index.php?category_id=" . $category->getVar('category_id', 'e') . "&amp;category_pid=" . $category->getVar('category_id', 'e') . "'>" . $category->getVar('category_title', 'e') . "</a>";
					} else {
						$ret = "<a href='" . DOWNLOADS_URL . "index.php?category_id=" . $category->getVar('category_id', 'e') . "&amp;category=" . $this->makeLink($category) . "'>" . $category->getVar('category_title', 'e') . "</a>";
					}
					if ($category->getVar('category_pid', 'e') == 0) {
						if (!$userside){
							return "<a href='" . DOWNLOADS_URL . "index.php?category_id=" . $category->getVar('category_id', 'e') . "'>" . _MI_DOWNLOADS_CATEGORY . "</a> &nbsp;:&nbsp; " . $ret;
						} else {
							return $ret;
						}
					} elseif ($category->getVar('category_pid','e') > 0) {
						$ret = $this->getBreadcrumbForPid($category->getVar('category_pid', 'e'), $userside) . " &nbsp;:&nbsp; " . $ret;
					}
				}
			} else {
				return $ret;
			}
		}
		return $ret;
	}

	//update hit-counter
	public function updateCounter($category_id) {
		global $downloads_isAdmin;
		$categoryObj = $this->get($category_id);
		if (!is_object($categoryObj)) return FALSE;

		if (isset($categoryObj->vars['counter']) && !is_object(icms::$user) || (!$downloads_isAdmin && $categoryObj->getVar('category_publisher', 'e') != icms::$user->uid ()) ) {
			$new_counter = $categoryObj->getVar('counter') + 1;
			$sql = 'UPDATE ' . $this->table . ' SET counter=' . $new_counter
				. ' WHERE ' . $this->keyName . '=' . $categoryObj->id();
			$this->query($sql, null, TRUE);
		}
		return TRUE;
	}
	// some related functions for storing
	protected function beforeSave(&$obj) {
		if ($obj->updating_counter)
		return TRUE;
		if ($obj->getVar('category_pid','e') == $obj->getVar('category_id','e')){
			$obj->setVar('category_pid', 0);
		}
		if (!$obj->getVar('category_img_upload') == "") {
			$obj->setVar('category_img', $obj->getVar('category_img_upload') );
		}
		$obj->setVar( 'category_published_date', (time() - 300) );
		return TRUE;
	}

	protected function afterSave(&$obj) {
		if ($obj->updating_counter)
		return TRUE;

		if (!$obj->getVar('category_notification_sent') && $obj->getVar('category_active', 'e') == TRUE && $obj->getVar('category_approve', 'e') == TRUE) {
			$obj->sendCategoryNotification('new_category');
			$obj->setVar('category_notification_sent', TRUE);
			$this->insert($obj);
		}
		if(!$obj->isNew() && ($obj->getVar('category_notification_sent') == 1) && ($obj->getVar('category_active', 'e') == TRUE) && ($obj->getVar('category_approve', 'e') == TRUE)) {
			$obj->sendCategoryNotification('category_modified');
		}
		return TRUE;
	}

	protected function afterDelete(& $obj) {
		$notification_handler = icms::handler( 'icms_data_notification' );
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname( icms::$module -> getVar( 'dirname' ) );
		$module_id = icms::$module->getVar('mid');
		$category = 'global';
		$category_id = $obj->id();
		// delete global notifications
		$notification_handler->unsubscribeByItem($module_id, $category, $category_id);
		return TRUE;
		//todo reactivate the log functionality
		$downloads_log_handler = icms_getModuleHandler("log", basename(dirname(__DIR__)), "downloads");
		if (!is_object(icms::$user)) {
			$log_uid = 0;
		} else {
			$log_uid = icms::$user->getVar("uid");
		}
		$logObj = $downloads_log_handler->create();
		$logObj->setVar('log_item_id', $obj->id() );
		$logObj->setVar('log_date', (time()-200) );
		$logObj->setVar('log_uid', $log_uid);
		$logObj->setVar('log_item', 1 );
		$logObj->setVar('log_case', 2 );
		$logObj->setVar('log_ip', xoops_getenv('REMOTE_ADDR') );
		$logObj->store(TRUE);
	}

}
