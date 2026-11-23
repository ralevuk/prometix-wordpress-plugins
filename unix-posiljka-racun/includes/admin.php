<?php
/**
 * Kutija "Pošiljka i račun" na ekranu porudžbine i obrada njenih dugmadi.
 *
 * @package UnixPosiljkaRacun
 */

defined( 'ABSPATH' ) || exit;

/**
 * ID ekrana za izmenu porudžbine (radi i sa HPOS i sa starim načinom čuvanja porudžbina).
 *
 * @return string
 */
function upr_order_screen_id() {
	return function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
}

add_filter(
	'plugin_action_links_' . plugin_basename( UPR_FILE ),
	function ( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=email' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Podešavanja mejlova', 'unix-posiljka-racun' ) . '</a>' );
		return $links;
	}
);

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'upr-posiljka-racun', __( 'Pošiljka i račun', 'unix-posiljka-racun' ), 'upr_render_meta_box', upr_order_screen_id(), 'side', 'high' );
	}
);

add_action(
	'admin_enqueue_scripts',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || upr_order_screen_id() !== $screen->id ) {
			return;
		}

		wp_enqueue_script( 'upr-admin', plugins_url( 'assets/admin.js', UPR_FILE ), array(), UPR_VERSION, true );
		wp_localize_script(
			'upr-admin',
			'uprAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'upr-send' ),
				'maxFileSize' => UPR_MAX_FILE_SIZE,
				'i18n'        => array(
					'sending'      => __( 'Slanje…', 'unix-posiljka-racun' ),
					'noTracking'   => __( 'Upišite broj pošiljke.', 'unix-posiljka-racun' ),
					'noFile'       => __( 'Izaberite fajl računa.', 'unix-posiljka-racun' ),
					'fileTooLarge' => __( 'Fajl je veći od 5 MB.', 'unix-posiljka-racun' ),
					'failed'       => __( 'Došlo je do greške. Pokušajte ponovo.', 'unix-posiljka-racun' ),
				),
			)
		);
	}
);

/**
 * Tekst o poslednjem slanju mejla.
 *
 * @param WC_Order $order    Porudžbina.
 * @param string   $meta_key Meta ključ sa vremenom slanja.
 * @param string   $never    Tekst kada mejl još nije poslat.
 * @return string
 */
function upr_sent_status( $order, $meta_key, $never ) {
	$timestamp = (int) $order->get_meta( $meta_key );
	if ( ! $timestamp ) {
		return $never;
	}

	/* translators: %s: date and time */
	return sprintf( __( 'Poslednji put poslato: %s', 'unix-posiljka-racun' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) );
}

/**
 * Ispisuje kutiju.
 *
 * @param WP_Post|WC_Order $post_or_order Porudžbina (WC_Order kod HPOS, inače WP_Post).
 */
function upr_render_meta_box( $post_or_order ) {
	$order = $post_or_order instanceof WP_Post ? wc_get_order( $post_or_order->ID ) : $post_or_order;
	if ( ! $order instanceof WC_Order ) {
		return;
	}

	$email = $order->get_billing_email();
	?>
	<div id="upr-box" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
		<?php if ( ! $email ) : ?>
			<p><?php esc_html_e( 'Porudžbina nema mejl adresu kupca, pa mejlovi ne mogu da se pošalju.', 'unix-posiljka-racun' ); ?></p>
		<?php else : ?>
			<p class="description">
				<?php
				/* translators: %s: customer email address */
				printf( esc_html__( 'Mejlovi se šalju na: %s', 'unix-posiljka-racun' ), '<strong>' . esc_html( $email ) . '</strong>' );
				?>
			</p>
		<?php endif; ?>

		<p>
			<label for="upr-tracking"><strong><?php esc_html_e( 'Broj pošiljke', 'unix-posiljka-racun' ); ?></strong></label>
			<input type="text" id="upr-tracking" class="widefat" maxlength="100" autocomplete="off" value="<?php echo esc_attr( $order->get_meta( '_upr_tracking_number' ) ); ?>" />
		</p>
		<p>
			<button type="button" class="button button-primary" id="upr-send-shipment" <?php disabled( ! $email ); ?>><?php esc_html_e( 'Sačuvaj i pošalji kupcu', 'unix-posiljka-racun' ); ?></button>
		</p>
		<p class="description" id="upr-shipment-status" aria-live="polite"><?php echo esc_html( upr_sent_status( $order, '_upr_shipment_sent', __( 'Mejl o pošiljci još nije poslat.', 'unix-posiljka-racun' ) ) ); ?></p>

		<hr />

		<p>
			<label for="upr-invoice"><strong><?php esc_html_e( 'Fiskalni račun', 'unix-posiljka-racun' ); ?></strong> <?php esc_html_e( '(PDF, JPG ili PNG, do 5 MB)', 'unix-posiljka-racun' ); ?></label>
			<input type="file" id="upr-invoice" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" style="max-width:100%;margin-top:6px;" />
		</p>
		<p>
			<button type="button" class="button button-primary" id="upr-send-invoice" <?php disabled( ! $email ); ?>><?php esc_html_e( 'Pošalji račun kupcu', 'unix-posiljka-racun' ); ?></button>
		</p>
		<p class="description" id="upr-invoice-status" aria-live="polite"><?php echo esc_html( upr_sent_status( $order, '_upr_invoice_sent', __( 'Račun još nije poslat.', 'unix-posiljka-racun' ) ) ); ?></p>
	</div>
	<?php
}

/**
 * Provera zahteva i učitavanje porudžbine za AJAX akcije. Prekida zahtev ako nešto nije u redu.
 *
 * @return WC_Order
 */
function upr_ajax_get_order() {
	check_ajax_referer( 'upr-send', 'nonce' );

	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	$order    = $order_id ? wc_get_order( $order_id ) : false;

	if ( ! $order instanceof WC_Order || ! current_user_can( 'edit_shop_order', $order_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Nemate dozvolu za ovu porudžbinu.', 'unix-posiljka-racun' ) ), 403 );
	}

	if ( ! $order->get_billing_email() ) {
		wp_send_json_error( array( 'message' => __( 'Porudžbina nema mejl adresu kupca.', 'unix-posiljka-racun' ) ), 400 );
	}

	return $order;
}

/**
 * Šalje mejl i vraća razlog ako slanje nije uspelo.
 *
 * @param string   $email_class Ključ mejla u WC mailer-u.
 * @param WC_Order $order       Porudžbina.
 * @param string   $argument    Broj pošiljke ili putanja do računa.
 * @return true|string True ako je poslato, inače poruka o grešci.
 */
function upr_send_email( $email_class, $order, $argument ) {
	$emails = WC()->mailer()->get_emails();
	if ( empty( $emails[ $email_class ] ) ) {
		return __( 'Mejl nije registrovan u WooCommerce-u.', 'unix-posiljka-racun' );
	}

	$mail_error = '';
	$on_failure = function ( $error ) use ( &$mail_error ) {
		$mail_error = $error->get_error_message();
	};

	add_action( 'wp_mail_failed', $on_failure );
	$sent = $emails[ $email_class ]->trigger( $order->get_id(), $argument );
	remove_action( 'wp_mail_failed', $on_failure );

	if ( $sent ) {
		return true;
	}

	$message = __( 'Mejl nije poslat.', 'unix-posiljka-racun' );

	return $mail_error ? $message . ' ' . $mail_error : $message;
}

add_action(
	'wp_ajax_upr_send_shipment',
	function () {
		$order    = upr_ajax_get_order();
		$tracking = isset( $_POST['tracking'] ) ? sanitize_text_field( wp_unslash( $_POST['tracking'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- provereno u upr_ajax_get_order().
		$tracking = mb_substr( $tracking, 0, 100 );

		if ( '' === $tracking ) {
			wp_send_json_error( array( 'message' => __( 'Upišite broj pošiljke.', 'unix-posiljka-racun' ) ), 400 );
		}

		// Broj se čuva i kada slanje mejla ne uspe, da ne mora ponovo da se kuca.
		$order->update_meta_data( '_upr_tracking_number', $tracking );
		$order->save();

		$result = upr_send_email( 'UPR_Email_Shipment', $order, $tracking );
		if ( true !== $result ) {
			wp_send_json_error( array( 'message' => __( 'Broj pošiljke je sačuvan.', 'unix-posiljka-racun' ) . ' ' . $result ), 500 );
		}

		$order->update_meta_data( '_upr_shipment_sent', time() );
		$order->save();
		$order->add_order_note(
			/* translators: 1: tracking number, 2: customer email */
			sprintf( __( 'Broj pošiljke %1$s poslat kupcu na %2$s.', 'unix-posiljka-racun' ), $tracking, $order->get_billing_email() ),
			0,
			true
		);

		wp_send_json_success( array( 'message' => upr_sent_status( $order, '_upr_shipment_sent', '' ) ) );
	}
);

add_action(
	'wp_ajax_upr_send_invoice',
	function () {
		$order = upr_ajax_get_order();

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput -- nonce je proveren u upr_ajax_get_order(), a fajl se proverava ispod.
		$file = isset( $_FILES['invoice'] ) && is_array( $_FILES['invoice'] ) ? $_FILES['invoice'] : null;
		// phpcs:enable

		if ( ! $file || UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
			wp_send_json_error( array( 'message' => __( 'Izaberite fajl računa.', 'unix-posiljka-racun' ) ), 400 );
		}

		if ( in_array( (int) $file['error'], array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) || (int) $file['size'] > UPR_MAX_FILE_SIZE ) {
			wp_send_json_error( array( 'message' => __( 'Fajl je veći od 5 MB.', 'unix-posiljka-racun' ) ), 400 );
		}

		if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Otpremanje fajla nije uspelo. Pokušajte ponovo.', 'unix-posiljka-racun' ) ), 400 );
		}

		// Tip se proverava po stvarnom sadržaju fajla, ne samo po ekstenziji.
		$original_name = sanitize_file_name( wp_unslash( $file['name'] ) );
		$filetype      = wp_check_filetype_and_ext( $file['tmp_name'], $original_name, upr_allowed_mimes() );
		if ( empty( $filetype['ext'] ) || empty( $filetype['type'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Dozvoljeni su samo PDF, JPG i PNG fajlovi.', 'unix-posiljka-racun' ) ), 400 );
		}

		// Fajl se privremeno drži van sajta, pod imenom koje kupac vidi u prilogu, i briše se odmah posle slanja.
		$temp_dir  = trailingslashit( get_temp_dir() ) . 'upr-' . wp_generate_password( 24, false );
		$temp_path = $temp_dir . '/fiskalni-racun-' . sanitize_file_name( $order->get_order_number() ) . '.' . $filetype['ext'];

		if ( ! wp_mkdir_p( $temp_dir ) || ! move_uploaded_file( $file['tmp_name'], $temp_path ) ) {
			if ( is_dir( $temp_dir ) ) {
				rmdir( $temp_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			}
			wp_send_json_error( array( 'message' => __( 'Fajl nije mogao da se pripremi za slanje.', 'unix-posiljka-racun' ) ), 500 );
		}

		try {
			$result = upr_send_email( 'UPR_Email_Invoice', $order, $temp_path );
		} finally {
			wp_delete_file( $temp_path );
			rmdir( $temp_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
		}

		if ( true !== $result ) {
			wp_send_json_error( array( 'message' => $result ), 500 );
		}

		$order->update_meta_data( '_upr_invoice_sent', time() );
		$order->save();
		$order->add_order_note(
			/* translators: 1: file name, 2: customer email */
			sprintf( __( 'Fiskalni račun (%1$s) poslat kupcu na %2$s.', 'unix-posiljka-racun' ), $original_name, $order->get_billing_email() ),
			0,
			true
		);

		wp_send_json_success( array( 'message' => upr_sent_status( $order, '_upr_invoice_sent', '' ) ) );
	}
);
