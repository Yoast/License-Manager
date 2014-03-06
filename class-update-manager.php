<?php

class Yoast_Update_Manager {
	
	/**
	* @var string
	*/
	protected $api_url;

	/**
	* @var string
	*/
	protected $item_name;

	/**
	* @var string
	*/
	protected $slug;

	/**
	* @var string
	*/
	protected $license_key;

	/**
	* @var string
	*/
	protected $version;

	/**
	* @var string
	*/
	protected $author;

	/**
	* @var string
	*/
	protected $text_domain = 'yoast';

	/**
	* @var WP_Error 
	*/
	protected $wp_error;

	/**
	* Constructor
	*
	* @param string $api_url The url to the EDD shop
	* @param string $item_name The item name in the EDD shop
	* @param string $license_key The (valid) license key
	* @param string $slug The slug. This is either the plugin main file path or the theme slug.
	* @param string $version The current plugin or theme version
	* @param string $author (optional) The item author.
	*/
	public function __construct( $api_url, $item_name, $license_key, $slug, $version, $author, $text_domain ) {

		$this->api_url = $api_url;
		$this->item_name = $item_name;
		$this->license_key = $license_key;
		$this->slug = $slug;
		$this->version = $version;
		$this->author = $author;
		$this->text_domain = $text_domain;
	}

	/**
	* If the update check returned a WP_Error, show it to the user
	*/ 
	public function show_update_error() {
		
		if( $this->wp_error === null ) {
			return;
		}

		?>
		<div class="error">
			<p><?php printf( __( '%s failed to check for updates because of the following error: <em>%s</em>', $this->text_domain ), $this->item_name, $this->wp_error->get_error_message() ); ?></p>
		</div>
		<?php
	}

	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @uses         get_bloginfo()
	 * @uses         wp_remote_post()
	 * @uses         is_wp_error()
	 *
	 * @return false||object
	 */
	protected function call_remote_api() {

		// only check if a transient is not set (or if it's expired)
		if( get_transient( $this->slug . '-update-check-error' ) !== false ) {
			return;
		}

		// setup api parameters
		$api_params = array(
				'edd_action' => 'get_version',
				'license'    => $this->license_key,
				'name'       => $this->item_name,
				'slug'       => $this->slug,
				'author'     => $this->author
		);

		// setup request parameters
		$request_params = array( 
			'timeout' => 15, 
			'sslverify' => false, 
			'body' => $api_params 
		);

		// call remote api
		$response = wp_remote_post( $this->api_url, $request_params );


		// wp / http error?
		if( is_wp_error( $response) ) {
			$this->wp_error = $response;

			// show error to user
			add_action( 'admin_notices', array( $this, 'show_update_error') );

			// set a transient to prevent checking for updates on every page load
			set_transient( $this->slug . '-update-check-error', true, 60 * 30 ); // 30 mins

			return false;
		}


		// decode response
		$response = json_decode( wp_remote_retrieve_body( $response ) );
		$response->sections = maybe_unserialize( $response->sections );
		return $response;
	}

}