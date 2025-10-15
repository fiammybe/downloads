<?php
/**
 * "Downloads" is a light weight download handling module for ImpressCMS
 *
 * File: /class/Review.php
 *
 * Class representing Download review objects
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
 * Review entity class
 *
 * Represents a download review with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsReview extends icms_ipf_Object
{
	/**
	 * Constructor
	 *
	 * @param icms_ipf_Handler $handler Handler instance
	 */
	public function __construct(icms_ipf_Handler &$handler)
	{
		parent::__construct($handler);

		$this->quickInitVar("review_id", XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar("review_item_id", XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar("review_uid", XOBJ_DTYPE_INT);
		$this->quickInitVar("review_case", XOBJ_DTYPE_INT, FALSE, FALSE, FALSE, 3);
		$this->quickInitVar("review_name", XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar("review_email", XOBJ_DTYPE_TXTBOX);
		$this->quickInitVar("review_message", XOBJ_DTYPE_TXTAREA, TRUE);
		$this->quickInitVar("review_ip", XOBJ_DTYPE_TXTBOX);
		$this->quickInitVar("review_date",XOBJ_DTYPE_LTIME);
		$this->initCommonVar('dohtml', FALSE, 1);

		$this->setControl("review_case", array("name" => "radio", "itemHandler" => "review", "method" => "getCase", "module" => "downloads"));

		$this->hideFieldFromForm(array("review_item_id", "review_uid", "review_ip", "review_date" ));

	}
	/**
	 * Get review message with HTML filtering
	 *
	 * @return string Filtered message
	 */
	public function getReviewMessage(): string
	{
		return icms_core_DataFilter::checkVar($this->getVar("review_message", "s"), "html", "output");
	}

	/**
	 * Get review email
	 *
	 * @return string Email address
	 */
	public function getReviewEmail(): string
	{
		return $this->getVar("review_email", "s");
	}

	/**
	 * Get formatted review published date
	 *
	 * @return string Formatted date
	 */
	public function getReviewPublishedDate(): string
	{
		$date = $this->getVar('review_date', 'e');
		return date(icms::$module->config['downloads_dateformat'], $date);
	}

	/**
	 * Get review avatar image
	 *
	 * @return string Avatar HTML image tag
	 */
	public function getReviewAvatar(): string
	{
		$review_uid = $this->getVar("review_uid", "e");
		if ((int)$review_uid > 0) {
			$review_user = icms::handler("icms_member")->getUser($review_uid);
			$review_avatar = $review_user->gravatar();
			return sprintf('<img src="%s" alt="avatar" />', $review_avatar);
		}

		$review_avatar = "blank.gif";
		return sprintf('<img src="%s/%s" alt="avatar" />', ICMS_UPLOAD_URL, $review_avatar);
	}

	/**
	 * Get review case label
	 *
	 * @return string Case label
	 */
	public function getCase(): string
	{
		$case = $this->getVar("review_case", "e");
		return match ($case) {
			'1' => _CO_DOWNLOADS_REVIEW_PRAISE,
			'2' => _CO_DOWNLOADS_REVIEW_SUGGESTION,
			'3' => _CO_DOWNLOADS_REVIEW_PROBLEM,
			'4' => _CO_DOWNLOADS_REVIEW_QUESTION,
			default => '',
		};
	}

	public function getReviewItem() {
		$item_id = $this->getVar("review_item_id", "e");
		$downloads_download_handler = icms_getModuleHandler("download", basename(dirname(__DIR__)), "downloads");
		$file = $downloads_download_handler->get($item_id);
		$filename = $file->getVar("download_title", "s");
		$url = DOWNLOADS_URL . 'singledownload.php?download_id=' . $item_id;
		return '<a href="' . $url . '" title="' . $filename . '">' . $filename . '</a>';
	}

	function toArray() {
		$ret = parent::toArray();
		$ret['date'] = $this->getReviewPublishedDate();
		$ret['message'] = $this->getReviewMessage();
		$ret['name'] = $this->getVar("review_name", "s");
		$ret['email'] = $this->getVar("review_email"); //getReviewEmail();
		$ret['avatar'] = $this->getReviewAvatar();
		$ret['case'] = $this->getCase();
		return $ret;
	}

}
