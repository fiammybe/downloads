<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/permissions.php
 * 
 * modinfo language file
 * 
 * @copyright	Copyright QM-B (Steffen Flohrer) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * ----------------------------------------------------------------------------------------------------------
 * 				Downloads
 * @since		1.00
 * @author		QM-B <qm-b@hotmail.de>
 * @version		$Id$
 * @package		downloads
 *
 */
ini_set('max_execution_time', 0);

ini_set('memory_limit', '256M');

include_once 'admin_header.php';
icms_cp_header();

icms::$module->displayAdminMenu(5, _MI_DOWNLOADS_MENU_PERMISSIONS);
$op = isset($_REQUEST['op']) && is_string($_REQUEST['op']) ? trim($_REQUEST['op']) : '';
$title_of_form = $perm_name = $restriction = '';
$anonymous = TRUE;
switch ($op) {
	case 'viewdownloads':
		$title_of_form = _AM_DOWNLOADS_PREMISSION_DOWNLOADS_VIEW;
		$perm_name = "download_grpperm";
		$restriction = "";
		$anonymous = TRUE;
		break;
		
	case 'viewcategory':
		$title_of_form = _AM_DOWNLOADS_PREMISSION_CATEGORY_VIEW;
		$perm_name = "category_grpperm";
		$restriction = "";
		$anonymous = TRUE;
		break;
		
	case 'submitdownloads':
		$title_of_form = _AM_DOWNLOADS_PREMISSION_CATEGORY_SUBMIT;
		$perm_name = "submit_downloads";
		$restriction = "";
		$anonymous = TRUE;
		break;
}

$opform = new icms_form_Simple('', 'opform', 'permissions.php', "get");
$op_select = new icms_form_elements_Select("", 'op', $op);
$op_select->setExtra('onchange="document.forms.opform.submit()"');
$op_select->addOption('', '-------------');
$op_select->addOption('viewdownloads', _AM_DOWNLOADS_PREMISSION_DOWNLOADS_VIEW);
$op_select->addOption('viewcategory', _AM_DOWNLOADS_PREMISSION_CATEGORY_VIEW);
$op_select->addOption('submitdownloads', _AM_DOWNLOADS_PREMISSION_CATEGORY_SUBMIT);
$opform->addElement($op_select);
$opform->display();

$has_items = FALSE;
$form = new icms_form_Groupperm($title_of_form, icms::$module->getVar('mid'), $perm_name, '', 'admin/permissions.php', $anonymous);

if($op == 'viewdownloads') {
	$downloads_download_handler = icms_getModuleHandler("download", DOWNLOADS_DIRNAME, "downloads");
	$downloads = $downloads_download_handler->getObjects(FALSE, TRUE);
	if($downloads) {
		foreach (array_keys($downloads) as $i) {
			if ($restriction == "") {
				$has_items = TRUE;
				$form->addItem($downloads[$i]->getVar('download_id'),
				$downloads[$i]->getVar('download_title'));
			}
		}
	}
	if ($has_items) {
		$form->display();
	} else {
		echo '<p>' . _AM_DOWNLOADS_NO_CAT_FOUND . '</p>';
	}
} elseif ($op == 'viewcategory') {
	$downloads_category_handler = icms_getModuleHandler("category", DOWNLOADS_DIRNAME, "downloads");
	$categories = $downloads_category_handler->getObjects(FALSE, TRUE);
	if($categories) {
		foreach (array_keys($categories) as $i) {
			if ($restriction == "") {
				$has_items = TRUE;
				$form->addItem($categories[$i]->getVar('category_id'),
				$categories[$i]->getVar('category_title'));
			}
		}
	}
	if ($has_items) {
		$form->display();
	} else {
		echo '<p>' . _AM_DOWNLOADS_NO_CAT_FOUND . '</p>';
	}
} elseif($op == 'submitdownloads') {
	$downloads_category_handler = icms_getModuleHandler("category", DOWNLOADS_DIRNAME, "downloads");
	$categories = $downloads_category_handler->getObjects(FALSE, TRUE);
	if($categories) {
		foreach (array_keys($categories) as $i) {
			if ($restriction == "") {
				$has_items = TRUE;
				$form->addItem($categories[$i]->getVar('category_id'),
				$categories[$i]->getVar('category_title'));
			}
		}
	}
	if ($has_items) {
		$form->display();
	} else {
		echo '<p>' . _AM_DOWNLOADS_NO_CAT_FOUND . '</p>';
	}
}

icms_cp_footer();