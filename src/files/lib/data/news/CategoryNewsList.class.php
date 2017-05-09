<?php

namespace cms\data\news;

use cms\data\category\NewsCategory;
use wcf\system\category\CategoryHandler;
use wcf\system\WCF;

/**
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class CategoryNewsList extends AccessibleNewsList {

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $categoryIDs) {
		parent::__construct();
		if (!empty($categoryIDs)) {
			$this->getConditionBuilder()->add('news_to_category.categoryID IN (?)', [$categoryIDs]);
			$this->getConditionBuilder()->add('news.newsID = news_to_category.newsID');
		}
		else
			$this->getConditionBuilder()->add('1=0');
		
		// delayed news filter
		foreach ($categoryIDs as $categoryID) {
			$category = new NewsCategory(CategoryHandler::getInstance()->getCategory($categoryID));
			if (!$category->getPermission('canViewDelayedNews')) $this->getConditionBuilder()->add('news.isDisabled = ?', [0]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function readObjectIDs() {
		$this->objectIDs = [];
		$sql = "SELECT	DISTINCT(news_to_category.newsID) AS objectID, time, news.newsID
				FROM	cms" . WCF_N . "_news_to_category news_to_category,
						cms" . WCF_N . "_news news
						" . $this->sqlConditionJoins . "
						" . $this->getConditionBuilder() . "
						" . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
		$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			$this->objectIDs[] = $row['objectID'];
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	cms" . WCF_N . "_news_to_category news_to_category,
				cms" . WCF_N . "_news news
			" . $this->sqlConditionJoins . "
			" . $this->getConditionBuilder();
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		$row = $statement->fetchArray();
		return $row['count'];
	}
}
