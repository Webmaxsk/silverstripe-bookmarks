<div id="bookmarks-actions">
	<% if canAddBookmark %>
		<a id="addBookmarkLink" href="$addBookmarkLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="addBookmark ajax-popup-link"><%t Bookmarks_Controller.ADDBOOKMARK.TITLE 'Add bookmark' %></a>
	<% end_if %>
</div>