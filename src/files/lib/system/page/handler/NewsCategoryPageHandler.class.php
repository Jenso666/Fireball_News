<?php

namespace cms\system\page\handler;
use cms\data\category\NewsCategory;
use wcf\data\category\Category;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;

/**
 * {@inheritDoc}
 */
class NewsCategoryPageHandler extends AbstractMenuPageHandler implements IOnlineLocationPageHandler {
	use TNewsOnlineLocationPageHandler;

	/**
	 * @inheritdoc
	 */
	public function getLink($objectID) {
		/** @var NewsCategory $category */
		$category =  new NewsCategory(new Category($objectID));
		$category->getLink($objectID);
	}

	/**
	 * @inheritdoc
	 */
	public function isValid($objectID) {
		/** @var NewsCategory $category */
		$category =  new NewsCategory(new Category($objectID));
		return ($category !== null);
	}

	/**
	 * @inheritdoc
	 */
	public function isVisible($objectID = null) {
		/** @var NewsCategory $category */
		$category =  new NewsCategory(new Category($objectID));

		if ($category === null)
			return false;

		return $category->isAccessible();
	}
}
