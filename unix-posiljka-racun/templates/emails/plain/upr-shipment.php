<?php
/**
 * Mejl kupcu sa brojem pošiljke (običan tekst).
 *
 * @package UnixPosiljkaRacun
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( ! empty( $order->get_billing_first_name() ) ) {
	/* translators: %s: Customer first name */
	echo sprintf( esc_html__( 'Poštovani/a %s,', 'unix-posiljka-racun' ), esc_html( $order->get_billing_first_name() ) ) . "\n\n";
} else {
	echo esc_html__( 'Poštovani,', 'unix-posiljka-racun' ) . "\n\n";
}

echo esc_html( wp_strip_all_tags( wptexturize( $intro ) ) ) . "\n\n";

echo "----------------------------------------\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
