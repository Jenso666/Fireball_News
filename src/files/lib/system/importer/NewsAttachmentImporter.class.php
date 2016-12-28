<?php

/**
 * @author    Florian Gail
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\importer;

use cms\data\news\News;
use cms\data\news\NewsEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\importer\AbstractAttachmentImporter;
use wcf\system\importer\ImportHandler;

/**
 * Importer for news attachments.
 */
class NewsAttachmentImporter extends AbstractAttachmentImporter {
	/**
	 * Initializes the news attachment importer.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType',
			'de.codequake.cms.news');
		$this->objectTypeID = $objectType->objectTypeID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['objectID'] = ImportHandler::getInstance()->getNewID('de.codequake.cms.news', $data['objectID']);
		if (!$data['objectID']) {
			return 0;
		}

		$attachmentID = parent::import($oldID, $data, $additionalData);
		if ($attachmentID && $attachmentID != $oldID) {
			// fix embedded attachments
			$news = new News($data['objectID']);

			if (($newMessage = $this->fixEmbeddedAttachments($news->message, $oldID, $attachmentID)) !== false) {
				$editor = new NewsEditor($news);
				$editor->update(array('message' => $newMessage,));
			}
		}

		return $attachmentID;
	}
}
