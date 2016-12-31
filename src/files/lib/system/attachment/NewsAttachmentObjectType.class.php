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
	 * {@inheritdoc}
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.fireball.news.attachmentMaxSize');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n",
			WCF::getSession()->getPermission('user.fireball.news.allowedAttachmentExtensions')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.fireball.news.maxAttachmentCount');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canDownload($objectID) {
		return WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canViewPreview($objectID) {
		return WCF::getSession()->getPermission('user.fireball.news.canDownloadAttachments');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		return WCF::getSession()->getPermission('user.fireball.news.canUploadAttachment');
	}

	/**
	 * {@inheritdoc}
	 */
	public function canDelete($objectID) {
		return WCF::getSession()->getPermission('user.fireball.news.canUploadAttachment');
	}
}
