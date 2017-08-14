<% if CurrentMember %>
	<div id="member_bookmarks">
		$BookmarksBreadcrumbs
		<% include BookmarkList %>
		<% include BookmarkActions %>
	</div>
	<% if isAjax %>
		<script type="text/javascript">
			sortable_and_droppable_bookmarks('$saveAllBookmarksLink', '$moveBookmarkToBookmarkFolderLink');
		</script>
	<% end_if %>
<% end_if %>