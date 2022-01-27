<?php
/**
 * Uninstall WooCommerce Product Auto Release.
 *
 * When WooCommerce Product Auto Release delete plugin then plugin options remove.
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wpar_settings' );
