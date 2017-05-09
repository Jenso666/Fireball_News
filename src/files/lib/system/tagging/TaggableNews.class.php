<?php

namespace cms\system\tagging;

use cms\data\news\TaggedNewsList;
use wcf\data\tag\Tag;
use wcf\system\tagging\ITaggable;

/**
 * Tagging implementation for news.
 *
 * @author      Jens Krumsieck
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class TaggableNews implements ITaggable {
	/**
	 * @inheritDoc
	 */
	public function getObjectList(Tag $tag) {
		return new TaggedNewsList($tag);
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'newsListItemsMessage';
	}

	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		return 'cms';
	}
}
