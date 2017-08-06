<% if CurrentMember %>
	<div id="member_bookmarks">
		<% include BookmarkList %>
		<% include BookmarkActions %>
	</div>
	<% if isAjax %>
		<script type="text/javascript">
			sortable_bookmarks('$saveAllBookmarksLink');
		</script>
	<% end_if %>
<% end_if %>