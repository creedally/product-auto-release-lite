<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check Woo_Product_Auto_Release_Lite_Admin class_exists or not.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Woo_Product_Auto_Release_Lite_Admin' ) ) {

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	class Woo_Product_Auto_Release_Lite_Admin {

		/**
		 * Menu slug.
		 *
		 * @since    1.0.0
		 */
		public $menu_slug = PRODUCT_AUTO_RELEASE_LITE_MENU_SLUG;

		/**
		 * The errors of this plugin.
		 *
		 * @since    1.0.0
		 */
		private static $errors = array();

		/**
		 * The messages of this plugin.
		 *
		 * @since    1.0.0
		 */
		private static $messages = array();

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'save' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'notify_simple_product_options' ) );
			add_action( 'woocommerce_update_product', array( $this, 'save_notify_simple_product_options' ) );
		}

		/**
		 * Add messages for this plugin.
		 *
		 * @since    1.0.0
		 */
		public static function add_message( $text ) {
			self::$messages[] = esc_attr($text);
		}

		/**
		 * Add errors for this plugin.
		 *
		 * @since    1.0.0
		 */
		public static function add_error( $text ) {
			self::$errors[] = esc_attr($text);
		}

		/**
		 * Register admin menu.
		 *
		 * @since    1.0.0
		 */
		public function register_admin_menu() {
			//Create plugin menu
			add_submenu_page(
				'options-general.php',
				__( 'Product Auto Release', 'product-auto-release-lite' ),
				__( 'Product Auto Release', 'product-auto-release-lite' ),
				'administrator',
				$this->menu_slug,
				array( $this, 'woo_product_auto_release_lite_settings_page' ),
				70,
			);
		}

		/**
		 * Get current page.
		 *
		 * @return string
		 * @since    1.0.0
		 */
		public function current_page() {

			return ! empty( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		}

		/**
		 * Get current section.
		 *
		 * @return string
		 * @since    1.0.0
		 */
		public function current_section() {

			$current_section = $this->current_page();

			$current_menu = '';
			if ( ! empty( $current_section ) && $current_section === $this->menu_slug ) {
				$current_menu = ! empty( $_REQUEST['tab'] ) ? esc_attr( $_REQUEST['tab'] ) : '';
			}

			return $current_menu;
		}

		/**
		 * Get menu items.
		 *
		 * @since    1.0.0
		 */
		public function menu_items() {

			return apply_filters(
				'product_auto_release_menu_items',
				array(
					''              => __( 'Auto Release', 'product-auto-release-lite' ),
                    'voted_lists'   => __( 'Voted lists', 'product-auto-release-lite' ),
					'uninstall'     => __( 'Uninstall', 'product-auto-release-lite' ),
				)
			);
		}

		/**
		 * Display menu.
		 *
		 * @since    1.0.0
		 */
		public function menu() {

			$menu_items = $this->menu_items();

			if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
				return;
			}

			$current_section = $this->current_section();
			$menu_link       = admin_url( 'admin.php?page=' . $this->menu_slug ); ?>
			<nav class="wpar-nav-tab-wrapper nav-tab-wrapper wp-clearfix">
				<?php
				foreach ( $menu_items as $key => $menu ) {
					if ( ! empty( $key ) ) {
						$menu_link .= '&tab=' . $key;
					}
					$active = '';
					if ( ! empty( $current_section ) && $key === $current_section ) {
						$active = 'nav-tab-active';
					} elseif ( empty( $current_section ) && strtolower( $menu ) === 'auto release' ) {
						$active = 'nav-tab-active';
					}
					?>
					<a href="<?php echo esc_attr( $menu_link ); ?>" class="wpar-nav-tab nav-tab <?php echo esc_attr( $active ); ?>"><?php echo esc_attr( $menu ); ?></a>
				<?php } ?>
				<a href="https://store.creedally.com/product/woocommerce-product-auto-release/" target="_blank" rel="nofollow" class="wpar-nav-button product-url"><img src="<?php echo PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . '/assets/images/icon-upgrade-pro.svg'; ?>"><p><?php _e( 'Upgrade to Pro', 'product-auto-release-lite' ); ?></p></a>
			</nav>
			<?php
		}

		/**
		 * Display heading title.
		 *
		 * @since    1.0.0
		 */
		public function heading_title() {
			$menu_items      = $this->menu_items();
			$current_section = $this->current_section();
			$heading_title   = ! empty( $menu_items[ $current_section ] ) ? esc_attr( $menu_items[ $current_section ] ) . ' ' . __( 'settings', 'product-auto-release-lite' ) : '';
			?>
			<h1 class="wp-heading-inline"><?php echo esc_attr( apply_filters( 'product_auto_release_heading_title', $heading_title ) ); ?></h1>
			<?php
		}

		/**
		 * Display notification.
		 *
		 * @since    1.0.0
		 */
		public function notification() {
			?>
			<div class="wpar-notification-wrap">
				<?php
				if ( count( self::$errors ) > 0 ) {
					foreach ( self::$errors as $error ) {
						echo '<div id="message" class="error inline notice is-dismissible"><p>' . esc_attr( $error ) . '</p></div>';
					}
				} elseif ( count( self::$messages ) > 0 ) {
					foreach ( self::$messages as $message ) {
						echo '<div id="message" class="updated inline notice is-dismissible"><p>' . esc_attr( $message ) . '</p></div>';
					}
				}
				?>
			</div>
			<?php
		}

		/**
		 * Menu settings page form content.
		 *
		 * @since 1.0.0
		 */
		public function woo_product_auto_release_lite_settings_page() {
			$current_page    = $this->current_page();
			$current_section = $this->current_section();
			?>
			<div class="wpar-wrap wrap">
				<div class="wpar-nav-wrap">
					<?php $this->menu(); ?>
				</div>
				<div class="wpar-content-row row">

					<div class="wpar-content-main">
						<?php
						$this->heading_title();
						$this->notification();
						$ignore_list = array( 'subscribers_list', 'voted_lists' );

						if ( empty( $_GET['tab'] ) || ! in_array( esc_attr($_GET['tab']), $ignore_list, true ) ) {?>
							<form method="post" id="wpar_form_main" action="" enctype="multipart/form-data">
						<?php } ?>
							<div class="wpar-content-wrap">
								<?php
								do_action( 'wpar_lite_section_before_content', $current_section );

								do_action( 'wpar_lite_section_content', $current_section );

								$section = '_general';
								if ( ! empty( $current_section ) ) {
									$section = '_' . strtolower( $current_section );
								}

								do_action( 'wpar_lite_section_content' . $section, $current_section );

								do_action( 'wpar_lite_section_after_content', $current_section );
								?>
							</div>
							<?php if ( empty( $_GET['tab'] ) || ! in_array( esc_attr($_GET['tab']), $ignore_list, true ) ) { ?>
								<p class="submit <?php echo ! empty( $section ) ? esc_attr( $section ) : ''; ?> ">
									<button name="save" class="button-primary" type="submit" value="submit"><?php esc_attr_e( 'Save changes', 'product-auto-release-lite' ); ?></button>
									<input type="hidden" name="action" value="wpar_form_action">
									<input type="hidden" name="_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpar_form_main' ) ); ?>">
									<input type="hidden" name="current_page" value="<?php echo esc_attr( $current_page ); ?>">
									<input type="hidden" name="current_section" value="<?php echo esc_attr( $current_section ); ?>">
								</p>
							<?php } ?>
						<?php if ( empty( $_GET['tab'] ) || ! in_array( esc_attr($_GET['tab']), $ignore_list, true ) ) { ?>
						</form>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * WP Auto Release settings save.
		 *
		 * @since 1.0.0
		 *
		 * @return bool|void
		 */
		public function save() {

			if ( isset( $_POST['action'] ) && ! empty( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) === 'wpar_form_action' ) {

				if ( ! empty( $_POST['_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ), 'wpar_form_main' ) ) {

					$current_section = ! empty( $_POST['current_section'] ) ? sanitize_text_field( $_POST['current_section'] ) : 'general';
					if ( has_action( 'wpar_save_section_' . $current_section ) ) {

						do_action( 'wpar_save_section_' . $current_section );
						return true;
					}

					$settings = wpar_sanitize_fields( $_POST );

					unset( $settings['save'] );
					unset( $settings['action'] );
					unset( $settings['_nonce'] );
					unset( $settings['current_page'] );
					unset( $settings['current_section'] );
					unset( $settings['current_sub_section'] );
					unset( $settings['section'] );

					$setting_key   = 'wpar_settings';
					$wpar_settings = get_option( $setting_key, true );

					if ( ! empty( $wpar_settings ) && is_array( $wpar_settings ) ) {
						$section = ! empty( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : '';
						if ( ! empty( $section ) ) {
							$wpar_settings[ $current_section ][ $section ] = $settings;
						} else {
							$wpar_settings[ $current_section ] = $settings;
						}
					} else {
						$wpar_settings = array(
							$current_section => $settings,
						);

						$section = ! empty( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : '';
						if ( ! empty( $section ) ) {
							$wpar_settings[ $current_section ][ $section ] = $settings;
						} else {
							$wpar_settings[ $current_section ] = $settings;
						}
					}

					update_option( $setting_key, $wpar_settings );

					do_action( 'wpar_after_save_section_' . $current_section );

					self::add_message( get_wpar_message( 'setting_saved' ) );
				} else {
					self::add_error( get_wpar_message( 'nonce_not_verified' ) );
				}
			}
		}

		/**
		 * Register the script and style for the admin area.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_admin_scripts() {

			wp_enqueue_style( 'product-auto-release-lite-datepicker', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/jquery.datetimepicker.min.css', array(), PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
			wp_enqueue_style( 'font-awesome-style', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/all.min.css', array(), PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
			wp_enqueue_style( 'product-auto-release-lite-admin', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/admin-style.css', array(), PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
			wp_enqueue_script( 'product-auto-release-lite-datepicker', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/js/jquery.datetimepicker.full.min.js', array( 'jquery' ), PRODUCT_AUTO_RELEASE_LITE_VERSION, true );
			wp_enqueue_script( 'product-auto-release-lite-admin', PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/js/product-auto-release-lite-admin.js', array( 'jquery' ), PRODUCT_AUTO_RELEASE_LITE_VERSION, true );
		}

		/**
		 * Display simple notify product options.
		 *
		 * @since 1.0.0
		 */
		public function notify_simple_product_options() {
			?>
			<div id="custom_wc_notify_tab_data" class="options_group notify-product show_if_simple show_if_external hidden">
				<div class="options_group">
					<?php
					woocommerce_wp_checkbox(
						array(
							'id'          => 'notify_product',
							'class'       => 'wc-notify-checkbox checkbox',
							'label'       => __( 'Enable Product Auto Release?', 'product-auto-release-lite' ),
							'desc_tip'    => true,
							'description' => __( 'Enable this option to start product auto release procedure.', 'product-auto-release-lite' ),
						)
					);

					woocommerce_wp_checkbox(
						array(
							'id'            => 'enable_notification',
							'class'         => 'wc-enable-notification-checkbox checkbox',
							'wrapper_class' => 'wc-enable-notification wc-notify-general-field',
							'label'         => __( 'Enable Product Release Settings?', 'product-auto-release-lite' ),
							'desc_tip'      => true,
							'description'   => __( 'Enable this option to add product release setting.', 'product-auto-release-lite' ),
						)
					);

					woocommerce_wp_radio(
						array(
							'id'            => 'notification_type',
							'class'         => 'wc-notification-type-checkbox checkbox ',
							'wrapper_class' => 'wc-notify-fields wc-notify-general-field',
							'label'         => __( 'Product Auto Release Type', 'product-auto-release-lite' ),
							'desc_tip'      => true,
							'value'         => 'voting',
							'options'       => array(
								'voting' => __( 'UP Vote', 'product-auto-release-lite' ),
							),
						)
					);

					woocommerce_wp_checkbox(
						array(
							'id'            => 'enable_show_total_votes',
							'class'         => 'wc-enable-show-total-votes checkbox',
							'wrapper_class' => 'notify_product_lead_field  wc-notify-fields wc-notify-general-field',
							'label'         => __( 'Show Total Votes on Product Page?', 'product-auto-release-lite' ),
							'desc_tip'      => true,
							'description'   => __( 'Enable this option display total votes on product page.', 'product-auto-release-lite' ),
						)
					);

					woocommerce_wp_text_input(
						array(
							'type'              => 'number',
							'id'                => 'notify_product_lead',
							'label'             => __( "Customer's Targeted Number", 'product-auto-release-lite' ),
							'wrapper_class'     => 'notify_product_lead_field wc-notify-fields wc-notify-general-field',
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => 1,
								'min'  => 1,
							),
							'class'             => 'regular-text',
						)
					);

					woocommerce_wp_checkbox(
						array(
							'id'            => 'enable_auto_release',
							'class'         => 'enable-email-auto-release-checkbox checkbox ',
							'wrapper_class' => 'wc-auto-release wc-notify-general-field',
							'label'         => __( 'Enable Countdown Timer?', 'product-auto-release-lite' ),
							'desc_tip'      => true,
							'description'   => __( 'Enable this option to set countdown timer for product release.', 'product-auto-release-lite' ),
						)
					);

					woocommerce_wp_text_input(
						array(
							'type'              => 'text',
							'id'                => 'auto_release_date',
							'label'             => __( 'Product Auto Release Date&Time', 'product-auto-release-lite' ),
							'wrapper_class'     => 'wc-auto-release-fields release-date-picker wc-notify-general-field',
							'desc_tip'          => true,
							'placeholder'       => date( 'Y-m-d H:s' ),
							'class'             => 'regular-text auto-release-date',
							'custom_attributes' => array( 'autocomplete' => 'off' ),
						)
					);

					woocommerce_wp_hidden_input(
						array(
							'id'    => 'woocommerce_meta_nonce',
							'value' => esc_attr( wp_create_nonce( 'woocommerce_save_data' ) ),
						),
					);

					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Update Simple product notify options fields.
		 *
		 * @param int $product_id
		 *
		 * @since 1.0.0
		 */
		public function save_notify_simple_product_options( int $product_id ) {
			if ( ! empty( $_POST['woocommerce_meta_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) {

				$check_notify_product    = check_notify_product( $product_id );
				$notify_product          = ! empty( $_POST['notify_product'] ) ? 'yes' : '';
				$enable_notification     = ! empty( $_POST['enable_notification'] ) ? 'yes' : 0;
				$notification_type       = ! empty( $_POST['notification_type'] ) ? sanitize_text_field( $_POST['notification_type'] ) : '';
				$enable_auto_release     = ! empty( $_POST['enable_auto_release'] ) ? sanitize_text_field( $_POST['enable_auto_release'] ) : '';
				$auto_release_date       = ! empty( $_POST['auto_release_date'] ) ? sanitize_text_field( $_POST['auto_release_date'] ) : '';
				$notify_product_lead     = ! empty( $_POST['notify_product_lead'] ) ? sanitize_text_field( $_POST['notify_product_lead'] ) : 0;
				$enable_show_total_votes = ! empty( $_POST['enable_show_total_votes'] ) ? 'yes' : '';

				if ( $check_notify_product && 'yes' !== $notify_product ) {
					wpar_lite_notify_users( $product_id );
				}

				update_post_meta( $product_id, 'notify_product', $notify_product );
				update_post_meta( $product_id, 'enable_notification', $enable_notification );
				update_post_meta( $product_id, 'enable_auto_release', $enable_auto_release );
				update_post_meta( $product_id, 'auto_release_date', $auto_release_date );
				update_post_meta( $product_id, 'notification_type', $notification_type );
				update_post_meta( $product_id, 'notify_product_lead', $notify_product_lead );
				update_post_meta( $product_id, 'enable_show_total_votes', $enable_show_total_votes );
			}
		}

	}

	new Woo_Product_Auto_Release_Lite_Admin();
}
