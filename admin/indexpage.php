<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/indexpage.php
 *
 * Edit the frontend indexpage - Modernized for ImpressCMS 2.0+
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

/**
 * Display the index page edit form
 *
 * @param int $indexkey The index page key
 * @param bool $indeximage Whether to show the image field
 * @return void
 */
function editform(int $indexkey = 1, bool $indeximage = true): void
{
	global $downloads_indexpage_handler, $icmsAdminTpl;

	$indexpageObj = $downloads_indexpage_handler->get($indexkey);
	$sform = $indexpageObj->getForm(_AM_DOWNLOADS_INDEXPAGE_EDIT, 'addindexpage');
	$sform->assign($icmsAdminTpl);

	$icmsAdminTpl->display('db:downloads_admin.html');
}

include_once 'admin_header.php';

$downloads_indexpage_handler = icms_getModuleHandler('indexpage', basename(dirname(__DIR__)), 'downloads');

$clean_op = isset($_GET['op']) ? filter_input(INPUT_GET, 'op') : '';
if (isset($_POST['op'])) {
	$clean_op = filter_input(INPUT_POST, 'op');
}

$valid_op = ['mod', 'addindexpage'];
$clean_indexkey = isset($_GET['indexkey']) ? (int)$_GET['indexkey'] : 1;

if (in_array($clean_op, $valid_op, true)) {
	match ($clean_op) {
		'mod' => (function() use ($clean_indexkey) {
			icms_cp_header();
			downloads_adminmenu(3, _MI_DOWNLOADS_MENU_INDEXPAGE);
			editform($clean_indexkey, false);
			icms_cp_footer();
		})(),
		'addindexpage' => (function() use ($downloads_indexpage_handler) {
			$controller = new icms_ipf_Controller($downloads_indexpage_handler);
			$controller->storeFromDefaultForm(
				_AM_DOWNLOADS_INDEXPAGE_MODIFIED,
				_AM_DOWNLOADS_INDEXPAGE_MODIFIED
			);
		})(),
		default => null
	};
}
