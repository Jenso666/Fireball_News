<?php

namespace cms\system\importer;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractCommentImporter;
use wcf\system\importer\ImportHandler;

/**
 * Importer for news comments
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCommentImporter extends AbstractCommentImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'de.codequake.cms.news.comment';

	/**
	 * Initializes the news comment importer.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent',
			'de.codequake.cms.news.comment');
		$this->objectTypeID = $objectType->objectTypeID;
	}

	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$data['objectID'] = ImportHandler::getInstance()->getNewID('de.codequake.cms.news', $data['objectID']);
		if (!$data['objectID']) {
			return 0;
		}

		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);

		return parent::import($oldID, $data);
	}
}
