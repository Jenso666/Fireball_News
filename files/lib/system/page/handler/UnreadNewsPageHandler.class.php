<?php

namespace cms\system\page\handler;

use cms\data\category\NewsCategory;
use cms\data\category\NewsCategoryCache;
use wcf\data\category\Category;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * {@inheritDoc}
 */
class UnreadNewsPageHandler extends AbstractMenuPageHandler {
	/**
	 * @var integer
	 */
	protected $counter;

	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return $this->getUnreadNews();
	}

	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return $this->getUnreadNews() !== 0;
	}

	protected function getUnreadNews() {
		if (!WCF::getUser()->userID) return 0;
		if ($this->counter !== null) return $this->counter;

		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('news.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('de.codequake.cms.news')]);
		$conditionBuilder->add('news.isDeleted = 0');
		$conditionBuilder->add('news.isDisabled = 0');
		$conditionBuilder->add('tracked_visit.visitTime IS NULL');
		$conditionBuilder->add('news_to_category.categoryID IN (?)', [NewsCategory::getAccessibleCategoryIDs()]);

		// apply language filter
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add('(news.languageID IN (?) OR news.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
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

		$counter = 0;
		while ($row = $statement->fetchArray()) {
			$counter =+ intval($row['count']);
		}

		$this->counter = $counter;
		return $counter;
	}
}
