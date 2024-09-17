<?php
/**
 * WPL - Site Health
 *
 * @since 1.0.0
 *
 * @package     WPL Toolkit
 * @subpackage  Site Health
 */

defined( 'ABSPATH' ) or die( 'you do not have access to this page!' );

if ( ! class_exists( 'WPL_Site_Health' ) ) {

	class WPL_Site_Health {

		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				throw new \Exception( 'You cannot create a second instance of WPL_Site_Health.' );
			}

			self::$_this = $this;
		}

		public static function this() {
			return self::$_this;
		}
	}
}
