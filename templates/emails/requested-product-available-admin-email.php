<?php
/**
 * Product available notify email for admin.
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$product_url_html = '<a href="' . esc_url( $product_url ) . '" target="_blank">' . esc_attr( $product_title ) . '</a>';

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<?php /* translators: %s: Customer username */ ?>
	<p><?php echo sprintf( esc_attr__( 'Hello %s,', 'product-auto-release-lite' ), esc_html( $admin_email ) ); ?></p>
<?php /* translators: %1$s: Site title, %2$s: Username */ ?>
	<p><?php echo sprintf( esc_attr__( '%1$s product is live now. you can review the product here, %2$s.', 'product-auto-release-lite' ), '<strong>' . esc_attr( $product_title ) . '</strong>', $product_url ); ?></p>
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
