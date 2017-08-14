<% if BookmarkFolders %>
	<ul id="bookmarks-breadcrumbs">
		<% loop BookmarkFolders %>
			<li<% if not last %> class="bookmarkFolder" data-id="$ID"<% end_if %>>
				<% if not last %>
					<a href="$Url?CurrentTitle=$Top.CurrentTitle&CurrentUrl=$Top.CurrentUrl" class="ajax-folder-link">$Title</a>
				<% else %>
					$Title
				<% end_if %>
			</li>
			<% if not last %>
				<li>&raquo;</li>
			<% end_if %>
		<% end_loop %>
	</ul>
<% end_if %>