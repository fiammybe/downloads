<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/Category.php
 *
 * Class representing Download category objects
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
 * Category entity class
 *
 * Represents a download category with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsCategory extends icms_ipf_seo_Object
{
	public bool $updating_counter = false;
	public bool $categories = true;

	/**
	 * Constructor
	 *
	 * @param icms_ipf_Handler $handler Handler instance
	 */
	public function __construct(icms_ipf_Handler &$handler)
	{
		icms_ipf_object::__construct($handler);

		$this->quickInitVar('category_id', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('category_title', XOBJ_DTYPE_TXTBOX, TRUE);
		$this->initCommonVar('short_url');
		$this->quickInitVar('category_pid', XOBJ_DTYPE_INT, FALSE);
		$this->quickInitVar('category_img', XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar('category_img_upload', XOBJ_DTYPE_IMAGE);
		$this->quickInitVar('category_description', XOBJ_DTYPE_TXTAREA);
		$this->quickInitVar('category_active', XOBJ_DTYPE_INT, FALSE, FALSE,FALSE, 1);
		$this->quickInitVar('category_inblocks',XOBJ_DTYPE_INT, FALSE, FALSE,FALSE, 1);
		$this->quickInitVar('category_approve',XOBJ_DTYPE_INT);
		$this->quickInitVar('category_published_date', XOBJ_DTYPE_LTIME);
		$this->quickInitVar('category_updated_date', XOBJ_DTYPE_LTIME);
		$this->quickInitVar('category_publisher', XOBJ_DTYPE_TXTBOX);
		$this->quickInitVar('category_submitter', XOBJ_DTYPE_TXTBOX);
		$this->quickInitVar('category_notification_sent', XOBJ_DTYPE_INT, FALSE);
		$this->initCommonVar('weight');
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml', FALSE, 1);
		$this->initCommonVar('dobr', TRUE, 1);
		$this->initCommonVar('doimage', TRUE, 1);
		$this->initCommonVar('dosmiley', TRUE, 1);
		$this->initCommonVar('docxode', TRUE, 1);
		$this->quickInitVar('category_sub', XOBJ_DTYPE_INT);
		// set controls
		$this->setControl('category_pid', 'parentcategory');
		$this->setControl('category_description', 'dhtmltextarea');
		$this->setControl('category_img', array( 'name' => 'select', 'itemHandler' => 'category', 'method' => 'getImageList', 'module' => 'downloads'));
		$this->setControl('category_img_upload', 'imageupload');
		$this->setControl('category_active', 'yesno');
		$this->setControl('category_inblocks', 'yesno');
		$this->setControl('category_approve', 'yesno');
		$this->setControl('category_grpperm', array('name' => 'selectmulti', 'itemHandler' => 'category', 'method' => 'getGroups', 'module' => 'downloads'));
		$this->setControl('category_uplperm', array('name' => 'selectmulti', 'itemHandler' => 'category', 'method' => 'getUplGroups', 'module' => 'downloads'));
		$this->setControl('category_publisher', 'user');
		// hide static fields from form
		$this->hideFieldFromForm(array('category_submitter', 'category_notification_sent', 'category_sub','counter', 'dohtml', 'dobr', 'doimage', 'dosmiley', 'docxode'));
		// hide fields from single view
		$this->hideFieldFromSingleView(array('category_notification_sent', 'weight', 'category_sub','counter', 'dohtml', 'dobr', 'doimage', 'dosmiley', 'docxode'));

		//make use of seo
		$this->initiateSEO();
	}

	/**
	 * Get publisher as userlink
	 *
	 * @param bool $link Whether to return as link
	 * @return string Publisher userlink
	 */
	public function getCategoryPublisher(bool $link = false): string
	{
		return icms_member_user_Handler::getUserLink($this->getVar('category_publisher', 'e'));
	}

	/**
	 * Get formatted published date
	 *
	 * @return string Formatted date
	 */
	public function getCategoryPublishedDate(): string
	{
		$date = $this->getVar('category_published_date', 'e');
		return date(icms::$module->config['downloads_dateformat'], $date);
	}

	/**
	 * Get formatted updated date
	 *
	 * @return string Formatted date
	 */
	public function getCategoryUpdatedDate(): string
	{
		$date = $this->getVar('category_updated_date', 'e');
		return date(icms::$module->config['downloads_dateformat'], $date);
	}

	/**
	 * Get category image tag
	 *
	 * @return string Image URL or empty string
	 */
	public function getCategoryImageTag(): string
	{
		$category_img = $this->getVar('category_img', 'e');
		if (!empty($category_img)) {
			return DOWNLOADS_UPLOAD_URL . 'category/' . $category_img;
		}
		return '';
	}

	/**
	 * Get category active status with toggle link
	 *
	 * @return string HTML link with status image
	 */
	public function category_active(): string
	{
		$active = $this->getVar('category_active', 'e');
		$categoryId = $this->getVar('category_id');
		$icon = $active ? 'visible.png' : 'hidden.png';
		$alt = $active ? 'Online' : 'Offline';

		return sprintf(
			'<a href="%scategory.php?category_id=%d&amp;op=visible"><img src="%s%s" alt="%s" /></a>',
			DOWNLOADS_ADMIN_URL,
			$categoryId,
			DOWNLOADS_IMAGES_URL,
			$icon,
			$alt
		);
	}

	/**
	 * Get category in blocks status with toggle link
	 *
	 * @return string HTML link with status image
	 */
	public function category_inblocks(): string
	{
		$active = $this->getVar('category_inblocks', 'e');
		$categoryId = $this->getVar('category_id');
		$icon = $active ? 'approved.png' : 'denied.png';
		$alt = $active ? 'Visible' : 'Hidden';

		return sprintf(
			'<a href="%scategory.php?category_id=%d&amp;op=changeShow"><img src="%s%s" alt="%s" /></a>',
			DOWNLOADS_ADMIN_URL,
			$categoryId,
			DOWNLOADS_IMAGES_URL,
			$icon,
			$alt
		);
	}

	/**
	 * Get category approve status with toggle link
	 *
	 * @return string HTML link with status image
	 */
	public function category_approve(): string
	{
		$active = $this->getVar('category_approve', 'e');
		$categoryId = $this->getVar('category_id');
		$icon = $active ? 'approved.png' : 'denied.png';
		$alt = $active ? 'Approved' : 'Denied';

		return sprintf(
			'<a href="%scategory.php?category_id=%d&amp;op=changeApprove"><img src="%s%s" alt="%s" /></a>',
			DOWNLOADS_ADMIN_URL,
			$categoryId,
			DOWNLOADS_IMAGES_URL,
			$icon,
			$alt
		);
	}

	/**
	 * Get category weight control
	 *
	 * @return string Rendered weight control
	 */
	public function getCategoryWeightControl(): string
	{
		$control = new icms_form_elements_Text('', 'weight[]', 5, 7, $this->getVar('weight', 'e'));
		$control->setExtra('style="text-align:center;"');
		return $control->render();
	}

	/**
	 * Get parent category
	 *
	 * @return string Parent category link or name
	 */
	public function category_pid(): string
	{
		static $category_pidArray;
		if (!is_array($category_pidArray)) {
			$category_pidArray = $this->handler->getCategoryListForPid();
		}
		$ret = $this->getVar('category_pid', 'e');
		if ($ret > 0) {
			return sprintf(
				'<a href="%sindex.php?category_id=%d">%s</a>',
				DOWNLOADS_URL,
				$ret,
				str_replace('-', '', $category_pidArray[$ret])
			);
		}
		return $category_pidArray[$ret];
	}

	/**
	 * Check if user has submit access
	 *
	 * @return bool True if access granted
	 */
	public function submitAccessGranted(): bool
	{
		$gperm_handler = icms::handler('icms_member_groupperm');
		$groups = is_object(icms::$user) ? icms::$user->getGroups() : [ICMS_GROUP_ANONYMOUS];
		$module = icms::handler('icms_module')->getByDirname(basename(dirname(__DIR__)));
		$viewperm = $gperm_handler->checkRight(
			'submit_downloads',
			$this->getVar('category_id', 'e'),
			$groups,
			$module->getVar('mid')
		);

		if (is_object(icms::$user) && icms::$user->getVar('uid') == $this->getVar('category_publisher', 'e')) {
			return true;
		}

		return $viewperm
			&& $this->getVar('category_active', 'e') == true
			&& $this->getVar('category_approve', 'e') == true;
	}

	/**
	 * Check if user has access to view category
	 *
	 * @param string|null $perm_name Permission name
	 * @return bool True if access granted
	 */
	public function accessGranted(?string $perm_name = null): bool
	{
		$gperm_handler = icms::handler('icms_member_groupperm');
		$groups = is_object(icms::$user) ? icms::$user->getGroups() : [ICMS_GROUP_ANONYMOUS];
		$module = icms::handler('icms_module')->getByDirname(basename(dirname(__FILE__, 2)));
		$viewperm = $gperm_handler->checkRight(
			'category_grpperm',
			$this->getVar('category_id', 'e'),
			$groups,
			icms::$module->getVar('mid')
		);

		if (is_object(icms::$user) && icms::$user->getVar('uid') == $this->getVar('category_publisher', 'e')) {
			return true;
		}

		return $viewperm
			&& $this->getVar('category_active', 'e') == true
			&& $this->getVar('category_approve', 'e') == true;
	}

	/**
	 * Check if user can edit and delete category
	 *
	 * @return bool True if user can edit/delete
	 */
	public function userCanEditAndDelete(): bool
	{
		global $downloads_isAdmin;
		if (!is_object(icms::$user)) {
			return false;
		}
		if ($downloads_isAdmin) {
			return true;
		}
		return $this->getVar('category_publisher', 'e') == icms::$user->getVar('uid');
	}

	/**
	 * Get view item link
	 *
	 * @param bool $onlyUrl Return only URL
	 * @param bool $linkTitle Link title
	 * @param bool $linkTarget Link target
	 * @return string View link HTML
	 */
	public function getViewItemLink(bool $onlyUrl = false, bool $linkTitle = false, bool $linkTarget = false): string
	{
		return sprintf(
			'<a href="%scategory.php?op=view&amp;category_id=%d" title="%s"><img src="%s/actions/viewmag.png" /></a>',
			DOWNLOADS_ADMIN_URL,
			$this->getVar('category_id', 'e'),
			_CO_DOWNLOADS_VIEW,
			ICMS_IMAGES_SET_URL
		);
	}

	/**
	 * Get item link
	 *
	 * @param bool $onlyUrl Return only URL
	 * @return string Item link or URL
	 */
	public function getItemLink(bool $onlyUrl = false): string
	{
		$seo = $this->handler->makelink($this);
		$url = sprintf(
			'%sindex.php?category_id=%d&amp;cat=%s',
			DOWNLOADS_URL,
			$this->getVar('category_id'),
			$seo
		);

		if ($onlyUrl) {
			return $url;
		}

		return sprintf(
			'<a href="%s" title="%s">%s</a>',
			$url,
			$this->getVar('category_title'),
			$this->getVar('category_title')
		);
	}

	/**
	 * Get preview item link
	 *
	 * @return string Preview link HTML
	 */
	public function getPreviewItemLink(): string
	{
		return sprintf(
			'<a href="%sindex.php?category_id=%d" title="%s" target="_blank">%s</a>',
			DOWNLOADS_URL,
			$this->getVar('category_id', 'e'),
			_CO_DOWNLOADS_PREVIEW,
			$this->getVar('category_title')
		);
	}

	/**
	 * Check if user can submit file (placeholder)
	 *
	 * @return void
	 */
	public function getUserCanSubmitFile(): void
	{
		// Placeholder method
	}

	/**
	 * Check if user can submit and return link
	 *
	 * @return string|false Submit link or false
	 */
	public function userCanSubmit(): string|false
	{
		$submit = $this->handler->userCanSubmit();
		if ($submit) {
			return DOWNLOADS_URL . 'category.php?op=mod&amp;category_pid=' . $this->getVar('category_id', 'e');
		}
		return false;
	}

	function toArray() {
		$ret = parent::toArray();
		$ret['id'] = $this->getVar("category_id", "e");
		$ret['title'] = $this->getVar("category_title", "e");
		$ret['img'] = $this->getCategoryImageTag();
		$ret['dsc'] = $this->getVar('category_description');
		$ret['published_date'] = $this->getCategoryPublishedDate();
		$ret['updated_date'] = $this->getCategoryUpdatedDate();
		$ret['publisher'] = $this->getCategoryPublisher(TRUE);
		$ret['editItemLink'] = $this->getEditItemLink(FALSE, TRUE, TRUE);
		$ret['deleteItemLink'] = $this->getDeleteItemLink(FALSE, TRUE, TRUE);
		$ret['userCanEditAndDelete'] = $this->userCanEditAndDelete();
		$ret['itemLink'] = $this->getItemLink(TRUE);
		$ret['catlink'] = $this->getItemLink(FALSE);
		$ret['accessgranted'] = $this->accessGranted('category_grpperm');
		$ret['user_upload'] = $this->submitAccessGranted();
		$ret['user_submit'] = $this->userCanSubmit();
		return $ret;
	}

	function sendCategoryNotification($case) {
		$valid_case = array('new_category', 'category_submitted', 'category_modified', 'category_approved');
		if(in_array($case, $valid_case, TRUE)) {
			$module = icms::handler('icms_module')->getByDirname(basename(dirname(dirname(__FILE__))));
			$mid = $module->getVar('mid');
			$tags ['CATEGORY_TITLE'] = $this->getVar('category_title');
			$tags ['CATEGORY_URL'] = $this->getItemLink(FALSE);
			switch ($case) {
                case 'category_modified':
                case 'category_submitted':
                case 'new_category':
					$category = 'global';
					$cat_id = 0;
					$recipient = array();
					break;

                case 'category_approved':
					$category = 'category';
					$cat_id = $this->id();
					$recipient = $this->getVar("category_publisher", "e");
					break;

			}
			icms::handler('icms_data_notification')->triggerEvent($category, $cat_id, $case, $tags, $recipient, $mid);
		}

	}

	function getReads() {
		return $this->getVar('counter');
	}

	function setReads($qtde = null) {
		$t = $this->getVar('counter');
		if (isset($qtde)) {
			$t += $qtde;
		} else {
			$t++;
		}
		$this->setVar('counter', $t);
	}
}
