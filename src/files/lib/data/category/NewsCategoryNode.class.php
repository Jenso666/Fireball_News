<?php

namespace cms\data\category;

use wcf\data\category\CategoryNode;

/**
 * Represents a news category node.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryNode extends CategoryNode {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = NewsCategory::class;

	protected $unreadNews = null;

	protected $news = null;

	/**
	 * @return int
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getUnreadNews() {
		if ($this->unreadNews === null) {
			$this->unreadNews = NewsCategoryCache::getInstance()->getUnreadNews($this->categoryID);
		}

		return $this->unreadNews;
	}

	/**
	 * @return int
	 * @throws \wcf\system\exception\SystemException
	 */
	public function getNews() {
		if ($this->news === null) {
			$this->news = NewsCategoryCache::getInstance()->getNews($this->categoryID);
		}

		return $this->news;
	}
	
	/**
	 * Returns node depth.
	 *
	 * @return	integer
	 */
	public function getDepth() {
		$element = $this;
		$depth = 1;
		
		while ($element->parentNode->parentNode != null) {
			$depth++;
			$element = $element->parentNode;
		}
		
		return $depth;
	}
}
