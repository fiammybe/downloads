<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/log.php
 *
 * View Downloads log - Modernized for ImpressCMS 2.0+
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

include_once 'admin_header.php';

$valid_op = ['view', ''];
$clean_op = isset($_GET['op']) ? filter_input(INPUT_GET, 'op') : '';
$clean_log_id = isset($_GET['log_id']) ? filter_input(INPUT_GET, 'log_id', FILTER_SANITIZE_NUMBER_INT) : 0;

$downloads_log_handler = icms_getModuleHandler('log', basename(dirname(__DIR__)), 'downloads');

if (in_array($clean_op, $valid_op, true)) {
	icms_cp_header();
	downloads_adminmenu(0, _MI_DOWNLOADS_MENU_LOG);

	match ($clean_op) {
		'view' => (function() use ($downloads_log_handler, $clean_log_id) {
			$logObj = $downloads_log_handler->get($clean_log_id);
			$logObj->displaySingleObject();
		})(),
		default => (function() use ($downloads_log_handler) {
			global $icmsAdminTpl;

			$criteria = null;

			// Create log table
			$objectTable = new icms_ipf_view_Table($downloads_log_handler, $criteria, []);
			$objectTable->addColumn(new icms_ipf_view_Column('log_item_id', false, false, 'getLogItemId'));
			$objectTable->addColumn(new icms_ipf_view_Column('log_item', false, false, 'getLogItem'));
			$objectTable->addColumn(new icms_ipf_view_Column('log_case', false, false, 'getLogCase'));
			$objectTable->addColumn(new icms_ipf_view_Column('log_date', 'center', 50, 'getLogDate'));
			$objectTable->addColumn(new icms_ipf_view_Column('log_ip', 'center', 50, 'getLogIP'));
			$objectTable->setDefaultOrder('DESC');
			$objectTable->setDefaultSort('log_date');

			$icmsAdminTpl->assign('downloads_log_table', $objectTable->fetch());
			$icmsAdminTpl->display('db:downloads_admin.html');
		})()
	};

	icms_cp_footer();
}
