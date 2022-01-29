<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Check WC_Email_User_Requested_Product_Available_Admin class exists or not
 */
if ( ! class_exists( 'WC_Email_User_Requested_Product_Available_Admin' ) ) {

	/**
	 * WC_Email_User_Requested_Product_Available_Admin class used to notify admin product is available now.
	 *
	 * @extends WC_Email
	 */
	class WC_Email_User_Requested_Product_Available_Admin extends WC_Email {

		/**
		 * Product id.
		 *
		 * @var int
		 * @since 1.0.0
		 */
		public $product_id;

		/**
		 * Product url.
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $product_url;

		/**
		 * Product title.
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $product_title;

		/**
		 * constructor.
		 */
		public function __construct() {
			$this->id             = 'requested_product_available_admin_email';
			$this->customer_email = false;
			$this->title          = __( 'Product Live Now - Admin', 'product-auto-release-lite' );
			$this->description    = __( 'This email sent to admin when product is live.', 'product-auto-release-lite' );

			$this->heading = __( '{product_name} is live now!', 'product-auto-release-lite' );
			$this->subject = __( '[{site_title}]: {product_name} is live now!', 'product-auto-release-lite' );

			$this->template_base  = PRODUCT_AUTO_RELEASE_LITE_PATH . 'templates/';
			$this->template_html  = 'emails/requested-product-available-admin-email.php';
			$this->template_plain = 'emails/plain/requested-product-available-admin-email.php';

			parent::__construct();

			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param $product_id
		 *
		 * @since 1.0.0
		 */
		public function trigger( $product_id ) {
			$this->setup_locale();

			$this->product_id    = $product_id;
			$this->product_url   = get_permalink( $product_id );
			$this->product_title = get_the_title( $product_id );
			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
			$this->restore_locale();
		}

		/**
		 * Get Content Html function.
		 *
		 * @return string
		 *
		 * @since 1.0.0
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => true,
					'plain_text'         => false,
					'email'              => $this,
					'product_id'         => $this->product_id,
					'product_url'        => $this->product_url,
					'product_title'      => $this->product_title,
					'admin_email'        => $this->recipient,
				),
				'',
				$this->template_base,
			);
		}

		/**
		 * get_content_plain function.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => true,
					'plain_text'         => false,
					'email'              => $this,
					'product_id'         => $this->product_id,
					'product_url'        => $this->product_url,
					'product_title'      => $this->product_title,
					'admin_email'        => $this->recipient,
				),
				'',
				$this->template_base,
			);
		}

		/**
		 * Admin Notify email form field.
		 *
		 * @since 1.0.0
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled'            => array(
					'title'   => __( 'Enable/Disable', 'product-auto-release-lite' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'product-auto-release-lite' ),
					'default' => 'yes',
				),
				'recipient'          => array(
					'title'       => __( 'Recipient', 'product-auto-release-lite' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'product-auto-release-lite' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'subject'            => array(
					'title'       => __( 'Subject', 'product-auto-release-lite' ),
					'type'        => 'text',
					'placeholder' => __( '[{site_title}]: {product_name} is live now!', 'product-auto-release-lite' ),
					'default'     => '',
				),
				'heading'            => array(
					'title'       => __( 'Email Heading', 'product-auto-release-lite' ),
					'type'        => 'text',
					'placeholder' => __( '{product_name} is live now!', 'product-auto-release-lite' ),
					'default'     => '',
				),
				'additional_content' => array(
					'title'       => __( 'Additional content', 'product-auto-release-lite' ),
					'description' => __( 'Text to appear below the main email content.', 'product-auto-release-lite' ),
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => __( 'N/A', 'product-auto-release-lite' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_additional_content(),
					'desc_tip'    => true,
				),
				'email_type'         => array(
					'title'   => __( 'Email type', 'product-auto-release-lite' ),
					'type'    => 'select',
					'default' => 'html',
					'class'   => 'email_type',
					'options' => array(
						'plain'     => __( 'Plain text', 'product-auto-release-lite' ),
						'html'      => __( 'HTML', 'product-auto-release-lite' ),
						'multipart' => __( 'Multipart', 'product-auto-release-lite' ),
					),
				),
			);
		}
	}
}

return new WC_Email_User_Requested_Product_Available_Admin();
