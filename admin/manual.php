<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/manual.php
 *
 * Module manual - Modernized for ImpressCMS 2.0+
 *
 * @copyright	Copyright QM-B (Steffen Flohrer) 2011
 * @copyright	The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * ----------------------------------------------------------------------------------------------------------
 * 				Downloads
 * @since		1.00
 * @author		QM-B <qm-b@hotmail.de>
 * @author		fiammybe (refactoring for 2.0)
 * @version		2.0.0
 * @package		downloads
 *
 */

include 'admin_header.php';

global $icmsConfig, $icmsAdminTpl;

$dirname = icms::$module->getVar('dirname');

icms_cp_header();
icms::$module->displayAdminMenu(0, _MI_DOWNLOADS_MENU_MANUAL);

$file = isset($_GET['file']) ? filter_input(INPUT_GET, 'file', FILTER_SANITIZE_SPECIAL_CHARS) : 'manual.html';
$lang = 'language/' . $icmsConfig['language'];
$manual = DOWNLOADS_ROOT_PATH . "$lang/$file";

if (!file_exists($manual)) {
	$lang = 'language/english';
	$manual = DOWNLOADS_ROOT_PATH . "$lang/$file";
}

$icmsAdminTpl->assign('manual_path', $manual);
$icmsAdminTpl->display('db:downloads_admin.html');

icms_cp_footer();
