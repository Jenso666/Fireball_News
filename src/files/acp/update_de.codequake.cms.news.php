<?php

/**
 * @author      Florian Gail
 * @copyright   2014-2017 codeQuake.de, mysterycode.de <https://www.mysterycode.de>
 * @license     LGPL-3.0 <https://github.com/codeQuake/Fireball_News/blob/v1.2/LICENSE>
 * @package     de.codequake.cms.news
 */

use wcf\data\language\item\LanguageItemEditor;
use wcf\data\language\item\LanguageItemList;
use wcf\data\option\category\OptionCategoryEditor;
use wcf\data\option\category\OptionCategoryList;
use wcf\data\option\OptionEditor;
use wcf\data\option\OptionList;
use wcf\data\user\group\option\UserGroupOptionEditor;
use wcf\data\user\group\option\UserGroupOptionList;

$package = $this->installation->getPackage();
$packageID = $package->packageID;

/**
 * REPLACE LANGUAGE ITEMS
 */
$languageItemList = new LanguageItemList();
$languageItemList->getConditionBuilder()->add('(languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?
	OR languageItem LIKE ?)', array(
		'cms.acp.menu.link.cms%',
		'wcf.acp.group.option.admin.cms%',
		'wcf.acp.group.option.category.admin.cms%',
		'wcf.acp.group.option.category.mod.cms%',
		'wcf.acp.group.option.category.user.cms%',
		'wcf.acp.group.option.mod.cms%',
		'wcf.acp.group.option.user.cms%',
		'wcf.acp.option.category.cms%',
		'wcf.acp.option.cms_%',
		'wcf.acl.option.category.de.codequake.cms%',
	));
$languageItemList->getConditionBuilder()->add('packageID = ?', array($packageID));
$languageItemList->readObjects();
$affectedObjects = $languageItemList->getObjects();
foreach ($affectedObjects as $object) {
	$newVal = str_replace(array(
		'cms.acp.menu.link.cms',
		'wcf.acp.group.option.admin.cms',
		'wcf.acp.group.option.category.admin.cms',
		'wcf.acp.group.option.category.mod.cms',
		'wcf.acp.group.option.category.user.cms',
		'wcf.acp.group.option.mod.cms',
		'wcf.acp.group.option.user.cms',
		'wcf.acp.option.category.cms',
		'wcf.acp.option.cms_',
		'wcf.acl.option.category.de.codequake.cms.page.user.cms.page',
		'wcf.acl.option.category.de.codequake.cms.page.user.cms.page.comment'
	), array(
		'fireball.acp.menu.link.fireball',
		'wcf.acp.group.option.admin.fireball',
		'wcf.acp.group.option.category.admin.fireball',
		'wcf.acp.group.option.category.mod.fireball',
		'wcf.acp.group.option.category.user.fireball',
		'wcf.acp.group.option.mod.fireball',
		'wcf.acp.group.option.user.fireball',
		'wcf.acp.option.category.fireball',
		'wcf.acp.option.fireball_',
		'wcf.acl.option.category.de.codequake.cms.page.user.fireball.page',
		'wcf.acl.option.category.de.codequake.cms.page.user.fireball.page.comment'
	), $object->languageItem);
	$objectEditor = new LanguageItemEditor($object);
	$objectEditor->update(array('languageItem' => $newVal));
}

/**
 * REPLACE OPTIONS
 */
$optionList = new OptionList();
$optionList->getConditionBuilder()->add('option_table.optionName LIKE ?', array('cms_%'));
$optionList->getConditionBuilder()->add('packageID = ?', array($packageID));
$optionList->readObjects();
$affectedObjects = $optionList->getObjects();
foreach ($affectedObjects as $object) {
	if ($object->optionName == 'cms_install_date') continue;
	$newVal = str_replace('cms_', 'fireball_', $object->optionName);
	$objectEditor = new OptionEditor($object);
	$objectEditor->update(array('optionName' => $newVal));
}

/**
 * REPLACE OPTION CATEGORIES
 */
$categoryList = new OptionCategoryList();
$categoryList->getConditionBuilder()->add('categoryName LIKE ?', array('cms%'));
$categoryList->getConditionBuilder()->add('packageID = ?', array($packageID));
$categoryList->readObjects();
$affectedObjects = $categoryList->getObjects();

foreach ($affectedObjects as $object) {
	$newVal = str_replace('cms', 'fireball', $object->categoryName);
	$objectEditor = new OptionCategoryEditor($object);
	$objectEditor->update(array('categoryName' => $newVal));
}

/**
 * REPLACE USER GROUP OPTIONS
 */
$groupOptionList = new UserGroupOptionList();
$groupOptionList->getConditionBuilder()->add('(optionName LIKE ? OR optionName LIKE ? OR optionName LIKE ?)', array(
	'admin.cms%',
	'mod.cms%',
	'user.cms%'
));
$groupOptionList->getConditionBuilder()->add('packageID = ?', array($packageID));
$groupOptionList->readObjects();
$affectedObjects = $groupOptionList->getObjects();
foreach ($affectedObjects as $object) {
	$old = array(
		'admin.cms',
		'mod.cms',
		'user.cms'
	);
	$new = array(
		'admin.fireball',
		'mod.fireball',
		'user.fireball'
	);
	$newName = str_replace($old, $new, $object->optionName);
	$newCat = str_replace($old, $new, $object->categoryName);
	$objectEditor = new UserGroupOptionEditor($object);
	$objectEditor->update(array(
		'optionName' => $newName,
		'categoryName' => $newCat
	));
}
