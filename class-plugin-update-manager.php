<?php

if( class_exists( 'Yoast_Update_Manager_v2' ) && ! class_exists( 'Yoast_Plugin_Update_Manager_v2', false ) ) {

	class Yoast_Plugin_Update_Manager_v2 extends Yoast_Update_Manager_v2 {

		/**
		 * Constructor
		 *
		 * @param Yoast_Product_v2 $product     The Product.
		 * @param string           $license_key The License entered.
		 */
		public function __construct( Yoast_Product_v2 $product, $license_key ) {
			parent::__construct( $product, $license_key );

			// setup hooks
			$this->setup_hooks();
		}

		/**
		* Setup hooks
		*/
		private function setup_hooks() {

			// check for updates
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'set_updates_available_data' ) );

			// get correct plugin information (when viewing details)
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		}

		/**
		* Check for updates and if so, add to "updates available" data
		*
		* @param object $data
		* @return object $data
		*/
		public function set_updates_available_data( $data ) {

			if ( empty( $data ) ) {
				return $data;
			}

			// send of API request to check for updates
			$remote_data = $this->get_remote_data();

			// did we get a response?
			if( $remote_data === false ) {
				return $data;
			}

			// compare local version with remote version
			if ( version_compare( $this->product->get_version(), $remote_data->new_version, '<' ) ) {

				// remote version is newer, add to data
				$data->response[ $this->product->get_file() ] = $remote_data;

			}

			return $data;
		}

		/**
		 * Gets new plugin version details (view version x.x.x details)
		 *
		 * @uses api_request()
		 *
		 * @param object $data
		 * @param string $action
		 * @param object $args (optional)
		 *
		 * @return object $data
		 */
		public function plugins_api_filter( $data, $action = '', $args = null ) {

			// only do something if we're checking for our plugin
			if ( $action !== 'plugin_information' || ! isset( $args->slug ) || $args->slug !== $this->product->get_slug() ) {
				return $data;
			}

			$api_response = $this->get_remote_data();

			// did we get a response?
			if ( $api_response === false ) {
				return $data;
			}

			// return api response
			return $this->cast_sections_property_to_array( $api_response );
		}

		/**
		 * Casts the sections attribute to an array.
		 *
		 * @param $data object The object that has a sections attribute that has to casted to a array.
		 *
		 * @return object the transformed object.
		 */
		private function cast_sections_property_to_array( $data ) {
			if ( isset( $data->sections ) ) {
				$data->sections = (array) $data->sections;
			}
			return $data;
		}
	}

}
