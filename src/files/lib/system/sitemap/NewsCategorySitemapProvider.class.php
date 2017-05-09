<?php

namespace cms\system\sitemap;

use cms\data\category\NewsCategoryNodeTree;
use wcf\system\sitemap\ISitemapProvider;
use wcf\system\WCF;

/**
 * Sitemap provider to list all news categories in sitemap.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategorySitemapProvider implements ISitemapProvider {
	public $objectTypeName = 'de.codequake.cms.category.news';

	/**
	 * {@inheritdoc}
	 */
	public function getTemplate() {
		$nodeTree = new NewsCategoryNodeTree($this->objectTypeName);
		$nodeList = $nodeTree->getIterator();

		WCF::getTPL()->assign(array('nodeList' => $nodeList,));

		return WCF::getTPL()->fetch('newsSitemap', 'cms');
	}
}
