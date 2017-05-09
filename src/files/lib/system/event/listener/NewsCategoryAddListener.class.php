<?php

namespace cms\system\event\listener;

use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */
class NewsCategoryAddListener implements IParameterizedEventListener {
	/**
	 * list of default additional data
	 * @var	array
	 */
	protected $defaultAdditionalData = array(
		'defaultNewsImageID' => null
	);
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if ($eventName == 'readParameters') {
			$eventObj->additionalData += $this->defaultAdditionalData;
		}
		else if ($eventName == 'validate') {
			// use default data
			foreach ($this->defaultAdditionalData as $key => $value) {
				if (!isset($eventObj->additionalData[$key])) {
					$eventObj->additionalData[$key] = $value;
				}
			}
		}
		else if ($eventName == 'saved') {
			foreach ($this->defaultAdditionalData as $key => $value) {
				$eventObj->additionalData[$key] = $value;
			}
		}
		else if ($eventName == 'assignVariables') {
			// use default data
			foreach ($this->defaultAdditionalData as $key => $value) {
				if (!isset($eventObj->additionalData[$key])) {
					$eventObj->additionalData[$key] = $value;
				}
			}
			
			WCF::getTPL()->assign(array(
				'defaultNewsImage' => !empty($eventObj->additionalData['defaultNewsImage']) ? $eventObj->additionalData['defaultNewsImage'] : null
			));
		}
	}
}
