<?php

namespace cms\data\news;

use cms\system\cache\builder\NewsCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * {@inheritDoc}
 */
class NewsCache extends SingletonFactory {
	/**
	 * cached objects
	 * @var News[]
	 */
	protected $cachedObjects = [];

	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cachedObjects = NewsCacheBuilder::getInstance()->getData([], 'news');
	}

	/**
	 * Returns the news object with the given id
	 * @param $newsID
	 * @return null|News
	 */
	public function getNews($newsID) {
		if (!empty($this->cachedObjects[$newsID])) {
			return $this->cachedObjects[$newsID];
		} else {
			return null;
		}
	}

	/**
	 * Returns a list of all cached news
	 * @return News[]
	 */
	public function getAllNews() {
		return $this->cachedObjects;
	}
}
