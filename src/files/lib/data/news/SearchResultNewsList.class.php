<?php

namespace cms\data\news;

/**
 * Represents a list of news as search result.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class SearchResultNewsList extends AccessibleNewsList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = SearchResultNews::class;
}
