<?php

/**
 * @author    Florian Frantzen
 * @copyright 2014-2015 codequake.de
 * @license   LGPL
 */
namespace cms\system\clipboard\action;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\clipboard\action\AbstractClipboardAction;
use wcf\system\WCF;

/**
 * Clipboard implementation for news.
 */
class NewsClipboardAction extends AbstractClipboardAction {
	/**
	 * {@inheritdoc}
	 */
	protected $actionClassActions = array('delete', 'enable', 'disable', 'trash', 'restore');
	
	/**
	 * {@inheritdoc}
	 */
	protected $supportedActions = array('delete', 'enable', 'disable', 'trash', 'restore');
	
	/**
	 * {@inheritdoc}
	 */
	public function execute(array $objects, ClipboardAction $action) {
		if (!empty($this->news)) {
			$this->news = $objects;
		}
		
		$item = parent::execute($objects, $action);
		if ($item === null) {
			return;
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
	 * {@inheritdoc}
	 */
	public function getTypeName() {
		return 'de.codequake.cms.news';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getClassName() {
		return 'cms\data\news\NewsAction';
	}
	
	/**
	 * @return integer[]
	 */
	protected function validateDelete() {
		$newsIDs = array();
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
		$newsIDs = array();
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
		$newsIDs = array();
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
		$newsIDs = array();
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
		$newsIDs = array();
		foreach ($this->objects as $news) {
			if ($news->isDeleted) {
				$newsIDs[] = $news->newsID;
			}
		}
		
		return $newsIDs;
	}
}
