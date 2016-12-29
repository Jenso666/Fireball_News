<?php

namespace wcf\system\cache\builder;

use cms\data\news\NewsList;

/**
 * {@inheritDoc}
 */
class NewsCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [
			'news' => []
		];

		$newsList = new NewsList();
		$newsList->readObjects();
		$data['news'] = $newsList->getObjects();

		return $data;
	}
}
