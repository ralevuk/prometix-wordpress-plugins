<?php
/**
 * Mejl kupcu sa brojem pošiljke.
 *
 * @package UnixPosiljkaRacun
 */

defined( 'ABSPATH' ) || exit;

/**
 * Mejl "porudžbina je poslata" sa brojem pošiljke.
 */
class UPR_Email_Shipment extends UPR_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'upr_shipment';
		$this->title          = __( 'Broj pošiljke', 'unix-posiljka-racun' );
		$this->description    = __( 'Šalje se kupcu ručno sa ekrana porudžbine, kada se upiše broj pošiljke.', 'unix-posiljka-racun' );
		$this->template_html  = 'emails/upr-shipment.php';
		$this->template_plain = 'emails/plain/upr-shipment.php';
		$this->placeholders   = array(
			'{tracking_number}' => '',
		);

		parent::__construct();
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Vaša porudžbina #{order_number} je poslata', 'unix-posiljka-racun' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Vaša porudžbina je poslata', 'unix-posiljka-racun' );
	}

	/**
	 * Podrazumevani uvodni tekst mejla.
	 *
	 * @return string
	 */
	public function get_default_intro() {
		return __( 'Vaša porudžbina #{order_number} je predata kurirskoj službi. Broj pošiljke je: {tracking_number}', 'unix-posiljka-racun' );
	}

	/**
	 * Šalje mejl sa brojem pošiljke.
	 *
	 * @param int    $order_id        ID porudžbine.
	 * @param string $tracking_number Broj pošiljke.
	 * @return bool Da li je mejl poslat.
	 */
	public function trigger( $order_id, $tracking_number = '' ) {
		$this->setup_locale();

		$sent = false;
		if ( $this->set_order( $order_id ) ) {
			$this->placeholders['{tracking_number}'] = $tracking_number;
			$sent                                    = $this->send_manual();
		}

		$this->restore_locale();

		return $sent;
	}
}
