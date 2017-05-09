<?php

namespace cms\data\category;

use wcf\data\category\CategoryNodeTree;

/**
 * Represent a tree of news category nodes.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryNodeTree extends CategoryNodeTree {
	/**
	 * {@inheritdoc}
	 */
	protected $nodeClassName = 'cms\data\category\NewsCategoryNode';
}
