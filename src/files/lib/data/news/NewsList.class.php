<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Represents a list of news.
 */
class NewsList extends DatabaseObjectList {
	/**
	 * {@inheritdoc}
	 */
	public $className = 'cms\data\news\News';

	public $categoryList = true;

	/**
	 * {@inheritdoc}
	 */
	public function readObjects() {
		parent::readObjects();

		if ($this->categoryList) {
			if (0 !== count($this->objectIDs)) {
				$conditionBuilder = new PreparedStatementConditionBuilder();
				$conditionBuilder->add('newsID IN (?)', array($this->objectIDs));

				$sql = '
                    SELECT *
                    FROM cms' . WCF_N . '_news_to_category
                    ' . $conditionBuilder;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditionBuilder->getParameters());

				while ($row = $statement->fetchArray()) {
					if (isset($this->objects[$row['newsID']])) {
						$this->objects[$row['newsID']]->setCategoryID($row['categoryID']);
					}
				}
			}
		}
	}

	/**
	 * @param bool $enable
	 */
	public function isCategoryList($enable = true) {
		$this->categoryList = $enable;
	}
}
