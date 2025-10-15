<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/IndexController.php
 *
 * Index controller for the downloads module
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
 * Index Controller for Downloads Module
 *
 * Handles the main index page operations
 *
 * @package Downloads\Controllers
 */
class DownloadsIndexController extends DownloadsBaseController
{
	/**
	 * Handle the request
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$this->index();
	}

	/**
	 * Display the main index page
	 *
	 * @return void
	 */
	public function index(): void
	{
		$categoryStart = $this->getInt('cat_nav', 0);
		$filesStart = $this->getInt('file_nav', 0);
		$categoryId = $this->getInt('category_id', 0);
		$downloadId = $this->getInt('download_id', 0);
		$uid = $this->getInt('uid', 0);
		$categoryPid = isset($_GET['category_pid']) ? $this->getInt('category_pid', 0) : ($uid ? false : 0);
		$op = $this->getString('op', '');

		// Get index page content
		$indexpageHandler = icms_getModuleHandler('indexpage', DOWNLOADS_DIRNAME, 'downloads');
		$indexpageObj = $indexpageHandler->get(1);
		$index = $indexpageObj->toArray();

		$data = [
			'downloads_index' => $index,
		];

		// Handle operations
		match ($op) {
			'getByTags' => $this->getByTags($filesStart, $data),
			'viewRecentDownloads' => $this->viewRecentDownloads($filesStart, $categoryId, $data),
			default => $this->showDefaultIndex($categoryStart, $filesStart, $categoryId, $categoryPid, $uid, $data),
		};
	}

	/**
	 * Get downloads by tags
	 *
	 * @param int $filesStart Starting position for pagination
	 * @param array $data Template data
	 * @return void
	 */
	private function getByTags(int $filesStart, array &$data): void
	{
		$tagId = $this->getInt('tag', 0);
		$downloads = $this->downloadHandler->getDownloads(
			$filesStart,
			$this->config['show_downloads'],
			$tagId,
			false,
			false,
			false
		);

		$data['downloads_files'] = $downloads;
		$data['byTags'] = true;

		$groups = $this->getUserGroups();
		$filesCount = $this->downloadHandler->getCountCriteria(
			true,
			true,
			$groups,
			'download_grpperm',
			false,
			false,
			0,
			$tagId
		);

		$extraArg = !empty($tagId) ? 'op=getByTags&tag_id=' . $tagId : false;
		$downloadPagenav = new icms_view_PageNav(
			$filesCount,
			$this->config['show_downloads'],
			$filesStart,
			'file_nav',
			$extraArg
		);

		$data['download_pagenav'] = $downloadPagenav->renderNav();

		$this->render('index', $data);
	}

	/**
	 * View recent downloads
	 *
	 * @param int $filesStart Starting position for pagination
	 * @param int $categoryId Category ID filter
	 * @param array $data Template data
	 * @return void
	 */
	private function viewRecentDownloads(int $filesStart, int $categoryId, array &$data): void
	{
		$downloads = $this->downloadHandler->getDownloads(
			$filesStart,
			$this->config['show_downloads'],
			false,
			false,
			false,
			$categoryId,
			'downloads_published_date',
			'DESC'
		);

		$data['downloads'] = $downloads;
		$data['byRecentDownloads'] = true;

		$groups = $this->getUserGroups();
		$count = $this->downloadHandler->getCountCriteria(
			true,
			true,
			$groups,
			'download_grpperm',
			false,
			false,
			$categoryId,
			'downloads_published_date',
			'DESC'
		);

		$extraArg = !empty($categoryId)
			? 'op=viewRecentDownloads&category_id=' . $categoryId
			: 'op=viewRecentDownloads';

		$downloadPagenav = new icms_view_PageNav(
			$count,
			$this->config['show_downloads'],
			$filesStart,
			'file_nav',
			$extraArg
		);

		$data['download_pagenav'] = $downloadPagenav->renderNav();

		// Assign breadcrumb cat-path
		if ($this->config['show_breadcrumbs'] == true) {
			$data['downloads_show_breadcrumb'] = true;
			$data['downloads_cat_path'] = $this->categoryHandler->getBreadcrumbForPid($categoryId, 1);
		} else {
			$data['downloads_cat_path'] = false;
		}

		$this->render('index', $data);
	}

	/**
	 * Show default index page
	 *
	 * @param int $categoryStart Starting position for category pagination
	 * @param int $filesStart Starting position for files pagination
	 * @param int $categoryId Category ID filter
	 * @param int|false $categoryPid Parent category ID
	 * @param int $uid User ID filter
	 * @param array $data Template data
	 * @return void
	 */
	private function showDefaultIndex(
		int $categoryStart,
		int $filesStart,
		int $categoryId,
		int|false $categoryPid,
		int $uid,
		array &$data
	): void {
		$groups = $this->getUserGroups();

		// Get categories
		if ($categoryPid !== false) {
			$categories = $this->categoryHandler->getCategories(
				$categoryStart,
				$this->config['show_subcats'],
				$categoryPid,
				'category_weight',
				'ASC',
				true,
				true,
				$groups,
				'category_grpperm'
			);

			$data['downloads_categories'] = $this->formatCategories($categories);

			$categoriesCount = $this->categoryHandler->getCountCriteria(
				true,
				true,
				$groups,
				'category_grpperm',
				$categoryPid
			);

			$categoryPagenav = new icms_view_PageNav(
				$categoriesCount,
				$this->config['show_subcats'],
				$categoryStart,
				'cat_nav'
			);

			$data['category_pagenav'] = $categoryPagenav->renderNav();
		}

		// Get downloads
		$downloads = $this->downloadHandler->getDownloads(
			$filesStart,
			$this->config['show_downloads'],
			false,
			false,
			$uid,
			$categoryId,
			'download_weight',
			'ASC'
		);

		$data['downloads_files'] = $downloads;

		$filesCount = $this->downloadHandler->getCountCriteria(
			true,
			true,
			$groups,
			'download_grpperm',
			false,
			$uid,
			$categoryId
		);

		$extraArg = !empty($categoryId) ? 'category_id=' . $categoryId : false;
		$downloadPagenav = new icms_view_PageNav(
			$filesCount,
			$this->config['show_downloads'],
			$filesStart,
			'file_nav',
			$extraArg
		);

		$data['download_pagenav'] = $downloadPagenav->renderNav();

		// Assign breadcrumb cat-path
		if ($this->config['show_breadcrumbs'] == true && $categoryId > 0) {
			$data['downloads_show_breadcrumb'] = true;
			$data['downloads_cat_path'] = $this->categoryHandler->getBreadcrumbForPid($categoryId, 1);
		} else {
			$data['downloads_cat_path'] = false;
		}

		$this->render('index', $data);
	}
}

