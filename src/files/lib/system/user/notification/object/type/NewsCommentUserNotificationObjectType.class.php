<?php

namespace cms\system\user\notification\object\type;

use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\WCF;

/**
 * Object type for news comment notifications.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
