<?php

class BookmarkFolder extends Bookmark {

	private static $has_many = array(
		'Children' => 'Bookmark'
	);

	public function fieldLabels($includerelations = true) {
		$cacheKey = $this->class . '_' . $includerelations;

		if (!isset(self::$_cache_field_labels[$cacheKey])) {
			$labels = parent::fieldLabels($includerelations);

			if ($includerelations)
				$labels['Children'] = _t('Bookmark.PLURALNAME', 'Bookmarks');

			self::$_cache_field_labels[$cacheKey] = $labels;
		}

		return self::$_cache_field_labels[$cacheKey];
	}

	public function getUrl() {
		return singleton('Bookmarks_Controller')->Link($this->exists() ? "folder/{$this->ID}" : null);
	}

	public function Children($filter = "", $sort = "", $join = "", $limit = "") {
		if ($join)
			throw new \InvalidArgumentException(
				'The $join argument has been removed. Use leftJoin($table, $joinClause) instead.'
			);

		$result = $this->AllChildren();

		if (!$this->exists()) return $result;

		return $result
			->filter('OwnerID', $this->OwnerID)
			->where($filter)
			->sort($sort)
			->limit($limit);
	}

	public function AllChildren() {
		return Bookmark::get()->filter('ParentID', $this->ID);
	}

	public function onBeforeDelete() {
		parent::onBeforeDelete();

		$chldren = $this->Children();
		foreach ($chldren as $child)
			$child->delete();
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Url');

		if ($bookmarksGridField = $fields->dataFieldByName('Children')) {
			$bookmarkFolder = $this;
			$bookmarksGridFieldConfig = $bookmarksGridField->getConfig();

			if (class_exists('GridFieldSortableRows'))
				$bookmarksGridFieldConfig->addComponent(new GridFieldSortableRows('Sort'));
			elseif (class_exists('GridFieldOrderableRows'))
				$bookmarksGridFieldConfig->addComponent(new GridFieldOrderableRows('Sort'));

			$bookmarksGridFieldConfig
				->removeComponentsByType('GridFieldAddExistingAutocompleter')
				->removeComponentsByType('GridFieldDeleteAction')
				->addComponent(new GridFieldDeleteAction())
				->getComponentByType('GridFieldDetailForm')
					->setItemEditFormCallback(function($form, $component) use($bookmarkFolder) {
						if (($record = $form->getRecord()) && !$record->exists()) {
							$fields = $form->Fields();

							if ($ownerField = $fields->dataFieldByName('OwnerID')) {
								$fields->replaceField('OwnerID',
									$ownerField->castedCopy('ReadonlyField')
										->setValue($ownerField->getSourceAsArray()[$bookmarkFolder->OwnerID])
										->setName('OwnerIDReadonly')
								);
								$fields->push(new HiddenField('OwnerID', null, $bookmarkFolder->OwnerID));
							}

							if ($parentField = $fields->dataFieldByName('ParentID')) {
								$fields->replaceField('ParentID',
									$parentField->castedCopy('ReadonlyField')
										->setValue($parentField->getSourceAsArray()[$bookmarkFolder->ID])
										->setName('ParentIDReadonly')
								);
								$fields->push(new HiddenField('ParentID', null, $bookmarkFolder->ID));
							}
						}
					});

			if (class_exists('GridFieldAddNewMultiClass'))
				$bookmarksGridFieldConfig
					->removeComponentsByType('GridFieldAddNewButton')
					->addComponent(new GridFieldAddNewMultiClass())
					->getComponentByType('GridFieldDetailForm')
						->setValidator(singleton('Bookmark')->getCMSValidator());

			$bookmarksDisplayFields = $bookmarksGridFieldConfig
				->getComponentByType('GridFieldDataColumns')->getDisplayFields($bookmarksGridField);

			unset($bookmarksDisplayFields['Owner.Name']);

			$bookmarksGridFieldConfig->getComponentByType('GridFieldDataColumns')->setDisplayFields($bookmarksDisplayFields);
		}

		return $fields;
	}

	public function getFrontEndFields($params = null) {
		$fields = new FieldList(array(
			$this->dbObject('Title')->scaffoldFormField(_t('BookmarkFolder.TITLE', 'Title'))
		));

		return $fields;
	}

	public function getFrontEndValidator() {
		$required = array(
			'Title'
		);

		return Bookmarks_Validator::create($required);
	}

	public function editLink() {
		return Controller::curr()->Link("editBookmarkFolder/{$this->ID}");
	}

	public function BookmarkHolder($currentTitle = null, $currentUrl = null) {
		return $currentTitle != null || $currentUrl != null ? $this->customise(array('CurrentTitle' => $currentTitle, 'CurrentUrl' => $currentUrl))->renderWith('BookmarkFolderHolder') : $this->renderWith('BookmarkFolderHolder');
	}
}