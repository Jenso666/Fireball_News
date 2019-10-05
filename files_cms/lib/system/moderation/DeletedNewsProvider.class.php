<?php

namespace cms\system\moderation;
use cms\data\news\DeletedNewsList;
use wcf\system\moderation\IDeletedContentProvider;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class DeletedNewsProvider implements IDeletedContentProvider {
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		return new DeletedNewsList();
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
