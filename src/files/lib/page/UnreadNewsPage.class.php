<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\page;

use wcf\system\language\LanguageFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Page for the news category list.
 */
class UnreadNewsPage extends NewsOverviewPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'newsOverview';

	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();

		if (!WCF::getUser()->userID) {
			$this->objectList->getConditionBuilder()->add('1 = 0');
			return;
		}

		$this->objectList->sqlConditionJoins .= ' LEFT JOIN wcf' . WCF_N . '_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = ' . VisitTracker::getInstance()->getObjectTypeID('de.codequake.cms.news') . ' AND tracked_visit.objectID = news.newsID AND tracked_visit.userID = ' . WCF::getUser()->userID . ')';
		$this->objectList->getConditionBuilder()->add('news.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('de.codequake.cms.news')]);
		$this->objectList->getConditionBuilder()->add('tracked_visit.visitTime IS NULL');

		// apply language filter
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->objectList->getConditionBuilder()->add('(news.languageID IN (?) OR news.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
	}
}
