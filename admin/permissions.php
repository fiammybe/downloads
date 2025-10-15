<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/permissions.php
 *
 * Permissions management page - Modernized for ImpressCMS 2.0+
 *
 * @copyright	Copyright QM-B (Steffen Flohrer) 2012
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

ini_set('max_execution_time', 0);
ini_set('memory_limit', '256M');

include_once 'admin_header.php';
icms_cp_header();

icms::$module->displayAdminMenu(5, _MI_DOWNLOADS_MENU_PERMISSIONS);

$op = isset($_REQUEST['op']) ? trim($_REQUEST['op']) : '';

[$title_of_form, $perm_name, $restriction, $anonymous] = match ($op) {
	'viewdownloads' => [
		_AM_DOWNLOADS_PREMISSION_DOWNLOADS_VIEW,
		'downloads_grpperm',
		'',
		true
	],
	'viewcategory' => [
		_AM_DOWNLOADS_PREMISSION_CATEGORY_VIEW,
		'category_grpperm',
		'',
		true
	],
	'submitdownloads' => [
		_AM_DOWNLOADS_PREMISSION_CATEGORY_SUBMIT,
		'submit_downloads',
		'',
		true
	],
	default => ['', '', '', false]
};

$opform = new icms_form_Simple('', 'opform', 'permissions.php', 'get');
$op_select = new icms_form_elements_Select('', 'op', $op);
$op_select->setExtra('onchange="document.forms.opform.submit()"');
$op_select->addOption('', '-------------');
$op_select->addOption('viewdownloads', _AM_DOWNLOADS_PREMISSION_DOWNLOADS_VIEW);
$op_select->addOption('viewcategory', _AM_DOWNLOADS_PREMISSION_CATEGORY_VIEW);
$op_select->addOption('submitdownloads', _AM_DOWNLOADS_PREMISSION_CATEGORY_SUBMIT);
$opform->addElement($op_select);
$opform->display();

if ($title_of_form) {
	$form = new icms_form_Groupperm(
		$title_of_form,
		icms::$module->getVar('mid'),
		$perm_name,
		'',
		'admin/permissions.php',
		$anonymous
	);

	match ($op) {
		'viewdownloads' => (function() use ($form, $restriction) {
			$downloads_download_handler = icms_getModuleHandler('download', DOWNLOADS_DIRNAME, 'downloads');
			$downloads = $downloads_download_handler->getObjects(false, true);
			if ($downloads) {
				foreach (array_keys($downloads) as $i) {
					if ($restriction === '') {
						$form->addItem(
							$downloads[$i]->getVar('download_id'),
							$downloads[$i]->getVar('download_title')
						);
					}
				}
			}
			$form->display();
		})(),
		'viewcategory', 'submitdownloads' => (function() use ($form, $restriction) {
			$downloads_category_handler = icms_getModuleHandler('category', DOWNLOADS_DIRNAME, 'downloads');
			$categories = $downloads_category_handler->getObjects(false, true);
			if ($categories) {
				foreach (array_keys($categories) as $i) {
					if ($restriction === '') {
						$form->addItem(
							$categories[$i]->getVar('category_id'),
							$categories[$i]->getVar('category_title')
						);
					}
				}
			}
			$form->display();
		})(),
		default => null
	};
}

icms_cp_footer();
