<?php
defined( 'ABSPATH' ) or die();


if ( ! class_exists( 'WPLTK_Cache' ) ) {
	class WPLTK_Cache {


		private static $_this;

		public function __construct() {
			if ( isset( self::$_this ) ) {
				throw new \Exception( 'You cannot create a second instance of WPL_Cache.' );
			}

			self::$_this = $this;
		}

		public static function this(): ?WPLTK_Cache {
			return self::$_this;
		}

		/**
		 * Flushes the cache for popular caching plugins to prevent mixed content errors
		 * When .htaccess is changed, all traffic should flow over https, so clear cache when possible.
		 * supported: W3TC, WP fastest Cache, Zen Cache, wp_rocket
		 *
		 * @since  2.0
		 *
		 * @access public
		 */
		public function flush(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			add_action( 'admin_head', array( $this, 'maybe_flush_w3tc_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_wp_optimize_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_litespeed_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_hummingbird_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_fastest_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_autoptimize_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_wp_rocket' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_cache_enabler' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_wp_super_cache' ) );
			add_action( 'admin_head', array( $this, 'maybe_flush_wpl_cache' ) );
		}

		public function maybe_flush_w3tc_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( function_exists( 'w3tc_flush_all' ) ) {
				if ( ! w3tc_flush_all() ) { // If W3 Total Cache is not installed, flush all caches
					error_log( '[WPL Cache] W3 Total Cache flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_wp_optimize_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( function_exists( 'wpo_cache_flush' ) ) {
				if ( ! wpo_cache_flush() ) {
					error_log( '[WPL Cache] WPO Cache flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_litespeed_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( class_exists( 'LiteSpeed' ) ) {
				if ( ! LiteSpeed\Purge::purge_all() ) {
					error_log( '[WPL Cache] LiteSpeed flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_hummingbird_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( is_callable( array( 'Hummingbird\WP_Hummingbird', 'flush_cache' ) ) ) {
				if ( ! LiteSpeed\Purge::purge_all() ) {
					error_log( '[WPL Cache] Hummingbird flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_fastest_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( class_exists( 'WpFastestCache' ) ) {
				$wpfc = new WpFastestCache();
				if ( $wpfc->deleteCache() ) {
					error_log( '[WPL Cache] WpFastestCache flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_autoptimize_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( class_exists( 'autoptimizeCache' ) ) {
				if ( ! autoptimizeCache::clearall() ) {
					error_log( '[WPL Cache] Autoptimize flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_wp_rocket(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( function_exists( 'rocket_clean_domain' ) ) {
				if ( ! rocket_clean_domain() ) {
					error_log( '[WPL Cache] WP Rocket flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_cache_enabler(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( class_exists( 'Cache_Enabler' ) ) {
				if ( ! Cache_Enabler::clear_complete_cache() ) {
					error_log( '[WPL Cache] Cache Enabler flush failed or plugin not installed.' );
					return;
				}
			}
		}

		public function maybe_flush_wp_super_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			if ( function_exists( 'wp_cache_clear_cache' ) ) {
				if ( ! wp_cache_clear_cache() ) {
					error_log( '[WPL Cache] WP Super Cache flush failed or plugin not installed.' );
					return;
				}
			}
		}

		/**
		 * Set cache data with an expiration time.
		 *
		 * @param string $key The cache key.
		 * @param mixed  $data The data to store in cache.
		 * @param int    $expiration Time in seconds for cache expiration.
		 * @return bool True on success, false on failure.
		 */
		public function set( $key, $data, $expiration = 3600 ): mixed {
			return set_transient( $key, $data, $expiration );
		}

		/**
		 * Get cache data by key.
		 *
		 * @param string $key The cache key.
		 * @return mixed The cached data or false if not found or expired.
		 */
		public function get( $key ): mixed {
			return get_transient( $key );
		}

		/**
		 * Delete cache data by key.
		 *
		 * @param string $key The cache key.
		 * @return bool True on success, false on failure.
		 */
		public function delete( $key ): mixed {
			return delete_transient( $key );
		}

		/**
		 * Clear all WPL-related cache by a specific pattern.
		 * Optionally use this function to clean cache related to WPL operations.
		 */
		public function maybe_flush_wpl_cache(): void {
			if ( ! wpltk_user_can_manage() ) {
				return;
			}

			global $wpdb;
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpl_%'" );
		}
	}//end class
}
