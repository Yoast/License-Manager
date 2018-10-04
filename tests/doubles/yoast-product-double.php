<?php

require_once dirname( __FILE__ ) . '/../../class-product.php';

/**
 * Class Yoast_Product_Double
 */
class Yoast_Product_Double extends Yoast_Product_v2 {

	/**
	 * Construct the real Product class with our fake data
	 */
	public function __construct() {
		parent::__construct( get_site_url(), 'test-product', 'slug-test-product', '1.0.0' );
	}
}
