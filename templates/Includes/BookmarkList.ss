<ul id="bookmarks-sortable">
	<% with CurrentMember %>
		<% if MyBookmarks %>
			<% loop MyBookmarks %>
				$BookmarkHolder($Top.CurrentTitle,$Top.CurrentUrl)
			<% end_loop %>
		<% end_if %>
	<% end_with %>
</ul>