<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/DownloadController.php
 *
 * Download controller for the downloads module
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

require_once __DIR__ . '/BaseController.php';

/**
 * Download Controller for Downloads Module
 *
 * Handles download operations (add, edit, delete)
 *
 * @package Downloads\Controllers
 */
class DownloadsDownloadController extends DownloadsBaseController
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
		$op = $this->getString('op', '');
		$downloadId = $this->getInt('download_id', 0);

		match ($op) {
			'mod' => $this->edit($downloadId),
			'adddownload' => $this->save(),
			'del' => $this->delete($downloadId),
			default => $this->redirect(DOWNLOADS_URL, _NOPERM, 3),
		};
	}

	/**
	 * Edit or create a download
	 *
	 * @param int $downloadId Download ID (0 for new)
	 * @return void
	 */
	public function edit(int $downloadId = 0): void
	{
		$downloadObj = $this->downloadHandler->get($downloadId);
		$logUid = $this->getCurrentUserId();

		if (!$downloadObj->isNew()) {
			// Editing existing download
			if (!$downloadObj->userCanEditAndDelete()) {
				$this->redirect($downloadObj->getItemLink(true), _NOPERM, 3);
				return;
			}

			$downloadObj->hideFieldFromForm([
				'download_updated',
				'download_broken',
				'download_mirror_approve',
				'meta_description',
				'meta_keywords',
				'download_publisher',
				'download_submitter',
				'download_approve',
				'download_published_date',
				'download_updated_date'
			]);

			$downloadObj->setVar('download_updated_date', time() - 100);
			$downloadObj->setVar('download_updated', true);

			$this->logAction($downloadObj->id(), $logUid, 0, 3);

			$sform = $downloadObj->getSecureForm(_MD_DOWNLOADS_DOWNLOAD_EDIT, 'adddownload');
			$catPath = $downloadObj->getVar('download_title') . ' > ' . _MD_DOWNLOADS_DOWNLOAD_EDIT;
		} else {
			// Creating new download
			if (!$this->categoryHandler->userCanSubmit()) {
				$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
				return;
			}

			$downloadObj->hideFieldFromForm([
				'download_updated',
				'download_broken',
				'download_mirror_approve',
				'meta_description',
				'meta_keywords',
				'download_publisher',
				'download_submitter',
				'download_approve',
				'download_published_date',
				'download_updated_date'
			]);

			$downloadObj->setVar('download_published_date', time() - 100);
			$downloadObj->setVar('download_approve', $this->config['downloads_needs_approve'] != 1);
			$downloadObj->setVar('download_mirror_approve', $this->config['mirror_needs_approve'] != 1);
			$downloadObj->setVar('download_submitter', $logUid);
			$downloadObj->setVar('download_publisher', $logUid);

			$this->logAction($downloadObj->id(), $logUid, 0, 1);

			$sform = $downloadObj->getSecureForm(_MD_DOWNLOADS_DOWNLOAD_CREATE, 'adddownload');
			$catPath = _MD_DOWNLOADS_DOWNLOAD_CREATE;
		}

		// Get index page content
		$indexpageHandler = icms_getModuleHandler('indexpage', DOWNLOADS_DIRNAME, 'downloads');
		$indexKey = $this->getInt('index_key', 1);
		$indexpageObj = $indexpageHandler->get($indexKey);
		$index = $indexpageObj->toArray();

		$data = [
			'downloads_index' => $index,
			'downloads_download_form' => $sform,
			'downloads_cat_path' => $catPath,
		];

		$this->render('forms', $data);
	}

	/**
	 * Save download
	 *
	 * @return void
	 */
	public function save(): void
	{
		if (!$this->validateToken()) {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$downloadId = $this->postInt('download_id', 0);
		$downloadObj = $this->downloadHandler->get($downloadId);

		// Set values from POST
		$downloadObj->setVar('download_title', $this->postString('download_title'));
		$downloadObj->setVar('download_description', $this->postString('download_description'));
		$downloadObj->setVar('download_cid', $this->postInt('download_cid', 0));
		$downloadObj->setVar('download_homepage', $this->postString('download_homepage'));
		$downloadObj->setVar('download_version', $this->postString('download_version'));
		$downloadObj->setVar('download_size', $this->postString('download_size'));
		$downloadObj->setVar('download_platform', $this->postString('download_platform'));
		$downloadObj->setVar('download_license', $this->postString('download_license'));
		$downloadObj->setVar('download_limitations', $this->postString('download_limitations'));
		$downloadObj->setVar('download_file', $this->postString('download_file'));
		$downloadObj->setVar('download_screenshot', $this->postString('download_screenshot'));
		$downloadObj->setVar('download_weight', $this->postInt('download_weight', 0));

		if ($downloadObj->isNew()) {
			$downloadObj->setVar('download_submitter', $this->getCurrentUserId());
			$downloadObj->setVar('download_publisher', $this->getCurrentUserId());
			$downloadObj->setVar('download_published_date', time());
			$downloadObj->setVar('download_approve', $this->config['downloads_needs_approve'] != 1);
			$downloadObj->setVar('download_mirror_approve', $this->config['mirror_needs_approve'] != 1);
		} else {
			$downloadObj->setVar('download_updated', true);
			$downloadObj->setVar('download_updated_date', time());
		}

		if ($this->downloadHandler->insert($downloadObj)) {
			$message = $downloadObj->isNew()
				? _MD_DOWNLOADS_DOWNLOAD_CREATED
				: _MD_DOWNLOADS_DOWNLOAD_MODIFIED;
			$this->redirect(DOWNLOADS_URL, $message, 2);
		} else {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_DOWNLOAD_SAVE_ERROR, 3);
		}
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
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$downloadObj = $this->downloadHandler->get($downloadId);

		if (!$downloadObj->userCanEditAndDelete()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		$confirm = $this->getInt('confirm', 0);

		if ($confirm) {
			if ($this->downloadHandler->delete($downloadObj)) {
				$this->logAction($downloadId, $this->getCurrentUserId(), 0, 2);
				$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_DOWNLOAD_DELETED, 2);
			} else {
				$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_DOWNLOAD_DELETE_ERROR, 3);
			}
		} else {
			// Show confirmation
			icms_cp_header();
			icms_core_Message::confirm(
				['op' => 'del', 'download_id' => $downloadId, 'confirm' => 1],
				$_SERVER['REQUEST_URI'],
				_MD_DOWNLOADS_DOWNLOAD_DELETE_CONFIRM
			);
			icms_cp_footer();
		}
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

