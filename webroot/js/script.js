'use strict';
(function() {
	var counter = 0;
	var ajax_image = function() {
		var on_over = function(event) {
			event.preventDefault();
		};
		$('.ajax-image').bind('dragover', on_over);

		var on_drop = function(event) {
			var files = event.originalEvent.dataTransfer.files;
			if (files.length > 0) {
				event.preventDefault();

				var file = files[0];

				var formData = new FormData();
                var div = $(this);
				formData.append(div.attr('data-name'), file);
				$.ajax({
					type: 'post',
					url: div.attr('data-upload'),
					processData: false,
					contentType: false,
					data: formData,
					dataType: 'json',
					success: function (data) {
						var img = $(this.getElementsByTagName('img')[0]);
                        img.attr('src', data + '?' + counter++);
					}.bind(this),
					error: function (data) {
						console.error(data);
					}
				});
			}
		};
		$('.ajax-image').bind('drop', on_drop);

		/// ^^^^ Deprecated ^^^^ ////

		$('.ai-drop').bind({
			'dragover': function(event) { event.preventDefault(); },
			'drop': function (event) {
				var files = event.originalEvent.dataTransfer.files;
				if (files.length > 0) {
					event.preventDefault();

					var file = files[0];

					var formData = new FormData();
					var drop = $(this);
					formData.append(drop.data('name'), file);
					$.ajax({
						type: 'post',
						url: drop.data('url'),
						processData: false,
						contentType: false,
						data: formData,
						dataType: 'json',
						success: function (data) {
							var drop = $(this);
							var img = drop.find('.ai-image');
							img.attr('src', drop.data('src') + '?' + counter++);
						}.bind(this),
						error: function (data) {
							console.error(data);
						}
					});
				}
			}
		});
	};

	$(ajax_image);
})();
