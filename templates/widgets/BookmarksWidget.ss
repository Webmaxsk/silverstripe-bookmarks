<% if CurrentMember %>
	<ol class="bookmark-list">
		<% if Bookmarks %>
			<% loop Bookmarks %>
				<li class="$ClassName">
					<% if ClassName='BookmarkFolder' %>
						<a href="$Url?CurrentTitle=$Top.controller.curr.CurrentTitle&CurrentUrl=$Top.controller.curr.CurrentUrl" class="ajax-folder-link">$Title</a>
					<% else %>
						<a href="$Url">$Title</a>
					<% end_if %>
				</li>
			<% end_loop %>
		<% end_if %>
	</ol>
<% end_if %>