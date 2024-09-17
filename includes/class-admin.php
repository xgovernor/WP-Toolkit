<?php
/**
 * Class Site_Health
 *
 * @since 1.0.0
 *
 * @package WPL Toolkit
 * @subpackage Admin
 */

defined( 'ABSPATH' ) or die( 'you do not have access to this page!' );

if ( ! class_exists( 'WPL_Admin' ) ) {
	class WPL_Admin {

		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die( 'you cannot create a second instance.' );
			}
			self::$_this = $this;
		}

		public static function this(): ?WPL_Admin {
			return self::$_this;
		}
	}
}
