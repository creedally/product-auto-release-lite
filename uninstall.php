<?php
/**
 * Uninstall Product Auto Release with Upvote & Countdown.
 *
 * When Product Auto Release with Upvote & Countdown plugin delete then remove settings.
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wpar_settings' );
