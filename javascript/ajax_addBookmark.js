$(document).on('click', "#Form_AddBookmarkForm_action_doAddBookmark", function(event) {
	event.preventDefault();

	var form = $("#Form_AddBookmarkForm");
	var submitButton = $(this);

	var bookmarks = $('#bookmarks-sortable');

	// silverstripe-member-widgets module
	var memberWidgetsIsotope = $('#memberwidgets-sortable.memberwidgets-isotope');

	$.ajax(form.attr('action'), {
		type: "POST",
		data: form.serialize(),
		beforeSend: function() {
			submitButton.attr('value',ss.i18n._t('Bookmark.PROCESSING', 'Processing...'));
			submitButton.attr("disabled", true);
		},
		success: function(data) {
			try {
				var json = jQuery.parseJSON(data);

				if(typeof json == 'object') {
					bookmarks.append(json.Bookmark);

					$('.bookmark-star').replaceWith(json.BookmarkStar);
					$('.BookmarksWidget .bookmark-list').replaceWith(json.BookmarkWidget);

					if (memberWidgetsIsotope.length)
						memberWidgetsIsotope.isotope('reloadItems').isotope({
							sortBy: 'original-order'
						});

					$.magnificPopup.open({
						items: {
							src: json.BookmarksLink,
							type: 'ajax'
						}
					});
				}
			}
			catch(err) {
				form.replaceWith(data);
				$('#Form_AddBookmarkForm fieldset .field :input:visible').focus();
				$('#Form_AddBookmarkForm :input[required]:visible').first().focus();
			}
		}
	});
});