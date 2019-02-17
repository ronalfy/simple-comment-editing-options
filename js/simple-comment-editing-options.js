jQuery( document ).ready( function( $ ) {
	if( 'compact' === sce_options.timer_appearance ) {
		sce_hooks.addFilter( 'sce.comment.timer.text', 'simple-comment-editing-options', function( timer_text, days_text, hours_text, minutes_text, seconds_text, days, hours, minutes, seconds ) {
			timer_text = '';
			if( days > 0 ) {
				if( days < 10 ) {
					timer_text += '' + '0' + days;
				} else {
					timer_text += days;
				}
				timer_text += ':';
			}
			if( hours > 0 ) {
				if( hours < 10 ) {
					timer_text += '' + '0' + hours;
				} else {
					timer_text += hours;
				}
				timer_text += ':';
			} else if( hours === 0 && days > 0 ) {
				timer_text += '00';
			}
			if( minutes > 0 ) {
				if( minutes < 10 ) {
					timer_text += '' + '0' + minutes;
				} else {
					timer_text += minutes;
				}
				timer_text += ':';
			} else if( minutes === 0 && hours > 0 ) {
				timer_text += '00';
			}
			if (seconds > 0) {
				if( seconds < 10 ) {
					timer_text += '' + '0' + seconds;
				} else {
					timer_text += seconds;
				}
			} else if( seconds === 0 && minutes > 0 ) {
				timer_text += '00';
			}
			return timer_text;
		} );
	}
	var simplecommenteditingoptions = $.simplecommenteditingoptions = $.fn.simplecommenteditingoptions = function() {
		var $this = this;
		return this.each( function() {
			var ajax_url = $( this ).find( 'a:first' ).attr( 'href' );
			var ajax_params = wpAjax.unserialize( ajax_url );
			var element = this;
			jQuery(element).on( 'sce.timer.loaded', function(e) {
				if ( sce_options.show_stop_timer ) {
					$( element ).find( '.sce-timer' ).after( '<div class="sce-timer-cancel-wrapper"><button class="sce-timer-cancel">' + sce_options.stop_timer_svg + sce_options.stop_timer_text + '</button></div>');
					$( element ).siblings( '.sce-textarea' ).find( ' .sce-timer' ).after( '<div class="sce-timer-cancel-wrapper"><button class="sce-timer-cancel">' + sce_options.stop_timer_svg + sce_options.stop_timer_text + '</button></div>' );
				}
			} );
			jQuery( element ).on( 'click', '.sce-timer-cancel', function( e ) {
				e.preventDefault();
				cancel_timer( element );
			} );
			jQuery( element ).siblings( '.sce-textarea' ).find( '.sce-timer' ).on( 'click', '.sce-timer-cancel', function( e ) {
				e.preventDefault();
				cancel_timer( element );
			} );
			function cancel_timer( element ) {
				$( element ).siblings( '.sce-textarea' ).off();
				$( element ).off();

				//Remove elements
				$( element ).parent().remove();

				$.post( ajax_url, { action: 'sce_stop_timer', comment_id: ajax_params.cid, post_id: ajax_params.pid, nonce: ajax_params._wpnonce }, function( response ) {
					// do nothing for now
				}, 'json' );
			}
		} );

	};
	$( '.sce-edit-button' ).simplecommenteditingoptions();
} );