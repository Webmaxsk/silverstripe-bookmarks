<?php

if (!class_exists('Widget')) {
	return;
}

class BookmarksWidget extends Widget {

	private static $title = 'Bookmarks';
	private static $cmsTitle = 'Bookmarks';
	private static $description = 'Displays the bookmarks of the currently logged-in user.';

	public function getBookmarks() {
		return ($currentMember = Member::currentUser()) ? $currentMember->getMyBookmarks() : null;
	}

	public function WidgetHolder() {
		if (Member::currentUser())
			return parent::WidgetHolder();
	}
}

class BookmarksWidget_Controller extends WidgetController {

	public function WidgetHolder() {
		return $this->getWidget()->WidgetHolder();
	}
}