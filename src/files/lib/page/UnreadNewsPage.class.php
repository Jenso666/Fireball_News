<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\news\AccessibleNewsList;
use wcf\page\SortablePage;

/**
 * Page for the news category list.
 */
class UnreadNewsPage extends SortablePage {
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

		$newsIDs = [0];
		$this->objectList->getConditionBuilder()->add('news.newsID IN (?)', [$newsIDs]);
		//TODO: fetch unread news only
	}
}
