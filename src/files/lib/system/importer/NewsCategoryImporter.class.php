<?php

namespace cms\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractCategoryImporter;

/**
 * Importer for news categories
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
