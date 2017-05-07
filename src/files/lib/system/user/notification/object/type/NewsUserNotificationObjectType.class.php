<?php

/**
 * @author    Florian Gail
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */

namespace cms\system\user\notification\object\type;

use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * Object type for news notifications.
 */
class NewsUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * {@inheritdoc}
	 */
	protected static $decoratorClassName = 'cms\system\user\notification\object\NewsUserNotificationObject';
	
	/**
	 * {@inheritdoc}
	 */
	protected static $objectClassName = 'cms\data\news\News';
	
	/**
	 * {@inheritdoc}
	 */
	protected static $objectListClassName = 'cms\data\news\NewsList';
}
