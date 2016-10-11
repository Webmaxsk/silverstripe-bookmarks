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
	<a href="$bookmarksLink?CurrentTitle=$Title&CurrentUrl=$CurrentUrl" class="showBookmarks u-push-lft-half" title="Záložky"><% include BookmarkStar %></a>
<% end_if %>