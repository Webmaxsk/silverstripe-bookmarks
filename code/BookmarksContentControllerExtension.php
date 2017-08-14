<?php

class BookmarksContentControllerExtension extends Extension {

	public function onAfterInit() {
		Requirements::css(BOOKMARKS_DIR . '/css/bookmarks.css');

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');

		Requirements::javascript(BOOKMARKS_DIR . '/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js');
		Requirements::javascript('vendor/dimsemenov/magnific-popup/dist/jquery.magnific-popup.min.js');

		Requirements::add_i18n_javascript(BOOKMARKS_DIR . '/javascript/lang');
		Requirements::javascript(BOOKMARKS_DIR . '/javascript/bookmarks.js');
		Requirements::javascript(BOOKMARKS_DIR . '/javascript/ajax_addBookmark.js');
		Requirements::javascript(BOOKMARKS_DIR . '/javascript/ajax_addBookmarkFolder.js');
		Requirements::javascript(BOOKMARKS_DIR . '/javascript/ajax_editBookmark.js');
		Requirements::javascript(BOOKMARKS_DIR . '/javascript/ajax_editBookmarkFolder.js');

		if (!$this->owner->request->isAjax())
			Requirements::customScript(<<<js
				sortable_and_droppable_bookmarks('{$this->saveAllBookmarksLink()}', '{$this->moveBookmarkToBookmarkFolderLink()}');
js
);
	}

	public function bookmarksLink() {
		return singleton('Bookmarks_Controller')->Link();
	}

	public function addBookmarkLink() {
		return Controller::curr()->Link('addBookmark');
	}

	public function addBookmarkFolderLink() {
		return Controller::curr()->Link('addBookmarkFolder');
	}

	public function saveAllBookmarksLink() {
		return singleton('Bookmarks_Controller')->Link('saveAllBookmarks');
	}

	public function moveBookmarkToBookmarkFolderLink() {
		return singleton('Bookmarks_Controller')->Link('moveBookmarkToBookmarkFolder');
	}

	public function isCurrentUrlInBookmarks($currentUrl = false) {
		$url = null;
		if ($currentUrl)
			$url = $currentUrl;

		if ($url == null)
			$url = urldecode($this->CurrentUrl());

		if ($url
		&& substr($url, 0, 1) !== '/'
		&& ($urlParts = parse_url($url)) && empty($urlParts['scheme']))
			$url = 'http://' . $url;

		return Bookmark::get()->filter(array('Url' => $url, 'OwnerID' => Member::currentUserID()))->filterByCallback(function($item, $list) {
			return $item->canView();
		})->exists();
	}

	public function CurrentTitle() {
		return $this->owner->request->getVar(__FUNCTION__) ?: $this->owner->Title;
	}

	public function CurrentUrl() {
		return $this->owner->request->getVar(__FUNCTION__) ?: urlencode($_SERVER['REQUEST_URI']);
	}
}