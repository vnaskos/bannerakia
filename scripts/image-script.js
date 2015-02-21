jQuery(document).ready(function($) {

	$('.custom_media_upload').click(function(e) {
		e.preventDefault();
		var custom_uploader = wp.media({
			title: 'Custom Title',
			button: {
				text: 'Set',
			},
			multiple: false	// True for selection of multiple files
		})
		.on('select', function() {
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			$('.custom_media_image').attr('src', attachment.url);
			$('.custom_media_url').val(attachment.url);
			$('.custom_media_id').val(attachment.id);
		})
		.open();
	});

});
