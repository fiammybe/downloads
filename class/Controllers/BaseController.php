<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /class/Controllers/BaseController.php
 *
 * Base controller for the downloads module
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

/**
 * Base Controller for Downloads Module
 *
 * Provides common functionality for all controllers in the module
 *
 * @package Downloads\Controllers
 */
abstract class DownloadsBaseController
{
	protected DownloadsDownloadHandler $downloadHandler;
	protected DownloadsCategoryHandler $categoryHandler;
	protected icms_view_Tpl $template;
	protected icms_core_Security $security;
	protected array $config;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->downloadHandler = icms_getModuleHandler('download', 'downloads');
		$this->categoryHandler = icms_getModuleHandler('category', 'downloads');
		$this->template = new icms_view_Tpl();
		$this->security = icms::$security;

		// Safely get module config
		if (isset(icms::$module) && is_object(icms::$module)) {
			$this->config = icms::$module->config;
		} else {
			// Fallback: load config manually
			$this->config = icms_getModuleConfig('downloads');
		}
	}

	/**
	 * Check if user has permission
	 *
	 * @param string $permission Permission name
	 * @param int $itemId Item ID (0 for global permissions)
	 * @return bool True if user has permission
	 */
	protected function checkPermission(string $permission, int $itemId = 0): bool
	{
		if (!icms::$user) {
			return false;
		}

		$gperm_handler = icms::handler('icms_member_groupperm');
		$groups = icms::$user->getGroups();

		return $gperm_handler->checkRight(
			$permission,
			$itemId,
			$groups,
			icms::$module->getVar('mid')
		);
	}

	/**
	 * Check if user is admin
	 *
	 * @return bool True if user is admin
	 */
	protected function isAdmin(): bool
	{
		if (!icms::$user) {
			return false;
		}

		return icms::$user->isAdmin(icms::$module->getVar('mid'));
	}

	/**
	 * Redirect with message
	 *
	 * @param string $url Redirect URL
	 * @param string $message Message to display
	 * @param int $delay Delay in seconds
	 * @return void
	 */
	protected function redirect(string $url, string $message, int $delay = 3): void
	{
		redirect_header($url, $delay, $message);
	}

	/**
	 * Render template
	 *
	 * @param string $template Template name (without .html extension)
	 * @param array $data Data to assign to template
	 * @return void
	 */
	protected function render(string $template, array $data = []): void
	{
		foreach ($data as $key => $value) {
			$this->template->assign($key, $value);
		}

		$this->template->display("db:downloads_{$template}.html");
	}

	/**
	 * Get current user ID
	 *
	 * @return int User ID or 0 if not logged in
	 */
	protected function getCurrentUserId(): int
	{
		return icms::$user ? icms::$user->getVar('uid') : 0;
	}

	/**
	 * Get user groups
	 *
	 * @return array Array of group IDs
	 */
	protected function getUserGroups(): array
	{
		return icms::$user ? icms::$user->getGroups() : [ICMS_GROUP_ANONYMOUS];
	}

	/**
	 * Validate CSRF token
	 *
	 * @return bool True if token is valid
	 */
	protected function validateToken(): bool
	{
		return $this->security->check();
	}

	/**
	 * Sanitize integer input
	 *
	 * @param string $key Input key
	 * @param int $default Default value
	 * @return int Sanitized integer
	 */
	protected function getInt(string $key, int $default = 0): int
	{
		return isset($_GET[$key]) ? filter_input(INPUT_GET, $key, FILTER_SANITIZE_NUMBER_INT) : $default;
	}

	/**
	 * Sanitize string input
	 *
	 * @param string $key Input key
	 * @param string $default Default value
	 * @return string Sanitized string
	 */
	protected function getString(string $key, string $default = ''): string
	{
		if (!isset($_GET[$key])) {
			return $default;
		}
		// Use htmlspecialchars instead of deprecated FILTER_SANITIZE_STRING
		return htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Get POST integer
	 *
	 * @param string $key Input key
	 * @param int $default Default value
	 * @return int Sanitized integer
	 */
	protected function postInt(string $key, int $default = 0): int
	{
		return isset($_POST[$key]) ? filter_input(INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT) : $default;
	}

	/**
	 * Get POST string
	 *
	 * @param string $key Input key
	 * @param string $default Default value
	 * @return string Sanitized string
	 */
	protected function postString(string $key, string $default = ''): string
	{
		if (!isset($_POST[$key])) {
			return $default;
		}
		// Use htmlspecialchars instead of deprecated FILTER_SANITIZE_STRING
		return htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Format downloads for template
	 *
	 * @param array $downloads Array of download objects
	 * @return array Formatted downloads
	 */
	protected function formatDownloads(array $downloads): array
	{
		$ret = [];
		foreach ($downloads as $download) {
			$ret[] = $download->toArray();
		}
		return $ret;
	}

	/**
	 * Format categories for template
	 *
	 * @param array $categories Array of category objects
	 * @return array Formatted categories
	 */
	protected function formatCategories(array $categories): array
	{
		$ret = [];
		foreach ($categories as $category) {
			$ret[] = $category->toArray();
		}
		return $ret;
	}
}

