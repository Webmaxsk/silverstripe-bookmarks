<?php

class BookmarksContentControllerExtension extends Extension {

	public function onAfterInit() {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-ui/jquery-ui.js");

		Requirements::javascript(BOOKMARKS_DIR."/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js");
		Requirements::javascript(BOOKMARKS_DIR."/magnific-popup/dist/jquery.magnific-popup.min.js");

		Requirements::add_i18n_javascript(BOOKMARKS_DIR."/javascript/lang");
		Requirements::javascript(BOOKMARKS_DIR."/javascript/bookmarks.js");
		Requirements::javascript(BOOKMARKS_DIR."/javascript/ajax_addBookmark.js");
		Requirements::javascript(BOOKMARKS_DIR."/javascript/ajax_editBookmark.js");
	}

	public function bookmarksLink() {
		return singleton('Bookmarks_Controller')->Link();
	}

	public function addBookmarkLink() {
		return singleton('Bookmarks_Controller')->Link('addBookmark');
	}

	public function saveAllBookmarksLink() {
		return singleton('Bookmarks_Controller')->Link('saveAll');
	}

	public function isCurrentUrlInBookmarks($currentUrl = false) {
		$url = null;
		if ($currentUrl)
			$url = $currentUrl;

		if ($url==null)
			$url = $_SERVER['REQUEST_URI'];

		if(
			$url
			&& !parse_url($url, PHP_URL_SCHEME)
			&& !preg_match('#^//#', $url)
			&& !preg_match('#^/#', $url)
		) {
			$url = 'http://' . $url;
		}

		return Bookmark::get()->filter(array('Url' => $url, 'OwnerID' => Member::currentUserID()))->filterByCallback(function($item, $list) {
			return $item->canView();
		})->exists();
	}

	public function CurrentUrl() {
		return $_SERVER['REQUEST_URI'];
	}
}