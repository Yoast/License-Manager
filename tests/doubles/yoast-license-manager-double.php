<?php

require_once dirname( __FILE__ ) . '/../../class-license-manager.php';

/**
 * Class Yoast_License_Manager_Double
 */
class Yoast_License_Manager_Double extends Yoast_License_Manager_v2 {

	public $product;

	protected $__notices = array();
	protected $license_api_response = array();

	public function __construct() {
		$this->product = new Yoast_Product_Double();

		parent::__construct( $this->product );
	}

	/**
	 * Needs to be defined.
	 */
	public function specific_hooks() {
	}

	/**
	 * Needs to be defined.
	 */
	public function setup_auto_updater() {
	}

	/**
	 * Expose protected function.
	 */
	public function get_curl_version() {
		return parent::get_curl_version();
	}

	/**
	 * Expose protected function.
	 */
	public function get_custom_message( $result ) {
		return parent::get_custom_message( $result );
	}

	/**
	 * Expose protected function.
	 */
	public function get_user_locale() {
		return parent::get_user_locale();
	}

	/**
	 * Expose protected function.
	 */
	public function get_successful_activation_message( $result ) {
		return parent::get_successful_activation_message( $result );
	}

	/**
	 * Expose protected function.
	 */
	public function get_unsuccessful_activation_message( $result ) {
		return parent::get_unsuccessful_activation_message( $result );
	}

	/**
	 * Override functionality to catch notices
	 * @todo mock this instead
	 */
	protected function set_notice( $message, $success = true ) {
		$this->__notices[] = array(
			'message' => $message,
			'success' => $success,
		);
	}

	/**
	 * Get caught notices
	 */
	public function __get_notices() {
		return $this->__notices;
	}

	/**
	 * Override functionality to return custom response
	 * @todo mock this instead
	 */
	public function call_license_api( $action ) {
		return $this->license_api_response[ $action ];
	}

	/**
	 * Set a certain response for a license_api call
	 *
	 * @param string $action   Action to respond to.
	 * @param mixed  $response Response to give back.
	 */
	public function set_license_api_response( $action, $response ) {
		$this->license_api_response[ $action ] = $response;
	}
}
