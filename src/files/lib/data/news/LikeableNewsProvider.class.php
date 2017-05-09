<?php

namespace cms\data\news;

use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Like provider for news.
 *
 * @author      Jens Krumsieck, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class LikeableNewsProvider extends AbstractObjectTypeProvider implements ILikeObjectTypeProvider {
	/**
	 * {@inheritdoc}
	 */
	public $className = 'cms\data\news\News';

	/**
	 * {@inheritdoc}
	 */
	public $decoratorClassName = 'cms\data\news\LikeableNews';

	/**
	 * {@inheritdoc}
	 */
	public $listClassName = 'cms\data\news\NewsList';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermissions(ILikeObject $object) {
		return $object->canRead();
	}
}
