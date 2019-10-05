<?php

namespace cms\system\page\handler;

use cms\data\category\NewsCategory;
use wcf\data\category\Category;
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
		if ($user->pageObjectID === null) {
			return '';
		}
		
		$category = new NewsCategory(new Category($user->pageObjectID));
		if ($category === null || !$category->isAccessible()) {
			return '';
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier, ['category' => $category]);
	}
}
