<?php

namespace cms\system\bbcode;

use cms\data\news\ViewableNews;
use wcf\system\bbcode\AbstractBBCode;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\WCF;

/**
 * Handles the [news] bbcode.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$newsID = 0;
		// get id attribute
		if (isset($openingTag['attributes'][0])) {
			$newsID = $openingTag['attributes'][0];
		}

		/** @var \cms\data\news\News $news */
		$news = ViewableNews::getNews($newsID);
		if ($news === null || !$news->newsID) {
			return '';
		}

		WCF::getTPL()->assign('_news', $news);

		return WCF::getTPL()->fetch('newsBBCodeTag', 'cms');
	}
}
