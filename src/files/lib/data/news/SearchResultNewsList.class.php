<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

/**
 * Represents a list of news as search result.
 */
class SearchResultNewsList extends AccessibleNewsList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = SearchResultNews::class;
}
