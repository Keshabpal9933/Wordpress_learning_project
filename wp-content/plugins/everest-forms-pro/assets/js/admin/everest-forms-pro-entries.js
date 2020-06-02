( function ( $, data ) {

	// Toggle entry stars.
	$( document ).on( 'click', '#everest-forms-entries-list .wp-list-table .indicator-star', function( event ) {
		event.preventDefault();

		var $this  = $( this ),
			task   = '',
			total  = Number( $( '#everest-forms-entries-list .starred-count' ).text() );

		if ( $this.hasClass( 'star' ) ) {
			total++;
			task = 'star';
			$this.attr( 'title', data.entry_unstar );
		} else {
			total--;
			task = 'unstar';
			$this.attr( 'title', data.entry_star );
		}

		$this.toggleClass( 'star unstar' );

		$( '#everest-forms-entries-list .starred-count' ).text( total );

		$.post( data.ajax_url, {
			task: task,
			nonce: data.nonce,
			entry_id: $this.data( 'id' ),
			action: 'everest_forms_entry_star'
		} );
	} );

	// Toggle entry read state.
	$( document ).on( 'click', '#everest-forms-entries-list .wp-list-table .indicator-read', function( event ) {
		event.preventDefault();

		var $this  = $( this ),
			task   = '',
			total  = Number( $( '#everest-forms-entries-list .unread-count' ).text() );

		if ( $this.hasClass( 'read' ) ) {
			total--;
			task = 'read';
			$this.attr( 'title', data.entry_unread );
		} else {
			total++;
			task = 'unread';
			$this.attr( 'title', data.entry_read );
		}

		$this.toggleClass( 'read unread' );
		$this.closest('tr').toggleClass( 'read unread' );

		$( '#everest-forms-entries-list .unread-count' ).text( total );

		// @todo handle error, revert and display toast notification.
		$.post( data.ajax_url, {
			task: task,
			nonce: data.nonce,
			entry_id: $this.data( 'id' ),
			action: 'everest_forms_entry_read'
		} );
	} );

})( jQuery, everest_forms_entries );
