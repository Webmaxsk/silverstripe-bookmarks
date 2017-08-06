<% if canView %>
	<li id="bookmark-$ID" class="bookmark">
		<a href="$Url" class="bookmark-title" title="$Title ($Url)">$Title</a>
		<% if canEdit || canDelete %>
			<a href="$editBookmarkLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="editBookmark ajax-popup-link"><%t Bookmarks_Controller.EDITBOOKMARK.ACTION 'Edit' %></a>
		<% end_if %>
	</li>
<% end_if %>