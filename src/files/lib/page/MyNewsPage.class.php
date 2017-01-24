<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\news\AccessibleNewsList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Page for the news category list.
 */
class MyNewsPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = FIREBALL_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleNewsList::class;

	/**
	 * @inheritDoc
	 */
	public $templateName = 'newsOverview';

	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();

		$this->objectList->getConditionBuilder()->add('news.userID = ?', [WCF::getUser()->userID]);
	}
}
