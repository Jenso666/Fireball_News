<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

/**
 * Represents the list of latest news.
 */
class LatestNewsList extends AccessibleNewsList {
	/**
	 * {@inheritdoc}
	 */
	public $sqlLimit = CMS_NEWS_LATEST_LIMIT;

	/**
	 * {@inheritdoc}
	 */
	public $sqlOrderBy = 'news.time DESC';
}
