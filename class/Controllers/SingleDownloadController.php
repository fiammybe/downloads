<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/SingleDownloadController.php
 *
 * Single download controller for the downloads module
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
 * Single Download Controller for Downloads Module
 *
 * Handles single download view and file download operations
 *
 * @package Downloads\Controllers
 */
class DownloadsSingleDownloadController extends DownloadsBaseController
{
	private DownloadsReviewHandler $reviewHandler;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->reviewHandler = icms_getModuleHandler('review', DOWNLOADS_DIRNAME, 'downloads');
	}

	/**
	 * Handle single download operations
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$op = $this->getString('op', '');

		match ($op) {
			'downfile' => $this->downloadFile(),
			'downfileMirror' => $this->downloadMirror(),
			'addreview' => $this->addReview(),
			'addtags' => $this->addTags(),
			default => $this->view(),
		};
	}

	/**
	 * View single download
	 *
	 * @return void
	 */
	public function view(): void
	{
		$downloadId = $this->getInt('download_id', 0);

		if ($downloadId === 0) {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_DOWNLOAD_NOT_FOUND, 3);
			return;
		}

		$downloadObj = $this->downloadHandler->get($downloadId);

		if (!$downloadObj || $downloadObj->isNew() || !$downloadObj->accessGranted()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		// Get index page content
		$indexpageHandler = icms_getModuleHandler('indexpage', DOWNLOADS_DIRNAME, 'downloads');
		$indexpageObj = $indexpageHandler->get(1);
		$index = $indexpageObj->toArray();

		// Get download details
		$download = $downloadObj->toArray();

		// Get reviews
		$reviews = $this->reviewHandler
			->where('review_item_id', $downloadId)
			->orderBy('review_date', 'DESC')
			->get();

		$data = [
			'downloads_index' => $index,
			'download' => $download,
			'reviews' => $reviews,
		];

		$this->render('singledownload', $data);
	}

	/**
	 * Download file
	 *
	 * @return void
	 */
	public function downloadFile(): void
	{
		$downloadId = $this->getInt('download_id', 0);
		$categoryId = $this->getInt('category_id', 0);

		$downloadObj = $this->downloadHandler->get($downloadId);

		if (!$downloadObj || $downloadObj->isNew() || !$downloadObj->accessGranted()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		$url = $downloadObj->getDownloadTag();

		// Force headers for download
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		// Increment download counter
		$downloadObj->incrementCounter();

		// Redirect to file
		header('Location: ' . $url);
		exit;
	}

	/**
	 * Download mirror file
	 *
	 * @return void
	 */
	public function downloadMirror(): void
	{
		$downloadId = $this->getInt('download_id', 0);
		$mirrorId = $this->getInt('mirror_id', 0);

		$downloadObj = $this->downloadHandler->get($downloadId);

		if (!$downloadObj || $downloadObj->isNew() || !$downloadObj->accessGranted()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		$url = $downloadObj->getMirrorTag($mirrorId);

		// Force headers for download
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		// Increment download counter
		$downloadObj->incrementCounter();

		// Redirect to file
		header('Location: ' . $url);
		exit;
	}

	/**
	 * Add review
	 *
	 * @return void
	 */
	public function addReview(): void
	{
		$reviewId = $this->getInt('review_id', 0);
		$downloadId = $this->getInt('download_id', 0);

		$downloadObj = $this->downloadHandler->get($downloadId);

		if (!$downloadObj || $downloadObj->isNew()) {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_DOWNLOAD_NOT_FOUND, 3);
			return;
		}

		$reviewObj = $this->reviewHandler->get($reviewId);
		$reviewUid = $this->getCurrentUserId();

		if ($reviewObj->isNew()) {
			$reviewObj->setVar('review_date', time() - 200);
			$reviewObj->setVar('review_item_id', $downloadId);
			$reviewObj->setVar('review_ip', xoops_getenv('REMOTE_ADDR'));
			$reviewObj->setVar('review_uid', $reviewUid);

			$sform = $reviewObj->getSecureForm(
				_MD_DOWNLOADS_REVIEW_ADD,
				'addreview',
				DOWNLOADS_URL . 'ajax.php?op=addreview&download_id=' . $downloadObj->id(),
				'OK',
				true,
				true
			);

			// Get index page content
			$indexpageHandler = icms_getModuleHandler('indexpage', DOWNLOADS_DIRNAME, 'downloads');
			$indexpageObj = $indexpageHandler->get(1);
			$index = $indexpageObj->toArray();

			$data = [
				'downloads_index' => $index,
				'downloads_review_form' => $sform,
			];

			$this->render('singledownload', $data);
		} else {
			exit;
		}
	}

	/**
	 * Add tags
	 *
	 * @return void
	 */
	public function addTags(): void
	{
		$tagId = $this->getInt('tag_id', 0);
		$downloadId = $this->getInt('download_id', 0);

		$downloadObj = $this->downloadHandler->get($downloadId);

		if (!$downloadObj || $downloadObj->isNew()) {
			$this->redirect(DOWNLOADS_URL, _MD_DOWNLOADS_DOWNLOAD_NOT_FOUND, 3);
			return;
		}

		$sprocketsModule = icms::handler('icms_module')->getByDirname('sprockets');

		if (icms_get_module_status('sprockets')) {
			$sprocketsTagHandler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
			$tagObj = $sprocketsTagHandler->get($tagId);

			$tagObj->hideFieldFromForm([
				'label_type',
				'parent_id',
				'navigation_element',
				'rss',
				'short_url',
				'meta_description',
				'meta_keywords'
			]);

			if ($tagObj->isNew()) {
				$tagObj->setVar('label_type', 0);
				$tagObj->setVar('navigation_element', 0);

				$tagForm = $tagObj->getSecureForm(
					_MD_DOWNLOADS_TAG_ADD,
					'addtags',
					DOWNLOADS_URL . 'ajax.php?op=addtags&download_id=' . $downloadObj->id(),
					'OK',
					true,
					true
				);

				// Get index page content
				$indexpageHandler = icms_getModuleHandler('indexpage', DOWNLOADS_DIRNAME, 'downloads');
				$indexpageObj = $indexpageHandler->get(1);
				$index = $indexpageObj->toArray();

				$data = [
					'downloads_index' => $index,
					'downloads_tag_form' => $tagForm,
				];

				$this->render('singledownload', $data);
			} else {
				exit;
			}
		}
	}
}

