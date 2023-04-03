<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check Woo_Product_Auto_Release_Lite class_exists or not.
 */
if ( ! class_exists( 'Woo_Product_Auto_Release_Lite' ) ) {

	/**
	 * The core plugin class.
	 *
	 * @since 1.0.0
	 */
	class  Woo_Product_Auto_Release_Lite {

		/**
		 * The instance of Woo_Product_Auto_Release class.
		 *
		 * @var Woo_Product_Auto_Release_Lite
		 *
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Create Woo_Product_Auto_Release class instant
		 *
		 * @return Woo_Product_Auto_Release_Lite
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Woo_Product_Auto_Release_Lite ) ) {
				self::$instance = new Woo_Product_Auto_Release_Lite();
				self::$instance->includes();
				self::$instance->setup_actions();
			}

			return self::$instance;
		}

		/**
		 * Define the core functionality of the plugin.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Includes require files.
		 *
		 * @since 1.0.0
		 */
		public function includes() {

			require PRODUCT_AUTO_RELEASE_LITE_PATH . 'includes/product-auto-release-lite-functions.php';

			if ( ! is_admin() ) {
				return;
			}
            require PRODUCT_AUTO_RELEASE_LITE_PATH . 'admin/class-voted-list-table.php';
			require PRODUCT_AUTO_RELEASE_LITE_PATH . 'admin/class-product-auto-release-lite-admin.php';
			require PRODUCT_AUTO_RELEASE_LITE_PATH . 'admin/class-product-auto-release-lite-settings-fields.php';
		}

		/**
		 * Setup plugins admin and public actions.
		 *
		 * @since 1.0.0
		 */
		private function setup_actions() {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'admin_init', array( $this, 'activation_redirect' ) );
			add_action( 'template_redirect', array( $this, 'add_product_notify_button' ) );
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'woocommerce_loop_add_to_cart_args_cb' ), 10, 2 );
			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'woocommerce_blocks_product_grid_item_html_cb' ), 9999, 3 );
			add_filter( 'product_auto_release_upvote_button_label_html', array( $this, 'upvote_button_label_html_cb' ) );
			add_filter( 'woocommerce_email_classes', array( $this, 'woocommerce_email_classes_cb' ) );
			add_action( 'wp_ajax_wpar_notify_request', array( $this, 'wpar_notify_request_cb' ) );
			add_action( 'wp_ajax_nopriv_wpar_notify_request', array( $this, 'wpar_notify_request_cb' ) );
			add_action( 'wp_ajax_notify_product_release', array( $this, 'notify_product_release_cb' ) );
			add_action( 'wp_ajax_notify_product_release', array( $this, 'notify_product_release_cb' ) );

			add_filter( 'woocommerce_email_format_string', array( $this, 'wc_email_format_string' ), 10, 2 );
		}

        /**
         * Activation redirect.
         */
        public function activation_redirect() {

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

            if ( empty( $active_plugins ) || ! in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
                return true;
            }

            if ( !get_transient( '_parl_activation_redirect' ) ) {
                return;
            }

            delete_transient( '_parl_activation_redirect' );

            if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
                return;
            }

            wp_safe_redirect( admin_url( 'admin.php?page='.PRODUCT_AUTO_RELEASE_LITE_MENU_SLUG ) );
            exit;
        }

		/**
		 * Register the script and style for the public area.
		 *
		 * @since  1.0.0
		 */
		public function enqueue_scripts() {

			$product_auto_release_object = array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'enter_valid_email' => get_wpar_message( 'enter_valid_email' ),
				'days'              => __( 'days', 'product-auto-release-lite' ),
				'hours'             => __( 'hours', 'product-auto-release-lite' ),
				'minutes'           => __( 'minutes', 'product-auto-release-lite' ),
				'seconds'           => __( 'seconds', 'product-auto-release-lite' ),
				'is_product_type'   => '',
				'auto_release_date' => '',
			);

			wp_enqueue_style( 'product-auto-release-lite-frontend', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/frontend-style.css', array(), PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
			wp_enqueue_style( 'font-awesome-style', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/all.min.css', array(), PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
			wp_enqueue_script( 'product-auto-release-lite', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/js/product-auto-release-lite.js', array( 'jquery' ), PRODUCT_AUTO_RELEASE_LITE_VERSION, true );
			wp_localize_script( 'product-auto-release-lite', 'product_auto_release_object', $product_auto_release_object );
		}

		/**
		 * Add notify product if  notify option.
		 *
		 * @since 1.0.0
		 */
		public function add_product_notify_button() {

			/* Remove add to cart button from the autorelease product */
			if ( is_single() && 'product' === get_post_type() ) {
				global $product;

				if ( is_single() && current_filter() === 'template_redirect' ) { /* Check current filter is template redirect or not */
					$product_id = get_the_ID();
					$product    = ! empty( $product_id ) ? wc_get_product( $product_id ) : (object) array(); /*assign global product*/
				}

				$product_id   = ! empty( $product ) ? $product->get_id() : 0; /* store product id in variable */
				$product_type = ! empty( $product ) ? $product->get_type() : ''; /* store product type in variable */

				$notify_product = check_notify_product( $product_id ); /* Check product is notify product or not */

                if ( $notify_product ) {
                    $this->remove_add_to_cart_button( $product_id, $product_type);
                    add_action( 'woocommerce_simple_add_to_cart', array( $this, 'add_notify_button' ), 30 );
                } else {
                    add_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
                }
			}
		}

        public function woocommerce_loop_add_to_cart_args_cb($args, $product){

            $product_id = $product->get_id();
            $product_type = $product->get_type();
            $notify_product = false;
            $product_url = $product->add_to_cart_url();
            $product_label = $product->add_to_cart_text();
            $notify_product = check_notify_product($product_id);

            if( $notify_product && 'simple' === $product_type){
                $product_url = get_the_permalink($product_id);
                $product_label = __('View product', 'product-auto-release-lite');
            }

            $html = sprintf(
                '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                esc_url( $product_url ),
                esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
                esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
                isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
                esc_html( $product_label )
            );

            return $html;
        }

		/**
		 * Replace loop Add to cart button via visit product.
		 *
		 * @param $html
		 * @param $data
		 * @param $product
		 * @return mixed
		 *
		 * @since 1.0.0
		 */
		public function woocommerce_blocks_product_grid_item_html_cb( $html, $data, $product ) {

			$product_id = $product->get_id();

			$notify_product = check_notify_product( $product_id );

			if ( ! $notify_product ) {
				return $html;
			}

			return "<li class=\"wc-block-grid__product\">
            <a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
                {$data->image}
                {$data->title}
            </a>
            {$data->badge}
            {$data->price}
            {$data->rating}
            <a href=\"{$data->permalink}\" rel=\"nofollow\" class=\"button product_type_simple\">" . __( 'View product', 'product-auto-release-lite' ) . '</a>
         </li>';
		}

		/**
		 * Add notification emails on WC settings
		 *
		 * @param $email_classes
		 * @return mixed
		 *
		 * @since 1.0.0
		 */
		public function woocommerce_email_classes_cb( $email_classes ) {
			$email_classes['WC_Email_Admin_Product_Request_Live'] = include PRODUCT_AUTO_RELEASE_LITE_PATH . 'includes/emails/class-wc-product-available-admin-email.php';

			return $email_classes;
		}

		/**
		 * Register user notification request for the product.
		 *
		 * @since 1.0.0
		 */
		public function wpar_notify_request_cb() {

			$status  = false;
			$type    = 'error';
			$message = get_wpar_message( 'something_went_wrong' );

			$reload = false;
			if ( ! empty( $_POST['_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ), 'woo_auto_release' ) ) {

				$product_id = ! empty( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : 0;

				if ( ! empty( $product_id ) && $product_id > 0 ) {
					$status = true;
					$type   = 'success';

					$notify_product            = get_post_meta( $product_id, 'notify_product', true );
					$notify_product_lead       = get_post_meta( $product_id, 'notify_product_lead', true );
					$enable_notification       = get_post_meta( $product_id, 'enable_notification', true );
					$notify_product_lead_count = get_post_meta( $product_id, 'notify_product_lead_count', true );
					$notify_product_voted_ip   = get_post_meta( $product_id, 'notify_product_voted_ip', true );
					$notify_product_lead_count = ! empty( $notify_product_lead_count ) ? esc_attr( (int) $notify_product_lead_count ) : 0;
					$notify_product_voted_ip   = ! empty( $notify_product_voted_ip ) ? $notify_product_voted_ip : array();

					if ( ! empty( $notify_product ) && 'yes' === esc_attr( $notify_product ) && ! empty( $enable_notification ) && 'yes' === $enable_notification ) {
						$server = ! empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
						if ( in_array( $server, $notify_product_voted_ip, true ) ) {
							$message = get_wpar_message( 'notification_vote_exists' );
						} else {
							$message = get_wpar_message( 'notification_vote_submitted' );
							$notify_product_lead_count++;
							$notify_product_voted_ip[] = $server;
							update_post_meta( $product_id, 'notify_product_voted_ip', $notify_product_voted_ip );
							update_post_meta( $product_id, 'notify_product_lead_count', $notify_product_lead_count );
						}
					} else {
						$message = get_wpar_message( 'product_is_available' );
						$reload  = true;
					}

					if ( ! empty( $notify_product_lead ) && esc_attr( $notify_product_lead ) <= $notify_product_lead_count ) {
						$this->reset_settings( $product_id );
						$reload = true;
					}
				}
			}

			$response = array(
				'status'  => $status,
				'message' => $message,
				'type'    => $type,
				'reload'  => $reload,
			);

			wp_send_json( $response );
		}

		/**
		 * Release product by timer.
		 *
		 * @since 1.0.0
		 */
		public function notify_product_release_cb() {

			$status  = false;
			$reload  = false;
			$type    = 'error';
			$message = get_wpar_message( 'something_went_wrong' );

			if ( ! empty( $_POST['_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ), 'woo_auto_release' ) ) {
				$status     = true;
				$type       = 'success';
				$product_id = ! empty( $_POST['product_id'] ) ? (int)sanitize_text_field( $_POST['product_id'] ) : 0;

				if ( ! empty( $product_id ) && 0 <= $product_id ) {

					$message = get_wpar_message( 'product_is_available' );
					$reload  = true;

					$this->reset_settings( $product_id );
				}
			}

			$response = array(
				'status'  => $status,
				'message' => $message,
				'type'    => $type,
				'reload'  => $reload,
			);

			wp_send_json( $response );
		}

		/**
		 * Reset Product Auto Release with Upvote & Countdown settings,
		 *
		 * @since 1.0.0
		 *
		 * @param $product_id
		 */
		public function reset_settings( $product_id ) {

			update_post_meta( $product_id, 'notify_product', '' );
			update_post_meta( $product_id, 'enable_notification', '' );
			update_post_meta( $product_id, 'enable_auto_release', '' );
			update_post_meta( $product_id, 'notification_type', '' );
			update_post_meta( $product_id, 'notify_product_lead', '' );
			update_post_meta( $product_id, 'notify_product_lead_count', 0 );
			update_post_meta( $product_id, 'notify_product_voted_ip', '' );
			wpar_lite_notify_users( $product_id );
		}

        /**
         * Remove add to cart button
         *
         * @param int $product_id
         * @param string $product_type
         *
         * @since 1.0.0
         */
		public function remove_add_to_cart_button( $product_id = 0, $product_type = '' ) {
			if ( empty( $product_id ) || empty( $product_type ) || $product_id < 1 ) {
				return;
			}
			if ( 'simple' === $product_type ) {
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
			}
		}

        /**
         * Add notify_button in single product page
         *
         * @since 1.0.0
         */
		public function add_notify_button() {
			global $product;
			if ( ! empty( $product->get_type() ) && 'simple' === $product->get_type() ) {
				$product_id = ! empty( $product ) ? $product->get_id() : 0;
				echo $this->add_notify_button_html( $product_id );
			}
		}

		/**
		 * Add notify_button
		 *
		 * @since 1.0.0
		 */
		public function add_notify_button_html( $product_id = 0 ) {

			if ( empty( $product_id ) || $product_id < 1 ) {
				global $product;
				$product_id = ! empty( $product ) ? $product->get_id() : 0;
			}

			$notify_product      = get_post_meta( $product_id, 'notify_product', true );
			$enable_notification = get_post_meta( $product_id, 'enable_notification', true );

			ob_start();

			if ( ! empty( $notify_product ) && 'yes' === $notify_product ) { ?>
				<div class="wc-auto-release-wrap" data-product-id="<?php echo $product_id; ?>">
					<?php
					echo '<input type="hidden" name="_nonce" value="' . esc_attr( wp_create_nonce( 'woo_auto_release' ) ) . '">';
					$enable_show_total_votes = get_post_meta( $product_id, 'enable_show_total_votes', true );
					if ( ! empty( $enable_notification ) && 'yes' === $enable_notification ) {
						?>
						<div class="voting-options-main-wrapper" id="voting_options_main_wrapper" >
							<div class='voting-option-wrapper' id="voting_option_wrapper" >
								<a href="javascript:void(0);" class="notify-product-button button" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-action="upvote" id="notify_product_voting_button"><?php echo apply_filters( 'product_auto_release_upvote_button_label_html', "<i class='far fa-thumbs-up'></i>" ); ?></a>
								<?php
								if ( ! empty( $enable_show_total_votes ) && 'yes' === esc_attr( $enable_show_total_votes ) ) {
									$notify_product_lead_count = get_post_meta( $product_id, 'notify_product_lead_count', true );
									$notify_product_lead_count = ! empty( $notify_product_lead_count ) ? $notify_product_lead_count : 0;
									?>
									<span class="total-voting-numbers"><?php echo sprintf( _n( '<strong>%s</strong> Vote', '<strong>%s</strong> Votes', $notify_product_lead_count, 'product-auto-release-lite' ), number_format_i18n( $notify_product_lead_count ) ); ?> </span>
								<?php } ?>
							</div>
						</div>
						<?php
					}
					$enable_auto_release = get_post_meta( $product_id, 'enable_auto_release', true );
					$auto_release_date   = get_post_meta( $product_id, 'auto_release_date', true );
					if ( ! empty( $enable_auto_release ) && ! empty( $auto_release_date ) ) {
						?>
						<div class="timer-main-wrapper" id="timer_main_wrapper">
							<div id="wpar_timer" class="wpar-timer" data-available-time="<?php echo esc_attr( $auto_release_date ); ?>"></div>
						</div>
					<?php } ?>
				</div>
				<?php
			}

			return  ob_get_clean();
		}

        /**
         * Replace email string with title
         *
         * @param $string
         * @param $wc_email
         * @return array|mixed|string|string[]
         *
         * @since 1.0.0
         */
		public function wc_email_format_string( $string, $wc_email ) {

			if ( ! empty( $string ) && ! empty( $wc_email->product_id ) && $wc_email->product_id > 0 ) {

				$product_title = get_the_title( $wc_email->product_id );

				$string = str_replace( '{product_name}', $product_title, $string );
			}

			return $string;
		}

        /**
         * Upvote button html
         *
         * @param $html
         * @return mixed|string|void
         *
         * @since 1.0.0
         */
		public function upvote_button_label_html_cb( $html = '' ) {
			$wpar_get_settings = wpar_get_settings( 'general' );

			$upvote_button = ! empty( $wpar_get_settings['upvote_button'] ) ? esc_attr( $wpar_get_settings['upvote_button'] ) : '';
			$upvote_icon   = ! empty( $wpar_get_settings['upvote_button_icon'] ) ? esc_attr( $wpar_get_settings['upvote_button_icon'] ) : '';
			$upvote_label  = ! empty( $wpar_get_settings['upvote_button_label'] ) ? esc_attr( $wpar_get_settings['upvote_button_label'] ) : '';
			$icon_html     = "<i class='far fa-thumbs-up'></i>";
			if ( ! empty( $upvote_icon ) ) {
				$icon_html = "<i class='$upvote_icon'></i>";
			}
			if ( ! empty( $upvote_button ) && 'both' === $upvote_button && ! empty( $upvote_label ) ) {
				$html = $icon_html . ' ' . $upvote_label;
			} elseif ( ! empty( $upvote_button ) && 'icon' === $upvote_button && ! empty( $upvote_icon ) ) {
				$html = $icon_html;
			} elseif ( ! empty( $upvote_button ) && 'text' === $upvote_button && ! empty( $upvote_label ) ) {
				$html = $upvote_label;
			}

			return $html;
		}

	}
}
