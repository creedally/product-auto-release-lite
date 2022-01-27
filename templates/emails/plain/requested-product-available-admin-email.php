<?php

/**
 * Admin requested product available email
 *
 * @since  1.0.0
 */
defined( 'ABSPATH' ) || exit;
$product_url_html = '<a href="' . esc_url( $product_url ) . '" target="_blank">' . esc_attr( $product_title ) . '</a>';
echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

esc_attr_e( 'Hello,', 'woo-product-auto-release-lite' ) . "\n\n";
/* translators: %1$s: Site title, %2$s: product url */
echo sprintf( esc_attr__( '%1$s product is live now. you can review the product here, %2$s.', 'woo-product-auto-release-lite' ), esc_attr( $product_title ), $product_url ) . "\n\n";

echo "\n\n----------------------------------------\n\n";
