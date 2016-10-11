function init_bookmarks() {
	$('.showBookmarks').magnificPopup({
		type: 'ajax',
		closeBtnInside: true,
		fixedContentPos: true,
		callbacks: {
			open: function() {
				return $('html').addClass('popup-opened');
			},
			close: function() {
				return $('html').removeClass('popup-opened');
			}
		},
		tClose: 'Zatvoriť (Esc)',
		tLoading: 'Prebieha načítanie...'
	});

	$(document).on('click', '#member_bookmarks .ajax-popup-link', function() {
		$.magnificPopup.open({
			items: {
				src: $(this).attr('href')
			}
		});

		return false;
	});
}