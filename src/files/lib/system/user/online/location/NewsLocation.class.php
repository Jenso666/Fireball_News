<?php

namespace cms\system\user\online\location;

use cms\data\news\News;
use wcf\data\user\online\UserOnline;
use wcf\system\user\online\location\IUserOnlineLocation;
use wcf\system\WCF;

/**
 * Location implementation for news entries.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsLocation implements IUserOnlineLocation {
	/**
	 * {@inheritdoc}
	 */
	public function cache(UserOnline $user) {
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(UserOnline $user, $languageVariable = '') {
		$news = new News($user->objectID);
		if ($news->newsID && $news->isVisible()) {
			return WCF::getLanguage()->getDynamicVariable($languageVariable, array('news' => $news,));
		}

		return '';
	}
}
