<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /singledownload.php
 *
 * single Downloads download object - Refactored to use SingleDownloadController
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

include_once "header.php";

$xoopsOption["template_main"] = "downloads_singledownload.html";

include_once ICMS_ROOT_PATH . "/header.php";

// Load controller
require_once ICMS_ROOT_PATH . '/modules/downloads/class/Controllers/BaseController.php';
require_once ICMS_ROOT_PATH . '/modules/downloads/class/Controllers/SingleDownloadController.php';

// Instantiate and handle request
$controller = new DownloadsSingleDownloadController();
$controller->handle();

// Note: All logic has been moved to SingleDownloadController
// The controller handles all operations and renders the appropriate template
$xoTheme->addScript('/modules/' . DOWNLOADS_DIRNAME . '/scripts/jquery.curvycorners.packed.js', array('type' => 'text/javascript'));
include_once "footer.php";

