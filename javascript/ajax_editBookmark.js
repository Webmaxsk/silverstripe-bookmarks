$(document).on('click', "#Form_EditBookmarkForm_action_doEditBookmark, #Form_EditBookmarkForm_action_doDeleteBookmark", function(event) {
	event.preventDefault();

	var form = $("#Form_EditBookmarkForm");
	var submitButton = $(this);

	var actionName = $(this).attr("name");
	var action = actionName+"="+$(this).attr("value");

	// silverstripe-member-widgets module
	var memberWidgetsIsotope = $('#memberwidgets-sortable.memberwidgets-isotope');

	$.ajax(form.attr('action'), {
		type: "POST",
		data: form.serialize()+"&"+action,
		beforeSend: function() {
			submitButton.attr('value','Prebieha odosielanie...');
			submitButton.attr("disabled", true);
		},
		success: function(data) {
			try {
				var json = jQuery.parseJSON(data);

				if(typeof json == 'object') {
					var bookmark = $('#bookmarks-sortable #bookmark-'+json.BookmarkID);

					if (actionName=='action_doDeleteBookmark') {
						bookmark.remove();

						if (memberWidgetsIsotope.length)
							memberWidgetsIsotope.isotope('reloadItems').isotope({
								sortBy: 'original-order'
							});
					}
					else
						bookmark.replaceWith(json.Bookmark);

					$('.bookmark-star').replaceWith(json.BookmarkStar);
					$('.BookmarksWidget .bookmark-list').replaceWith(json.BookmarkWidget);

					$.magnificPopup.open({
						items: {
							src: json.BookmarksLink
						}
					});
				}
			}
			catch(err) {
				form.replaceWith(data);
				$('#Form_EditBookmarkForm fieldset .field :input:visible').focus();
				$('#Form_EditBookmarkForm :input[required]:visible').first().focus();
			}
		}
	});
});