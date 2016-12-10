<?php

/**
 * @author    Florian Gail
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractACLImporter;

/**
 * Importer for the news category access control layer.
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
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.acl', 'de.codequake.cms.category.news');
		$this->objectTypeID = $objectType->objectTypeID;

		parent::__construct();
	}
}
