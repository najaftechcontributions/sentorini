<?php
/**
 * Admin Dashboard for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Admin_Dashboard {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_sbt_export_bookings', [$this, 'export_bookings_csv']);
        add_filter('manage_sbt_booking_posts_columns', [$this, 'booking_columns']);
        add_action('manage_sbt_booking_posts_custom_column', [$this, 'booking_column_content'], 10, 2);
        add_filter('manage_sbt_tour_posts_columns', [$this, 'tour_columns']);
        add_action('manage_sbt_tour_posts_custom_column', [$this, 'tour_column_content'], 10, 2);

        // Add quick edit support for booking status
        add_action('quick_edit_custom_box', [$this, 'booking_quick_edit'], 10, 2);
        add_action('save_post_sbt_booking', [$this, 'save_booking_status_quick_edit']);
        add_action('admin_footer', [$this, 'booking_quick_edit_script']);

        // Make status column sortable and editable
        add_filter('manage_edit-sbt_booking_sortable_columns', [$this, 'booking_sortable_columns']);

        // AJAX handlers for booking actions
        add_action('wp_ajax_sbt_update_booking_status', [$this, 'ajax_update_booking_status']);
        add_action('wp_ajax_sbt_cancel_booking', [$this, 'ajax_cancel_booking']);
        add_action('wp_ajax_sbt_confirm_booking', [$this, 'ajax_confirm_booking']);
        add_action('wp_ajax_sbt_refund_booking', [$this, 'ajax_refund_booking']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Santorini Boat Tours',
            'Boat Tours',
            'manage_options',
            'santorini-boat-tours',
            [$this, 'dashboard_page'],
            'dashicons-palmtree',
            30
        );
        
        add_submenu_page(
            'santorini-boat-tours',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'santorini-boat-tours',
            [$this, 'dashboard_page']
        );
        
        add_submenu_page(
            'santorini-boat-tours',
            'All Bookings',
            'All Bookings',
            'manage_options',
            'edit.php?post_type=sbt_booking'
        );
        
        add_submenu_page(
            'santorini-boat-tours',
            'All Tours',
            'All Tours',
            'manage_options',
            'edit.php?post_type=sbt_tour'
        );
        
        add_submenu_page(
            'santorini-boat-tours',
            'Availability Calendar',
            'Calendar',
            'manage_options',
            'sbt-calendar',
            [$this, 'calendar_page']
        );
        
        add_submenu_page(
            'santorini-boat-tours',
            'Settings',
            'Settings',
            'manage_options',
            'sbt-settings',
            [$this, 'settings_page']
        );
    }
    
    public function dashboard_page() {
        global $wpdb;

        // Get stats
        $booking_counts = wp_count_posts('sbt_booking');
        $total_bookings = isset($booking_counts->publish) ? $booking_counts->publish : 0;
        $confirmed_bookings = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'sbt_booking'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'booking_status'
            AND pm.meta_value = 'confirmed'
        ");
        
        $pending_bookings = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'sbt_booking'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'booking_status'
            AND pm.meta_value = 'pending'
        ");
        
        $total_revenue = $wpdb->get_var("
            SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2))) FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
            WHERE p.post_type = 'sbt_booking'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'booking_total_amount'
            AND pm2.meta_key = 'booking_status'
            AND pm2.meta_value = 'confirmed'
        ");
        
        // Get recent bookings
        $recent_bookings = get_posts([
            'post_type' => 'sbt_booking',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        ?>
        <div class="wrap">
            <h1>Santorini Boat Tours Dashboard</h1>
            
            <div class="sbt-dashboard-stats">
                <div class="sbt-stat-card">
                    <div class="sbt-stat-icon" style="background: #dbeafe;">
                        <span class="dashicons dashicons-tickets-alt" style="color: #3b82f6; font-size: 32px;"></span>
                    </div>
                    <div class="sbt-stat-content">
                        <h3>Total Bookings</h3>
                        <p class="sbt-stat-number"><?php echo esc_html($total_bookings); ?></p>
                    </div>
                </div>
                
                <div class="sbt-stat-card">
                    <div class="sbt-stat-icon" style="background: #d1fae5;">
                        <span class="dashicons dashicons-yes-alt" style="color: #10b981; font-size: 32px;"></span>
                    </div>
                    <div class="sbt-stat-content">
                        <h3>Confirmed</h3>
                        <p class="sbt-stat-number"><?php echo esc_html($confirmed_bookings); ?></p>
                    </div>
                </div>
                
                <div class="sbt-stat-card">
                    <div class="sbt-stat-icon" style="background: #fef3c7;">
                        <span class="dashicons dashicons-clock" style="color: #f59e0b; font-size: 32px;"></span>
                    </div>
                    <div class="sbt-stat-content">
                        <h3>Pending Payment</h3>
                        <p class="sbt-stat-number"><?php echo esc_html($pending_bookings); ?></p>
                    </div>
                </div>
                
                <div class="sbt-stat-card">
                    <div class="sbt-stat-icon" style="background: #dcfce7;">
                        <span class="dashicons dashicons-money-alt" style="color: #059669; font-size: 32px;"></span>
                    </div>
                    <div class="sbt-stat-content">
                        <h3>Total Revenue</h3>
                        <p class="sbt-stat-number"><?php echo esc_html(get_option('sbt_currency_symbol', '€') . number_format($total_revenue, 2)); ?></p>
                        <p style="font-size: 12px; margin-top: 5px; color: var(--sbt-gray-600);">From confirmed bookings</p>
                    </div>
                </div>
            </div>
            
            <div class="sbt-dashboard-actions" style="margin: 30px 0;">
                <a href="<?php echo admin_url('post-new.php?post_type=sbt_tour'); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> Add New Tour
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=sbt_booking'); ?>" class="button button-large">
                    View All Bookings
                </a>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                    <input type="hidden" name="action" value="sbt_export_bookings">
                    <?php wp_nonce_field('sbt_export_bookings'); ?>
                    <button type="submit" class="button button-large">
                        <span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Export to CSV
                    </button>
                </form>
            </div>
            
            <h2>Recent Bookings</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Tour</th>
                        <th>Date</th>
                        <th>Passengers</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_bookings as $booking): 
                        $status = get_field('booking_status', $booking->ID);
                        $status_colors = [
                            'pending' => '#f59e0b',
                            'confirmed' => '#10b981',
                            'cancelled' => '#ef4444',
                            'completed' => '#6b7280',
                            'refunded' => '#8b5cf6'
                        ];
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html(get_field('booking_confirmation_code', $booking->ID)); ?></strong></td>
                        <td><?php echo esc_html(get_field('booking_customer_first_name', $booking->ID) . ' ' . get_field('booking_customer_last_name', $booking->ID)); ?></td>
                        <td><?php echo esc_html(get_the_title(get_field('booking_tour', $booking->ID))); ?></td>
                        <td><?php echo esc_html(date('M j, Y', strtotime(get_field('booking_date', $booking->ID)))); ?></td>
                        <td><?php echo esc_html(get_field('booking_passengers', $booking->ID)); ?></td>
                        <td><?php echo esc_html(get_option('sbt_currency_symbol', '€') . number_format(get_field('booking_total_amount', $booking->ID), 2)); ?></td>
                        <td>
                            <span style="background: <?php echo esc_attr($status_colors[$status] ?? '#6b7280'); ?>; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px; text-transform: uppercase;">
                                <?php echo esc_html($status); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public function calendar_page() {
        ?>
        <div class="wrap">
            <h1>Availability Calendar</h1>
            <div id="sbt-calendar-container"></div>
            <script>
                // Calendar will be rendered here by admin.js
            </script>
        </div>
        <?php
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Santorini Boat Tours Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('sbt_settings');
                do_settings_sections('sbt-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public function register_settings() {
        // General Settings
        add_settings_section(
            'sbt_general_settings',
            'General Settings',
            null,
            'sbt-settings'
        );

        // Payment requirement setting
        register_setting('sbt_settings', 'sbt_require_payment');
        add_settings_field(
            'sbt_require_payment',
            'Require Payment',
            [$this, 'render_checkbox_field'],
            'sbt-settings',
            'sbt_general_settings',
            [
                'key' => 'sbt_require_payment',
                'description' => 'If unchecked, bookings will be submitted for free without payment gateway'
            ]
        );

        $general_fields = [
            'sbt_currency' => 'Currency Code',
            'sbt_currency_symbol' => 'Currency Symbol',
            'sbt_booking_buffer_hours' => 'Booking Buffer (hours)',
            'sbt_admin_email' => 'Admin Email',
            'sbt_from_email' => 'From Email',
            'sbt_from_name' => 'From Name'
        ];

        foreach ($general_fields as $key => $label) {
            register_setting('sbt_settings', $key);
            add_settings_field($key, $label, [$this, 'render_text_field'], 'sbt-settings', 'sbt_general_settings', ['key' => $key]);
        }
        
        // Stripe Settings
        add_settings_section(
            'sbt_stripe_settings',
            'Stripe Payment Settings',
            null,
            'sbt-settings'
        );
        
        $stripe_fields = [
            'sbt_enable_stripe' => 'Enable Stripe',
            'sbt_stripe_publishable_key' => 'Publishable Key',
            'sbt_stripe_secret_key' => 'Secret Key',
            'sbt_stripe_webhook_secret' => 'Webhook Secret'
        ];
        
        foreach ($stripe_fields as $key => $label) {
            register_setting('sbt_settings', $key);
            $type = $key === 'sbt_enable_stripe' ? 'checkbox' : 'text';
            add_settings_field($key, $label, [$this, "render_{$type}_field"], 'sbt-settings', 'sbt_stripe_settings', ['key' => $key]);
        }
        
        // PayPal Settings
        add_settings_section(
            'sbt_paypal_settings',
            'PayPal Payment Settings',
            null,
            'sbt-settings'
        );
        
        $paypal_fields = [
            'sbt_enable_paypal' => 'Enable PayPal',
            'sbt_paypal_client_id' => 'Client ID',
            'sbt_paypal_secret' => 'Secret',
            'sbt_paypal_mode' => 'Mode (sandbox/live)'
        ];
        
        foreach ($paypal_fields as $key => $label) {
            register_setting('sbt_settings', $key);
            $type = $key === 'sbt_enable_paypal' ? 'checkbox' : 'text';
            add_settings_field($key, $label, [$this, "render_{$type}_field"], 'sbt-settings', 'sbt_paypal_settings', ['key' => $key]);
        }
        
        // reCAPTCHA Settings
        add_settings_section(
            'sbt_recaptcha_settings',
            'reCAPTCHA v3 Settings',
            null,
            'sbt-settings'
        );
        
        $recaptcha_fields = [
            'sbt_enable_recaptcha' => 'Enable reCAPTCHA',
            'sbt_recaptcha_site_key' => 'Site Key',
            'sbt_recaptcha_secret_key' => 'Secret Key'
        ];
        
        foreach ($recaptcha_fields as $key => $label) {
            register_setting('sbt_settings', $key);
            $type = strpos($key, 'enable') !== false ? 'checkbox' : 'text';
            add_settings_field($key, $label, [$this, "render_{$type}_field"], 'sbt-settings', 'sbt_recaptcha_settings', ['key' => $key]);
        }
    }
    
    public function render_text_field($args) {
        $key = $args['key'];
        $value = get_option($key, '');
        echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text">';
    }
    
    public function render_checkbox_field($args) {
        $key = $args['key'];
        $value = get_option($key, false);
        $description = isset($args['description']) ? $args['description'] : '';

        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr($key) . '" value="1" ' . checked(1, $value, false) . '>';
        if ($description) {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }
        echo '</label>';
    }
    
    public function export_bookings_csv() {
        check_admin_referer('sbt_export_bookings');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $bookings = get_posts([
            'post_type' => 'sbt_booking',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'Confirmation Code',
            'Customer Name',
            'Email',
            'Phone',
            'Tour',
            'Date',
            'Time',
            'Passengers',
            'Total Amount',
            'Status',
            'Payment Method',
            'Payment ID',
            'Booking Date'
        ]);
        
        foreach ($bookings as $booking) {
            $tour_id = get_field('booking_tour', $booking->ID);
            
            fputcsv($output, [
                get_field('booking_confirmation_code', $booking->ID),
                get_field('booking_customer_first_name', $booking->ID) . ' ' . get_field('booking_customer_last_name', $booking->ID),
                get_field('booking_customer_email', $booking->ID),
                get_field('booking_customer_phone', $booking->ID),
                get_the_title($tour_id),
                get_field('booking_date', $booking->ID),
                get_field('booking_time', $booking->ID),
                get_field('booking_passengers', $booking->ID),
                get_field('booking_total_amount', $booking->ID),
                get_field('booking_status', $booking->ID),
                get_field('booking_payment_method', $booking->ID),
                get_field('booking_payment_id', $booking->ID),
                get_the_date('Y-m-d H:i:s', $booking->ID)
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function booking_columns($columns) {
        return [
            'cb' => $columns['cb'],
            'confirmation_code' => 'Code',
            'customer' => 'Customer',
            'tour' => 'Tour',
            'date' => 'Date',
            'passengers' => 'Passengers',
            'amount' => 'Amount',
            'status' => 'Status'
        ];
    }
    
    public function booking_column_content($column, $post_id) {
        switch ($column) {
            case 'confirmation_code':
                echo '<strong>' . esc_html(get_field('booking_confirmation_code', $post_id)) . '</strong>';
                break;
            case 'customer':
                echo esc_html(get_field('booking_customer_first_name', $post_id) . ' ' . get_field('booking_customer_last_name', $post_id));
                echo '<br><small>' . esc_html(get_field('booking_customer_email', $post_id)) . '</small>';
                break;
            case 'tour':
                echo esc_html(get_the_title(get_field('booking_tour', $post_id)));
                break;
            case 'date':
                echo esc_html(date('M j, Y', strtotime(get_field('booking_date', $post_id))));
                break;
            case 'passengers':
                echo esc_html(get_field('booking_passengers', $post_id));
                break;
            case 'amount':
                $total = get_field('booking_total_amount', $post_id);
                $passengers = get_field('booking_passengers', $post_id);
                $dates = get_field('booking_dates', $post_id) ?: [get_field('booking_date', $post_id)];
                $num_days = count($dates);
                echo '<strong>' . esc_html(get_option('sbt_currency_symbol', '€') . number_format($total, 2)) . '</strong>';
                if ($num_days > 1 || $passengers > 1) {
                    echo '<br><small>' . $num_days . ' day' . ($num_days > 1 ? 's' : '') . ' × ' . $passengers . ' pax</small>';
                }
                break;
            case 'status':
                $status = get_field('booking_status', $post_id);
                $colors = [
                    'pending' => '#f59e0b',
                    'confirmed' => '#10b981',
                    'cancelled' => '#ef4444',
                    'completed' => '#6b7280',
                    'refunded' => '#8b5cf6'
                ];
                // Add data attribute for quick edit
                echo '<span class="sbt-booking-status-badge" data-status="' . esc_attr($status) . '" style="background: ' . esc_attr($colors[$status] ?? '#6b7280') . '; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px; text-transform: uppercase; cursor: pointer;" title="Click row actions to quick edit">' . esc_html($status) . '</span>';
                break;
        }
    }

    public function booking_quick_edit($column_name, $post_type) {
        if ($post_type !== 'sbt_booking' || $column_name !== 'status') {
            return;
        }

        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;

        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title">Booking Status</span>
                    <span class="input-text-wrap">
                        <select name="booking_status" style="width: 100%;">
                            <option value="">— No Change —</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }

    public function save_booking_status_quick_edit($post_id) {
        // Check if quick edit or bulk edit
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['booking_status']) && !empty($_POST['booking_status'])) {
            $status = sanitize_text_field($_POST['booking_status']);
            $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'refunded'];

            if (in_array($status, $allowed_statuses)) {
                update_field('booking_status', $status, $post_id);

                // Log the status change
                $current_user = wp_get_current_user();
                $log = get_post_meta($post_id, '_booking_log', true);
                if (!is_array($log)) {
                    $log = [];
                }
                $log[] = [
                    'timestamp' => current_time('mysql'),
                    'action' => 'status_changed',
                    'note' => 'Status changed to ' . $status . ' via quick edit',
                    'user_id' => $current_user->ID,
                    'user_name' => $current_user->display_name
                ];
                update_post_meta($post_id, '_booking_log', $log);
            }
        }
    }

    public function booking_quick_edit_script() {
        global $current_screen;

        if ($current_screen->post_type !== 'sbt_booking') {
            return;
        }

        ?>
        <script type="text/javascript">
        (function($) {
            // Populate quick edit fields with current values
            var $wp_inline_edit = inlineEditPost.edit;
            inlineEditPost.edit = function(id) {
                $wp_inline_edit.apply(this, arguments);

                var postId = 0;
                if (typeof(id) == 'object') {
                    postId = parseInt(this.getId(id));
                }

                if (postId > 0) {
                    var $row = $('#post-' + postId);
                    var status = $row.find('.sbt-booking-status-badge').data('status');

                    if (status) {
                        $('#edit-' + postId + ' select[name="booking_status"]').val(status);
                    }
                }
            };
        })(jQuery);
        </script>
        <?php
    }

    public function booking_sortable_columns($columns) {
        $columns['status'] = 'booking_status';
        $columns['date'] = 'booking_date';
        $columns['amount'] = 'booking_total_amount';
        return $columns;
    }
    
    public function tour_columns($columns) {
        $new_columns = [
            'cb' => $columns['cb'],
            'title' => 'Title',
            'tour_type' => 'Type',
            'capacity' => 'Capacity',
            'price' => 'Price',
            'date' => 'Date'
        ];
        return $new_columns;
    }
    
    public function tour_column_content($column, $post_id) {
        switch ($column) {
            case 'tour_type':
                echo esc_html(ucwords(str_replace('_', ' ', get_field('tour_type', $post_id))));
                break;
            case 'capacity':
                echo esc_html(get_field('tour_max_capacity', $post_id)) . ' passengers';
                break;
            case 'price':
                $price = get_field('tour_price', $post_id);
                $per_person = get_field('tour_price_per_person', $post_id);
                echo esc_html(get_option('sbt_currency_symbol', '€') . number_format($price, 2));
                echo $per_person ? ' <small>/day/person</small>' : ' <small>/day</small>';
                break;
        }
    }

    /**
     * AJAX handler for updating booking status
     */
    public function ajax_update_booking_status() {
        check_ajax_referer('sbt-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $booking_id = intval($_POST['booking_id']);
        $status = sanitize_text_field($_POST['status']);

        $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'refunded'];
        if (!in_array($status, $allowed_statuses)) {
            wp_send_json_error('Invalid status');
        }

        update_field('booking_status', $status, $booking_id);

        // Log the status change
        $current_user = wp_get_current_user();
        $log = get_post_meta($booking_id, '_booking_log', true);
        if (!is_array($log)) {
            $log = [];
        }
        $log[] = [
            'timestamp' => current_time('mysql'),
            'action' => 'status_changed',
            'note' => 'Status changed to ' . $status,
            'user_id' => $current_user->ID,
            'user_name' => $current_user->display_name
        ];
        update_post_meta($booking_id, '_booking_log', $log);

        wp_send_json_success([
            'message' => 'Status updated successfully',
            'status' => $status
        ]);
    }

    /**
     * AJAX handler for cancelling booking
     */
    public function ajax_cancel_booking() {
        check_ajax_referer('sbt-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $booking_id = intval($_POST['booking_id']);

        // Get booking details
        $tour_ids = get_field('booking_tours', $booking_id) ?: [get_field('booking_tour', $booking_id)];
        $dates = get_field('booking_dates', $booking_id) ?: [get_field('booking_date', $booking_id)];
        $passengers = get_field('booking_passengers', $booking_id);

        // Release capacity
        $availability = SBT_Availability::instance();
        foreach ($tour_ids as $tour_id) {
            foreach ($dates as $date) {
                $availability->release_capacity($tour_id, $date, $passengers);
            }
        }

        update_field('booking_status', 'cancelled', $booking_id);

        // Log the cancellation
        $current_user = wp_get_current_user();
        $log = get_post_meta($booking_id, '_booking_log', true);
        if (!is_array($log)) {
            $log = [];
        }
        $log[] = [
            'timestamp' => current_time('mysql'),
            'action' => 'cancelled',
            'note' => 'Booking cancelled by admin',
            'user_id' => $current_user->ID,
            'user_name' => $current_user->display_name
        ];
        update_post_meta($booking_id, '_booking_log', $log);

        wp_send_json_success('Booking cancelled successfully');
    }

    /**
     * AJAX handler for confirming booking
     */
    public function ajax_confirm_booking() {
        check_ajax_referer('sbt-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $booking_id = intval($_POST['booking_id']);

        update_field('booking_status', 'confirmed', $booking_id);

        // Log the confirmation
        $current_user = wp_get_current_user();
        $log = get_post_meta($booking_id, '_booking_log', true);
        if (!is_array($log)) {
            $log = [];
        }
        $log[] = [
            'timestamp' => current_time('mysql'),
            'action' => 'confirmed',
            'note' => 'Booking confirmed by admin',
            'user_id' => $current_user->ID,
            'user_name' => $current_user->display_name
        ];
        update_post_meta($booking_id, '_booking_log', $log);

        // Send confirmation email
        $email = SBT_Email_Notifications::instance();
        $email->send_booking_confirmed($booking_id);

        wp_send_json_success('Booking confirmed successfully');
    }

    /**
     * AJAX handler for refunding booking
     */
    public function ajax_refund_booking() {
        check_ajax_referer('sbt-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $booking_id = intval($_POST['booking_id']);

        // Get booking details
        $tour_ids = get_field('booking_tours', $booking_id) ?: [get_field('booking_tour', $booking_id)];
        $dates = get_field('booking_dates', $booking_id) ?: [get_field('booking_date', $booking_id)];
        $passengers = get_field('booking_passengers', $booking_id);

        // Release capacity
        $availability = SBT_Availability::instance();
        foreach ($tour_ids as $tour_id) {
            foreach ($dates as $date) {
                $availability->release_capacity($tour_id, $date, $passengers);
            }
        }

        update_field('booking_status', 'refunded', $booking_id);

        // Log the refund
        $current_user = wp_get_current_user();
        $log = get_post_meta($booking_id, '_booking_log', true);
        if (!is_array($log)) {
            $log = [];
        }
        $log[] = [
            'timestamp' => current_time('mysql'),
            'action' => 'refunded',
            'note' => 'Booking refunded by admin',
            'user_id' => $current_user->ID,
            'user_name' => $current_user->display_name
        ];
        update_post_meta($booking_id, '_booking_log', $log);

        wp_send_json_success('Booking refunded successfully');
    }
}
