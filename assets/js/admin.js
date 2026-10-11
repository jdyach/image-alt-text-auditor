/* global iataData, jQuery */
( function ( $ ) {
    'use strict';

    /**
     * Handle inline Save button clicks.
     */
    $( document ).on( 'click', '.iata-save-btn', function () {
        var $btn        = $( this );
        var attachmentId = $btn.data( 'id' );
        var $row        = $( '#iata-row-' + attachmentId );
        var $input      = $row.find( '.iata-alt-input' );
        var $status     = $row.find( '.iata-status' );
        var altText     = $input.val().trim();

        // Require non-empty alt text.
        if ( '' === altText ) {
            $status
                .removeClass( 'saving saved' )
                .addClass( 'error' )
                .text( 'Alt text cannot be empty.' );
            $input.focus();
            return;
        }

        // Disable UI while saving.
        $btn.prop( 'disabled', true ).text( iataData.i18n.saving );
        $input.prop( 'disabled', true );
        $status.removeClass( 'saved error' ).addClass( 'saving' ).text( iataData.i18n.saving );

        $.ajax( {
            url:    iataData.ajaxUrl,
            method: 'POST',
            data:   {
                action:        'iata_save_alt',
                nonce:         iataData.nonce,
                attachment_id: attachmentId,
                alt_text:      altText,
            },
            success: function ( response ) {
                if ( response.success ) {
                    $status.removeClass( 'saving error' ).addClass( 'saved' ).text( iataData.i18n.saved );

                    // Fade the row out — it no longer belongs in the audit list.
                    setTimeout( function () {
                        $row.addClass( 'iata-row-saved' );
                        setTimeout( function () {
                            $row.remove();

                            // If the table is now empty, reload to show the all-clear message.
                            if ( 0 === $( '.iata-table tbody tr' ).length ) {
                                window.location.reload();
                            }
                        }, 1700 );
                    }, 400 );
                } else {
                    handleError( $btn, $input, $status, response.data && response.data.message );
                }
            },
            error: function () {
                handleError( $btn, $input, $status, null );
            },
        } );
    } );

    /**
     * Allow saving with Enter key from the input field.
     */
    $( document ).on( 'keydown', '.iata-alt-input', function ( e ) {
        if ( 13 === e.which ) {
            e.preventDefault();
            $( this ).closest( 'tr' ).find( '.iata-save-btn' ).trigger( 'click' );
        }
    } );

    /**
     * Re-enable UI and show an error message.
     *
     * @param {jQuery} $btn
     * @param {jQuery} $input
     * @param {jQuery} $status
     * @param {string|null} message
     */
    function handleError( $btn, $input, $status, message ) {
        $btn.prop( 'disabled', false ).text( 'Save' );
        $input.prop( 'disabled', false ).focus();
        $status
            .removeClass( 'saving saved' )
            .addClass( 'error' )
            .text( message || iataData.i18n.error );
    }

} )( jQuery );
