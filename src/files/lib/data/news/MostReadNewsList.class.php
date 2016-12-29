<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

/**
 * Represents the list of most read news.
 */
class MostReadNewsList extends AccessibleNewsList {
	/**
	 * @inheritDoc
	 */
	public $sqlLimit = FIREBALL_NEWS_LATEST_LIMIT;

	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'news.clicks DESC';
}
