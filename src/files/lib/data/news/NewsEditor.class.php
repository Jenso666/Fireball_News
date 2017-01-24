<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Functions to edit a news.
 */
class NewsEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = News::class;

	/**
	 * @param int[] $categoryIDs
	 * @throws \wcf\system\database\exception\DatabaseQueryException
	 * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
	 * @throws \wcf\system\database\exception\DatabaseTransactionException
	 */
	public function updateCategoryIDs(array $categoryIDs = []) {
		// remove old assigns
		$sql = '
            DELETE FROM cms' . WCF_N . '_news_to_category
            WHERE newsID = ?';
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->newsID]);

		// assign new categories
		if (0 !== count($categoryIDs)) {
			WCF::getDB()->beginTransaction();

			$sql = '
                INSERT INTO cms' . WCF_N . '_news_to_category
                    (categoryID, newsID)
                VALUES (?, ?)';
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($categoryIDs as $categoryID) {
				$statement->execute([
					$categoryID,
					$this->newsID,
				]);
			}

			WCF::getDB()->commitTransaction();
		}
	}
}
