<?php

namespace cms\data\news;

use cms\data\category\NewsCategory;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of accessible news
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class AccessibleNewsList extends ViewableNewsList {
	/**
	 * {@inheritdoc}
	 */
	public $sqlOrderBy = 'news.time DESC';

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		parent::__construct();

		// accessible news categories
		$accessibleCategoryIDs = NewsCategory::getAccessibleCategoryIDs();
		if (0 !== count($accessibleCategoryIDs)) {
			$this->getConditionBuilder()->add('news.newsID IN (SELECT newsID FROM cms' . WCF_N . '_news_to_category WHERE categoryID IN (?))',
				array($accessibleCategoryIDs));
		}
		else {
			$this->getConditionBuilder()->add('1=0');
		}

		// get default settings
		if (!WCF::getSession()->getPermission('mod.fireball.news.canModerateNews')) {
			$this->getConditionBuilder()->add('news.isDisabled = 0');
		}
		if (!WCF::getSession()->getPermission('mod.fireball.news.canModerateNews')) {
			$this->getConditionBuilder()->add('news.isDeleted = 0');
		}

		// can view delayed news
		if (!WCF::getSession()->getPermission('user.fireball.news.canViewDelayedNews')) {
			$this->getConditionBuilder()->add('news.isDelayed = 0');
		}

		// language Filter
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->getConditionBuilder()->add('(news.languageID IN (?) OR news.languageID IS NULL)',
				array(WCF::getUser()->getLanguageIDs()));
		}
	}
}
