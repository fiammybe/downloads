<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /download.php
 * 
 * single Downloads download object
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

include_once "header.php";

$xoopsOption["template_main"] = "downloads_singledownload.html";

include_once ICMS_ROOT_PATH . "/header.php";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////// MAIN HEADINGS ///////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

$clean_index_key = $indexpageObj = $downloads_indexpage_handler = $indexpageObj = '';
$clean_index_key = isset($_GET['index_key']) ? filter_input(INPUT_GET, 'index_key', FILTER_SANITIZE_NUMBER_INT) : 1;
$downloads_indexpage_handler = icms_getModuleHandler( 'indexpage', icms::$module -> getVar( 'dirname' ), 'downloads' );

$indexpageObj = $downloads_indexpage_handler->get($clean_index_key);
$index = $indexpageObj->toArray();
$icmsTpl->assign('downloads_index', $index);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// MAIN PART /////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

$clean_download_id = isset($_GET['download_id']) ? filter_input(INPUT_GET, 'download_id', FILTER_SANITIZE_NUMBER_INT) : 0;

$downloads_download_handler = icms_getModuleHandler("download", basename(dirname(__FILE__)), "downloads");

$downloadObj = $downloads_download_handler->get($clean_download_id);

if($downloadObj && !$downloadObj->isNew() && $downloadObj->accessGranted()) {
	$file = $downloadObj->toArray();
	$icmsTpl->assign("file", $file);
	$down_link = DOWNLOADS_URL . 'ajax.php?op=getFile&download_id=' . $downloadObj->id();
	$icmsTpl->assign('down_link', $down_link);
	if($downloadsConfig['downloads_show_down_disclaimer'] == 1) {
		
	} else {
		
	}
	$icmsTpl->assign("broken_link", DOWNLOADS_URL . "ajax.php?op=report_broken&download_id=" . $downloadObj->id() );
	$newfile = downloads_display_new( $downloadObj->getVar( 'download_published_date' ) );
	if($newfile) {
		$icmsTpl->assign('file_is_new', TRUE );
		$icmsTpl->assign('file_is_new_img', $newfile );
	} else {
		$icmsTpl->assign('file_is_new', FALSE );
	}
	if( $downloadObj->getVar('download_updated') == true && $downloadObj-getVar('download_updated_date' !== 0)) {
		$newfile = downloads_display_updated( $downloadObj->getVar( 'download_published_date' ) );
		if($newfile) {
			$icmsTpl->assign('file_is_updated', TRUE );
			$icmsTpl->assign('file_is_updatedimg', $newfile );
		} else {
			$icmsTpl->assign('file_is_new', FALSE );
		}
	}
	if($downloadsConfig['use_mirror'] == 1) {
		$icmsTpl->assign('show_mirror', true);
	} else {
		$icmsTpl->assign('show_mirror', false);
	}
	if($downloadsConfig['downloads_show_down_disclaimer'] == 1) {
		$icmsTpl->assign('show_down_disclaimer', $downloadsConfig['downloads_show_down_disclaimer'] );
		$icmsTpl->assign('down_disclaimer', $downloadsConfig['downloads_down_disclaimer']);
	} else {
		$icmsTpl->assign('show_down_disclaimer', false);
	}
	$albumModule = icms_getModuleInfo('album');
	if ($downloadsConfig['use_album'] == 1 && $albumModule){
		$icmsTpl->assign('album_module', true);
		$directory_name = basename(dirname( __FILE__ ) );
		$script_name = getenv("SCRIPT_NAME");
		$document_root = str_replace('modules/' . $directory_name . '/download.php', '', $script_name);
		$albumConfig = icms_getModuleConfig ($albumModule->getVar('name') );
		$album_id = $downloadObj->getVar('download_album');
		$album_images_handler = icms_getModuleHandler( 'images', $albumModule -> getVar( 'dirname' ), 'album' );
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('img_active', 1));
		$criteria->add(new icms_db_criteria_Item('a_id', $album_id ));
		$imagesObjects = $album_images_handler->getObjects($criteria, true, true);
		$album_images = array();
		foreach ( $imagesObjects as $imagesObj ) {
			$image = $imagesObj -> toArray();
			$image['img_url'] = $document_root . 'uploads/' . $albumModule->getVar('dirname') . '/images/' . $imagesObj->getVar('img_url', 'e');
			$image['show_images_per_row'] = $albumConfig['show_images_per_row'];
			$image['thumbnail_width'] = $albumConfig['thumbnail_width'];
			$image['thumbnail_height'] = $albumConfig['thumbnail_height'];
			$album_images[] = $image;
		}
		$album_image_rows = array_chunk($album_images, $albumConfig['show_images_per_row']);
		$album_row_margins = 'style="margin:' . $albumConfig['thumbnail_margin_top'] . 'px 0px ' . $albumConfig['thumbnail_margin_bottom'] . 'px 0px;"';
		$album_image_margins = 'align="center" style="display:inline-block; margin: 0px ' . $albumConfig['thumbnail_margin_right'] . 'px 0px ' . $albumConfig['thumbnail_margin_left'] . 'px;"';
		$icmsTpl->assign('album_images', $album_images);
		$icmsTpl->assign('album_image_rows', $album_image_rows);
		$icmsTpl->assign('album_row_margins', $album_row_margins);
		$icmsTpl->assign('album_image_margins', $album_image_margins);
	} else {
		$icmsTpl->assign('album_module', false );
	}
	$catalogueModule = icms_getModuleInfo('catalogue');
	if ($downloadsConfig['use_catalogue'] == 1 && $catalogueModule){
		$item_id = $downloadObj->getVar('catalogue_item');
		if (!$item_id == 0) {
			$icmsTpl->assign('catalogue_module', true);
			$catalogueConfig = icms_getModuleConfig ($catalogueModule->getVar('dirname') );
			$catalogue_item_handler = icms_getModuleHandler ('item', $catalogueModule->getVar('dirname'), 'catalogue');
			$catalogueObj = $catalogue_item_handler->get($item_id);
			$item_price = $catalogueObj->getVar('price');
			$base_currency = $catalogueConfig['base_currency'];
			if($catalogueConfig['show_prices'] == 1) {
				$icmsTpl->assign('show_price', true);
				$icmsTpl->assign('base_currency', $base_currency);
				$icmsTpl->assign('item_price', $item_price);
			} else {
				$icmsTpl->assign('show_price', false);
			}
			$catalogue_item_link = ICMS_URL . "/modules/" . $catalogueModule->getVar('dirname') . "/item.php?item_id=" . $catalogueObj->id();
			$icmsTpl->assign('item_link', $catalogue_item_link);
		}
	}
	if ($downloadsConfig['show_breadcrumbs'] == true) {
		$downloads_category_handler = icms_getModuleHandler('category', basename(dirname(__FILE__)), 'downloads');
		$icmsTpl->assign('downloads_cat_path', $downloads_category_handler->getBreadcrumbForPid($downloadObj->getVar('download_cid', 'e'), 1));
	} else {
		$icmsTpl->assign('downloads_cat_path', false);
	}
	$icms_metagen = new icms_ipf_Metagen($downloadObj->getVar("download_title"), $downloadObj->getVar("meta_keywords", "n"), $downloadObj->getVar("meta_description", "n"));
	$icms_metagen->createMetaTags();
} else {
	redirect_header (DOWNLOADS_URL, 3, _NO_PERM);
}
$icmsTpl->assign('downloads_show_breadcrumb', $downloadsConfig['show_breadcrumbs'] == true);

$xoTheme->addScript('/modules/' . DOWNLOADS_DIRNAME . '/scripts/downloads.js', array('type' => 'text/javascript'));

include_once "footer.php";