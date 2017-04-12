<% if CurrentMember %>
	<div id="member_bookmarks">
		<ul id="bookmarks-sortable">
			<% if MyBookmarks %>
				<% loop MyBookmarks %>
					$BookmarkHolder($Top.CurrentTitle,$Top.CurrentUrl)
				<% end_loop %>
			<% end_if %>
		</ul>
		<a id="addBookmarkLink" href="$addBookmarkLink?CurrentTitle=$CurrentTitle&CurrentUrl=$CurrentUrl" class="addBookmark ajax-popup-link"><%t Bookmarks_Controller.ADDBOOKMARK.TITLE 'Add bookmark' %></a>
	</div>
<% end_if %>
<script type="text/javascript">
	$(document).ready(function() {
		$('#bookmarks-sortable').sortable({
			cursor: 'move',
			placeholder: 'bookmark-placeholder',
			forcePlaceholderSize: true,
			axis: 'y',
			stop: function( event, ui ) {
				$.ajax({
					data: $(this).sortable('serialize'),
					type: 'POST',
					url: '$saveAllBookmarksLink',
					success: function(data) {
						$('.BookmarksWidget .bookmark-list').replaceWith(data);
					}
				});
			}
		});
	});
</script>