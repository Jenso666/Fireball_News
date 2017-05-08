<?php

namespace cms\system\cache\builder;

use cms\data\news\NewsList;
use wcf\system\cache\builder\AbstractCacheBuilder;

class NewsCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = array();
		
		$newsList = new NewsList();
		$newsList->readObjects();
		$data['news'] = $newsList->getObjects();
		
		return $data;
	}
}
