<?php
/**
 * Plugin Name: Unix – Pošiljka i račun
 * Description: Na ekranu porudžbine dodaje kutiju iz koje se kupcu šalje mejl sa brojem pošiljke i mejl sa fiskalnim računom u prilogu. Izrada: Prometix – Vuković Radovan.
 * Version: 1.0.0
 * Author: Prometix – Vuković Radovan
 * Author URI: https://prometix.net/
 * Requires Plugins: woocommerce
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 8.2
 * WC tested up to: 11.1
 * License: GPL-2.0-or-later
 * Text Domain: unix-posiljka-racun
 *
 * @package UnixPosiljkaRacun
 */

defined( 'ABSPATH' ) || exit;

define( 'UPR_VERSION', '1.0.0' );
define( 'UPR_FILE', __FILE__ );
define( 'UPR_DIR', plugin_dir_path( __FILE__ ) );

// Najveća dozvoljena veličina računa i dozvoljeni tipovi fajla.
define( 'UPR_MAX_FILE_SIZE', 5 * MB_IN_BYTES );

/**
 * Dozvoljeni tipovi fajla za račun (ekstenzije => MIME tip).
 *
 * @return array
 */
function upr_allowed_mimes() {
	return array(
		'pdf'          => 'application/pdf',
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
	);
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', UPR_FILE, true );
		}
	}
);

add_filter(
	'woocommerce_email_classes',
	function ( $emails ) {
		require_once UPR_DIR . 'includes/class-upr-email.php';
		require_once UPR_DIR . 'includes/class-upr-email-shipment.php';
		require_once UPR_DIR . 'includes/class-upr-email-invoice.php';

		$emails['UPR_Email_Shipment'] = new UPR_Email_Shipment();
		$emails['UPR_Email_Invoice']  = new UPR_Email_Invoice();
		return $emails;
	}
);

if ( is_admin() ) {
	require_once UPR_DIR . 'includes/admin.php';
}
