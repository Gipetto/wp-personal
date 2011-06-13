jQuery(function($){
	var CF = CF || {};
	
	CF.postFormats = function($) {
		return {
			switchTab: function(clicked) {
				var $this = $(clicked),
					$tab = $this.closest('li');

				if (!$this.hasClass('current')) {
					$this.addClass('current');
					$tab.siblings().find('a').removeClass('current');
					this.switchWPFormat($this.attr('href'));
				}
			},
			
			switchWPFormat: function(format_hash) {
				$(format_hash).trigger('click');
				switch (format_hash) {
					case '#post-format-0':
						CF.postFormats.standard();
						break;
					case '#post-format-status':
					case '#post-format-link':
					case '#post-format-image':
					case '#post-format-gallery':
					case '#post-format-video':
					case '#post-format-quote':
						eval('CF.postFormats.' + format_hash.replace('#post-format-', '') + '();');
				}
			},

			standard: function() {
				$('#cfpf-format-link-url, #cfpf-format-quote-fields').hide();
				$('#titlewrap').show();
				$('#postimagediv-placeholder').replaceWith($('#postimagediv'));
			},
			
			status: function() {
				$('#titlewrap, #cfpf-format-link-url, #cfpf-format-quote-fields').hide();
				$('#postimagediv-placeholder').replaceWith($('#postimagediv'));
			},

			link: function() {
				$('#cfpf-format-quote-fields').hide();
				$('#titlewrap, #cfpf-format-link-url').show();
				$('#postimagediv-placeholder').replaceWith($('#postimagediv'));
			},
			
			image: function() {
				$('#cfpf-format-link-url').hide();
				$('#titlewrap').show();
				$('#postimagediv').after('<div id="postimagediv-placeholder"></div>').insertAfter('#titlediv');
			},

			gallery: function() {
				$('#postimagediv-placeholder').replaceWith($('#postimagediv'));
			},

			video: function() {
				$('#cfpf-format-link-url, #cfpf-format-quote-fields').hide();
				$('#titlewrap, #cfpf-format-video-fields').show();
				$('#postimagediv-placeholder').replaceWith($('#postimagediv'));
			},

			quote: function() {
				$('#titlewrap, #cfpf-format-link-url').hide();
				$('#cfpf-format-quote-fields').show();
				$('#postimagediv-placeholder').replaceWith($('#postimagediv'));
			}
		};
	}(jQuery);
	
	// move tabs in to place
	$('#cf-post-format-tabs').insertBefore($('form#post'));
	$('#cfpf-format-link-url').insertAfter($('#titlediv'));
	$('#cfpf-format-video-fields').insertAfter($('#titlediv'));
	$('#cfpf-format-quote-fields').insertBefore($('#normal-sortables'));
	
	// tab switch
	$('#cf-post-format-tabs a').live('click', function(e) {
		CF.postFormats.switchTab(this);
		e.stopPropagation();
		e.preventDefault();
	}).filter('.current').each(function() {
		CF.postFormats.switchWPFormat($(this).attr('href'));
//		eval('CF.postFormats.' + $(this).attr('href').replace('#post-format-', '') + '();');
	});
});