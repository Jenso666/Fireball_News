<?php

namespace cms\system\moderation;
use cms\data\news\DeletedNewsList;
use wcf\system\moderation\IDeletedContentProvider;

/**
 * @author	Florian Gail
 * @copyright	2013-2017 codeQuake
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
