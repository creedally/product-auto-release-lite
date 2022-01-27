<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */

/**
 * Check WC_PRODUCT_AUTO_RELEASE_LITE_Activator class exits or not.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'WC_PRODUCT_AUTO_RELEASE_LITE_Activator' ) ) {

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since 1.0.0
	 */
	class WC_PRODUCT_AUTO_RELEASE_LITE_Activator {

		/**
		 * Update plugin settings during activation.
		 *
		 * @since 1.0.0
		 */
		public static function activate() {

        }
    }
}
