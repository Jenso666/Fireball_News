<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\object\type;

use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\WCF;

/**
 * Object type for news comment notifications.
 */
class NewsCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * {@inheritdoc}
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentUserNotificationObject';

	/**
	 * {@inheritdoc}
	 */
	protected static $objectClassName = 'wcf\data\comment\Comment';

	/**
	 * {@inheritdoc}
	 */
	protected static $objectListClassName = 'wcf\data\comment\CommentList';

	/**
	 * {@inheritdoc}
	 */
	public function getOwnerID($objectID) {
		$sql = '
            SELECT news.userID
			FROM wcf' . WCF_N . '_comment comment
			LEFT JOIN cms' . WCF_N . '_news news
			ON (news.newsID = comment.objectID)
			WHERE comment.commentID = ?';
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectID));
		$row = $statement->fetchArray();

		return ($row ? $row['userID'] : 0);
	}
}
