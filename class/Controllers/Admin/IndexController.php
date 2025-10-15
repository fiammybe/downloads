<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/Admin/IndexController.php
 *
 * Admin index controller for the downloads module
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
 * Admin Index Controller for Downloads Module
 *
 * Handles the admin dashboard
 *
 * @package Downloads\Controllers\Admin
 */
class DownloadsAdminIndexController extends DownloadsBaseController
{
	/**
	 * Display admin dashboard
	 *
	 * @return void
	 */
	public function index(): void
	{
		if (!$this->isAdmin()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		icms_cp_header();
		downloads_adminmenu(0, _MI_DOWNLOADS_MENU_INDEX);

		// Check broken downloads
		$brokenCriteria = new icms_db_criteria_Compo();
		$brokenCriteria->add(new icms_db_criteria_Item('download_broken', true));
		$brokenCount = $this->downloadHandler->getCount($brokenCriteria, true, false);

		// Get total counts
		$totalFiles = $this->downloadHandler->getCount();
		$totalCats = $this->categoryHandler->getCount();

		// Check files to approve
		$downloadApproveCount = 0;
		if ($this->config['downloads_needs_approve'] == 1) {
			$approveCriteria = new icms_db_criteria_Compo();
			$approveCriteria->add(new icms_db_criteria_Item('download_approve', 0));
			$downloadApproveCount = $this->downloadHandler->getCount($approveCriteria, true, false);
		}

		// Check mirrors to approve
		$mirrorCriteria = new icms_db_criteria_Compo();
		$mirrorCriteria->add(new icms_db_criteria_Item('download_mirror_approve', 0));
		$mirrorsCount = $this->downloadHandler->getCount($mirrorCriteria, true, false);

		// Check categories to approve
		$categoryApproveCount = 0;
		if ($this->config['category_needs_approve'] == 1) {
			$catApproveCriteria = new icms_db_criteria_Compo();
			$catApproveCriteria->add(new icms_db_criteria_Item('category_approve', 0));
			$categoryApproveCount = $this->categoryHandler->getCount($catApproveCriteria, true, false);
		}

		// Get mime types
		$mimetypes = $this->getMimeTypes();

		// Display dashboard
		echo '<div style="margin: 2em 0em;"><p>' . _AM_DOWNLOADS_INDEX_WARNING . '</p></div>';

		echo '<fieldset style="border: #E8E8E8 1px solid;">
				<legend style="display: inline; font-weight: bold; color: #0A3760;">' . _AM_DOWNLOADS_INDEX . '</legend>
				<div style="display: table; padding: 8px;">
					<div style="display: table-row;">
						<div style="display: table-cell; padding: 5px;">' . _AM_DOWNLOADS_INDEX_TOTAL_FILES . '</div>
						<div style="display: table-cell; padding: 5px; font-weight: bold;">' . $totalFiles . '</div>
					</div>
					<div style="display: table-row;">
						<div style="display: table-cell; padding: 5px;">' . _AM_DOWNLOADS_INDEX_TOTAL_CATS . '</div>
						<div style="display: table-cell; padding: 5px; font-weight: bold;">' . $totalCats . '</div>
					</div>';

		if ($this->config['downloads_needs_approve'] == 1) {
			echo '<div style="display: table-row;">
						<div style="display: table-cell; padding: 5px;">' . _AM_DOWNLOADS_INDEX_DOWNLOADS_TO_APPROVE . '</div>
						<div style="display: table-cell; padding: 5px; font-weight: bold;">' . $downloadApproveCount . '</div>
					</div>';
		}

		if ($this->config['category_needs_approve'] == 1) {
			echo '<div style="display: table-row;">
						<div style="display: table-cell; padding: 5px;">' . _AM_DOWNLOADS_INDEX_CATEGORIES_TO_APPROVE . '</div>
						<div style="display: table-cell; padding: 5px; font-weight: bold;">' . $categoryApproveCount . '</div>
					</div>';
		}

		echo '<div style="display: table-row;">
					<div style="display: table-cell; padding: 5px;">' . _AM_DOWNLOADS_INDEX_MIRRORS_TO_APPROVE . '</div>
					<div style="display: table-cell; padding: 5px; font-weight: bold;">' . $mirrorsCount . '</div>
				</div>
				<div style="display: table-row;">
					<div style="display: table-cell; padding: 5px;">' . _AM_DOWNLOADS_INDEX_BROKEN_DOWNLOADS . '</div>
					<div style="display: table-cell; padding: 5px; font-weight: bold;">' . $brokenCount . '</div>
				</div>
			</div>
		</fieldset>';

		if (!empty($mimetypes)) {
			echo '<fieldset style="border: #E8E8E8 1px solid; margin-top: 1em;">
					<legend style="display: inline; font-weight: bold; color: #0A3760;">' . _AM_DOWNLOADS_INDEX_MIMETYPES . '</legend>
					<div style="padding: 8px;">' . $mimetypes . '</div>
				</fieldset>';
		}

		icms_cp_footer();
	}

	/**
	 * Get mime types for the module
	 *
	 * @return string Comma-separated list of mime types
	 */
	private function getMimeTypes(): string
	{
		$mimetypeHandler = icms_getModuleHandler('mimetype', 'system');
		$criteria = new icms_db_criteria_Compo();
		$mimetypeObjects = $mimetypeHandler->getObjects($criteria, true, false);

		$mimetype = '';
		foreach (array_keys($mimetypeObjects) as $key) {
			$mimetypeObj = $mimetypeObjects[$key];
			if (in_array(icms::$module->getVar('dirname'), [$mimetypeObj->getVar('dirname')])) {
				$mimetype .= $mimetypeObj->getVar('name') . ', ';
			}
		}

		return rtrim($mimetype, ', ');
	}

	/**
	 * Handle quick operations
	 *
	 * @return void
	 */
	public function handleOperation(): void
	{
		if (!$this->isAdmin()) {
			$this->redirect(DOWNLOADS_URL, _NOPERM, 3);
			return;
		}

		$op = $this->getString('op', '');
		$downloadId = $this->getInt('download_id', 0);
		$categoryId = $this->getInt('category_id', 0);

		match ($op) {
			'visible' => $this->toggleVisibility($downloadId),
			'changeShow' => $this->toggleShow($downloadId),
			'changeApprove' => $this->toggleApprove($downloadId),
			'changeWeight' => $this->changeWeight($downloadId),
			default => $this->index(),
		};
	}

	/**
	 * Toggle visibility
	 *
	 * @param int $downloadId Download ID
	 * @return void
	 */
	private function toggleVisibility(int $downloadId): void
	{
		$downloadObj = $this->downloadHandler->get($downloadId);

		if ($downloadObj && !$downloadObj->isNew()) {
			$currentValue = $downloadObj->getVar('download_visible');
			$downloadObj->setVar('download_visible', !$currentValue);
			$this->downloadHandler->insert($downloadObj);
		}

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
}

