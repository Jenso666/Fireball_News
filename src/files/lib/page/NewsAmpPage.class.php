<?php

/**
 * @author    Florian Gail
 * @copyright 2014-2017 codequake.de
 * @license   LGPL
 */

namespace cms\page;

use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Page for amp implementation for news entries.
 */
class NewsAmpPage extends NewsPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'ampNews';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('NewsAmp', ['object' => $this->news]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'regularCanonicalURL' => $this->news->getLink()
		]);
	}
}
