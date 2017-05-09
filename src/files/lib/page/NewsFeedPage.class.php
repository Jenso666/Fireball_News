<?php

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
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsFeedPage extends AbstractFeedPage {
	/**
	 * amount of news which should bet fetched
	 * @var integer
	 */
	public $itemsPerPage = FIREBALL_NEWS_PER_PAGE;

	/**
	 * @inheritDoc
	 *
	 * @throws \wcf\system\exception\IllegalLinkException if a list of category ids was given with this request but at
	 *                                                    least one id does not belong to a existing category.
	 * @throws \wcf\system\exception\PermissionDeniedException if a list of category ids was given with this request
	 *                                                         but at least one category is not accessible by the
	 *                                                         current user.
	 */
	public function readParameters() {
		parent::readParameters();

		if (empty($this->objectIDs)) {
			$this->objectIDs = NewsCategory::getAccessibleCategoryIDs();
		} else {
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
	 * @inheritDoc
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
