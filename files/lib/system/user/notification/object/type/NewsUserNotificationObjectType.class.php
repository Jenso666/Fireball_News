<?php

namespace cms\system\user\notification\object\type;

use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * Object type for news notifications.
 *
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
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
