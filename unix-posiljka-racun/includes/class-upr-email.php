<?php
/**
 * Zajednička osnova za mejlove koji se šalju ručno sa ekrana porudžbine.
 *
 * @package UnixPosiljkaRacun
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ručno slani mejl kupcu vezan za porudžbinu.
 */
abstract class UPR_Email extends WC_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customer_email = true;
		$this->manual         = true;
		$this->email_group    = 'order-updates';
		$this->template_base  = UPR_DIR . 'templates/';
		$this->placeholders   = array_merge(
			array(
				'{order_date}'   => '',
				'{order_number}' => '',
			),
			$this->placeholders
		);

		parent::__construct();
	}

	/**
	 * Podrazumevani uvodni tekst mejla.
	 *
	 * @return string
	 */
	abstract public function get_default_intro();

	/**
	 * Uvodni tekst mejla, sa zamenjenim oznakama.
	 *
	 * @return string
	 */
	public function get_intro() {
		return $this->format_string( $this->get_option( 'intro', $this->get_default_intro() ) );
	}

	/**
	 * Podešavanja mejla: ovi mejlovi se šalju samo ručno, pa nema uključivanja i isključivanja.
	 */
	public function init_form_fields() {
		parent::init_form_fields();

		unset( $this->form_fields['enabled'] );

		$intro = array(
			'intro' => array(
				'title'       => __( 'Uvodni tekst', 'unix-posiljka-racun' ),
				'description' => sprintf(
					/* translators: %s: list of placeholders */
					__( 'Tekst ispod pozdrava. Dostupne oznake: %s', 'unix-posiljka-racun' ),
					'<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>'
				),
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => $this->get_default_intro(),
				'type'        => 'textarea',
				'default'     => $this->get_default_intro(),
				'desc_tip'    => true,
			),
		);

		// Uvodni tekst ide ispred "Additional content", gde ga korisnik i očekuje.
		$position = array_search( 'additional_content', array_keys( $this->form_fields ), true );
		if ( false === $position ) {
			$this->form_fields += $intro;
		} else {
			$this->form_fields = array_slice( $this->form_fields, 0, $position, true ) + $intro + array_slice( $this->form_fields, $position, null, true );
		}
	}

	/**
	 * Postavlja porudžbinu i primaoca.
	 *
	 * @param int $order_id ID porudžbine.
	 * @return bool False ako porudžbina ne postoji.
	 */
	protected function set_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$this->object                         = $order;
		$this->recipient                      = $order->get_billing_email();
		$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
		$this->placeholders['{order_number}'] = $order->get_order_number();

		return true;
	}

	/**
	 * Šalje mejl bez provere "enabled", jer se pokreće samo ručno.
	 *
	 * @return bool
	 */
	protected function send_manual() {
		$recipient = $this->get_recipient();
		if ( ! $recipient ) {
			return false;
		}

		return (bool) $this->send( $recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Promenljive koje se prosleđuju šablonu.
	 *
	 * @param bool $plain_text Da li je mejl običan tekst.
	 * @return array
	 */
	protected function get_template_args( $plain_text ) {
		return array(
			'order'              => $this->object,
			'email_heading'      => $this->get_heading(),
			'intro'              => $this->get_intro(),
			'additional_content' => $this->get_additional_content(),
			'sent_to_admin'      => false,
			'plain_text'         => $plain_text,
			'email'              => $this,
		);
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, $this->get_template_args( false ), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, $this->get_template_args( true ), '', $this->template_base );
	}

	/**
	 * Default content to show below main email content.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Za sva pitanja stojimo Vam na raspolaganju.', 'unix-posiljka-racun' );
	}
}
