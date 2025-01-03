<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /footer.php
 * 
 * Frontend footer
 * 
 * @copyright	Copyright QM-B (Steffen Flohrer) 2011
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * ----------------------------------------------------------------------------------------------------------
 * 				Downloads
 * @since		1.00
 * @author		QM-B <qm-b@hotmail.de>
 * @version		$Id$
 * @package		downloads
 *
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");
/**
 * check, if rss feeds are enabled. if so, display link
 */
if(icms::$module->config['use_rss'] == 1) {
	$icmsTpl->assign("downloads_show_rss", TRUE);
}
/**
 * check, if breadcrumb should be displayed
 */
if( icms::$module->config['show_breadcrumbs'] == TRUE ) {
	$icmsTpl->assign('downloads_show_breadcrumb', TRUE);
} else {
	$icmsTpl->assign('downloads_show_breadcrumb', FALSE);
}
$icmsTpl->assign('thumbnail_width', icms::$module->config['thumbnail_width']);
$icmsTpl->assign('thumbnail_height', icms::$module->config['thumbnail_height']);
$icmsTpl->assign('file_thumbnail_width', icms::$module->config['file_img_thumbnail_width']);
$icmsTpl->assign('file_thumbnail_height', icms::$module->config['file_img_thumbnail_height']);
$icmsTpl->assign("downloads_adminpage", "<a href='" . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . "/admin/index.php'>" ._MD_DOWNLOADS_ADMIN_PAGE . "</a>");
$icmsTpl->assign("downloads_is_admin", icms_userIsAdmin(DOWNLOADS_DIRNAME));
$icmsTpl->assign('downloads_url', DOWNLOADS_URL);
$icmsTpl->assign('downloads_module_home', '<a href="' . DOWNLOADS_URL . '" title="' . icms::$module->getVar('name') . '">' . icms::$module->getVar('name') . '</a>');
$icmsTpl->assign('downloads_images_url', DOWNLOADS_IMAGES_URL);
/**
 * force downloads.js to header
 */
$xoTheme->addScript('/modules/' . DOWNLOADS_DIRNAME . '/scripts/jquery.qtip.min.js', array('type' => 'text/javascript'));
$xoTheme->addStylesheet('/modules/' . DOWNLOADS_DIRNAME . '/scripts/jquery.qtip.min.css');
$xoTheme->addScript('/modules/' . DOWNLOADS_DIRNAME . '/scripts/downloads.js', array('type' => 'text/javascript'));
include_once ICMS_ROOT_PATH . '/footer.php';
