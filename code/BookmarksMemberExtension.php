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
			$bookmarksGridFieldConfig = $bookmarksGridField->getConfig();

			if (class_exists('GridFieldSortableRows'))
				$bookmarksGridFieldConfig->addComponent(new GridFieldSortableRows('Sort'));
			elseif (class_exists('GridFieldOrderableRows'))
				$bookmarksGridFieldConfig->addComponent(new GridFieldOrderableRows('Sort'));

			$bookmarksGridFieldConfig
				->removeComponentsByType($bookmarksGridFieldConfig->getComponentByType('GridFieldAddExistingAutocompleter'))
				->removeComponentsByType($bookmarksGridFieldConfig->getComponentByType('GridFieldDeleteAction'))
				->addComponent(new GridFieldDeleteAction());

			$bookmarksDisplayFields = $bookmarksGridFieldConfig
				->getComponentByType('GridFieldDataColumns')->getDisplayFields($bookmarksGridField);

			unset($bookmarksDisplayFields['Owner.Name']);

			$bookmarksGridFieldConfig->getComponentByType('GridFieldDataColumns')->setDisplayFields($bookmarksDisplayFields);
		}
	}

	public function getMyBookmarks() {
		$filter = array(
			'OwnerID' => Member::currentUserID()
		);

		return Bookmark::get()->filter($filter)->filterByCallback(function($item, $list) {
			return $item->canView();
		});
	}
}