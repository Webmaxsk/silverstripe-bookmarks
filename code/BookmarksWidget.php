<?php

if (!class_exists('Widget')) {
    return;
}

class BookmarksWidget extends Widget {

    private static $title = 'Bookmarks';
    private static $cmsTitle = 'Bookmarks';
    private static $description = 'Displays a bookmarks.';

    public function getBookmarks() {
        return singleton('ContentController')->getMyBookmarks();
    }
}