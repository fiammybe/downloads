<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/Indexpage.php
 * 
 * Class representing Download indexpage objects
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

defined('ICMS_ROOT_PATH') or die('ICMS root path not defined');

/**
 * Indexpage entity class
 *
 * Represents the index page configuration with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsIndexpage extends icms_ipf_seo_Object
{
	/**
	 * Constructor
	 *
	 * @param icms_ipf_Handler $handler Handler instance
	 */
	public function __construct(icms_ipf_Handler &$handler)
	{
		parent::__construct($handler);

		$this->quickInitVar("index_key", XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar("index_image", XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar('index_img_upload', XOBJ_DTYPE_IMAGE);
		$this->quickInitVar("index_header", XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar("index_heading", XOBJ_DTYPE_TXTAREA, FALSE);
		$this->quickInitVar("index_footer", XOBJ_DTYPE_TXTAREA, FALSE);
		$this->initCommonVar("dohtml", FALSE, 1);
		$this->initCommonVar("dobr", FALSE, 1);
		$this->initCommonVar("doimage", FALSE, 1);
		$this->initCommonVar("dosmiley", FALSE, 1);
		$this->initCommonVar("docxode", FALSE, FALSE, FALSE, 1);

		$this->setControl('index_img_upload', 'imageupload');
		$this->setControl('index_heading','dhtmltextarea');
		$this->setControl('index_footer', array('name' => 'textarea', 'form_editor' => 'htmlarea'));
		$this->setControl('index_image', array( 'name' => 'select', 'itemHandler' => 'indexpage', 'method' => 'getImageList', 'module' => 'downloads'));
	}

	/**
	 * Get index image HTML
	 *
	 * @return string Image HTML or empty string
	 */
	public function getIndexImg(): string
	{
		$indeximage = $this->getVar('index_image', 'e');
		if ($indeximage != 0 && $indeximage != "") {
			$image_tag = DOWNLOADS_UPLOAD_URL . 'indexpage/' . $indeximage;
			return sprintf('<div class="downloads_indeximage"><img src="%s" /></div>', $image_tag);
		}
		return '';
	}

	/**
	 * Get index header HTML
	 *
	 * @return string Header HTML
	 */
	public function getIndexHeader(): string
	{
		$indexheader = $this->getVar('index_header', 'e');
		return sprintf('<div class="downloads_indexheader">%s</div>', $indexheader);
	}

	/**
	 * Get index heading HTML
	 *
	 * @return string Heading HTML
	 */
	public function getIndexHeading(): string
	{
		$indexheading = icms_core_DataFilter::checkVar($this->getVar('index_heading', 's'), 'html', 'output');
		return sprintf('<div class="downloads_indexheading">%s</div>', $indexheading);
	}

	/**
	 * Get index footer HTML
	 *
	 * @return string Footer HTML
	 */
	public function getIndexFooter(): string
	{
		$indexfooter = icms_core_DataFilter::checkVar($this->getVar('index_footer', 's'), 'html', 'output');
		return sprintf('<div class="downloads_indexfooter">%s</div>', $indexfooter);
	}

	/**
	 * Convert object to array
	 *
	 * @return array Object data as array
	 */
	public function toArray(): array
	{
		$ret = parent::toArray();
		$ret['image'] = $this->getIndexImg();
		$ret['header'] = $this->getIndexHeader();
		$ret['heading'] = $this->getIndexHeading();
		$ret['footer'] = $this->getIndexFooter();
		return $ret;
	}
	
}
