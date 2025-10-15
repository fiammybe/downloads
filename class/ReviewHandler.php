<?php
/**
 * 'Downloads' is a light weight download handling module for ImpressCMS
 *
 * File: /class/ReviewHandler.php
 *
 * Classes responsible for managing Downloads review objects
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
 * Review Handler with Fluent Interface
 *
 * Manages CRUD operations for review objects with modern PHP 8.2+ features
 *
 * @package Downloads
 */
class DownloadsReviewHandler extends icms_ipf_Handler
{
	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db Database connection
	 */
	public function __construct(icms_db_legacy_Database &$db)
	{
		parent::__construct($db, "review", "review_id", "review_item_id", "review_message", "downloads");
	}

	/**
	 * Get reviews for an item
	 *
	 * @param int $start Starting position
	 * @param int $limit Number of items to retrieve
	 * @param string $order Order field
	 * @param string $sort Sort direction
	 * @param int|null $review_item_id Item ID to filter by
	 * @return array Array of reviews
	 */
	public function getReviews(
		int $start = 0,
		int $limit = 0,
		string $order = 'review_date',
		string $sort = 'DESC',
		?int $review_item_id = null
	): array {
		$criteria = new icms_db_criteria_Compo();
		if ($start) {
			$criteria->setStart($start);
		}
		if ($limit) {
			$criteria->setLimit($limit);
		}
		$criteria->setSort($order);
		$criteria->setOrder($sort);
		$criteria->add(new icms_db_criteria_Item('review_item_id', $review_item_id));

		$reviews = $this->getObjects($criteria, true, false);
		$ret = [];
		foreach ($reviews as $review) {
			$ret[$review['review_id']] = $review;
		}
		return $ret;
	}

	/**
	 * Get review case options
	 *
	 * @return array Case options
	 */
	public function getCase(): array
	{
		return [
			1 => _CO_DOWNLOADS_REVIEW_PRAISE,
			2 => _CO_DOWNLOADS_REVIEW_SUGGESTION,
			3 => _CO_DOWNLOADS_REVIEW_PROBLEM,
			4 => _CO_DOWNLOADS_REVIEW_QUESTION
		];
	}

	/**
	 * Before insert hook
	 *
	 * @param DownloadsReview $obj Review object
	 * @return bool True on success
	 */
	protected function beforeInsert(DownloadsReview &$obj): bool
	{
		$message = $obj->getVar("review_message", "s");
		$smessage = strip_tags($message, '<b><i><a><br>');
		$smessage = icms_core_DataFilter::checkVar($smessage, "html", "input");
		$obj->setVar("review_message", $smessage);

		$email = $obj->getVar("review_email", "s");
		$displayConfig = icms::$module->config['display_reviews_email'];

		$email = match ($displayConfig) {
			1 => icms_core_DataFilter::checkVar($email, 'email', 1, 0),
			2 => icms_core_DataFilter::checkVar($email, 'email', 0, 0),
			3 => icms_core_DataFilter::checkVar($email, 'email', 1, 1),
			4 => icms_core_DataFilter::checkVar($email, 'email', 0, 1),
			default => $email,
		};

		$obj->setVar("review_email", $email);
		return true;
	}
}
