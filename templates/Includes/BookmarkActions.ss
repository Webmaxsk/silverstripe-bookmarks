<div id="bookmarks-actions">
	<% if canAddBookmark %>
		<a id="addBookmarkLink" href="$addBookmarkLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="addBookmark ajax-popup-link"><%t Bookmarks_Controller.ADDBOOKMARK.TITLE 'Add bookmark' %></a>
	<% end_if %>
	<% if canAddBookmarkFolder %>
		<a id="addBookmarkFolderLink" href="$addBookmarkFolderLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="addBookmarkFolder ajax-popup-link"><%t Bookmarks_Controller.ADDBOOKMARKFOLDER.TITLE 'Add folder' %></a>
	<% end_if %>
</div>