<?php

namespace cms\data\category;

use cms\data\news\News;
use cms\system\cache\builder\NewsCategoryCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the news category cache.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryCache extends SingletonFactory {
	/**
	 * uncached news
	 * @var News[][]
	 */
	protected $unreadNews;

	/**
	 * cached news by category-id
	 * @var News[][]
	 */
	protected $news;

	/**
	 * cached label groups
	 * @var	integer[][]
	 */
	protected $cachedLabelGroups = [];

	/**
	 * @inheritDoc
	 */
	protected function init() {
		parent::init();

		$this->cachedLabelGroups = NewsCategoryCacheBuilder::getInstance()->getData([], 'labelGroups');
	}

	/**
	 * @param integer $categoryID
	 *
	 * @return News[]
	 */
	public function getNews($categoryID) {
		if ($this->news === null) {
			$this->initNews();
		}

		return isset($this->news[$categoryID]) ? $this->news[$categoryID] : [];
	}

	/**
	 * @param int $categoryID
	 *
	 * @return News[]
	 */
	public function getUnreadNews($categoryID) {
		if ($this->unreadNews === null) {
			$this->initUnreadNews();
		}

		return isset($this->unreadNews[$categoryID]) ? $this->unreadNews[$categoryID] : [];
	}

	protected function initNews() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('news.isDeleted = 0');
		$conditionBuilder->add('news.isDisabled = 0');

		// apply language filter
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$conditionBuilder->add('(news.languageID IN (?) OR news.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
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
			$conditionBuilder->add('news.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('de.codequake.cms.news')]);
			$conditionBuilder->add('news.isDeleted = 0');
			$conditionBuilder->add('news.isDisabled = 0');
			$conditionBuilder->add('tracked_visit.visitTime IS NULL');
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

			while ($row = $statement->fetchArray()) {
				$this->unreadNews[$row['categoryID']] = $row['count'];
			}
		}
	}

	/**
	 * @param integer $categoryID
	 * @return array|\integer[]|\integer[][]
	 */
	public function getLabelGroupIDs($categoryID = null) {
		if ($categoryID === null) {
			return $this->cachedLabelGroups;
		}

		if (isset($this->cachedLabelGroups[$categoryID])) {
			return $this->cachedLabelGroups[$categoryID];
		}

		return [];
	}



}
