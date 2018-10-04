<?php

class Test_Yoast_License_Manager extends Yst_License_Manager_UnitTestCase {

	/** @var Yoast_License_Manager_Double */
	private $class;

	/**
	 * Include the necessary files for these tests to be able to run.
	 */
	public static function setUpBeforeClass() {
		require_once dirname( __FILE__ ) . '/doubles/yoast-product-double.php';
		require_once dirname( __FILE__ ) . '/doubles/yoast-license-manager-double.php';
	}

	/**
	 * Set up before each test
	 */
	public function setUp() {
		// Create a new double for every run.
		$this->class = new Yoast_License_Manager_Double();
	}

	/**
	 * Make sure the API url is correct in the product
	 *
	 * @covers Yoast_Product_v2::get_api_url()
	 */
	public function test_get_api_url() {
		$this->assertEquals( $this->class->product->get_api_url(), get_site_url() );
	}

	/**
	 * Make sure the API url is correct in the product
	 *
	 * @covers Yoast_License_Manager_v2::get_curl_version()
	 */
	public function test_get_curl_version_WITH_curl_installed_on_test_server() {
		$curl_result = $this->class->get_curl_version();

		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();

			$this->assertEquals( $curl_result, $curl_version['version'] );
		}
		else {
			$this->assertFalse( $curl_result );
		}
	}

	/**
	 * Tests message for successful unlimited license activation
	 *
	 * @covers Yoast_License_Manager_v2::get_successful_activation_message()
	 */
	public function test_get_successful_activation_message_unlimited() {

		$api_response = (object) array(
			'license_limit' => 0,
			'expiry_date'   => false,
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have an unlimited license. ';

		$result = $this->class->get_successful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for successful activation with remaining activations
	 *
	 * @covers Yoast_License_Manager_v2::get_successful_activation_message()
	 */
	public function test_get_successful_activation_message_limited_license() {
		$api_response = (object) array(
			'license_limit' => 8,
			'site_count'    => 2,
			'expires'       => false,
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have used 2/8 activations. ';

		$result = $this->class->get_successful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for successful activation with upgrade message
	 *
	 * @covers Yoast_License_Manager_v2::get_successful_activation_message()
	 */
	public function test_get_successful_activation_message_limited_license_upgrade() {
		$api_response = (object) array(
			'site_count'    => 2,
			'license_limit' => 3,
			'expires'       => false,
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have used 2/3 activations. ';
		$message .= sprintf( '<a href="%s">Did you know you can upgrade your license?</a> ', $this->class->product->get_extension_url( 'license-nearing-limit-notice' ) );

		$result = $this->class->get_successful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for successful activation which will expire soon
	 *
	 * @covers Yoast_License_Manager_v2::get_successful_activation_message()
	 */
	public function test_get_successful_activation_message_limited_license_extend() {
		$days_left = 5;

		$api_response = (object) array(
			'license_limit' => 0,
			'expires'       => date( DATE_RSS, ( time() + ( $days_left * 86400 ) ) ),
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have an unlimited license. ';
		$message .= sprintf( '<a href="%s">Your license is expiring in 5 days, would you like to extend it?</a> ', $this->class->product->get_extension_url( 'license-expiring-notice' ) );

		$result = $this->class->get_successful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for successful activation which will expire tomorrow
	 *
	 * @covers Yoast_License_Manager_v2::get_successful_activation_message()
	 */
	public function test_get_successful_activation_message_limited_license_extend_tomorrow() {
		$days_left = 1;

		$api_response = (object) array(
			'license_limit' => 0,
			'expires'       => date( DATE_RSS, ( time() + ( $days_left * 86400 ) ) ),
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have an unlimited license. ';
		$message .= sprintf( '<a href="%s">Your license is expiring in 1 day, would you like to extend it?</a> ', $this->class->product->get_extension_url( 'license-expiring-notice' ) );

		$result = $this->class->get_successful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for successful activation which expires soon and can be upgraded
	 *
	 * @covers Yoast_License_Manager_v2::get_successful_activation_message()
	 */
	public function test_get_successful_activation_message_limited_license_upgrade_extend() {
		$days_left = 5;

		$api_response = (object) array(
			'site_count'    => 2,
			'license_limit' => 3,
			'expires'       => date( DATE_RSS, ( time() + ( $days_left * 86400 ) ) ),
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have used 2/3 activations. ';
		$message .= sprintf( '<a href="%s">Did you know you can upgrade your license?</a> ', $this->class->product->get_extension_url( 'license-nearing-limit-notice' ) );
		$message .= sprintf( '<a href="%s">Your license is expiring in 5 days, would you like to extend it?</a> ', $this->class->product->get_extension_url( 'license-expiring-notice' ) );

		$result = $this->class->get_successful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for unsuccessful activation without specific error code
	 *
	 * @covers Yoast_License_Manager_v2::get_unsuccessful_activation_message()
	 */
	public function test_get_unsuccessful_activation_message() {
		$api_response = (object) array(
			'error' => '',
		);

		$message = 'Failed to activate your license, your license key seems to be invalid.';

		$result = $this->class->get_unsuccessful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for unsuccessful activation when activation limit is reached
	 *
	 * @covers Yoast_License_Manager_v2::get_unsuccessful_activation_message()
	 */
	public function test_get_unsuccessful_activation_message_no_activations_left() {
		$api_response = (object) array(
			'error' => 'no_activations_left',
		);

		$message = sprintf( 'You\'ve reached your activation limit. You must <a href="%s">upgrade your license</a> to use it on this site.', $this->class->product->get_extension_url( 'license-at-limit-notice' ) );

		$result = $this->class->get_unsuccessful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests message for unsuccessful activation when license is expired
	 *
	 * @covers Yoast_License_Manager_v2::get_unsuccessful_activation_message()
	 */
	public function test_get_unsuccessful_activation_message_expired() {
		$api_response = (object) array(
			'error' => 'expired',
		);

		$message = sprintf( 'Your license has expired. You must <a href="%s">extend your license</a> in order to use it again.', $this->class->product->get_extension_url( 'license-expired-notice' ) );

		$result = $this->class->get_unsuccessful_activation_message( $api_response );

		$this->assertEquals( $result, $message );
	}

	/**
	 * Tests regular custom message
	 *
	 * @covers Yoast_License_Manager_v2::get_custom_message()
	 */
	public function test_get_custom_message() {
		$message = 'Normal HTML Message';

		$api_response = (object) array(
			'custom_message' => $message,
		);

		$result = $this->class->get_custom_message( $api_response );

		$this->assertEquals( $result, '<br />' . $message );
	}

	/**
	 * Tests locale specific custom message
	 *
	 * @covers Yoast_License_Manager_v2::get_custom_message()
	 */
	public function test_get_custom_message_locale() {
		$message = 'locale message';

		$locale = $this->class->get_user_locale();

		$api_response = (object) array(
			'custom_message_' . $locale => $message,
		);

		$result = $this->class->get_custom_message( $api_response );

		$this->assertEquals( $result, '<br />' . $message );
	}

	/**
	 * Tests notice on licence API unsuccessful request
	 *
	 * @covers Yoast_License_Manager_v2::activate_license()
	 */
	public function test_activate_license_failed_api_response() {
		// API response will be `false`.
		$this->class->set_license_api_response( 'activate', false );

		$activated = $this->class->activate_license();

		// Activation will fail.
		$this->assertFalse( $activated );

		// No notice will be triggered.
		$this->assertEquals( array(), $this->class->__get_notices() );
	}

	/**
	 * Tests notice on successful unlimited license activation
	 *
	 * @covers Yoast_License_Manager_v2::activate_license()
	 */
	public function test_activate_license_success() {
		$api_response = (object) array(
			'license'       => 'valid',
			'license_limit' => 0,
		);

		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have an unlimited license. ';

		$this->class->set_license_api_response( 'activate', $api_response );
		$this->class->activate_license();

		$expected = array(
			array(
				'message' => $message,
				'success' => true,
			),
		);
		$this->assertEquals( $expected, $this->class->__get_notices() );
	}

	/**
	 * Tests notice on successful unlimited license activation with custom message
	 *
	 * @covers Yoast_License_Manager_v2::activate_license()
	 */
	public function test_activate_license_success_custom_message() {
		$api_response = (object) array(
			'license'        => 'valid',
			'license_limit'  => 0,
			'custom_message' => 'This is an HTML description.',
		);


		$message  = 'Your test-product license has been activated. ';
		$message .= 'You have an unlimited license. ';

		$this->class->set_license_api_response( 'activate', $api_response );
		$this->class->activate_license();

		$expected = array(
			array(
				'message' => $message . '<br />' . $api_response->custom_message,
				'success' => true,
			),
		);
		$this->assertEquals( $expected, $this->class->__get_notices() );
	}

	/**
	 * Tests state on failed deactivation
	 *
	 * @covers Yoast_License_Manager_v2::deactivate_license()
	 */
	public function test_deactivate_license_failed_response() {
		// Current state is activated.
		$this->class->set_license_status( 'activated' );

		// API response will be `false`.
		$this->class->set_license_api_response( 'deactivate', false );

		$deactivated = $this->class->deactivate_license();

		$this->assertFalse( $deactivated );
		$this->assertEquals( 'activated', $this->class->get_license_status() );
	}

	/**
	 * Tests message on successful deactivation
	 *
	 * @covers Yoast_License_Manager_v2::deactivate_license()
	 */
	public function test_deactivate_license_success() {
		$api_response = (object) array(
			'license' => 'deactivated',
		);

		$message = 'Your test-product license has been deactivated.';

		$this->class->set_license_status( 'activated' );
		$this->class->set_license_api_response( 'deactivate', $api_response );

		$this->assertTrue( $this->class->deactivate_license() );

		$expected = array(
			array(
				'message' => $message,
				'success' => true,
			),
		);
		$this->assertEquals( $expected, $this->class->__get_notices() );
	}

	/**
	 * Tests message on successful deactivation with custom message
	 *
	 * @covers Yoast_License_Manager_v2::deactivate_license()
	 */
	public function test_deactivate_license_success_custom_message() {
		$api_response = (object) array(
			'license'        => 'deactivated',
			'custom_message' => 'This is an HTML description.',
		);

		$message = 'Your test-product license has been deactivated.';

		$this->class->set_license_status( 'activated' );
		$this->class->set_license_api_response( 'deactivate', $api_response );

		$this->assertTrue( $this->class->deactivate_license() );

		$expected = array(
			array(
				'message' => $message . '<br />' . $api_response->custom_message,
				'success' => true,
			),
		);
		$this->assertEquals( $expected, $this->class->__get_notices() );
	}

	/**
	 * Tests message on unsuccessful deactivation
	 *
	 * @covers Yoast_License_Manager_v2::deactivate_license()
	 */
	public function test_deactivate_license_failed() {
		$api_response = (object) array(
			'license' => 'activated', // Expected to be deactivated.
		);

		$message = 'Failed to deactivate your test-product license.';

		$this->class->set_license_status( 'activated' );
		$this->class->set_license_api_response( 'deactivate', $api_response );

		$this->assertFalse( $this->class->deactivate_license() );

		$expected = array(
			array(
				'message' => $message,
				'success' => false,
			),
		);
		$this->assertEquals( $expected, $this->class->__get_notices() );
	}

	/**
	 * Tests message on unsuccessful deactivation with custom message
	 *
	 * @covers Yoast_License_Manager_v2::deactivate_license()
	 */
	public function test_deactivate_license_failed_custom_message() {
		$api_response = (object) array(
			'license'        => 'activated', // Expected to be deactivated.
			'custom_message' => 'This is an HTML description.',
		);

		$message = 'Failed to deactivate your test-product license.';

		$this->class->set_license_status( 'activated' );
		$this->class->set_license_api_response( 'deactivate', $api_response );

		$this->assertFalse( $this->class->deactivate_license() );

		$expected = array(
			array(
				'message' => $message . '<br />' . $api_response->custom_message,
				'success' => false,
			),
		);
		$this->assertEquals( $expected, $this->class->__get_notices() );
	}

	/**
	 * Tests if allowed HTML tags still remain after parsing
	 *
	 * @covers Yoast_License_Manager_v2::get_custom_message()
	 */
	public function test_custom_message_allowed_html_tags() {
		$message = 'Normal HTML Message with a <a href="http://example.com">link</a>.';

		$api_response = (object) array(
			'custom_message' => $message,
		);

		$result = $this->class->get_custom_message( $api_response );

		$this->assertEquals( $result, '<br />' . $message );
	}

	/**
	 * Tests if allowed HTML tags still remain after parsing
	 *
	 * @covers Yoast_License_Manager_v2::get_custom_message()
	 */
	public function test_custom_message_allowed_html_tags_attributes() {
		$message  = 'Normal HTML Message<br /> with a <a href="http://example.com" target="_blank" title="bla" style="invalid">link</a>.';
		$expected = 'Normal HTML Message<br /> with a <a href="http://example.com" target="_blank" title="bla">link</a>.';

		$api_response = (object) array(
			'custom_message' => $message,
		);

		$result = $this->class->get_custom_message( $api_response );

		$this->assertEquals( $result, '<br />' . $expected );
	}

	/**
	 * Tests if non-allowed tags are being removed from the message
	 *
	 * @covers Yoast_License_Manager_v2::get_custom_message()
	 */
	public function test_custom_message_disallowed_html_tags() {
		$message  = 'Normal HTML Message with a <strong>link</strong>.';
		$expected = 'Normal HTML Message with a link.';

		$api_response = (object) array(
			'custom_message' => $message,
		);

		$result = $this->class->get_custom_message( $api_response );

		$this->assertEquals( $result, '<br />' . $expected );
	}
}
