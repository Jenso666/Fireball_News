<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\category;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the news category cache.
 */
class NewsCategoryCache extends SingletonFactory {
	protected $unreadNews;

	protected $news = array();

	/**
	 * @param int $categoryID
	 *
	 * @return int
	 */
	public function getNews($categoryID) {
		if ($this->news === null) {
			$this->initNews();
		}

		if (array_key_exists($categoryID, $this->news)) {
			return $this->news[$categoryID];
		}

		return 0;
	}

	/**
	 * @param int $categoryID
	 *
	 * @return int
	 */
	public function getUnreadNews($categoryID) {
		if ($this->news === null) {
			$this->initUnreadNews();
		}

		if (array_key_exists($categoryID, $this->unreadNews)) {
			return $this->unreadNews[$categoryID];
		}

		return 0;
	}

	protected function initNews() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('news.isDeleted = 0');
		$conditionBuilder->add('news.isDisabled = 0');

		// apply language filter
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add('(news.languageID IN (?) OR news.languageID IS NULL)', array(WCF::getUser()->getLanguageIDs(),));
		}

		$sql = 'SELECT		COUNT(*) AS count, news_to_category.categoryID
				FROM		cms' . WCF_N . '_news news
				LEFT JOIN	cms' . WCF_N . '_news_to_category news_to_category
				ON		(news_to_category.newsID = news.newsID)
				' . $conditionBuilder . '
				GROUP BY	news_to_category.categoryID';
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$this->news[$row['categoryID']] = $row['count'];
		}
	}

	protected function initUnreadNews() {
		if (WCF::getUser()->userID) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('news.lastChangeTime > ?', array(VisitTracker::getInstance()->getVisitTime('de.codequake.cms.news'),));
			$conditionBuilder->add('news.isDeleted = 0');
			$conditionBuilder->add('news.isDisabled = 0');
			$conditionBuilder->add('tracked_visit.visitTime IS NULL');
			// apply language filter
			if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
				$conditionBuilder->add('(news.languageID IN (?) OR news.languageID IS NULL)', array(WCF::getUser()->getLanguageIDs(),));
			}

			$sql = 'SELECT		COUNT(*) AS count, news_to_category.categoryID
				FROM		cms' . WCF_N . '_news news
				LEFT JOIN	wcf' . WCF_N . '_tracked_visit tracked_visit
				ON		(tracked_visit.objectTypeID = ' . VisitTracker::getInstance()->getObjectTypeID('de.codequake.cms.news') . ' AND tracked_visit.objectID = news.newsID AND tracked_visit.userID = ' . WCF::getUser()->userID . ')
				LEFT JOIN	cms' . WCF_N . '_news_to_category news_to_category
				ON		(news_to_category.newsID = news.newsID)
				' . $conditionBuilder . '
				GROUP BY	news_to_category.categoryID';
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());

			while ($row = $statement->fetchArray()) {
				$this->unreadNews[$row['categoryID']] = $row['count'];
			}
		}
	}
}
