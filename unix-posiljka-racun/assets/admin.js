( function () {
	'use strict';

	var box = document.getElementById( 'upr-box' );
	if ( ! box || ! window.uprAdmin ) {
		return;
	}

	var cfg = window.uprAdmin;
	var tracking = document.getElementById( 'upr-tracking' );
	var invoice = document.getElementById( 'upr-invoice' );

	function setStatus( el, text, isError ) {
		el.textContent = text;
		el.style.color = isError ? '#b32d2e' : '';
	}

	function send( action, button, statusEl, fields ) {
		var label = button.textContent;
		var data = new FormData();

		data.append( 'action', action );
		data.append( 'nonce', cfg.nonce );
		data.append( 'order_id', box.getAttribute( 'data-order-id' ) );
		Object.keys( fields ).forEach( function ( key ) {
			data.append( key, fields[ key ] );
		} );

		button.disabled = true;
		button.textContent = cfg.i18n.sending;

		return fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				var message = json && json.data && json.data.message ? json.data.message : cfg.i18n.failed;
				setStatus( statusEl, message, ! ( json && json.success ) );
				return !! ( json && json.success );
			} )
			.catch( function () {
				setStatus( statusEl, cfg.i18n.failed, true );
				return false;
			} )
			.then( function ( ok ) {
				button.disabled = false;
				button.textContent = label;
				return ok;
			} );
	}

	var shipmentButton = document.getElementById( 'upr-send-shipment' );
	var shipmentStatus = document.getElementById( 'upr-shipment-status' );

	shipmentButton.addEventListener( 'click', function () {
		var value = tracking.value.trim();
		if ( ! value ) {
			setStatus( shipmentStatus, cfg.i18n.noTracking, true );
			tracking.focus();
			return;
		}
		send( 'upr_send_shipment', shipmentButton, shipmentStatus, { tracking: value } );
	} );

	// Enter u polju šalje mejl o pošiljci umesto da sačuva celu porudžbinu.
	tracking.addEventListener( 'keydown', function ( event ) {
		if ( 'Enter' === event.key ) {
			event.preventDefault();
			shipmentButton.click();
		}
	} );

	var invoiceButton = document.getElementById( 'upr-send-invoice' );
	var invoiceStatus = document.getElementById( 'upr-invoice-status' );

	invoiceButton.addEventListener( 'click', function () {
		var file = invoice.files && invoice.files[ 0 ];
		if ( ! file ) {
			setStatus( invoiceStatus, cfg.i18n.noFile, true );
			return;
		}
		if ( file.size > cfg.maxFileSize ) {
			setStatus( invoiceStatus, cfg.i18n.fileTooLarge, true );
			return;
		}
		send( 'upr_send_invoice', invoiceButton, invoiceStatus, { invoice: file } ).then( function ( ok ) {
			if ( ok ) {
				invoice.value = '';
			}
		} );
	} );
} )();
