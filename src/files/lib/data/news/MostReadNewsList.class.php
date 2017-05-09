<?php

namespace cms\data\news;

/**
 * Represents the list of most read news
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class MostReadNewsList extends AccessibleNewsList {
	/**
	 * {@inheritdoc}
	 */
	public $sqlLimit = FIREBALL_NEWS_LATEST_LIMIT;

	/**
	 * {@inheritdoc}
	 */
	public $sqlOrderBy = 'news.clicks DESC';
}
