<?php

/**
 * @author    Jens Krumsieck
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\user\notification\object\type;

use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * Object type for news comment response notifications.
 */
class NewsCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentResponseUserNotificationObject';

	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = 'wcf\data\comment\response\CommentResponse';

	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = 'wcf\data\comment\response\CommentResponseList';
}
