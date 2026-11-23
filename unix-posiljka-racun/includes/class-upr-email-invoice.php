<?php
/**
 * Mejl kupcu sa fiskalnim računom u prilogu.
 *
 * @package UnixPosiljkaRacun
 */

defined( 'ABSPATH' ) || exit;

/**
 * Mejl sa fiskalnim računom u prilogu.
 */
class UPR_Email_Invoice extends UPR_Email {

	/**
	 * Putanja do fajla računa koji se šalje u prilogu.
	 *
	 * @var string
	 */
	protected $invoice_path = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'upr_invoice';
		$this->title          = __( 'Fiskalni račun', 'unix-posiljka-racun' );
		$this->description    = __( 'Šalje se kupcu ručno sa ekrana porudžbine, sa fiskalnim računom u prilogu.', 'unix-posiljka-racun' );
		$this->template_html  = 'emails/upr-invoice.php';
		$this->template_plain = 'emails/plain/upr-invoice.php';

		parent::__construct();
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Fiskalni račun za porudžbinu #{order_number}', 'unix-posiljka-racun' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Fiskalni račun', 'unix-posiljka-racun' );
	}

	/**
	 * Podrazumevani uvodni tekst mejla.
	 *
	 * @return string
	 */
	public function get_default_intro() {
		return __( 'Hvala Vam na kupovini. U prilogu ovog mejla nalazi se fiskalni račun za Vašu porudžbinu #{order_number}.', 'unix-posiljka-racun' );
	}

	/**
	 * Get email attachments.
	 *
	 * @return array
	 */
	public function get_attachments() {
		$attachments = parent::get_attachments();

		if ( $this->invoice_path ) {
			$attachments[] = $this->invoice_path;
		}

		return $attachments;
	}

	/**
	 * Izvršava funkciju bez uputstava za plaćanje koja načini plaćanja dodaju iznad tabele porudžbine
	 * (npr. "Porudžbinu plaćate prilikom preuzimanja paketa"), jer im nije mesto u mejlu sa računom.
	 *
	 * @param callable $render Funkcija koja vraća sadržaj mejla.
	 * @return string
	 */
	protected function without_payment_instructions( $render ) {
		$removed = array();

		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			$callback = array( $gateway, 'email_instructions' );
			$priority = has_action( 'woocommerce_email_before_order_table', $callback );

			if ( false !== $priority ) {
				remove_action( 'woocommerce_email_before_order_table', $callback, $priority );
				$removed[] = array( $callback, $priority );
			}
		}

		try {
			return $render();
		} finally {
			foreach ( $removed as list( $callback, $priority ) ) {
				add_action( 'woocommerce_email_before_order_table', $callback, $priority, 3 );
			}
		}
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return $this->without_payment_instructions(
			function () {
				return parent::get_content_html();
			}
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return $this->without_payment_instructions(
			function () {
				return parent::get_content_plain();
			}
		);
	}

	/**
	 * Šalje mejl sa računom u prilogu.
	 *
	 * @param int    $order_id     ID porudžbine.
	 * @param string $invoice_path Putanja do fajla računa.
	 * @return bool Da li je mejl poslat.
	 */
	public function trigger( $order_id, $invoice_path = '' ) {
		$this->setup_locale();

		$sent = false;
		if ( $invoice_path && is_readable( $invoice_path ) && $this->set_order( $order_id ) ) {
			$this->invoice_path = $invoice_path;
			$sent               = $this->send_manual();
			$this->invoice_path = '';
		}

		$this->restore_locale();

		return $sent;
	}
}
