<ul id="bookmarks-sortable">
	<% with CurrentMember %>
		<% if getMyBookmarks($Top.CurrentBookmarkFolder.ID) %>
			<% loop getMyBookmarks($Top.CurrentBookmarkFolder.ID) %>
				$BookmarkHolder($Top.CurrentTitle,$Top.CurrentUrl)
			<% end_loop %>
		<% end_if %>
	<% end_with %>
</ul>