<?php

namespace cms\system\page\handler;

use cms\data\news\AccessibleNewsList;
use cms\data\news\ViewableNewsList;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\WCF;

/**
 * {@inheritDoc}
 */
class MyNewsPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		if (!WCF::getUser()->userID) return false;
		
		$newsList = new ViewableNewsList();
		$newsList->getConditionBuilder()->add('news.userID = ? OR ? IN (SELECT tnews.newsID FROM cms'.WCF_N.'_news_to_user tnews WHERE tnews.newsID = news.newsID)', [WCF::getUser()->userID, WCF::getUser()->userID]);
		$newsList->readObjects();
		$news = $newsList->getObjects();

		return count($news) > 0;
	}
}
