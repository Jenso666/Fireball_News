<?php

namespace cms\system\attachment;

use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.fireball.news.attachmentMaxSize');
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n",
			WCF::getSession()->getPermission('user.fireball.news.allowedAttachmentExtensions')));
	}

	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.fireball.news.maxAttachmentCount');
	}

	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		return WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments');
	}

	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments');
	}

	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		return WCF::getSession()->getPermission('user.fireball.news.canUploadAttachment');
	}

	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		return WCF::getSession()->getPermission('user.fireball.news.canUploadAttachment');
	}
}
