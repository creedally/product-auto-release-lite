<?php
// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check check_notify_product function exists or not.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'check_notify_product' ) ) {

	/**
	 * Check the product is notify product or not
	 *
	 * @param int $product_id
	 * @return false|mixed|void
	 *
	 * @since 1.0.0
	 */
	function check_notify_product( $product_id = 0 ) {

		if ( empty( $product_id ) || $product_id <= 0 ) {
			return false;
		}

		$notify_product                = get_post_meta( $product_id, 'notify_product', true );
		$notify_product_lead           = get_post_meta( $product_id, 'notify_product_lead', true );
		$notify_product_lead_generated = get_post_meta( $product_id, 'notify_product_lead_generated', true );
		$is_product_counter            = get_post_meta( $product_id, 'enable_auto_release', true );
		$product_counter_time          = get_post_meta( $product_id, 'auto_release_date', true );

		$is_notify = false;
		if ( ! empty( $notify_product ) && ( ( ! empty( $notify_product_lead ) && ( empty( $notify_product_lead_generated ) || $notify_product_lead_generated < $notify_product_lead ) ) || ( ! empty( $is_product_counter ) && ! empty( $product_counter_time ) ) ) ) {
			$is_notify = true;
		}

		return apply_filters( 'product_auto_release_notify_product', $is_notify, $product_id );
	}
}

/**
 * Check get wpar message function exists or not.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'get_wpar_message' ) ) {

	/**
	 * Get all messages
	 *
	 * @param string $key
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	function get_wpar_message( $key = '' ) {

		if ( empty( $key ) ) {
			return '';
		}

		$messages = array(
			'something_went_wrong'        => __( 'Something went wrong, please try after some time.', 'product-auto-release-lite' ),
			'setting_saved'               => __( 'Your settings has been saved.', 'product-auto-release-lite' ),
			'nonce_not_verified'          => __( 'Nonce not verified.', 'product-auto-release-lite' ),
			'product_is_available'        => __( 'Product is available now.', 'product-auto-release-lite' ),
			'notification_vote_submitted' => __( 'Thank you for voting for this product release, we will release it soon.', 'product-auto-release-lite' ),
			'notification_vote_exists'    => __( 'You have already voted for this product.', 'product-auto-release-lite' ),
		);

		$global_messages = apply_filters( 'product_auto_release_global_messages', $messages );

		return ! empty( $global_messages[ $key ] ) ? $global_messages[ $key ] : '';
	}
}

/**
 * Check get_wpar_message function exists or not.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wpar_lite_notify_users' ) ) {

	/**
	 * Notify admin adn users that product available for which they requested.
	 *
	 * @param $product_id
	 *
	 * @since 1.0.0
	 */
	function wpar_lite_notify_users( $product_id ) {

		$mailer = WC()->mailer();
		$mails  = $mailer->get_emails();

		if ( ! empty( $mails ) ) {

			foreach ( $mails as $mail ) {
				if ( 'requested_product_available_admin_email' === $mail->id ) {
					$mail->trigger( $product_id );
				}
			}
		}

	}
}

/**
 * Check wpar_get_settings function exists or not.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wpar_get_settings' ) ) {

	/**
	 * Get plugin section settings.
	 *
	 * @param string $section
	 * @param bool $all
	 *
	 * @return false|mixed|string|void
	 * @since 1.0.0
	 *
	 */
	function wpar_get_settings( $section = '', $all = false ) {

		$settings = get_option( 'wpar_settings', true );

		if ( $all ) {
			return $settings;
		}

		return ! empty( $settings[ $section ] ) ? $settings[ $section ] : '';
	}
}

if ( ! function_exists( 'wpar_sanitize_fields' ) ) {

	function wpar_sanitize_fields( $fields ) {

		$settings = array();

		if ( ! empty( $fields ) && is_array( $fields ) ) {
			foreach ( $fields as $key => $field ) {
				if ( ! empty( $field ) && is_array( $field ) ) {
					$sanitize_field = wpar_sanitize_fields( $field );
				} else {
					$sanitize_field = sanitize_text_field( $field );
				}

				$settings[ $key ] = $sanitize_field;
			}
		}

		return $settings;
	}
}
