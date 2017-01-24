<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractLikeImporter;
use wcf\system\importer\ImportHandler;

/**
 * Importer for news likes.
 */
class NewsLikeImporter extends AbstractLikeImporter {
	/**
	 * Initializes the news like importer.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject',
			'de.codequake.cms.likeableNews');
		$this->objectTypeID = $objectType->objectTypeID;
	}

	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['objectID'] = ImportHandler::getInstance()->getNewID('de.codequake.cms.news', $data['objectID']);

		return parent::import($oldID, $data);
	}
}
