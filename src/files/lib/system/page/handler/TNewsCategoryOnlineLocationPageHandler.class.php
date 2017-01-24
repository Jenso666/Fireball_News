<?php

namespace cms\system\page\handler;
use cms\data\news\NewsCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\page\handler\TOnlineLocationPageHandler;
use wcf\system\WCF;

/**
 * {@inheritDoc}
 */
trait TNewsCategoryOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;

	/**
	 * @inheritdoc
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if ($user->pageObjectID === null)
			return '';

		$news = NewsCache::getInstance()->getNews($user->pageObjectID);
		if ($news === null || !$news->canRead())
			return '';

		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier, ['news' => $news]);
	}
}
