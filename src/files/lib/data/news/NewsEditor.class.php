<?php

namespace cms\data\news;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Functions to edit a news
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsEditor extends DatabaseObjectEditor {
	/**
	 * {@inheritdoc}
	 */
	protected static $baseClass = 'cms\data\news\News';

	/**
	 * @param int[] $categoryIDs
	 */
	public function updateCategoryIDs(array $categoryIDs = array()) {
		// remove old assigns
		$sql = '
            DELETE FROM cms' . WCF_N . '_news_to_category
            WHERE newsID = ?';
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->newsID));

		// assign new categories
		if (0 !== count($categoryIDs)) {
			WCF::getDB()->beginTransaction();

			$sql = '
                INSERT INTO cms' . WCF_N . '_news_to_category
                    (categoryID, newsID)
                VALUES (?, ?)';
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($categoryIDs as $categoryID) {
				$statement->execute(array(
					$categoryID,
					$this->newsID,
				));
			}

			WCF::getDB()->commitTransaction();
		}
	}
}
