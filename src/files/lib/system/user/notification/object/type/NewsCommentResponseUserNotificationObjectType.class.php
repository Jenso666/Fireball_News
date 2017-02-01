<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\object\type;

use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * Object type for news comment response notifications.
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
