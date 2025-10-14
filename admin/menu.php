<?php
/**
 * Downloads module for ImpressCMS
 *
 * File: /admin/menu.php
 *
 * Admin menu definition
 *
 * @copyright  Copyright QM-B (Steffen Flohrer) 2011
 * @copyright  The ImpressCMS Project
 * @license    GPL 2.0
 * @package    downloads
 * @since      1.00
 * @author     QM-B <qm-b@hotmail.de>
 * @author     fiammybe
 * @version    2.0.0
 */

defined("ICMS_ROOT_PATH") or die("ImpressCMS root path not defined");

$adminmenu = [];

$adminmenu[] = [
	'title' => _MI_DOWNLOADS_MENU_INDEX,
	'link'  => 'admin/index.php'
];

$adminmenu[] = [
	'title' => _MI_DOWNLOADS_MENU_DOWNLOAD,
	'link'  => 'admin/download.php'
];

$adminmenu[] = [
	'title' => _MI_DOWNLOADS_MENU_CATEGORY,
	'link'  => 'admin/category.php'
];

$adminmenu[] = [
	'title' => _MI_DOWNLOADS_MENU_INDEXPAGE,
	'link'  => 'admin/indexpage.php?op=mod&indexkey=1'
];

$adminmenu[] = [
	'title' => _MI_DOWNLOADS_MENU_REVIEW,
	'link'  => 'admin/review.php'
];

$adminmenu[] = [
	'title' => _MI_DOWNLOADS_MENU_PERMISSIONS,
	'link'  => 'admin/permissions.php'
];

// Header menu
$downloadsModule = icms_getModuleInfo(basename(dirname(__DIR__)));
$moddir = basename(dirname(__DIR__));

$headermenu = [];

$headermenu[] = [
	'title' => _CO_ICMS_GOTOMODULE,
	'link'  => ICMS_URL . '/modules/' . $moddir . '/'
];

$headermenu[] = [
	'title' => _PREFERENCES,
	'link'  => '../../system/admin.php?fct=preferences&amp;op=showmod&amp;mod=' . $downloadsModule->getVar('mid')
];

$headermenu[] = [
	'title' => _MI_DOWNLOADS_MENU_TEMPLATES,
	'link'  => '../../system/admin.php?fct=tplsets&op=listtpl&tplset=' . icms::$config['template_set'] . '&moddir=' . $moddir
];

$headermenu[] = [
	'title' => _CO_ICMS_UPDATE_MODULE,
	'link'  => ICMS_URL . '/modules/system/admin.php?fct=modulesadmin&op=update&module=' . $moddir
];

$headermenu[] = [
	'title' => _MI_DOWNLOADS_MENU_IMPORT,
	'link'  => ICMS_URL . '/modules/' . $moddir . '/admin/import.php'
];

$headermenu[] = [
	'title' => _MI_DOWNLOADS_MENU_MANUAL,
	'link'  => ICMS_URL . '/modules/' . $moddir . '/admin/manual.php'
];

$headermenu[] = [
	'title' => _MI_DOWNLOADS_MENU_LOG,
	'link'  => ICMS_URL . '/modules/' . $moddir . '/admin/log.php'
];

$headermenu[] = [
	'title' => _MODABOUT_ABOUT,
	'link'  => ICMS_URL . '/modules/' . $moddir . '/admin/about.php'
];
