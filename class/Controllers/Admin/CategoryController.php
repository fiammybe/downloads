<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/Admin/CategoryController.php
 *
 * Admin category controller for the downloads module
 *
 * @copyright  Copyright QM-B (Steffen Flohrer) 2011
 * @copyright  The ImpressCMS Project
 * @license    GPL 2.0
 * @package    downloads
 * @since      2.0.0
 * @author     fiammybe
 * @version    2.0.0
 */

defined('ICMS_ROOT_PATH') or die('ICMS root path not defined');

require_once dirname(__DIR__) . '/BaseController.php';

/**
 * Admin Category Controller for Downloads Module
 *
 * Handles admin category operations
 *
 * @package Downloads\Controllers\Admin
 */
class DownloadsAdminCategoryController extends DownloadsBaseController
{
	private DownloadsLogHandler $logHandler;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->logHandler = icms_getModuleHandler('log', DOWNLOADS_DIRNAME, 'downloads');
	}

	/**
	 * Handle category operations
	 *
	 * @return void
	 */
	public function handle(): void
	{
		if (!$this->isAdmin()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		$op = $this->getString('op', '');
		$categoryId = $this->getInt('category_id', 0);

		match ($op) {
			'mod', 'changedField' => $this->edit($categoryId),
			'addcategory' => $this->save(),
			'del' => $this->delete($categoryId),
			'visible' => $this->toggleVisibility($categoryId),
			'changeShow' => $this->toggleShow($categoryId),
			'changeApprove' => $this->toggleApprove($categoryId),
			'changeWeight' => $this->changeWeight($categoryId),
			default => $this->listCategories(),
		};
	}

	/**
	 * List all categories
	 *
	 * @return void
	 */
	public function listCategories(): void
	{
		icms_cp_header();
		downloads_adminmenu(2, _MI_DOWNLOADS_MENU_CATEGORY);

		$objectTable = new icms_ipf_view_Table($this->categoryHandler, []);
		$objectTable->addColumn(new icms_ipf_view_Column('category_title', 'left', 200));
		$objectTable->addColumn(new icms_ipf_view_Column('category_description', 'left'));
		$objectTable->addColumn(new icms_ipf_view_Column('category_weight', 'center', 50));
		$objectTable->addColumn(new icms_ipf_view_Column('category_approve', 'center', 80));

		$objectTable->addIntroButton('addcategory', 'category.php?op=mod', _AM_DOWNLOADS_CATEGORY_CREATE);
		$objectTable->addActionButton('edit', false, 'category.php?op=mod&category_id=');
		$objectTable->addActionButton('delete', false, 'category.php?op=del&category_id=');

		$icmsAdminTpl = new icms_view_Tpl();
		$objectTable->render($icmsAdminTpl);

		$icmsAdminTpl->display('db:downloads_admin.html');
		icms_cp_footer();
	}

	/**
	 * Edit or create a category
	 *
	 * @param int $categoryId Category ID (0 for new)
	 * @return void
	 */
	public function edit(int $categoryId = 0): void
	{
		icms_cp_header();

		$categoryObj = $this->categoryHandler->get($categoryId);
		$logUid = $this->getCurrentUserId();

		if (!$categoryObj->isNew()) {
			$categoryObj->hideFieldFromForm([
				'category_published_date',
				'category_updated_date'
			]);

			$categoryObj->setVar('category_updated_date', time() - 100);

			$this->logAction($categoryObj->getVar('category_id'), $logUid, 1, 3);

			downloads_adminmenu(2, _MI_DOWNLOADS_MENU_CATEGORY . ' > ' . _MI_DOWNLOADS_CATEGORY_EDIT);
			$sform = $categoryObj->getForm(_AM_DOWNLOADS_EDIT, 'addcategory');
		} else {
			$categoryObj->hideFieldFromForm([
				'category_approve',
				'category_published_date',
				'category_updated_date'
			]);

			$categoryObj->setVar('category_published_date', time() - 100);
			$categoryObj->setVar('category_approve', true);
			$categoryObj->setVar('category_submitter', $logUid);

			$this->logAction($categoryObj->getVar('category_id'), $logUid, 1, 1);

			downloads_adminmenu(2, _MI_DOWNLOADS_MENU_CATEGORY . ' > ' . _MI_DOWNLOADS_CATEGORY_CREATINGNEW);
			$sform = $categoryObj->getForm(_AM_DOWNLOADS_CREATE, 'addcategory');
		}

		$icmsAdminTpl = new icms_view_Tpl();
		$sform->assign($icmsAdminTpl);
		$icmsAdminTpl->display('db:downloads_admin.html');

		icms_cp_footer();
	}

	/**
	 * Save category
	 *
	 * @return void
	 */
	public function save(): void
	{
		if (!$this->validateToken()) {
			$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$controller = new icms_ipf_Controller($this->categoryHandler);
		$controller->storeFromDefaultForm(_AM_DOWNLOADS_CREATED, _AM_DOWNLOADS_MODIFIED);
	}

	/**
	 * Delete category
	 *
	 * @param int $categoryId Category ID
	 * @return void
	 */
	public function delete(int $categoryId): void
	{
		if (!$this->validateToken()) {
			$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$controller = new icms_ipf_Controller($this->categoryHandler);
		$controller->handleObjectDeletion();

		$this->logAction($categoryId, $this->getCurrentUserId(), 1, 2);
	}

	/**
	 * Toggle visibility
	 *
	 * @param int $categoryId Category ID
	 * @return void
	 */
	private function toggleVisibility(int $categoryId): void
	{
		$controller = new icms_ipf_Controller($this->categoryHandler);
		$controller->handleObjectVisibilityToggle();

		$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_VISIBILITY_CHANGED, 2);
	}

	/**
	 * Toggle show status
	 *
	 * @param int $categoryId Category ID
	 * @return void
	 */
	private function toggleShow(int $categoryId): void
	{
		$categoryObj = $this->categoryHandler->get($categoryId);

		if ($categoryObj && !$categoryObj->isNew()) {
			$currentValue = $categoryObj->getVar('category_show');
			$categoryObj->setVar('category_show', !$currentValue);
			$this->categoryHandler->insert($categoryObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_SHOW_CHANGED, 2);
	}

	/**
	 * Toggle approve status
	 *
	 * @param int $categoryId Category ID
	 * @return void
	 */
	private function toggleApprove(int $categoryId): void
	{
		$categoryObj = $this->categoryHandler->get($categoryId);

		if ($categoryObj && !$categoryObj->isNew()) {
			$currentValue = $categoryObj->getVar('category_approve');
			$categoryObj->setVar('category_approve', !$currentValue);
			$this->categoryHandler->insert($categoryObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_APPROVE_CHANGED, 2);
	}

	/**
	 * Change weight
	 *
	 * @param int $categoryId Category ID
	 * @return void
	 */
	private function changeWeight(int $categoryId): void
	{
		$weight = $this->postInt('category_weight', 0);
		$categoryObj = $this->categoryHandler->get($categoryId);

		if ($categoryObj && !$categoryObj->isNew()) {
			$categoryObj->setVar('category_weight', $weight);
			$this->categoryHandler->insert($categoryObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_WEIGHT_CHANGED, 2);
	}

	/**
	 * Log an action
	 *
	 * @param int $itemId Item ID
	 * @param int $uid User ID
	 * @param int $item Item type (1 = category)
	 * @param int $case Action case (1 = create, 2 = delete, 3 = edit)
	 * @return void
	 */
	private function logAction(int $itemId, int $uid, int $item, int $case): void
	{
		$logObj = $this->logHandler->create();
		$logObj->setVar('log_item_id', $itemId);
		$logObj->setVar('log_date', time() - 200);
		$logObj->setVar('log_uid', $uid);
		$logObj->setVar('log_item', $item);
		$logObj->setVar('log_case', $case);
		$logObj->setVar('log_ip', xoops_getenv('REMOTE_ADDR'));
		$logObj->store(true);
	}
}

