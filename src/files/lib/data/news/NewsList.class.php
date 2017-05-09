<?php

namespace cms\data\news;

use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Represents a list of news.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = News::class;

	public $categoryList = true;

	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();

		if ($this->categoryList) {
			if (0 !== count($this->objectIDs)) {
				$conditionBuilder = new PreparedStatementConditionBuilder();
				$conditionBuilder->add('newsID IN (?)', [$this->objectIDs]);

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
