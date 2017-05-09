<?php

namespace cms\system\user\notification\object\type;

use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * Object type for news comment response notifications.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentResponseUserNotificationObject::class;

	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = CommentResponse::class;

	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentResponseList::class;
}
