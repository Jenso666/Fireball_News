<?php

namespace cms\system\page\handler;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryCache;
use wcf\data\category\Category;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\WCF;

/**
 * {@inheritDoc}
 */
class UnreadNewsPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		$categoryIDs = NewsCategory::getAccessibleCategoryIDs();
		$counter = 0;
		foreach ($categoryIDs as $categoryID) {
			$counter += NewsCategoryCache::getInstance()->getUnreadNews($categoryID);
		}

		return $counter;
	}

	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		$categoryIDs = NewsCategory::getAccessibleCategoryIDs();
		foreach ($categoryIDs as $categoryID) {
			if (NewsCategoryCache::getInstance()->getUnreadNews($categoryID)) {
				return true;
			}
		}

		return false;
	}
}
