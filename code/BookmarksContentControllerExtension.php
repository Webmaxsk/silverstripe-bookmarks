<?php

class BookmarksContentControllerExtension extends Extension {

	public function onAfterInit() {
		Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript(THIRDPARTY_DIR."/jquery-ui/jquery-ui.js");

		Requirements::javascript(BOOKMARKS_DIR."/magnific-popup/dist/jquery.magnific-popup.min.js");
		Requirements::javascript(BOOKMARKS_DIR."/javascript/bookmarks.js");
		Requirements::javascript(BOOKMARKS_DIR."/javascript/ajax_addBookmark.js");
		Requirements::javascript(BOOKMARKS_DIR."/javascript/ajax_editBookmark.js");

		Requirements::customScript("
			init_bookmarks();
		");
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

	public function getMyBookmarks() {
		return Bookmark::get()->filter('MemberID',Member::currentUserID());
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

		return DataObject::get_one('Bookmark', array('Url' => $url, 'MemberID' => Member::currentUserID())) ? true : false;
	}

	public function CurrentUrl() {
		return $_SERVER['REQUEST_URI'];
	}
}