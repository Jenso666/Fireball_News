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
	protected $news = array();

	/**
	 * {@inheritdoc}
	 */
	protected $actionClassActions = array('delete',);

	/**
	 * {@inheritdoc}
	 */
	protected $supportedActions = array('delete',);

	/**
	 * {@inheritdoc}
	 */
	public function execute(array $objects, ClipboardAction $action) {
		if (0 === count($this->news)) {
			$this->news = $objects;
		}

		$item = parent::execute($objects, $action);
		if ($item === null) {
			return;
		}

		switch ($action->actionName) {
			case 'delete':
				$item->addParameter('objectIDs', array_keys($this->news));
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

	protected function validateDelete() {
		$newsIDs = array();
		foreach ($this->news as $news) {
			if ($news->canModerate()) {
				$newsIDs[] = $news->newsID;
			}
		}

		return $newsIDs;
	}
}
