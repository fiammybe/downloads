<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /index.php
 *
 * Frontend indexpage - Refactored to use IndexController
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

include_once 'header.php';

$xoopsOption['template_main'] = 'downloads_index.html';

include_once ICMS_ROOT_PATH . '/header.php';

// Load controller
require_once ICMS_ROOT_PATH . '/modules/downloads/class/Controllers/BaseController.php';
require_once ICMS_ROOT_PATH . '/modules/downloads/class/Controllers/IndexController.php';

// Instantiate and handle request
$controller = new DownloadsIndexController();
$controller->handle();

// Note: All logic has been moved to IndexController
// The controller handles all operations and renders the appropriate template
include_once 'footer.php';
