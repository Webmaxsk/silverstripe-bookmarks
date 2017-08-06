<% if canView %>
	<li id="bookmark-$ID" class="bookmark" data-id="$ID">
		<a href="$Url" class="bookmark-title" title="$Title ($Url)">$Title</a>
		<% if canEdit || canDelete %>
			<a href="$editLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="editBookmark ajax-popup-link"><%t Bookmarks_Controller.EDITBOOKMARK.ACTION 'Edit' %></a>
		<% end_if %>
	</li>
<% end_if %>
