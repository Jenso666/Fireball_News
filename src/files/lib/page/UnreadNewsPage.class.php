<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\news\AccessibleNewsList;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

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
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign('hasMarkedItems', ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news')));
	}
}
