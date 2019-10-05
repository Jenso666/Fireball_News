<?php

namespace cms\system\sitemap\object;

use cms\data\news\News;
use wcf\data\DatabaseObject;
use wcf\system\sitemap\object\AbstractSitemapObjectObjectType;

/**
 * Fireball News sitemap implementation.
 *
 * @author	Florian Gail
 * @copyright	2013 - 2017 codeQuake
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * @package	de.codequake.cms.news
 */
class NewsSitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return News::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLastModifiedColumn() {
		return 'lastChangeTime';
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		/** @var News $object */
		return $object->canRead();
	}
}
