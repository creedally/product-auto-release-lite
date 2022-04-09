<?php

/**
 * Check WP_List_Table class exists or not.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if( ! class_exists('Voted_List_Table') ) {
    /**
     * Voted List Table class.
     *
     * @since 1.0.0
     */
    class Voted_List_Table extends WP_List_Table
    {

        /**
         * Prepare table list items.
         *
         * @since 1.0.0
         */
        public function prepare_items()
        {

            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();

            $data = $this->table_data();
            usort($data, array(&$this, 'sort_data'));

            $per_page = 20;

            $current_page = $this->get_pagenum();

            $total_items = count($data);

            $this->set_pagination_args(
                array(
                    'total_items' => $total_items,
                    'per_page' => $per_page,
                )
            );

            $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

            $this->_column_headers = array($columns, $hidden, $sortable);

            $this->items = $data;
        }

        /**
         * Get table columns.
         *
         * @return array Get columns.
         * @since 1.0.0
         *
         */
        public function get_columns()
        {

            return array(
                'product_title' => esc_html__('Product Title', 'product-auto-release-lite'),
                'ip_address' => esc_html__('IP Address', 'product-auto-release-lite'),
                'vote' => esc_html__('Vote', 'product-auto-release-lite'),
            );
        }

        /**
         * Get table hidden columns.
         *
         * @return array Get hidden columns.
         * @since 1.0.0
         *
         */
        public function get_hidden_columns()
        {

            return array();
        }

        /**
         * Get sortable columns.
         *
         * @return array Get sortable columns.
         * @since 1.0.0
         *
         */
        public function get_sortable_columns()
        {

            return array(
                'product_title' => array('product_title', false),
                'ip_address' => array('ip_address', false),
            );
        }

        /**
         * Get voted table data.
         *
         * @return array Table data.
         * @since 1.0.0
         *
         */
        private function table_data()
        {

            $data = array();

            $selected_id = !empty($_GET['product_id']) ? sanitize_text_field( $_GET['product_id'] ) : '';

            $product_args = array(
                'post_type' => array('product', 'product_variation'),
                'post_status' => array('pending', 'draft', 'future', 'trash', 'publish'),
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'notify_product_voted_ip',
                        'compare' => 'EXISTS',
                    ),
                ),
            );
            if (!empty($selected_id) && (int)$selected_id > 0) {
                $product_args['post__in'] = array($selected_id);
            }

            $newsletter_query = new WP_Query($product_args);

            if ($newsletter_query->have_posts()) {

                while ($newsletter_query->have_posts()) {
                    $newsletter_query->the_post();
                    $product_voter_ips = get_post_meta(get_the_ID(), 'notify_product_voted_ip', true);

                    $product_voter_ips = !empty($product_voter_ips) ? $product_voter_ips : array();

                    if (!empty($product_voter_ips) && is_array($product_voter_ips)) {
                        foreach ($product_voter_ips as $product_voter_ip) {
                            $data[] = array(
                                'product_title' => get_the_title(),
                                'ip_address' => $product_voter_ip,
                                'vote' => 1,
                            );
                        }
                    }
                }
            }

            return $data;
        }

        /**
         * Get table column item.
         *
         * @param array|object $item Get column item.
         * @param string $column_name Get column name.
         *
         * @return string
         * @since 1.0.0
         *
         */
        public function column_default($item, $column_name)
        {

            switch ($column_name) {
                case 'product_title':
                case 'ip_address':
                case 'vote':
                    return $item[$column_name];
                default:
                    return $item;
            }
        }

        /**
         * Table data sorting.
         *
         * @param $a
         * @param $b
         *
         * @return float|int
         * @since 1.0.0
         *
         */
        private function sort_data($a, $b)
        {

            $order_by = 'product_title';
            $order = 'desc';
            $order_by = !empty( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : $order_by;

            $order = !empty($_GET['order']) ? sanitize_text_field( $_GET['order'] ) : $order ;

            $result = strcmp($a[$order_by], $b[$order_by]);

            if ('asc' === $order) {
                return $result;
            }

            return -$result;
        }
    }
}