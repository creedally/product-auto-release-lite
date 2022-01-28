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
        public static function get_instance(): Woo_Product_Auto_Release_Lite {
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

            require WOO_PRODUCT_AUTO_RELEASE_LITE_PATH . 'includes/woocommerce-product-auto-release-lite-functions.php';

            if ( ! is_admin() ) {
                return;
            }

            require WOO_PRODUCT_AUTO_RELEASE_LITE_PATH . 'admin/class-woocommerce-product-auto-release-lite-admin.php';
            require WOO_PRODUCT_AUTO_RELEASE_LITE_PATH . 'admin/class-woocommerce-product-auto-release-lite-settings-fields.php';
        }

        /**
         * Setup plugins admin and public actions.
         *
         * @since 1.0.0
         */
        private function setup_actions() {

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'template_redirect', array( $this, 'add_product_notify_button' ) );
            add_action( 'woocommerce_before_shop_loop_item', array( $this, 'add_product_notify_button' ) );
            add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'woocommerce_blocks_product_grid_item_html_cb' ), 9999, 3 );
            add_filter( 'upvote_button_label_html', array($this, 'upvote_button_label_html_cb'));
            add_filter( 'woocommerce_email_classes', array( $this, 'woocommerce_email_classes_cb' ) );
            add_action( 'wp_ajax_wpar_notify_request', array( $this, 'wpar_notify_request_cb' ) );
            add_action( 'wp_ajax_nopriv_wpar_notify_request', array( $this, 'wpar_notify_request_cb' ) );
            add_action( 'wp_ajax_notify_product_release', array( $this, 'notify_product_release_cb' ) );
            add_action( 'wp_ajax_notify_product_release', array( $this, 'notify_product_release_cb' ) );

            add_filter( 'woocommerce_email_format_string', array( $this, 'wc_email_format_string' ), 10, 2 );
        }

        /**
         * Register the script and style for the public area.
         *
         * @since  1.0.0
         */
        public function enqueue_scripts() {

            $woocommerce_product_auto_release_object = array(
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'enter_valid_email' => get_wpar_message( 'enter_valid_email' ),
                'days'              => __( 'days', 'woocommerce-product-auto-release-lite' ),
                'hours'             => __( 'hours', 'woocommerce-product-auto-release-lite' ),
                'minutes'           => __( 'minutes', 'woocommerce-product-auto-release-lite' ),
                'seconds'           => __( 'seconds', 'woocommerce-product-auto-release-lite' ),
                'is_product_type'   => '',
                'auto_release_date' => '',
            );

            wp_enqueue_style( 'woocommerce-product-auto-release-lite-frontend', WOO_PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/frontend-style.css', array(), WOO_PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
            wp_enqueue_style( 'font-awesome-style', WOO_PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/css/all.min.css', array(), WOO_PRODUCT_AUTO_RELEASE_LITE_VERSION, 'all' );
            wp_enqueue_script( 'woocommerce-product-auto-release-lite', WOO_PRODUCT_AUTO_RELEASE_LITE_PLUGIN_URL . 'assets/js/woocommerce-product-auto-release-lite.js', array( 'jquery' ), WOO_PRODUCT_AUTO_RELEASE_LITE_VERSION, true );
            wp_localize_script( 'woocommerce-product-auto-release-lite', 'woocommerce_product_auto_release_object', $woocommerce_product_auto_release_object );
        }

        /**
         * Add notify product if  notify option.
         *
         * @since 1.0.0
         */
        public function add_product_notify_button() {

            /* Add product in cart and redirect on checkout page */

            if ( ! empty( $_REQUEST['buy_now'] ) && '1' === sanitize_text_field( $_REQUEST['buy_now'] ) ) {

                $product_id   = ! empty( $_REQUEST['product_id'] ) ? sanitize_text_field( $_REQUEST['product_id'] ) : 0;

                WC()->cart->empty_cart();

                WC()->cart->add_to_cart( $product_id, 1 );

                wp_safe_redirect( wc_get_checkout_url() );
                exit;
            }

            /* Remove add to cart button from the autorelease product */

            $loop = false;
            if ( 'woocommerce_before_shop_loop_item' === current_filter() ) { /* Check loop product or not */
                $loop = true;
            }
            if ( $loop || 'product' === get_post_type() ) {
                global $product;

                if ( is_single() && current_filter() === 'template_redirect' ) { /* Check current filter is template redirect or not */
                    $product_id = get_the_ID();
                    $product    = ! empty( $product_id ) ? wc_get_product( $product_id ) : (object) array(); /*assign global product*/
                }

                $product_id   = ! empty( $product ) ? $product->get_id() : 0; /* store product id in variable */
                $product_type = ! empty( $product ) ? $product->get_type() : ''; /* store product type in variable */

                $notify_product = check_notify_product( $product_id ); /* Check product is notify product or not */

                if ( $notify_product ) {

                    $this->remove_add_to_cart_button( $product_id, $product_type, $loop );

                    if ( ! $loop ) {
                        add_action( 'woocommerce_simple_add_to_cart', array( $this, 'add_notify_button' ), 30 );
                    } else {
                        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_view_more_button' ), 20 );
                    }
                } else {
                    add_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
                    add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
                    remove_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_view_more_button' ), 20 );
                }
            }
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
            <a href=\"{$data->permalink}\" rel=\"nofollow\" class=\"button product_type_simple\">" . __( 'View product', 'woocommerce-product-auto-release-lite' ) . '</a>
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
            $email_classes['WC_Email_Admin_Product_Request_Live'] = include WOO_PRODUCT_AUTO_RELEASE_LITE_PATH . 'includes/emails/class-wc-product-available-admin-email.php';

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

                $product_id   = ! empty( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : 0;

                if ( ! empty( $product_id ) && $product_id > 0 ) {
                    $status       = true;
                    $type         = 'success';

                    $notify_product            = get_post_meta( $product_id, 'notify_product', true );
                    $notify_product_lead       = get_post_meta( $product_id, 'notify_product_lead', true );
                    $notify_product_lead_count = get_post_meta( $product_id, 'notify_product_lead_count', true );
                    $notify_product_voted_ip   = get_post_meta( $product_id, 'notify_product_voted_ip', true );
                    $notify_product_lead_count = ! empty( $notify_product_lead_count ) ? esc_attr( (int) $notify_product_lead_count ) : 0;
                    $notify_product_voted_ip   = ! empty( $notify_product_voted_ip ) ? $notify_product_voted_ip : array();

                    if ( ! empty( $notify_product ) && 'yes' === esc_attr( $notify_product ) ) {
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
                $status       = true;
                $type         = 'success';
                $product_id   = ! empty( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : 0;

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
         * Reset WooCommerce Auto Release settings,
         *
         * @since 1.0.0
         *
         * @param $product_id
         */
        public function reset_settings( $product_id ) {

            update_post_meta( $product_id, 'notify_product', '' );
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
         * @param false $loop
         *
         * @since 1.0.0
         */
        public function remove_add_to_cart_button( int $product_id = 0, string $product_type = '', bool $loop = false ) {
            if ( empty( $product_id ) || empty( $product_type ) || $product_id < 1 ) {
                return;
            }
            if ( 'simple' === $product_type && ! $loop ) {
                remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
            } else {
                remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
            }
        }

        public function add_notify_button() {
            global $product;
            if( !empty( $product->get_type() ) && 'simple' === $product->get_type() ){
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

            ob_start();

            if ( ! empty( $notify_product ) && 'yes' === $notify_product ) { ?>
                <div class="wc-auto-release-wrap" data-product-id="<?php echo $product_id; ?>">
                    <?php
                    echo '<input type="hidden" name="_nonce" value="' . esc_attr( wp_create_nonce( 'woo_auto_release' ) ) . '">';
                    $enable_show_total_votes = get_post_meta( $product_id, 'enable_show_total_votes', true );
                    ?>
                    <div class="voting-options-main-wrapper" id="voting_options_main_wrapper" >
                        <div class='voting-option-wrapper' id="voting_option_wrapper" >
                            <a href="javascript:void(0);" class="notify-product-button button" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-action="upvote" id="notify_product_voting_button"><?php echo apply_filters("upvote_button_label_html","<i class='far fa-thumbs-up'></i>"); ?></a>
                            <?php
                            if ( ! empty( $enable_show_total_votes ) && 'yes' === esc_attr( $enable_show_total_votes ) ) {
                                $notify_product_lead_count = get_post_meta( $product_id, 'notify_product_lead_count', true );
                                ?>
                                <span class="total-voting-numbers"><?php echo sprintf( _n( '<strong>%s</strong> Vote', '<strong>%s</strong> Votes', $notify_product_lead_count, 'text-domain' ), number_format_i18n( $notify_product_lead_count ) ); ?> </span>
                            <?php } ?>
                        </div>
                    </div>
                <?php
                    $enable_auto_release = get_post_meta( $product_id, 'enable_auto_release', true );
                    $auto_release_date   = get_post_meta( $product_id, 'auto_release_date', true );
                    if ( ! empty( $enable_auto_release ) && ! empty( $auto_release_date ) && strtotime( $auto_release_date ) > strtotime( 'now' ) ) { ?>
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
         * Add View product button
         *
         * @since 1.0.0
         */
        public function add_view_more_button() {

            global $product;

            if ( 'woocommerce_after_shop_loop_item' === current_filter() && ! empty( $product ) ) { /* Check loop product or not */
                $product_id = $product->get_id();
            }

            if ( empty( $product_id ) || $product_id < 1 ) {
                return;
            } ?>
            <a href='<?php echo esc_url( get_permalink( $product_id ) ); ?>' rel='nofollow' class='button product_type_simple'> <?php esc_attr_e( 'View product', 'woocommerce-product-auto-release-lite' ); ?> </a>
            <?php
        }

        public function wc_email_format_string( $string, $wc_email ) {

            if ( ! empty( $string ) && ! empty( $wc_email->product_id ) && $wc_email->product_id > 0 ) {

                $product_title = get_the_title( $wc_email->product_id );

                $string = str_replace( '{product_name}', $product_title, $string );
            }

            return $string;
        }

        public function upvote_button_label_html_cb( $html = '' ){
            $wpar_get_settings       = wpar_get_settings( 'general' );

            $upvote_button = !empty( $wpar_get_settings['upvote_button'] ) ? esc_attr( $wpar_get_settings['upvote_button'] ) : '';
            $upvote_icon = !empty( $wpar_get_settings['upvote_button_icon'] ) ? esc_attr( $wpar_get_settings['upvote_button_icon'] ) : '';
            $upvote_label = !empty( $wpar_get_settings['upvote_button_label'] ) ? esc_attr( $wpar_get_settings['upvote_button_label'] ) : '';
            $icon_html = "<i class='far fa-thumbs-up'></i>";
            if( !empty( $upvote_icon ) ){
                $icon_html = "<i class='$upvote_icon'></i>";
            }
            if( !empty( $upvote_button ) && 'both' === $upvote_button && !empty( $upvote_label ) ){
                $html = $icon_html.' '.$upvote_label;
            } elseif ( !empty( $upvote_button ) && 'icon' === $upvote_button && !empty( $upvote_icon ) ){
                $html = $icon_html;
            }elseif ( !empty( $upvote_button ) && 'text' === $upvote_button && !empty( $upvote_label ) ){
                $html = $upvote_label;
            }

            return $html;
        }

    }
}
