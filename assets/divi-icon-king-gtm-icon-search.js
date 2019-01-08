(function( $ ) {

	var filter__icon_list_container = ( $('div#et-fb-app').length == 1 ) ? '.et-fb-option-container' : '.et-pb-option-container';
	var filter__icon_list 					= ( $('div#et-fb-app').length == 1 ) ? 'ul.et-fb-font-icon-list li' : 'ul.et_font_icon li';
	var icon_list;

	var delay = ( function() {
  		var timer = 0;
  		return function( callback, ms ){
    		clearTimeout ( timer );
    		timer = setTimeout( callback, ms );
  		};
	})();

	function reset_search( el ) {
		el.closest( '.dikg_icon_filter' ).find( '.dikg_icon_filter__input' ).val('');
	}

	function clear_active_control() {
		$( '.dikg_icon_filter__control_option--active' ).removeClass( 'dikg_icon_filter__control_option--active' );
	}

	$( document ).on( 'keyup', '.dikg_icon_filter__input', function() {

		var $that = $(this);
		var value = $that.val();

		delay( function() {

			icon_list = $that.closest( filter__icon_list_container ).find( filter__icon_list );

			clear_active_control();

			if( !value ){
				icon_list.show();
				return;
			}

			var val = $.trim( value ).replace( / +/g, ' ' ).toLowerCase();

			icon_list.hide().filter('li[title*="'+val+'"]').show();

		}, 500);
	});

	// Filter by Family
	$( document ).on( 'click', '.dikg_icon_filter__control_family', function() {

		clear_active_control();

		var $this = $( this ).addClass( 'dikg_icon_filter__control_option--active' );

		reset_search( $this );

		var value = $this.data('value');
		icon_list = $this.closest( filter__icon_list_container ).find( filter__icon_list );

		icon_list.show().filter( function() {
        var text = $(this).data('family').replace( /\s+/g, ' ' ).toLowerCase();
        return !~text.indexOf( value );
    	}).hide();
	});

	// Filter by Icon Style
	$( document ).on( 'click', '.dikg_icon_filter__control_style', function() {

		clear_active_control();

		var $this = $( this ).addClass( 'dikg_icon_filter__control_option--active' );

		reset_search( $this );

		var value = $this.data('value');
		icon_list = $this.closest( filter__icon_list_container ).find( filter__icon_list );

		icon_list.show().filter( function() {
        	var text = $(this).data('style').replace( /\s+/g, ' ' ).toLowerCase();
        	return !~text.indexOf( value );
    	}).hide();	});

	$(document).on( 'click', '.dikg_icon_filter--closed', function() {

		clear_active_control();

		var $parent_container = $( this );

		$parent_container
		.removeClass( 'dikg_icon_filter--closed' )
		.addClass( 'dikg_icon_filter--open' )
		.prepend( '<input type="text" class="dikg_icon_filter__input" value="" placeholder="Whatcha looking for?" />' );

		$parent_container.find( '.dikg_icon_filter__btn' )
		.removeClass( 'dikg_icon_filter--visible' )
		.addClass( 'dikg_icon_filter--hidden' );

		$parent_container.find( '.dikg_icon_filter__controls' )
		.removeClass( 'dikg_icon_filter--hidden' )
		.addClass( 'dikg_icon_filter--visible' );

		$parent_container.find( '.dikg_icon_filter__input' ).focus();
	});


	// Reset filter input
	$(document).on( 'click', '.dikg_icon_filter__all', function() {

		clear_active_control();

		var $this = $( this );
		reset_search( $this );

		$this.closest( filter__icon_list_container ).find( filter__icon_list ).show();
	});

	// Close filtering
	$(document).on( 'click', '.dikg_icon_filter__close', function() {

		clear_active_control();

		var $parent_container = $( this ).closest( '.dikg_icon_filter' );

		$parent_container.find( '.dikg_icon_filter__btn' )
		.removeClass( 'dikg_icon_filter--hidden' )
		.addClass( 'dikg_icon_filter--visible' );

		$parent_container.find( '.dikg_icon_filter__controls' )
		.removeClass( 'dikg_icon_filter--visible' )
		.addClass( 'dikg_icon_filter--hidden' );

		$parent_container.find( '.dikg_icon_filter__input' ).val('');
		$parent_container.find( '.dikg_icon_filter__input' ).remove();

		$parent_container
		.removeClass( 'dikg_icon_filter--open' )
		.addClass( 'dikg_icon_filter--closed' );

		$(this).closest( filter__icon_list_container ).find( filter__icon_list ).show();
	});

})(jQuery);