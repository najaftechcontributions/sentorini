<?php
/**
 * Booking Manager for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Booking_Manager {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    public function init() {
        // Hook for booking reminders
        add_action('sbt_send_booking_reminder', [$this, 'send_booking_reminder']);
    }
    
    public function create_booking($data) {
        global $wpdb;
        
        // Validate data
        $validation = $this->validate_booking_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Check availability
        $availability = SBT_Availability::instance();
        $is_available = $availability->check_availability(
            $data['tour_id'],
            $data['date'],
            $data['passengers']
        );
        
        if (!$is_available) {
            return new WP_Error('not_available', 'This tour is not available for the selected date and passenger count.');
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Reserve capacity
            $reserved = $availability->reserve_capacity(
                $data['tour_id'],
                $data['date'],
                $data['passengers']
            );
            
            if (!$reserved) {
                throw new Exception('Failed to reserve capacity');
            }
            
            // Calculate total amount
            $tour_price = get_field('tour_price', $data['tour_id']);
            $price_per_person = get_field('tour_price_per_person', $data['tour_id']);
            
            $total_amount = $price_per_person ? ($tour_price * $data['passengers']) : $tour_price;
            
            // Generate confirmation code
            $confirmation_code = $this->generate_confirmation_code();
            
            // Create booking post
            $booking_id = wp_insert_post([
                'post_type' => 'sbt_booking',
                'post_status' => 'publish',
                'post_title' => sprintf(
                    'Booking #%s - %s - %s',
                    $confirmation_code,
                    get_the_title($data['tour_id']),
                    $data['date']
                )
            ]);
            
            if (is_wp_error($booking_id)) {
                throw new Exception('Failed to create booking');
            }
            
            // Save booking meta data
            update_field('booking_tour', $data['tour_id'], $booking_id);
            update_field('booking_date', $data['date'], $booking_id);
            update_field('booking_passengers', $data['passengers'], $booking_id);
            update_field('booking_customer_email', $data['email'], $booking_id);
            update_field('booking_customer_first_name', $data['first_name'], $booking_id);
            update_field('booking_customer_last_name', $data['last_name'], $booking_id);
            update_field('booking_customer_phone', $data['phone'], $booking_id);
            update_field('booking_customer_country', $data['country'] ?? '', $booking_id);
            update_field('booking_special_requests', $data['special_requests'] ?? '', $booking_id);
            update_field('booking_status', 'pending', $booking_id);
            update_field('booking_total_amount', $total_amount, $booking_id);
            update_field('booking_confirmation_code', $confirmation_code, $booking_id);
            
            // Save or update customer
            $customer_id = $this->save_customer([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'country' => $data['country'] ?? ''
            ]);
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Schedule reminder email (24 hours before tour)
            $tour_date = strtotime($data['date'] . ' ' . get_field('tour_departure_time', $data['tour_id']));
            $reminder_time = $tour_date - (24 * HOUR_IN_SECONDS);
            
            if ($reminder_time > time()) {
                wp_schedule_single_event($reminder_time, 'sbt_send_booking_reminder', [$booking_id]);
            }
            
            // Send pending confirmation email
            $email_notifications = SBT_Email_Notifications::instance();
            $email_notifications->send_booking_pending($booking_id);
            
            return [
                'booking_id' => $booking_id,
                'confirmation_code' => $confirmation_code,
                'total_amount' => $total_amount,
                'status' => 'pending'
            ];
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('booking_failed', $e->getMessage());
        }
    }
    
    public function confirm_booking($booking_id, $payment_id, $payment_method) {
        // Update booking status
        update_field('booking_status', 'confirmed', $booking_id);
        update_field('booking_payment_id', $payment_id, $booking_id);
        update_field('booking_payment_method', $payment_method, $booking_id);
        
        // Send confirmation email
        $email_notifications = SBT_Email_Notifications::instance();
        $email_notifications->send_booking_confirmation($booking_id);
        
        // Log the confirmation
        $this->log_booking_action($booking_id, 'confirmed', 'Payment received via ' . $payment_method);
        
        return true;
    }
    
    public function cancel_booking($booking_id) {
        $booking = get_post($booking_id);
        
        if (!$booking || $booking->post_type !== 'sbt_booking') {
            return new WP_Error('not_found', 'Booking not found');
        }
        
        $status = get_field('booking_status', $booking_id);
        
        if ($status === 'cancelled') {
            return new WP_Error('already_cancelled', 'Booking is already cancelled');
        }
        
        // Release capacity
        $tour_id = get_field('booking_tour', $booking_id);
        $date = get_field('booking_date', $booking_id);
        $passengers = get_field('booking_passengers', $booking_id);
        
        $availability = SBT_Availability::instance();
        $availability->release_capacity($tour_id, $date, $passengers);
        
        // Update status
        update_field('booking_status', 'cancelled', $booking_id);
        
        // Send cancellation email
        $email_notifications = SBT_Email_Notifications::instance();
        $email_notifications->send_booking_cancellation($booking_id);
        
        // Log the cancellation
        $this->log_booking_action($booking_id, 'cancelled', 'Booking cancelled');
        
        return true;
    }
    
    public function send_booking_reminder($booking_id) {
        $status = get_field('booking_status', $booking_id);
        
        // Only send reminder for confirmed bookings
        if ($status !== 'confirmed') {
            return;
        }
        
        $email_notifications = SBT_Email_Notifications::instance();
        $email_notifications->send_booking_reminder($booking_id);
    }
    
    private function validate_booking_data($data) {
        $required_fields = ['tour_id', 'date', 'passengers', 'first_name', 'last_name', 'email', 'phone'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', sprintf('Missing required field: %s', $field));
            }
        }
        
        // Validate tour exists
        $tour = get_post($data['tour_id']);
        if (!$tour || $tour->post_type !== 'sbt_tour') {
            return new WP_Error('invalid_tour', 'Invalid tour ID');
        }
        
        // Validate date is in the future
        if (strtotime($data['date']) < strtotime('today')) {
            return new WP_Error('invalid_date', 'Booking date must be in the future');
        }
        
        // Validate passenger count
        $max_capacity = get_field('tour_max_capacity', $data['tour_id']);
        if ($data['passengers'] > $max_capacity) {
            return new WP_Error('exceeds_capacity', sprintf('Maximum capacity is %d passengers', $max_capacity));
        }
        
        // Validate email
        if (!is_email($data['email'])) {
            return new WP_Error('invalid_email', 'Invalid email address');
        }
        
        // Check if tour is available on this day of week
        $available_days = get_field('tour_available_days', $data['tour_id']);
        $day_of_week = strtolower(date('l', strtotime($data['date'])));
        
        if (!in_array($day_of_week, $available_days)) {
            return new WP_Error('not_available_day', 'Tour is not available on this day of the week');
        }
        
        // Check blackout dates
        $blackout_dates = get_field('tour_blackout_dates', $data['tour_id']);
        if (is_array($blackout_dates)) {
            foreach ($blackout_dates as $blackout) {
                if ($blackout['blackout_date'] === $data['date']) {
                    return new WP_Error('blackout_date', 'This date is not available');
                }
            }
        }
        
        // Check booking buffer
        $buffer_hours = get_option('sbt_booking_buffer_hours', 24);
        $booking_cutoff = strtotime($data['date'] . ' ' . get_field('tour_departure_time', $data['tour_id'])) - ($buffer_hours * HOUR_IN_SECONDS);
        
        if (time() > $booking_cutoff) {
            return new WP_Error('too_late', sprintf('Bookings must be made at least %d hours in advance', $buffer_hours));
        }
        
        return true;
    }
    
    private function generate_confirmation_code() {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Check if code already exists
        $args = [
            'post_type' => 'sbt_booking',
            'meta_query' => [
                [
                    'key' => 'booking_confirmation_code',
                    'value' => $code,
                    'compare' => '='
                ]
            ]
        ];
        
        $existing = get_posts($args);
        
        if (!empty($existing)) {
            return $this->generate_confirmation_code(); // Generate new code if duplicate
        }
        
        return $code;
    }
    
    private function save_customer($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_customers';
        
        // Check if customer exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE email = %s",
            $data['email']
        ));
        
        if ($existing) {
            // Update existing customer
            $wpdb->update(
                $table,
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'country' => $data['country']
                ],
                ['email' => $data['email']],
                ['%s', '%s', '%s', '%s'],
                ['%s']
            );
            
            return $existing->id;
        } else {
            // Insert new customer
            $wpdb->insert(
                $table,
                [
                    'email' => $data['email'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'country' => $data['country']
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );
            
            return $wpdb->insert_id;
        }
    }
    
    private function log_booking_action($booking_id, $action, $note = '') {
        $log = get_post_meta($booking_id, '_booking_log', true);
        
        if (!is_array($log)) {
            $log = [];
        }
        
        $log[] = [
            'timestamp' => current_time('mysql'),
            'action' => $action,
            'note' => $note,
            'user_id' => get_current_user_id()
        ];
        
        update_post_meta($booking_id, '_booking_log', $log);
    }
    
    public function get_booking_log($booking_id) {
        return get_post_meta($booking_id, '_booking_log', true) ?: [];
    }
}
