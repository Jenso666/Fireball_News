<?php

namespace cms\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractACLImporter;

/**
 * Importer for the news category access control layer.
 *
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryACLImporter extends AbstractACLImporter {
	/**
	 * {@inheritdoc}
	 */
	protected $objectTypeName = 'de.codequake.cms.category.news';

	/**
	 * Initializes the news category acl importer.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.acl',
			'de.codequake.cms.category.news');
		$this->objectTypeID = $objectType->objectTypeID;

		parent::__construct();
	}
}
