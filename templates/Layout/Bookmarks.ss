<% if isAjax %>
	<div class="white-popup">
		<h2>$Title</h2>
		<% if $Content %>
			<div>$Content</div>
		<% end_if %>
		$Form
		<br>
		<% include BookmarksMenu %>
	</div>
<% else %>
	<a href="$bookmarksLink?CurrentTitle=$Title&CurrentUrl=$CurrentUrl" class="showBookmarks" title="<%t Bookmark.PLURALNAME 'Bookmarks' %>"><% include BookmarkStar %></a>
<% end_if %>