<?php

namespace cms\data\news;

use wcf\system\clipboard\ClipboardHandler;

/**
 * Represents a list of deleted news
 *
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
