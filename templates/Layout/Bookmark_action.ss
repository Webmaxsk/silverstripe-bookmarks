<% if isAjax %>
	<div class="white-popup">
		<h2>$Title</h2>
		<% if $Content %>
			<div class="typography">$Content</div>
		<% end_if %>
		$Form
	</div>
<% else %>
	<h1>$Title</h1>
	<% if $Content %>
		<div class="typography">$Content</div>
	<% end_if %>
	$Form
<% end_if %>