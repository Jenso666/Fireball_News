<?php

namespace cms\system\cache\builder;

use cms\data\news\NewsList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
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
