<?php

class BookmarksMemberExtension extends DataExtension {

	private static $has_many = array(
		'Bookmarks' => 'Bookmark'
	);
}