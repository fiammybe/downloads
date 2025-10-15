<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/CategoryController.php
 *
 * Category controller for the downloads module
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
 * Category Controller for Downloads Module
 *
 * Handles category operations (add, edit, delete)
 *
 * @package Downloads\Controllers
 */
class DownloadsCategoryController extends DownloadsBaseController
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
		$op = $this->getString('op', '');
		$categoryId = $this->getInt('category_id', 0);

		match ($op) {
			'mod' => $this->edit($categoryId),
			'addcategory' => $this->save(),
			'del' => $this->delete($categoryId),
			default => $this->redirect(DOWNLOADS_URL, _NOPERM, 3),
		};
	}

	/**
	 * Edit or create a category
	 *
	 * @param int $categoryId Category ID (0 for new)
	 * @return void
	 */
	public function edit(int $categoryId = 0): void
	{
		$categoryPid = $this->getInt('category_pid', 0);
		$categoryObj = $this->categoryHandler->get($categoryId);

		$logUid = $this->getCurrentUserId();

		if (!$categoryObj->isNew()) {
			// Editing existing category
			if (!$categoryObj->userCanEditAndDelete()) {
				$this->redirect($categoryObj->getItemLink(true), _NOPERM, 3);
				return;
			}

			$categoryObj->hideFieldFromForm([
				'meta_description',
				'meta_keywords',
				'category_updated',
				'category_publisher',
				'category_submitter',
				'category_approve',
				'category_published_date',
				'category_updated_date'
			]);

			$categoryObj->setVar('category_updated_date', time() - 100);
			$categoryObj->setVar('category_updated', true);

			$this->logAction($categoryObj->getVar('category_id'), $logUid, 1, 3);

			$sform = $categoryObj->getSecureForm(_MD_DOWNLOADS_CATEGORY_EDIT, 'addcategory');
			$catPath = $categoryObj->getVar('category_title') . ' : ' . _MD_DOWNLOADS_CATEGORY_EDIT;
		} else {
			// Creating new category
			if (!$this->categoryHandler->userCanSubmit()) {
				$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
				return;
			}

			$categoryObj->hideFieldFromForm([
				'category_pid',
				'meta_description',
				'meta_keywords',
				'category_updated',
				'category_publisher',
				'category_submitter',
				'category_approve',
				'category_published_date',
				'category_updated_date'
			]);

			$categoryObj->setVar('category_published_date', time() - 100);
			$categoryObj->setVar('category_approve', $this->config['category_needs_approve'] != 1);
			$categoryObj->setVar('category_submitter', $logUid);
			$categoryObj->setVar('category_publisher', $logUid);
			$categoryObj->setVar('category_pid', $categoryPid);

			$this->logAction($categoryObj->getVar('category_id'), $logUid, 1, 1);

			$sform = $categoryObj->getSecureForm(_MD_DOWNLOADS_CATEGORY_CREATE, 'addcategory');
			$catPath = _MD_DOWNLOADS_CATEGORY_CREATE;
		}

		// Get index page content
		$indexpageHandler = icms_getModuleHandler('indexpage', DOWNLOADS_DIRNAME, 'downloads');
		$indexKey = $this->getInt('index_key', 1);
		$indexpageObj = $indexpageHandler->get($indexKey);
		$index = $indexpageObj->toArray();

		$data = [
			'downloads_index' => $index,
			'downloads_category_form' => $sform,
			'downloads_cat_path' => $catPath,
		];

		$this->render('forms', $data);
	}

	/**
	 * Save category
	 *
	 * @return void
	 */
	public function save(): void
	{
		if (!$this->validateToken()) {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$categoryId = $this->postInt('category_id', 0);
		$categoryObj = $this->categoryHandler->get($categoryId);

		// Set values from POST
		$categoryObj->setVar('category_title', $this->postString('category_title'));
		$categoryObj->setVar('category_description', $this->postString('category_description'));
		$categoryObj->setVar('category_logo', $this->postString('category_logo'));
		$categoryObj->setVar('category_weight', $this->postInt('category_weight', 0));

		if ($categoryObj->isNew()) {
			$categoryObj->setVar('category_pid', $this->postInt('category_pid', 0));
			$categoryObj->setVar('category_submitter', $this->getCurrentUserId());
			$categoryObj->setVar('category_publisher', $this->getCurrentUserId());
			$categoryObj->setVar('category_published_date', time());
			$categoryObj->setVar('category_approve', $this->config['category_needs_approve'] != 1);
		} else {
			$categoryObj->setVar('category_updated', true);
			$categoryObj->setVar('category_updated_date', time());
		}

		if ($this->categoryHandler->insert($categoryObj)) {
			$message = $categoryObj->isNew()
				? _MD_DOWNLOADS_CATEGORY_CREATED
				: _MD_DOWNLOADS_CATEGORY_MODIFIED;
			$this->redirect(DOWNLOADS_URL, $message, 2);
		} else {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_CATEGORY_SAVE_ERROR, 3);
		}
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
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_SECURITY_CHECK_FAILED, 3);
			return;
		}

		$categoryObj = $this->categoryHandler->get($categoryId);

		if (!$categoryObj->userCanEditAndDelete()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		$confirm = $this->getInt('confirm', 0);

		if ($confirm) {
			if ($this->categoryHandler->delete($categoryObj)) {
				$this->logAction($categoryId, $this->getCurrentUserId(), 1, 2);
				$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_CATEGORY_DELETED, 2);
			} else {
				$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_CATEGORY_DELETE_ERROR, 3);
			}
		} else {
			// Show confirmation
			icms_cp_header();
			icms_core_Message::confirm(
				['op' => 'del', 'category_id' => $categoryId, 'confirm' => 1],
				$_SERVER['REQUEST_URI'],
				_MD_DOWNLOADS_CATEGORY_DELETE_CONFIRM
			);
			icms_cp_footer();
		}
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

