<?php

namespace cms\page;

use cms\data\category\NewsCategoryNodeTree;
use cms\data\news\AccessibleNewsList;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\WCF;

/**
 * Page for the news category list.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsOverviewPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = FIREBALL_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleNewsList::class;

	/**
	 * list of available categories
	 * @var \cms\data\category\NewsCategory[]
	 */
	public $categoryList;

	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();

		$categoryTree = new NewsCategoryNodeTree('de.codequake.cms.category.news');
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign([
			'categoryList' => $this->categoryList,
			'allowSpidersToIndexThisPage' => true,
			'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('de.codequake.cms.news'))
		]);
	}
}
