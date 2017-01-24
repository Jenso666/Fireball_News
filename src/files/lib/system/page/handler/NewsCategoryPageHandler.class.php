<?php

namespace cms\system\page\handler;

use cms\data\category\NewsCategory;
use wcf\data\category\Category;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\WCF;

/**
 * {@inheritDoc}
 */
class NewsCategoryPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TNewsOnlineLocationPageHandler;

	/**
	 * @inheritdoc
	 */
	public function getLink($objectID) {
		/** @var NewsCategory $category */
		$category =  new NewsCategory(new Category($objectID));
		return $category->getLink();
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

	/**
	 * @inheritdoc
	 */
	public function lookup($searchString) {
		$sql = "(
			SELECT  categoryID, title, title as name
			FROM    wcf" . WCF_N . "_category
			WHERE   title = ?
		)
		UNION
		(
			SELECT  0 as categoryID, languageItemValue as title, languageItem as name
			FROM    wcf" . WCF_N . "_language_item
			WHERE   languageID = ?
					AND languageItem LIKE ?
					AND languageItemValue LIKE ?
		)";
		$statement = WCF::getDB()->prepareStatement($sql, 10);
		$statement->execute([
			'%' . $searchString . '%',
			WCF::getLanguage()->languageID,
			'wcf.category.title%',
			'%' . $searchString . '%'
		]);

		$results = [];
		while ($row = $statement->fetchArray()) {
			$categoryID = $row['categoryID'];
			if ($row['categoryID']) {
				if ($row['title'] === 'wcf.category.title' . $categoryID)
					continue;
			} else {
				if (preg_match('~^wcf\.category\.title(\d+)$~', $row['title'], $matches))
					$categoryID = $matches[1];
				else
					continue;
			}

			$category = new NewsCategory(new Category($categoryID));

			$results[] = [
				'description' => $category->description,
				'image' => 'fa-text-o',
				'link' => $category->getLink(),
				'objectID' => $categoryID,
				'title' => $row['title']
			];
		}

		return $results;
	}
}
