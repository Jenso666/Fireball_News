<?php

namespace cms\data\news;

/**
 * Represent a list of news as rss feed entries
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsFeedList extends CategoryNewsList {
	/**
	 * {@inheritdoc}
	 */
	public $decoratorClassName = 'cms\data\news\NewsFeed';
}
