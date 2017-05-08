<?php

namespace cms\system\label\object;

use cms\data\category\NewsCategoryCache;
use wcf\system\label\object\AbstractLabelObjectHandler;
use wcf\system\label\LabelHandler;

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
		$this->labelGroups = empty($groupIDs) ? [] : LabelHandler::getInstance()->getLabelGroups($groupIDs);
	}
}
