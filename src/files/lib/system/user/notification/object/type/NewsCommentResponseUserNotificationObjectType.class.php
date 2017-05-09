<?php

namespace cms\system\user\notification\object\type;

use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

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
	 * {@inheritdoc}
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentResponseUserNotificationObject';

	/**
	 * {@inheritdoc}
	 */
	protected static $objectClassName = 'wcf\data\comment\response\CommentResponse';

	/**
	 * {@inheritdoc}
	 */
	protected static $objectListClassName = 'wcf\data\comment\response\CommentResponseList';
}
