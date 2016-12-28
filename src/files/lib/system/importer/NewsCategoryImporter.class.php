<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractCategoryImporter;

/**
 * Importer for news categories.
 */
class NewsCategoryImporter extends AbstractCategoryImporter {
	/**
	 * {@inheritdoc}
	 */
	protected $objectTypeName = 'de.codequake.cms.category.news';

	/**
	 * Initializes the news category importer.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.category',
			'de.codequake.cms.category.news');
		$this->objectTypeID = $objectType->objectTypeID;
	}
}
