<?php

namespace cms\data\news;
use wcf\system\clipboard\ClipboardHandler;

/**
 * Represents a list of deleted news
 *
 * @author	Florian Gail
 * @copyright	2013-2017 codeQuake
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DeletedNewsList extends ViewableNewsList {
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'news.deleteTime DESC';

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();

		// add condition
		$this->getConditionBuilder()->add('news.isDeleted = ?', array(1));
	}

	/**
	 * @inheritDoc
	 */
	public function getMarkedItems() {
		$objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news');
		return ClipboardHandler::getInstance()->hasMarkedItems($objectTypeID);
	}
}
