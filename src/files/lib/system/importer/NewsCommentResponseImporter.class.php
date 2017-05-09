<?php

namespace cms\system\importer;

use wcf\system\importer\AbstractCommentResponseImporter;

/**
 * Importer for news comment responses
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCommentResponseImporter extends AbstractCommentResponseImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'de.codequake.cms.news.comment';
}
