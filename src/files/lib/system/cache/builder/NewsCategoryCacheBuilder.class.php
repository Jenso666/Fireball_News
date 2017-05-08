<?php

namespace cms\system\cache\builder;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

class NewsCategoryCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = array(
			'labelGroups' => array()
		);
		
		// get object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.objectType', 'de.codequake.cms.news.category');
		if ($objectType !== null) {
			// fetch data
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("objectTypeID = ?", array($objectType->objectTypeID));
			
			$sql = "SELECT	groupID, objectID
			FROM	wcf" . WCF_N . "_label_group_to_object " . $conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!isset($data['labelGroups'][$row['objectID']])) {
					$data['labelGroups'][$row['objectID']] = array();
				}
				
				$data['labelGroups'][$row['objectID']][] = $row['groupID'];
			}
		}
		
		return $data;
	}
}
