<% if CurrentMember %>
	<ol class="bookmark-list">
		<% if Bookmarks %>
			<% loop Bookmarks %>
				<li class="$ClassName"><a href="$Url">$Title</a></li>
			<% end_loop %>
		<% end_if %>
	</ol>
<% end_if %>