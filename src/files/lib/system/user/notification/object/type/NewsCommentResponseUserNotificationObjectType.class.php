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
class NewsCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType
{
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
