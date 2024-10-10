<?php
/**
 * Class WP_Theme_JSON_Cache_Manager
 *
 * Manages caching for merged theme JSON data to enhance performance.
 *
 * @package WordPress
 * @since X.X.X
 */

class WP_Theme_JSON_Cache_Manager {

	/**
	 * Stores cached merged data for different origins.
	 *
	 * @since X.X.X
	 * @var array
	 */
	private static $cache = array(
		'default' => null,
		'blocks'  => null,
		'theme'   => null,
		'custom'  => null,
	);

	/**
	 * Tracks the last style update count to determine cache validity.
	 *
	 * @since X.X.X
	 * @var int
	 */
	private static $last_style_update_count = 0;

	/**
	 * Tracks the last block count to determine cache validity.
	 *
	 * @since X.X.X
	 * @var int
	 */
	private static $last_block_count = 0;

	/**
	 * Clears the cached merged data for all origins.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	public static function clear_cache() {
		self::$cache = array_fill_keys( array_keys( self::$cache ), null );
	}

	/**
	 * Handles changes to theme support features by invalidating relevant caches.
	 *
	 * @since X.X.X
	 *
	 * @param string $feature The feature that has changed.
	 * @param mixed  $args    Optional. Additional arguments related to the feature change.
	 *                        Default is null.
	 * @return void
	 */
	public static function handle_theme_support_change( $feature, $args = null ) {
		self::$cache['theme'] = null;
		self::$cache['custom'] = null;
	}

	/**
	 * Retrieves cached merged data for a given origin or generates it if not cached or invalid.
	 *
	 * @since X.X.X
	 *
	 * @param string   $origin        The origin of the merged data. Accepts 'default', 'blocks', 'theme', or 'custom'.
	 * @param callable $data_generator A callback function to generate the merged data if not cached.
	 * @return WP_Theme_JSON The merged theme JSON data.
	 */
	public static function get_cached_merged_data( $origin, $data_generator ) {
		if ( self::needs_update( $origin ) ) {
			self::$cache[ $origin ] = call_user_func( $data_generator, $origin );
			self::update_validation_state();
		}
		return self::$cache[$origin];
	}

	/**
	 * Determines whether the cached data for a given origin needs to be updated.
	 *
	 * @since X.X.X
	 *
	 * @param string $origin The origin of the merged data to check.
	 * @return bool True if the cache needs to be updated, false otherwise.
	 */
	private static function needs_update( $origin ) {
		if ( null === self::$cache[$origin] ) {
			return true;
		}

		// Check for changes in style and block counts
		$style_registry = WP_Block_Styles_Registry::get_instance();
		$current_style_update_count = $style_registry->get_style_update_count();
		$block_registry = WP_Block_Type_Registry::get_instance();
		$current_block_count = $block_registry->get_block_update_count();

		if ( self::$last_style_update_count < $current_style_update_count || self::$last_block_count < $current_block_count ) {
			self::clear_cache();
			self::$last_style_update_count = $current_style_update_count;
			self::$last_block_count = $current_block_count;
			return true;
		}

		return false;
	}

	/**
	 * Updates the internal state to reflect the current style and block counts.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	private static function update_validation_state() {
		$style_registry = WP_Block_Styles_Registry::get_instance();
		self::$last_style_update_count = $style_registry->get_style_update_count();
		$block_registry = WP_Block_Type_Registry::get_instance();
		self::$last_block_count = $block_registry->get_block_update_count();
	}
}
