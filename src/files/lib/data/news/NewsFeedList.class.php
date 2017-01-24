<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

/**
 * Represent a list of news as rss feed entries.
 */
class NewsFeedList extends CategoryNewsList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = NewsFeed::class;
}
