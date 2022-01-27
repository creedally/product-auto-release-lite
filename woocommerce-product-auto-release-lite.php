<?php
/**
* Plugin Name:   WooCommerce Product Auto Release Lite
* Plugin URI:    https://creedally.com/
* Description:   Auto release your products with a upvote rules of your convenience to attract more users, drive the more initial sale of your products.
* Version:       1.0.0
* Author:        CreedAlly
* Author URI:    https://creedally.com/
* License:       GPL-2.0+
* License URI:   http://www.gnu.org/licenses/gpl-2.0.txt
* ProductID:     233
* Text Domain:   woo-product-auto-release-lite
* Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin Version.
if ( ! defined( 'WOO_PRODUCT_AUTO_RELEASE_LITE_VERSION' ) ) {
	define( 'WOO_PRODUCT_AUTO_RELEASE_LITE_VERSION', '1.0.0' );
}

// Define plugin Path.
if ( ! defined( 'WOO_PRODUCT_AUTO_RELEASE_LITE_PATH' ) ) {
	define( 'WOO_PRODUCT_AUTO_RELEASE_LITE_PATH', plugin_dir_path( __FILE__ ) );
}

// Define plugin Url.
if ( ! defined( 'WOO_PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL' ) ) {
	define( 'WOO_PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Define plugin menu Slug.
if ( ! defined( 'WOO_PRODUCT_AUTO_RELEASE_LITE_MENU_SLUG' ) ) {
	define( 'WOO_PRODUCT_AUTO_RELEASE_MENU_LITE_SLUG', 'woo-product-auto-release-lite' );
}

/**
 * Check is admin or not.
 *
 * @since 1.0.0
 */
if ( is_admin() ) {

	/**
	 * Check deactivate_plugins function exists or not.
	 * if function not exists then include plugin.php file.
	 *
	 * @since 1.0.0
	 */
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$active_plugins = (array) get_option( 'active_plugins', array() );

	/**
	 * Check activate plugins or not.
	 * if WooCommerce plugins not activate then WC Product Inquiry plugin deactivate and display notice.
	 *
	 * @since 1.0.0
	 */
	if ( empty( $active_plugins ) || ! in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {

		/**
		 * Deactivate WC Product Inquiry plugin.
		 *
		 * @since 1.0.0
		 */
		deactivate_plugins( plugin_basename( __FILE__ ) );

		/**
		 * Display WooCommerce plugin requires notice.
		 *
		 * @since 1.0.0
		 */
		add_action(
			'admin_notices',
			function() {
				/* translators: %1$s: Product Title, %2$s: product link tag start, %3$s: product link tag end */
				echo '<div class="notice notice-error is-dismissible"><p><strong>' . sprintf( __( '%1$s requires %2$s WooCommerce %3$s plugin to be installed and active.', 'woo-product-auto-release-lite' ), ' WC Product Inquiry Lite', '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' ) . '</strong></p></div>';
			}
		);
	}
}
register_activation_hook( __FILE__, 'activate_wc_product_auto_release_lite' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/activator.php
 *
 * @since 1.0.0
 */
function activate_wc_product_auto_release_lite() {
    require_once 'includes/activator.php';
    WC_PRODUCT_AUTO_RELEASE_LITE_Activator::activate();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since 1.0.0
 */
require_once 'includes/class-woo-product-auto-release-lite.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
add_action(
    'plugins_loaded',
    function () {
        Woo_Product_Auto_Release_Lite::get_instance();
    }
);
