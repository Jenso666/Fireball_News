<?php

namespace cms\system\user\online\location;

use cms\data\category\NewsCategory;
use wcf\data\user\online\UserOnline;
use wcf\system\category\CategoryHandler;
use wcf\system\user\online\location\IUserOnlineLocation;
use wcf\system\WCF;

/**
 * Location implementation for news categories.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryLocation implements IUserOnlineLocation {
	/**
	 * {@inheritdoc}
	 */
	public function cache(UserOnline $user) {
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(UserOnline $user, $languageVariable = '') {
		if ($category = CategoryHandler::getInstance()->getCategory($user->objectID)) {
			$category = new NewsCategory($category);

			if ($category->getPermission('canView')) {
				return WCF::getLanguage()->getDynamicVariable($languageVariable, array('category' => $category,));
			}
		}

		return '';
	}
}
