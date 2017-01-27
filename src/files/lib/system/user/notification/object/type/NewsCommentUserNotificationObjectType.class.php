<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\object\type;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\WCF;

/**
 * Object type for news comment notifications.
 */
class NewsCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentUserNotificationObject::class;

	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Comment::class;

	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentList::class;

	/**
	 * @inheritDoc
	 */
	public function getOwnerID($objectID) {
		$sql = '
            SELECT news.userID
			FROM wcf' . WCF_N . '_comment comment
			LEFT JOIN cms' . WCF_N . '_news news
			ON (news.newsID = comment.objectID)
			WHERE comment.commentID = ?';
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectID]);
		$row = $statement->fetchArray();

		return ($row ? $row['userID'] : 0);
	}
}
