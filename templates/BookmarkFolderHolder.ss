<% if canView %>
	<li id="bookmark-$ID" class="bookmarkFolder" data-id="$ID">
		<a href="$Url?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="bookmark-title ajax-folder-link" title="$Title">$Title</a>
		<% if canEdit || canDelete %>
			<a href="$editLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="editBookmarkFolder ajax-popup-link"><%t Bookmarks_Controller.EDITBOOKMARKFOLDER.ACTION 'Edit' %></a>
		<% end_if %>
	</li>
<% end_if %>