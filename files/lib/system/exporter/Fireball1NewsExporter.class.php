<?php
namespace cms\system\exporter;

use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\PackageCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\exporter\AbstractExporter;
use wcf\system\importer\ImportHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

class Fireball1NewsExporter extends AbstractExporter {
	/**
	 * wcf installation number
	 * @var	integer
	 */
	protected $dbNo = 0;
	
	/**
	 * category cache
	 * @var	array
	 */
	protected $categoryCache = array();
	
	/**
	 * @see \wcf\system\exporter\AbstractExporter::$methods
	 */
	protected $methods = array(
		'de.codequake.cms.category.news' => 'NewsCategories',
		'de.codequake.cms.category.news.acl' => 'NewsCategoryACLs',
		'de.codequake.cms.news' => 'NewsEntries',
		'de.codequake.cms.news.comment' => 'NewsComments',
		'de.codequake.cms.news.comment.response' => 'NewsCommentResponses',
		'de.codequake.cms.news.like' => 'NewsLikes',
		'de.codequake.cms.news.attachment' => 'NewsAttachments'
	);
	
	/**
	 * @see	\wcf\system\exporter\AbstractExporter::$limits
	 */
	protected $limits = array(
		'de.codequake.cms.category.news' => 300,
		'de.codequake.cms.category.news.acl' => 50,
		'de.codequake.cms.news' => 200,
		'de.codequake.cms.attachment' => 100
	);
	
	/**
	 * @see	\wcf\system\exporter\IExporter::init()
	 */
	public function init() {
		parent::init();
		
		if (preg_match('/^cms(\d+)_$/', $this->databasePrefix, $match)) {
			$this->dbNo = $match[1];
		}
		
		// file system path
		if (! empty($this->fileSystemPath)) {
			if (! @file_exists($this->fileSystemPath . 'lib/core.functions.php') && @file_exists($this->fileSystemPath . 'wcf/lib/core.functions.php')) {
				$this->fileSystemPath = $this->fileSystemPath . 'wcf/';
			}
		}
	}
	
	/**
	 * @see	\wcf\system\exporter\IExporter::validateFileAccess()
	 */
	public function validateFileAccess() {
// 		if (in_array('de.codequake.cms.news', $this->selectedData)) {
// 			if (empty($this->fileSystemPath) || !@file_exists($this->fileSystemPath.'lib/core.functions.php')) {
// 				return false;
// 			}
// 		}
		
		return true;
	}
	
	/**
	 * @see \wcf\system\exporter\IExporter::getSupportedData()
	 */
	public function getSupportedData() {
		return array(
			'de.codequake.cms.category.news' => array(
				'de.codequake.cms.category.news.acl'
			),
			'de.codequake.cms.news' => array(
				'de.codequake.cms.news.comment',
				'de.codequake.cms.news.like',
				'de.codequake.cms.news.attachment'
			)
		);
	}
	
	/**
	 * @see \wcf\system\exporter\IExporter::validateDatabaseAccess()
	 */
	public function validateDatabaseAccess() {
		parent::validateDatabaseAccess();
		
		$sql = "SELECT	packageID, packageDir, packageVersion
			FROM	wcf".$this->dbNo."_package
			WHERE	package = ?";
		$statement = $this->database->prepareStatement($sql, 1);
		$statement->execute(array('de.codequake.cms'));
		$row = $statement->fetchArray();
		
		if ($row !== false) {
			// check cms version
			if (substr($row['packageVersion'], 0, 1) != 1) {
				throw new DatabaseException('Cannot find Fireball CMS 1.x installation', $this->database);
			}
		} else {
			throw new DatabaseException('Cannot find Fireball CMS installation', $this->database);
		}
	}
	
	/**
	 *
	 * @see \wcf\system\exporter\IExporter::getQueue()
	 */
	public function getQueue() {
		$queue = array();
		
		// category
		if (in_array('de.codequake.cms.category.news', $this->selectedData)) {
			$queue[] = 'de.codequake.cms.category.news';
			
			if (in_array('de.codequake.cms.category.news.acl', $this->selectedData)) {
				$queue[] = 'de.codequake.cms.category.news.acl';
			}
		}
		
		// news
		if (in_array('de.codequake.cms.news', $this->selectedData)) {
			$queue[] = 'de.codequake.cms.news';

			if (in_array('de.codequake.cms.news.comment', $this->selectedData)) {
				$queue[] = 'de.codequake.cms.news.comment';
				$queue[] = 'de.codequake.cms.news.comment.response';
			}
			
			if (in_array('de.codequake.cms.news.like', $this->selectedData)) {
				$queue[] = 'de.codequake.cms.news.like';
			}
			
			if (in_array('de.codequake.cms.news.attachment', $this->selectedData)) {
				$queue[] = 'de.codequake.cms.news.attachment';
			}
		}
		
		return $queue;
	}
	
	/**
	 * @see	\wcf\system\exporter\IExporter::getDefaultDatabasePrefix()
	 */
	public function getDefaultDatabasePrefix() {
		return 'cms1_';
	}
	
	/**
	 * Counts categories.
	 */
	public function countNewsCategories() {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.category', 'de.codequake.cms.category.news');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".$this->dbNo."_category
			WHERE	objectTypeID = ?";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($objectTypeID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Exports categories.
	 */
	public function exportNewsCategories($offset, $limit) {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.category', 'de.codequake.cms.category.news');
		
		$sql = "SELECT		*
			FROM		wcf".$this->dbNo."_category
			WHERE		objectTypeID = ?
			ORDER BY	parentCategoryID, showOrder, categoryID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute(array($objectTypeID));
		
		while ($row = $statement->fetchArray()) {
			$this->categoryCache[$row['parentCategoryID']][] = $row;
		}
		
		$this->exportCategoriesRecursively();
	}
	
	/**
	 * Exports the categories of the given parent recursively.
	 */
	protected function exportCategoriesRecursively($parentID = 0) {
		if (!isset($this->categoryCache[$parentID]))
			return;
		
		foreach ($this->categoryCache[$parentID] as $category) {
			$additionalData = @unserialize($category['additionalData']);
			
			// import category
			$categoryID = ImportHandler::getInstance()->getImporter('de.codequake.cms.category.news')->import($category['categoryID'], array(
				'parentCategoryID' => $category['parentCategoryID'],
				'title' => $category['title'],
				'description' => $category['description'],
				'showOrder' => $category['showOrder'],
				'time' => $category['time'],
				'isDisabled' => $category['isDisabled'],
				'additionalData' => serialize(array())
			));
			
			$this->updateCategoryI18nData($categoryID, $category);
			
			$this->exportCategoriesRecursively($category['categoryID']);
		}
	}
	
	/**
	 * Counts ACLs.
	 */
	protected function countNewsCategoryACLs() {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.acl', 'de.codequake.cms.category.news');
		
		$sql = "SELECT	(
					(
						SELECT		COUNT(*)
						FROM		wcf".$this->dbNo."_acl_option_to_group option_to_group
						LEFT JOIN	wcf".$this->dbNo."_acl_option acl_option
						ON		(acl_option.optionID = option_to_group.optionID)
						WHERE		acl_option.objectTypeID = ?
					)
					+
					(
						SELECT		COUNT(*)
						FROM		wcf".$this->dbNo."_acl_option_to_user option_to_user
						LEFT JOIN	wcf".$this->dbNo."_acl_option acl_option
						ON		(acl_option.optionID = option_to_user.optionID)
						WHERE		acl_option.objectTypeID = ?
					)
				) AS count";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($objectTypeID, $objectTypeID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Returns ACLs.
	 */
	protected function getCategoryACLs($offset, $limit) {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.acl', 'de.codequake.cms.category.news');
		
		$sql = "(
				SELECT		acl_option.optionName, acl_option.optionID,
						option_to_group.objectID, option_to_group.optionValue, 0 AS userID, option_to_group.groupID
				FROM		wcf".$this->dbNo."_acl_option_to_group option_to_group
				LEFT JOIN	wcf".$this->dbNo."_acl_option acl_option
				ON		(acl_option.optionID = option_to_group.optionID)
				WHERE		acl_option.objectTypeID = ?
			)
			UNION
			(
				SELECT		acl_option.optionName, acl_option.optionID,
						option_to_user.objectID, option_to_user.optionValue, option_to_user.userID, 0 AS groupID
				FROM		wcf".$this->dbNo."_acl_option_to_user option_to_user
				LEFT JOIN	wcf".$this->dbNo."_acl_option acl_option
				ON		(acl_option.optionID = option_to_user.optionID)
				WHERE		acl_option.objectTypeID = ?
			)
			ORDER BY	optionID, objectID, groupID, userID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute(array($objectTypeID, $objectTypeID));
		
		$acls = array();
		while ($row = $statement->fetchArray()) {
			$data = array(
				'objectID' => $row['objectID'],
				'optionName' => $row['optionName'],
				'optionValue' => $row['optionValue']
			);
			
			if ($row['userID'])
				$data['userID'] = $row['userID'];
			if ($row['groupID'])
				$data['groupID'] = $row['groupID'];
			
			$acls[] = $data;
		}
		
		return $acls;
	}
	
	/**
	 * Exports ACLs.
	 */
	public function exportNewsCategoryACLs($offset, $limit) {
		$acls = $this->getCategoryACLs($offset, $limit);
		
		foreach ($acls as $data) {
			$optionName = $data['optionName'];
			unset($data['optionName']);
			
			ImportHandler::getInstance()->getImporter('de.codequake.cms.category.news.acl')->import(0,
				$data,
				array(
					'optionName' => $optionName
				)
			);
		}
	}
	
	/**
	 * Counts blog entries.
	 */
	public function countNewsEntries() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	cms" . $this->dbNo . "_news";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Exports blog entries.
	 */
	public function exportNewsEntries($offset, $limit) {
		$newsIDs = array();
		$sql = "SELECT	*
			FROM	cms" . $this->dbNo . "_news
			ORDER BY	newsID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$newsIDs[] = $row['newsID'];
		}
			
		$tags = $this->getTags($newsIDs);
		
		// get the news
		$sql = "SELECT		news.*, language.languageCode
			FROM		cms" . $this->dbNo . "_news news
			LEFT JOIN	wcf".$this->dbNo."_language language
			ON		(language.languageID = news.languageID)";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$additionalData = array();
			
			$sql = "SELECT	*
				FROM	cms" . $this->dbNo . "_news_to_category
				WHERE 	newsID = ?";
			$statement2 = $this->database->prepareStatement($sql);
			$statement2->execute(array($row['newsID']));
			while ($assignment = $statement2->fetchArray()) {
				// categories
				$additionalData['categories'][] = $assignment['categoryID'];
			}
			
			ImportHandler::getInstance()->getImporter('de.codequake.cms.news')->import($row['newsID'], array(
				'userID' => ($row['userID'] ?  : null),
				'username' => ($row['username'] ?  : ''),
				'subject' => $row['subject'],
				'message' => $row['message'],
				'time' => $row['time'],
				'comments' => $row['comments'],
				'enableSmilies' => $row['enableSmilies'],
				'enableHtml' => $row['enableHtml'],
				'enableBBCodes' => $row['enableBBCodes'],
				'isDisabled' => $row['isDisabled'],
				'isDeleted' => $row['isDeleted'],
				'ipAddress' => $row['ipAddress'],
				'cumulativeLikes' => $row['cumulativeLikes'],
			), $additionalData);
			
			if ($row['languageCode'])
				$additionalData['languageCode'] = $row['languageCode'];
			
			if (isset($tags[$row['newsID']]))
				$additionalData['tags'] = $tags[$row['newsID']];
			
		}
	}
	
	/**
	 * Counts news comments.
	 */
	public function countNewsComments() {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.comment.commentableContent', 'de.codequake.cms.news.comment');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf" . $this->dbNo . "_comment
			WHERE	objectTypeID = ?";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($objectTypeID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Exports news comments.
	 */
	public function exportNewsComments($offset, $limit) {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.comment.commentableContent', 'de.codequake.cms.news.comment');
		
		$sql = "SELECT	*
			FROM	wcf" . $this->dbNo . "_comment
			WHERE	objectTypeID = ?
			ORDER BY	commentID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute(array($objectTypeID));
		
		while ($row = $statement->fetchArray()) {
			$id = ImportHandler::getInstance()->getImporter('de.codequake.cms.news.comment')->import($row['commentID'], array(
				'objectID' => $row['objectID'],
				'userID' => $row['userID'],
				'username' => $row['username'],
				'message' => $row['message'],
				'time' => $row['time'],
				'objectTypeID' => $objectTypeID,
				'responses' => 0,
				'responseIDs' => serialize(array())
			));
		}
	}
	
	/**
	 * Counts news comment responses.
	 */
	public function countNewsCommentResponses() {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.comment.commentableContent', 'de.codequake.cms.news.comment');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf" . $this->dbNo . "_comment_response
			WHERE	commentID IN (
					SELECT	commentID
					FROM	wcf".$this->dbNo."_comment
					WHERE	objectTypeID = ?
				)";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($objectTypeID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Exports news comment responses.
	 */
	public function exportNewsCommentResponses($offset, $limit) {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.comment.commentableContent', 'de.codequake.cms.news.comment');
		
		$sql = "SELECT	*
			FROM	wcf" . $this->dbNo . "_comment_response
			WHERE	commentID IN (
					SELECT	commentID
					FROM	wcf".$this->dbNo."_comment
					WHERE	objectTypeID = ?
				)
			ORDER BY	responseID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute(array($objectTypeID));
		
		while ($row = $statement->fetchArray()) {
			ImportHandler::getInstance()->getImporter('de.codequake.cms.news.comment.response')->import($row['responseID'], array(
				'commentID' => $row['commentID'],
				'time' => $row['time'],
				'userID' => $row['userID'],
				'username' => $row['username'],
				'message' => $row['message'],
			));
		}
	}
	
	public function countNewsAttachments() {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.attachment.objectType', 'de.codequake.cms.news');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".$this->dbNo."_attachment
			WHERE	objectTypeID = ?
				AND objectID IS NOT NULL";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($objectTypeID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	public function exportNewsAttachments($offset, $limit) {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.attachment.objectType', 'de.codequake.cms.news');
		
		$sql = "SELECT	*
			FROM	wcf".$this->dbNo."_attachment
			WHERE	objectTypeID = ?
				AND objectID IS NOT NULL
			ORDER BY	attachmentID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute(array($objectTypeID));
		
		while ($row = $statement->fetchArray()) {
			$fileLocation = $this->fileSystemPath . 'attachments/' . substr($row['fileHash'], 0, 2) . '/' . $row['attachmentID'] . '-' . $row['fileHash'];
			
			ImportHandler::getInstance()->getImporter('de.codequake.cms.news.attachment')->import($row['attachmentID'], array(
				'objectID' => $row['objectID'],
				'userID' => ($row['userID'] ?: null),
				'filename' => $row['filename'],
				'filesize' => $row['filesize'],
				'fileType' => $row['fileType'],
				'fileHash' => $row['fileHash'],
				'isImage' => $row['isImage'],
				'width' => $row['width'],
				'height' => $row['height'],
				'downloads' => $row['downloads'],
				'lastDownloadTime' => $row['lastDownloadTime'],
				'uploadTime' => $row['uploadTime'],
				'showOrder' => $row['showOrder']
			), array('fileLocation' => $fileLocation));
		}
	}
	
	/**
	 * Counts likes.
	 */
	public function countNewsLikes() {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.like.likeableObject', 'de.codequake.cms.likeableNews');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".$this->dbNo."_like
			WHERE	objectTypeID = ?";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($objectTypeID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Exports likes.
	 */
	public function exportNewsLikes($offset, $limit) {
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.like.likeableObject', 'de.codequake.cms.likeableNews');
		
		$sql = "SELECT	*
			FROM	wcf".$this->dbNo."_like
			WHERE	objectTypeID = ?
			ORDER BY	likeID";
		$statement = $this->database->prepareStatement($sql, $limit, $offset);
		$statement->execute(array($objectTypeID));
		
		while ($row = $statement->fetchArray()) {
			ImportHandler::getInstance()->getImporter('de.codequake.cms.news.like')->import(0, array(
				'objectID' => $row['objectID'],
				'objectUserID' => $row['objectUserID'],
				'userID' => $row['userID'],
				'likeValue' => $row['likeValue'],
				'time' => $row['time']
			));
		}
	}
	
	/**
	 * Returns the id of the object type with the given name.
	 *
	 * @param	string		$definitionName
	 * @param	string		$objectTypeName
	 * @return	integer
	 */
	protected function getObjectTypeID($definitionName, $objectTypeName) {
		$sql = "SELECT	objectTypeID
			FROM	wcf".$this->dbNo."_object_type
			WHERE	objectType = ?
				AND definitionID = (
					SELECT definitionID FROM wcf".$this->dbNo."_object_type_definition WHERE definitionName = ?
				)";
		$statement = $this->database->prepareStatement($sql, 1);
		$statement->execute(array($objectTypeName, $definitionName));
		$row = $statement->fetchArray();
		
		if ($row !== false) {
			return $row['objectTypeID'];
		}
		
		return null;
	}
	
	/**
	 * Returns the values of the language item with the given name.
	 *
	 * @param	string		$languageItem
	 * @param	integer		$packageID
	 * @return	array
	 */
	private function getLanguageItemValues($languageItem) {
		$sql = "SELECT		language_item.languageItemValue, language_item.languageCustomItemValue,
					language_item.languageUseCustomValue, language.languageCode
			FROM		wcf".$this->dbNo."_language_item language_item
			LEFT JOIN	wcf".$this->dbNo."_language language
			ON		(language.languageID = language_item.languageID)
			WHERE		language_item.languageItem = ?";
		$statement = $this->database->prepareStatement($sql);
		$statement->execute(array($languageItem));
		
		$values = array();
		while ($row = $statement->fetchArray()) {
			$values[$row['languageCode']] = ($row['languageUseCustomValue'] ? $row['languageCustomItemValue'] : $row['languageItemValue']);
		}
		
		return $values;
	}
	
	/**
	 * Imports language variables.
	 *
	 * @param	string		$languageCategory
	 * @param	string		$languageItem
	 * @param	array		$languageItemValues
	 * @param	integer		$packageID
	 * @return	array
	 */
	private function importLanguageVariable($languageCategory, $languageItem, array $languageItemValues) {
		$packageID = PackageCache::getInstance()->getPackageID('de.codequake.cms');
		
		// get language category id
		$sql = "SELECT	languageCategoryID
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategory = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($languageCategory));
		$row = $statement->fetchArray();
		
		$languageCategoryID = $row['languageCategoryID'];
		
		$importableValues = array();
		foreach ($languageItemValues as $languageCode => $value) {
			$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
			if ($language === null)
				continue;
			
			$importableValues[$language->languageID] = $value;
		}
		
		$count = count($importableValues);
		
		if ($count > 1) {
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue,
							languageItemOriginIsSystem, languageCategoryID,
							packageID)
				VALUES		(?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($importableValues as $languageID => $value) {
				$statement->execute(array(
					$languageID,
					$languageItem,
					$value,
					0,
					$languageCategoryID,
					$packageID
				));
			}
			
			return $languageItem;
		} else if ($count == 1) {
			return reset($importableValues);
		}
		
		return false;
	}
	
	/**
	 * Updates the i18n data of the category with the given id.
	 *
	 * @param	integer		$categoryID
	 * @param	array		$category
	 */
	private function updateCategoryI18nData($categoryID, array $category) {
		// get title
		if (preg_match('~wcf.category.category.title.category\d+~', $category['title'])) {
			$titleValues = $this->getLanguageItemValues($category['title']);
			$title = $this->importLanguageVariable('wcf.category', 'wcf.category.category.title.category'.$categoryID, $titleValues);
			if ($title === false)
				$title = 'Imported Category '.$categoryID;
		}
		
		// get description
		if (preg_match('~wcf.category.category.title.category\d+.description~', $category['description'])) {
			$descriptionValues = $this->getLanguageItemValues($category['description']);
			$description = $this->importLanguageVariable('wcf.category', 'wcf.category.category.description.category'.$categoryID, $descriptionValues);
			if ($description === false)
				$description = '';
		}
		
		// update category
		$updateData = array();
		if (!empty($title))
			$updateData['title'] = $title;
		if (!empty($description))
			$updateData['description'] = $description;
		
		if (count($updateData)) {
			$importedCategory = new Category(null, array('categoryID' => $categoryID));
			$editor = new CategoryEditor($importedCategory);
			$editor->update($updateData);
		}
	}
	
	/**
	 * Returns a list of tags.
	 * 
	 * @param	array		$newsIDs
	 * @return	array
	 */
	private function getTags(array $newsIDs) {
		$tags = array();
		$objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.tagging.taggableObject', 'de.codequake.cms.news');
		
		// prepare conditions
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('tag_to_object.objectTypeID = ?', array($objectTypeID));
		$conditionBuilder->add('tag_to_object.objectID IN (?)', array($newsIDs));
		
		// read tags
		$sql = "SELECT		tag.name, tag_to_object.objectID
			FROM		wcf".$this->dbNo."_tag_to_object tag_to_object
			LEFT JOIN	wcf".$this->dbNo."_tag tag
			ON		(tag.tagID = tag_to_object.tagID)
			".$conditionBuilder;
		$statement = $this->database->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		while ($row = $statement->fetchArray()) {
			if (!isset($tags[$row['objectID']]))
				$tags[$row['objectID']] = array();
			
			$tags[$row['objectID']][] = $row['name'];
		}
		
		return $tags;
	}
}
