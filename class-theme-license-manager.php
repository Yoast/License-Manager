<?php

if ( class_exists( 'Yoast_License_Manager_v2' ) && ! class_exists( 'Yoast_Theme_License_Manager_v2', false ) ) {

	class Yoast_Theme_License_Manager_v2 extends Yoast_License_Manager_v2 {

		/**
		 * Setup auto updater for themes
		 */
		public function setup_auto_updater() {
			if ( $this->license_is_valid() ) {
				// setup auto updater
				require_once dirname( __FILE__ ) . '/class-update-manager.php';
				require_once dirname( __FILE__ ) . '/class-theme-update-manager.php';
				new Yoast_Theme_Update_Manager_v2( $this->product, $this );
			}
		}

		/**
		 * Setup hooks
		 */
		public function specific_hooks() {
			// remotely deactivate license upon switching away from this theme
			add_action( 'switch_theme', array( $this, 'deactivate_license' ) );

			// Add the license menu
			add_action( 'admin_menu', array( $this, 'add_license_menu' ) );
		}

		/**
		 * Add license page and add it to Themes menu
		 */
		public function add_license_menu() {
			add_theme_page(
				sprintf( __( '%s License', $this->product->get_text_domain() ), $this->product->get_item_name() ),
				__( 'Theme License', $this->product->get_text_domain() ),
				'manage_options',
				'theme-license',
				array( $this, 'show_license_page' )
			);
		}

		/**
		 * Shows license page
		 */
		public function show_license_page() {
			?>
			<div class="wrap">
				<?php settings_errors(); ?>

				<?php $this->show_license_form( false ); ?>
			</div>
			<?php
		}
	}

}
