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
