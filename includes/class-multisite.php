<?php
/**
 * WPL Toolkit - Multisite
 *
 * @since 1.0.0
 *
 * @package WPL_Toolkit
 * @subpackage Multisite
 */

define( 'ABSPATH' ) || die( 'No direct access!' );


if ( ! class_exists( 'WPLTK_Multisite' ) ) {
	/**
	 * WPL Toolkit - Multisite
	 *
	 * @since 1.0.0
	 */
	class WPLTK_Multisite {

		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) && self::$_this instanceof WPLTK_Multisite ) {
				wp_die( 'You can not create more than one instance of WPLTK_Multisite' );
			}

			self::$_this = $this;
		}

		public static function this(): WPLTK_Multisite {
			return self::$_this;
		}
	}
}
