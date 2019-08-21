<?php

namespace cms\system\clipboard\action;

use cms\data\news\NewsAction;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\clipboard\action\AbstractClipboardAction;
use wcf\system\WCF;

/**
 * Clipboard implementation for news
 *
 * @author      Florian Frantzen, Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsClipboardAction extends AbstractClipboardAction {
	protected $news = [];

	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete', 'enable', 'disable', 'trash', 'restore'];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = ['delete', 'enable', 'disable', 'trash', 'restore'];
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $objects, ClipboardAction $action) {
		if (!empty($this->news)) {
			$this->news = $objects;
		}
		
		$item = parent::execute($objects, $action);
		if ($item === null) {
			return null;
		}
		
		switch ($action->actionName) {
			case 'delete':
				$item->addParameter('objectIDs', array_keys($this->objects));
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.de.codequake.cms.news.delete.confirmMessage', array('count' => $item->getCount(),)));
				$item->addParameter('className', $this->getClassName());
				$item->setName('de.codequake.cms.news.delete');
				break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'de.codequake.cms.news';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return NewsAction::class;
	}
	
	/**
	 * @return integer[]
	 */
	protected function validateDelete() {
		$newsIDs = [];
		foreach ($this->objects as $news) {
			if ($news->canModerate()) {
				$newsIDs[] = $news->newsID;
			}
		}
		
		return $newsIDs;
	}
	
	/**
	 * @return integer[]
	 */
	public function validateEnable() {
		$newsIDs = [];
		foreach ($this->objects as $news) {
			if ($news->isDisabled && !$news->isDeleted) {
				$newsIDs[] = $news->newsID;
			}
		}
		
		return $newsIDs;
	}
	
	/**
	 * @return integer[]
	 */
	public function validateDisable() {
		$newsIDs = [];
		foreach ($this->objects as $news) {
			if (!$news->isDisabled && !$news->isDeleted) {
				$newsIDs[] = $news->newsID;
			}
		}
		
		return $newsIDs;
	}
	
	/**
	 * @return integer[]
	 */
	public function validateTrash() {
		$newsIDs = [];
		foreach ($this->objects as $news) {
			if (!$news->isDeleted) {
				$newsIDs[] = $news->newsID;
			}
		}
		
		return $newsIDs;
	}
	
	/**
	 * @return integer[]
	 */
	public function validateRestore() {
		$newsIDs = [];
		foreach ($this->objects as $news) {
			if ($news->isDeleted) {
				$newsIDs[] = $news->newsID;
			}
		}
		
		return $newsIDs;
	}
}
