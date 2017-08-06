<% if isAjax %>
	<div class="white-popup">
		<h2>$Title</h2>
		<% if $Content %>
			<div>$Content</div>
		<% end_if %>
		$Form
		<% include BookmarksMenu %>
	</div>
<% else %>
	<h1>$Title</h1>
	<% if $Content %>
		<div>$Content</div>
	<% end_if %>
	$Form
	<% include BookmarksMenu %>
<% end_if %>