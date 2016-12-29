<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\category\NewsCategoryNodeTree;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Page for the news category list.
 */
class NewsOverviewPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = CMS_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 */
	public $objectListClassName = 'cms\data\news\AccessibleNewsList';

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

		WCF::getTPL()->assign(array(
			'categoryList' => $this->categoryList,
			'allowSpidersToIndexThisPage' => true
		));
	}
}
