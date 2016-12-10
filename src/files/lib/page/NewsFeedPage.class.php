<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use cms\data\category\NewsCategory;
use cms\data\news\NewsFeedList;
use wcf\page\AbstractFeedPage;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Page for the news rss feed.
 */
class NewsFeedPage extends AbstractFeedPage {
	public $itemsPerPage = CMS_NEWS_PER_PAGE;

	/**
	 * {@inheritdoc}
	 *
	 * @throws \wcf\system\exception\IllegalLinkException if a list of category ids was given with this request but at
	 *                                                    least one id does not belong to a existing category.
	 * @throws \wcf\system\exception\PermissionDeniedException if a list of category ids was given with this request
	 *                                                         but at least one category is not accessible by the
	 *                                                         current user.
	 */
	public function readParameters() {
		parent::readParameters();

		if (0 === count($this->objectIDs)) {
			$this->objectIDs = NewsCategory::getAccessibleCategoryIDs();
		}
		else {
			foreach ($this->objectIDs as $objectID) {
				$category = NewsCategory::getCategory($objectID);

				if ($category === null) {
					throw new IllegalLinkException();
				}

				if (!$category->isAccessible()) {
					throw new PermissionDeniedException();
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function readData() {
		parent::readData();

		$this->title = WCF::getLanguage()->get('cms.page.news');

		$this->items = new NewsFeedList($this->objectIDs);
		$this->items->sqlLimit = $this->itemsPerPage;
		$this->items->readObjects();

		if (1 === count($this->objectIDs)) {
			$this->title = CategoryHandler::getInstance()->getCategory(reset($this->objectIDs))->getTitle();
		}
	}
}
