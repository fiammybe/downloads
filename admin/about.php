<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /admin/about.php
 *
 * About page of the module - Modernized for ImpressCMS 2.0+
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

$aboutObj = new icms_ipf_About();
$aboutObj->render();
