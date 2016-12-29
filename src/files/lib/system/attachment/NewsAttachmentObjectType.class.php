<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\attachment;

use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 *
 */
class NewsAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.cms.news.attachmentMaxSize');
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n",
			WCF::getSession()->getPermission('user.cms.news.allowedAttachmentExtensions')));
	}

	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.cms.news.maxAttachmentCount');
	}

	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		return WCF::getSession()->getPermission('user.cms.news.canDownloadAttachments');
	}

	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return WCF::getSession()->getPermission('user.cms.news.canDownloadAttachments');
	}

	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		return WCF::getSession()->getPermission('user.cms.news.canUploadAttachment');
	}

	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		return WCF::getSession()->getPermission('user.cms.news.canUploadAttachment');
	}
}
