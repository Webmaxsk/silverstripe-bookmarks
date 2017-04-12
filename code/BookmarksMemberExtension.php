<?php

class BookmarksMemberExtension extends DataExtension {

	private static $has_many = array(
		'Bookmarks' => 'Bookmark'
	);

	public function updateFieldLabels(&$labels) {
		$field_labels = Config::inst()->get($this->class, 'field_labels');

		$field_labels['Bookmarks'] = _t('Bookmark.PLURALNAME', 'Bookmarks');

		if($field_labels)
			$labels = array_merge($labels, $field_labels);
	}
}