<?php

use wcf\system\WCF;
use wcf\util\ArrayUtil;

/** @var \wcf\data\package\Package $package */
$package = $this->installation->getPackage();

$sql = "INSERT INTO wcf".WCF_N."_package_installation_sql_log (packageID, sqlTable, sqlColumn, sqlIndex) VALUES (?, ?, ?, ?)";
$logStatement = WCF::getDB()->prepareStatement($sql);

try {
	$statement = WCF::getDB()->prepareStatement("CREATE TABLE cms1_news_to_user (newsID INT(10), userID INT(10), PRIMARY KEY (userID, newsID));");
	$statement->execute();
	$logStatement->execute($package->packageID, 'cms1_news_to_user', '', '');
	
}
catch (\Exception $e) {}
catch (\Error $e) {}

try {
	$fk = getGenericIndexName('cms1_news_to_user', 'userID', 'fk');
	$statement = WCF::getDB()->prepareStatement("ALTER TABLE cms1_news_to_user ADD FOREIGN KEY " . $fk . " (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;");
	$statement->execute();
	$logStatement->execute($package->packageID, 'cms1_news_to_user', '', $fk);
}
catch (\Exception $e) {}
catch (\Error $e) {}

try {
	$fk = getGenericIndexName('cms1_news_to_user', 'newsID', 'fk');
	$statement = WCF::getDB()->prepareStatement("ALTER TABLE cms1_news_to_user ADD FOREIGN KEY " . $fk . " (newsID) REFERENCES cms1_news (newsID) ON DELETE CASCADE;");
	$statement->execute();
	$logStatement->execute($package->packageID, 'cms1_news_to_user', '', $fk);
}
catch (\Exception $e) {}
catch (\Error $e) {}

/**
 * Creates a generic index name.
 *
 * @param	string		$tableName
 * @param	string		$columns
 * @param	string		$suffix
 * @return	string		index name
 */
function getGenericIndexName($tableName, $columns, $suffix = '') {
	// get first column
	$columns = ArrayUtil::trim(explode(',', $columns));
	
	return md5($tableName . '_' . reset($columns)) . ($suffix ? '_' . $suffix : '');
}
