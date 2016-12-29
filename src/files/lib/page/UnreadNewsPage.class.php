<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use wcf\page\SortablePage;

/**
 * Page for the news category list.
 */
class UnreadNewsPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CMS_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = 'cms\data\news\AccessibleNewsList';

	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();

		//TODO: fetch unread news only
	}
}
