<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/index.php
 *
 * index page in ACP - Refactored to use Admin/IndexController
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

include_once "admin_header.php";

// Load controller
require_once ICMS_ROOT_PATH . '/modules/downloads/class/Controllers/BaseController.php';
require_once ICMS_ROOT_PATH . '/modules/downloads/class/Controllers/Admin/IndexController.php';

// Instantiate and handle request
$controller = new DownloadsAdminIndexController();
$controller->index();

// Note: All logic has been moved to Admin/IndexController
// The controller handles all operations and renders the admin dashboard
icms_cp_footer();
