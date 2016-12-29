<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\data\news;

use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Like provider for news.
 */
class LikeableNewsProvider extends AbstractObjectTypeProvider implements ILikeObjectTypeProvider {
	/**
	 * @inheritDoc
	 */
	public $className = 'cms\data\news\News';

	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = 'cms\data\news\LikeableNews';

	/**
	 * @inheritDoc
	 */
	public $listClassName = 'cms\data\news\NewsList';

	/**
	 * @inheritDoc
	 */
	public function checkPermissions(ILikeObject $object) {
		return $object->canRead();
	}
}
