<?php

namespace cms\system\label\object;

use cms\data\category\NewsCategoryCache;
use wcf\system\label\object\AbstractLabelObjectHandler;
use wcf\system\label\LabelHandler;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsLabelObjectHandler extends AbstractLabelObjectHandler {
	/**
	 * @var        integer
	 */
	protected $categoryID = 0;
	
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'de.codequake.cms.news';
	
	/**
	 * @param        integer $categoryID
	 */
	public function setCategoryID($categoryID) {
		$this->categoryID = $categoryID;
		
		// load label groups
		$groupIDs = NewsCategoryCache::getInstance()->getLabelGroupIDs($this->categoryID);
		$this->labelGroups = empty($groupIDs) ? array() : LabelHandler::getInstance()->getLabelGroups($groupIDs);
	}
}
