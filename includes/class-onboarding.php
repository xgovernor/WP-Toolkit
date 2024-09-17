<?php
/**
 * WPL Toolkit onboarding class
 *
 * @since 1.0.0
 *
 * @package WPL Toolkit
 * @subpackage Onboarding
 */

defined( 'BASEPATH' ) or exit( 'No direct script access allowed' );

if ( ! isset_class( 'WPLTK_Onboarding' ) ) {
	class WPLTK_Onboarding {

		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				throw new \Exception( 'You cannot create a second instance of WPLTK_Onboarding.' );
			}

			self::$_this = $this;
		}

		public static function this() {
			return self::$_this;
		}
	}
}
