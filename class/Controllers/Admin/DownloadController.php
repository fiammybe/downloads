<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/Admin/DownloadController.php
 *
 * Admin download controller for the downloads module
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
 * Admin Download Controller for Downloads Module
 *
 * Handles admin download operations
 *
 * @package Downloads\Controllers\Admin
 */
class DownloadsAdminDownloadController extends DownloadsBaseController
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
	 * Handle download operations
	 *
	 * @return void
	 */
	public function handle(): void
	{
		if (!$this->isAdmin()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		// Check if categories exist
		$count = $this->categoryHandler->getCount(false, true, false);
		if ($count <= 0) {
			$this->redirect(DOWNLOADS_ADMIN_URL . 'category.php', _AM_DOWNLOADS_NO_CAT_FOUND, 3);
			return;
		}

		$op = $this->getString('op', '');
		$downloadId = $this->getInt('download_id', 0);

		match ($op) {
			'mod', 'changedField' => $this->edit($downloadId),
			'adddownload' => $this->save(),
			'del' => $this->delete($downloadId),
			'visible' => $this->toggleVisibility($downloadId),
			'changeShow' => $this->toggleShow($downloadId),
			'changeBroken' => $this->toggleBroken($downloadId),
			'changeApprove' => $this->toggleApprove($downloadId),
			'changeMirrorApprove' => $this->toggleMirrorApprove($downloadId),
			'changeWeight' => $this->changeWeight($downloadId),
			default => $this->listDownloads(),
		};
	}

	/**
	 * List all downloads
	 *
	 * @return void
	 */
	public function listDownloads(): void
	{
		icms_cp_header();
		downloads_adminmenu(1, _MI_DOWNLOADS_MENU_DOWNLOAD);

		$objectTable = new icms_ipf_view_Table($this->downloadHandler, []);
		$objectTable->addColumn(new icms_ipf_view_Column('download_title', 'left', 200));
		$objectTable->addColumn(new icms_ipf_view_Column('download_cid', 'left', 150));
		$objectTable->addColumn(new icms_ipf_view_Column('download_version', 'center', 80));
		$objectTable->addColumn(new icms_ipf_view_Column('download_counter', 'center', 80));
		$objectTable->addColumn(new icms_ipf_view_Column('download_approve', 'center', 80));
		$objectTable->addColumn(new icms_ipf_view_Column('download_broken', 'center', 80));

		$objectTable->addIntroButton('adddownload', 'download.php?op=mod', _AM_DOWNLOADS_DOWNLOAD_CREATE);
		$objectTable->addActionButton('edit', false, 'download.php?op=mod&download_id=');
		$objectTable->addActionButton('delete', false, 'download.php?op=del&download_id=');

		$icmsAdminTpl = new icms_view_Tpl();
		$objectTable->render($icmsAdminTpl);

		$icmsAdminTpl->display('db:downloads_admin.html');
		icms_cp_footer();
	}

	/**
	 * Edit or create a download
	 *
	 * @param int $downloadId Download ID (0 for new)
	 * @return void
	 */
	public function edit(int $downloadId = 0): void
	{
		icms_cp_header();

		$downloadObj = $this->downloadHandler->get($downloadId);
		$logUid = $this->getCurrentUserId();

		if (!$downloadObj->isNew()) {
			$downloadObj->hideFieldFromForm([
				'download_published_date',
				'download_updated_date',
				'download_approve'
			]);

			$downloadObj->setVar('download_updated_date', time() - 100);

			$this->logAction($downloadObj->id(), $logUid, 0, 3);

			downloads_adminmenu(1, _MI_DOWNLOADS_MENU_DOWNLOAD . ' > ' . _MI_DOWNLOADS_DOWNLOAD_EDIT);
			$sform = $downloadObj->getForm(_AM_DOWNLOADS_EDIT, 'adddownload');
		} else {
			$downloadObj->hideFieldFromForm([
				'download_published_date',
				'download_updated_date',
				'download_approve'
			]);

			$downloadObj->setVar('download_approve', true);
			$downloadObj->setVar('download_mirror_approve', true);
			$downloadObj->setVar('download_has_mirror', true);
			$downloadObj->setVar('download_published_date', time() - 100);
			$downloadObj->setVar('download_submitter', $logUid);

			$this->logAction($downloadObj->id(), $logUid, 0, 1);

			downloads_adminmenu(1, _MI_DOWNLOADS_MENU_DOWNLOAD . ' > ' . _MI_DOWNLOADS_DOWNLOAD_CREATINGNEW);
			$sform = $downloadObj->getForm(_AM_DOWNLOADS_CREATE, 'adddownload');
		}

		$icmsAdminTpl = new icms_view_Tpl();
		$sform->assign($icmsAdminTpl);
		$icmsAdminTpl->display('db:downloads_admin.html');

		icms_cp_footer();
	}

	/**
	 * Save download
	 *
	 * @return void
	 */
	public function save(): void
	{
		if (!$this->validateToken()) {
			$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$controller = new icms_ipf_Controller($this->downloadHandler);
		$controller->storeFromDefaultForm(_AM_DOWNLOADS_CREATED, _AM_DOWNLOADS_MODIFIED);
	}

	/**
	 * Delete download
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	public function delete(int $downloadId): void
	{
		if (!$this->validateToken()) {
			$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$controller = new icms_ipf_Controller($this->downloadHandler);
		$controller->handleObjectDeletion();

		$this->logAction($downloadId, $this->getCurrentUserId(), 0, 2);
	}

	/**
	 * Toggle visibility
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function toggleVisibility(int $downloadId): void
	{
		$controller = new icms_ipf_Controller($this->downloadHandler);
		$controller->handleObjectVisibilityToggle();

		$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_VISIBILITY_CHANGED, 2);
	}

	/**
	 * Toggle show status
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function toggleShow(int $downloadId): void
	{
		$downloadObj = $this->downloadHandler->get($downloadId);

		if ($downloadObj && !$downloadObj->isNew()) {
			$currentValue = $downloadObj->getVar('download_show');
			$downloadObj->setVar('download_show', !$currentValue);
			$this->downloadHandler->insert($downloadObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_SHOW_CHANGED, 2);
	}

	/**
	 * Toggle broken status
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function toggleBroken(int $downloadId): void
	{
		$downloadObj = $this->downloadHandler->get($downloadId);

		if ($downloadObj && !$downloadObj->isNew()) {
			$currentValue = $downloadObj->getVar('download_broken');
			$downloadObj->setVar('download_broken', !$currentValue);
			$this->downloadHandler->insert($downloadObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_BROKEN_CHANGED, 2);
	}

	/**
	 * Toggle approve status
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function toggleApprove(int $downloadId): void
	{
		$downloadObj = $this->downloadHandler->get($downloadId);

		if ($downloadObj && !$downloadObj->isNew()) {
			$currentValue = $downloadObj->getVar('download_approve');
			$downloadObj->setVar('download_approve', !$currentValue);
			$this->downloadHandler->insert($downloadObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_APPROVE_CHANGED, 2);
	}

	/**
	 * Toggle mirror approve status
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function toggleMirrorApprove(int $downloadId): void
	{
		$downloadObj = $this->downloadHandler->get($downloadId);

		if ($downloadObj && !$downloadObj->isNew()) {
			$currentValue = $downloadObj->getVar('download_mirror_approve');
			$downloadObj->setVar('download_mirror_approve', !$currentValue);
			$this->downloadHandler->insert($downloadObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_MIRROR_APPROVE_CHANGED, 2);
	}

	/**
	 * Change weight
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function changeWeight(int $downloadId): void
	{
		$weight = $this->postInt('download_weight', 0);
		$downloadObj = $this->downloadHandler->get($downloadId);

		if ($downloadObj && !$downloadObj->isNew()) {
			$downloadObj->setVar('download_weight', $weight);
			$this->downloadHandler->insert($downloadObj);
		}

		$this->redirect(DOWNLOADS_ADMIN_URL . 'download.php', _AM_DOWNLOADS_WEIGHT_CHANGED, 2);
	}

	/**
	 * Log an action
	 *
	 * @param int $itemId Item ID
	 * @param int $uid User ID
	 * @param int $item Item type (0 = download)
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

