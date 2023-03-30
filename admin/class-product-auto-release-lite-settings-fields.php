<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check Woo_Product_Auto_Release_Lite_Admin_Fields class exists or not.
 */
if ( ! class_exists( 'Woo_Product_Auto_Release_Lite_Admin_Fields' ) ) {

	/**
	 * Add settings page content.
	 *
	 * @since 1.0.0
	 */
	class Woo_Product_Auto_Release_Lite_Admin_Fields extends Woo_Product_Auto_Release_Lite_Admin {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			add_action( 'wpar_lite_section_content_general', array( $this, 'general_settings' ) );
            add_action( 'wpar_lite_section_content_voted_lists', array( $this, 'voted_lists_settings' ) );
			add_action( 'wpar_lite_section_content_uninstall', array( $this, 'uninstall_settings' ) );
		}

		/**
		 * Add general settings.
		 *
		 * @since 1.0.0
		 */
		public function general_settings() {

			do_action( 'wpar_before_general_settings_content' );

			$wpar_get_settings       = wpar_get_settings( 'general' );
            $upvote_button = !empty( $wpar_get_settings['upvote_button'] ) ? $wpar_get_settings['upvote_button'] : 'icon';
			$upvote_button_icon     = ! empty( $wpar_get_settings['upvote_button_icon'] ) ? esc_attr( $wpar_get_settings['upvote_button_icon'] ) : '';
			$upvote_button_label     = ! empty( $wpar_get_settings['upvote_button_label'] ) ? esc_attr( $wpar_get_settings['upvote_button_label'] ) : '';
			?>
			<table class="wpar-option-table form-table">
				<tbody>
                    <tr>
                        <th><?php esc_attr_e( 'Select button label type', 'product-auto-release-lite' ); ?></th>
                        <td>
                            <label for="both"><input type="radio" name="upvote_button" <?php checked($upvote_button,'both'); ?> id="both" value='both' > <?php _e('Both', 'product-auto-release-lite'); ?></label>
                            <label for="icon"><input type="radio" name="upvote_button" <?php checked($upvote_button,'icon'); ?> id="icon" value='icon' > <?php _e('Icon', 'product-auto-release-lite'); ?> </label>
                            <label for="text"><input type="radio" name="upvote_button" <?php checked($upvote_button,'text'); ?> id="text" value='text' > <?php _e('Text', 'product-auto-release-lite'); ?> </label>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_attr_e( 'Button Icon', 'product-auto-release-lite' ); ?></th>
                        <td>
                            <input type="text" placeholder="<?php _e( 'far fa-thumbs-up', 'product-auto-release-lite' ); ?>" name="upvote_button_icon" class="regular-text upvote-button-icon" value="<?php esc_attr_e( $upvote_button_icon, 'product-auto-release-lite' ); ?>" >
                        </td>
                    </tr>
					<tr>
						<th><?php esc_attr_e( 'Button Text', 'product-auto-release-lite' ); ?></th>
						<td>
							<input type="text" placeholder="<?php _e( 'Add vote', 'product-auto-release-lite' ); ?>" name="upvote_button_label" class="regular-text" value="<?php esc_attr_e( $upvote_button_label, 'product-auto-release-lite' ); ?>" >
						</td>
					</tr>
				</tbody>
			</table>
			<?php
			do_action( 'wpar_after_general_settings_content' );
		}

        /**
         * Add voted lists settings.
         *
         * @since 1.0.0
         */
        public function voted_lists_settings() {

            do_action( 'wpar_before_voted_lists_settings_content' );

            $selected_id = ! empty( $_GET['product_id'] ) ? sanitize_text_field( $_GET['product_id'] ) : '';

            ?>
            <div class="wc-product-voted-lists-filter">
                <form method="get" id="wpar_form_main" action="" enctype="multipart/form-data">
                    <input type="hidden" name="page" value="product-auto-release">
                    <input type="hidden" name="tab" value="voted_lists">
                    <label>
                        <select name="product_id" id="product_id">
                            <option value=""><?php _e( 'Select product', 'product-auto-release-lite' ); ?></option>
                            <?php
                            $product_args = array(
                                'post_type'   => array( 'product', 'product_variation' ),
                                'post_status' => array( 'pending', 'draft', 'future', 'trash', 'publish' ),
                                'meta_query'  => array(
                                    'relation' => 'OR',
                                    array(
                                        'key'     => 'notify_product_voted_ip',
                                        'compare' => 'EXISTS',
                                    ),
                                ),
                            );

                            $voted_query = new WP_Query( $product_args );
                            if ( $voted_query->have_posts() ) {
                                while ( $voted_query->have_posts() ) {
                                    $voted_query->the_post();
                                    ?>
                                    <option <?php selected( $selected_id, get_the_ID() ); ?> value="<?php echo get_the_ID(); ?>"><?php echo get_the_title(); ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                        <input class="button button-primary" type="submit" name="submit" value="<?php _e( 'Search', 'product-auto-release-lite' ); ?>" />
                    </label>
                </form>
            </div>
            <div class="voted-list-table-wrap">
                <?php
                $voted_table = new Voted_List_Table();
                $voted_table->prepare_items();
                $voted_table->display();
                ?>
            </div>
            <?php

            do_action( 'wpar_after_voted_lists_settings_content' );

        }

		/**
		 * Add uninstall settings.
		 *
		 * @since 1.0.0
		 */
		public function uninstall_settings() {

			do_action( 'wpar_before_uninstall_settings_content' );

			$wpar_get_settings = wpar_get_settings( 'uninstall' );
			$remove_settings   = ! empty( $wpar_get_settings['remove_settings'] ) ? esc_attr( $wpar_get_settings['remove_settings'] ) : '';
			?>
			<table class="wpar-option-table form-table">
				<tbody>
					<tr>
						<th><label for="wpar_uninstall"><?php esc_attr_e( 'Remove data on uninstall?', 'product-auto-release-lite' ); ?></label></th>
						<td>
							<label>
								<input type="checkbox" <?php checked( $remove_settings, '1' ); ?> name="remove_settings" id="remove_settings" class="regular-text" value="1">
								<?php esc_attr_e( 'Check this box if you would like to remove all of data when the plugin is deleted.', 'product-auto-release-lite' ); ?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
			<?php

			do_action( 'wpar_after_uninstall_settings_content' );
		}
	}

	new Woo_Product_Auto_Release_Lite_Admin_Fields();
}
