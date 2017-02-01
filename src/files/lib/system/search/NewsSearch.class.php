<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\search;

use cms\data\category\NewsCategory;
use cms\data\news\SearchResultNewsList;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\search\AbstractSearchableObjectType;
use wcf\system\WCF;

/**
 * Search implementation to search for news.
 */
class NewsSearch extends AbstractSearchableObjectType {
	public $messageCache = [];

	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs, array $additionalData = null) {
		$list = new SearchResultNewsList();
		$list->getConditionBuilder()->add('news.newsID IN (?)', [$objectIDs]);
		$list->readObjects();

		foreach ($list->getObjects() as $item) {
			$this->messageCache[$item->newsID] = $item;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		return 'cms';
	}

	/**
	 * @inheritDoc
	 */
	public function getObject($objectID) {
		if (isset($this->messageCache[$objectID])) {
			return $this->messageCache[$objectID];
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getTableName() {
		return 'cms' . WCF_N . '_news';
	}

	/**
	 * @inheritDoc
	 */
	public function getIDFieldName() {
		return $this->getTableName() . '.newsID';
	}

	/**
	 * @inheritDoc
	 */
	public function getConditions(IForm $form = null) {
		$conditionBuilder = new PreparedStatementConditionBuilder();

		// accessible category ids
		$categoryIDs = NewsCategory::getAccessibleCategoryIDs();
		if (empty($categoryIDs)) {
			throw new PermissionDeniedException();
		}
		$conditionBuilder->add($this->getTableName() . '.newsID IN (SELECT newsID FROM cms' . WCF_N . '_news_to_category WHERE categoryID IN (?))',
			[$categoryIDs]);

		// default conditions
		$conditionBuilder->add($this->getTableName() . '.isDisabled = 0');
		$conditionBuilder->add($this->getTableName() . '.isDeleted = 0');

		// language
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add('(' . $this->getTableName() . '.languageID IN (?) OR ' . $this->getTableName() . '.languageID IS NULL)',
				[WCF::getUser()->getLanguageIDs()]);
		}

		return $conditionBuilder;
	}
}
