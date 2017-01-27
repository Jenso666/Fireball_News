<?php

namespace cms\system\page\handler;

use cms\data\news\AccessibleNewsList;
use wcf\system\page\handler\AbstractMenuPageHandler;

/**
 * {@inheritDoc}
 */
class MyNewsPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		$newsList = new AccessibleNewsList();
		$newsList->readObjects();
		$news = $newsList->getObjects();

		return count($news) > 0;
	}
}
