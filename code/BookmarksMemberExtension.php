<?php

class BookmarksMemberExtension extends DataExtension {

	private static $has_many = array(
		'Bookmarks' => 'Bookmark'
	);

	public function updateFieldLabels(&$labels) {
		$field_labels = Config::inst()->get($this->class, 'field_labels');

		$field_labels['Bookmarks'] = _t('Bookmark.PLURALNAME', 'Bookmarks');

		if ($field_labels)
			$labels = array_merge($labels, $field_labels);
	}

	public function updateCMSFields(FieldList $fields) {
		if ($bookmarksGridField = $fields->dataFieldByName('Bookmarks')) {
			if (($myBookmarks = $this->owner->getMyBookmarks()) && $myBookmarks->count())
				$bookmarksGridField->setList($myBookmarks);

			$bookmarksGridFieldConfig = $bookmarksGridField->getConfig();

			if (class_exists('GridFieldSortableRows'))
				$bookmarksGridFieldConfig->addComponent(new GridFieldSortableRows('Sort'));
			elseif (class_exists('GridFieldOrderableRows'))
				$bookmarksGridFieldConfig->addComponent(new GridFieldOrderableRows('Sort'));

			$bookmarksGridFieldConfig
				->removeComponentsByType('GridFieldAddExistingAutocompleter')
				->removeComponentsByType('GridFieldDeleteAction')
				->addComponents(new GridFieldDeleteAction())
				->getComponentByType('GridFieldDetailForm')
					->setItemEditFormCallback(function($form, $component) {
						if (($record = $form->getRecord()) && !$record->exists()) {
							$fields = $form->Fields();

							if ($parentField = $fields->dataFieldByName('ParentID'))
								$fields->makeFieldReadonly('ParentID');
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
	}

	public function getMyBookmarks($bookmarkFolderID = null) {
		$filter = array(
			'OwnerID' => $this->owner->ID
		);

		if ($bookmarkFolderID)
			$filter['ParentID'] = $bookmarkFolderID;
		else
			$filter['ParentID'] = 0;

		return Bookmark::get()->filter($filter)->filterByCallback(function($item, $list) {
			return $item->canView();
		});
	}
}