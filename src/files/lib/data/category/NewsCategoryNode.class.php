<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\category;

use wcf\data\category\CategoryNode;

/**
 * Represents a news category node.
 */
class NewsCategoryNode extends CategoryNode {
	/**
	 * {@inheritdoc}
	 */
	protected static $baseClass = 'cms\data\category\NewsCategory';

	protected $unreadNews;

	protected $news;

	/**
	 * @return int
	 */
	public function getUnreadNews() {
		if ($this->unreadNews === null) {
			$this->unreadNews = NewsCategoryCache::getInstance()->getUnreadNews($this->categoryID);
		}

		return $this->unreadNews;
	}

	/**
	 * @return int
	 */
	public function getNews() {
		if ($this->news === null) {
			$this->news = NewsCategoryCache::getInstance()->getNews($this->categoryID);
		}

		return $this->news;
	}
}
