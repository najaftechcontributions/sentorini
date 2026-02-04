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

        // Normalize to arrays for consistent handling
        $tour_ids = is_array($data['tour_ids']) ? $data['tour_ids'] : [$data['tour_id']];
        $dates = is_array($data['dates']) ? $data['dates'] : [$data['date']];
        $destinations = isset($data['destinations']) && is_array($data['destinations']) ? $data['destinations'] : [];

        // Check availability for all tour-date combinations
        $availability = SBT_Availability::instance();
        foreach ($tour_ids as $tour_id) {
            foreach ($dates as $date) {
                $is_available = $availability->check_availability(
                    $tour_id,
                    $date,
                    $data['passengers']
                );

                if (!$is_available) {
                    $tour_title = get_the_title($tour_id);
                    return new WP_Error('not_available',
                        sprintf('Tour "%s" is not available on %s for %d passengers.',
                        $tour_title, $date, $data['passengers']));
                }
            }
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Reserve capacity for all tour-date combinations
            foreach ($tour_ids as $tour_id) {
                foreach ($dates as $date) {
                    $reserved = $availability->reserve_capacity(
                        $tour_id,
                        $date,
                        $data['passengers']
                    );

                    if (!$reserved) {
                        throw new Exception('Failed to reserve capacity for tour #' . $tour_id . ' on ' . $date);
                    }
                }
            }

            // Calculate total amount for all tours and dates
            // Formula: price × passengers × days (for per-person pricing)
            // or: price × days (for per-tour pricing)
            $total_amount = 0;
            $tour_titles = [];
            foreach ($tour_ids as $tour_id) {
                $tour_price = get_field('tour_price', $tour_id);
                $price_per_person = get_field('tour_price_per_person', $tour_id);
                $tour_titles[] = get_the_title($tour_id);

                // Calculate for this tour: price × days × passengers (if per person)
                if ($price_per_person) {
                    // Per day per passenger pricing
                    $total_amount += ($tour_price * $data['passengers'] * count($dates));
                } else {
                    // Per day pricing (flat rate)
                    $total_amount += ($tour_price * count($dates));
                }
            }

            // Generate confirmation code
            $confirmation_code = $this->generate_confirmation_code();

            // Create descriptive booking title
            $tour_summary = count($tour_titles) > 1
                ? count($tour_titles) . ' tours'
                : $tour_titles[0];

            $date_summary = count($dates) > 1
                ? count($dates) . ' days (' . $dates[0] . ' - ' . end($dates) . ')'
                : $dates[0];

            $destination_summary = !empty($destinations)
                ? ' → ' . implode(' → ', array_slice($destinations, 0, 2))
                : '';

            // Create booking post
            $booking_id = wp_insert_post([
                'post_type' => 'sbt_booking',
                'post_status' => 'publish',
                'post_title' => sprintf(
                    'Booking #%s - %s - %s%s',
                    $confirmation_code,
                    $tour_summary,
                    $date_summary,
                    $destination_summary
                )
            ]);

            if (is_wp_error($booking_id)) {
                throw new Exception('Failed to create booking');
            }

            // Save booking meta data
            update_field('booking_tours', $tour_ids, $booking_id); // Array of tour IDs
            update_field('booking_dates', $dates, $booking_id); // Array of dates
            update_field('booking_destinations', $destinations, $booking_id); // Array of destinations
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

            // Also save legacy single fields for backward compatibility
            update_field('booking_tour', $tour_ids[0], $booking_id);
            update_field('booking_date', $dates[0], $booking_id);

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

            // Schedule reminder email (24 hours before first tour)
            $first_tour_id = $tour_ids[0];
            $first_date = $dates[0];
            $departure_time = get_field('tour_departure_time', $first_tour_id) ?: '09:00';
            $tour_date = strtotime($first_date . ' ' . $departure_time);
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
                'status' => 'pending',
                'tour_count' => count($tour_ids),
                'date_count' => count($dates),
                'has_destinations' => !empty($destinations)
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
        // Check for required fields
        $required_fields = ['passengers', 'first_name', 'last_name', 'email', 'phone'];

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', sprintf('Missing required field: %s', $field));
            }
        }

        // Validate email
        if (!is_email($data['email'])) {
            return new WP_Error('invalid_email', 'Invalid email address');
        }

        // Normalize tour_ids and dates to arrays
        $tour_ids = [];
        if (!empty($data['tour_ids']) && is_array($data['tour_ids'])) {
            $tour_ids = $data['tour_ids'];
        } elseif (!empty($data['tour_id'])) {
            $tour_ids = [$data['tour_id']];
        }

        if (empty($tour_ids)) {
            return new WP_Error('missing_field', 'At least one tour must be selected');
        }

        $dates = [];
        if (!empty($data['dates']) && is_array($data['dates'])) {
            $dates = $data['dates'];
        } elseif (!empty($data['date'])) {
            $dates = [$data['date']];
        }

        if (empty($dates)) {
            return new WP_Error('missing_field', 'At least one date must be selected');
        }

        // Validate each tour
        foreach ($tour_ids as $tour_id) {
            $tour = get_post($tour_id);
            if (!$tour || $tour->post_type !== 'sbt_tour') {
                return new WP_Error('invalid_tour', 'Invalid tour ID: ' . $tour_id);
            }

            // Validate passenger count against capacity
            $max_capacity = get_field('tour_max_capacity', $tour_id);
            if ($data['passengers'] > $max_capacity) {
                return new WP_Error('exceeds_capacity', sprintf('Tour "%s" maximum capacity is %d passengers', get_the_title($tour_id), $max_capacity));
            }
        }

        // Validate each date
        $today = strtotime('today');
        $buffer_hours = get_option('sbt_booking_buffer_hours', 24);

        foreach ($dates as $date) {
            // Validate date is in the future
            if (strtotime($date) < $today) {
                return new WP_Error('invalid_date', 'Booking date must be in the future: ' . $date);
            }

            // Validate each tour on this date
            foreach ($tour_ids as $tour_id) {
                // Check if tour is available on this day of week
                $available_days = get_field('tour_available_days', $tour_id);
                $day_of_week = strtolower(date('l', strtotime($date)));

                if (is_array($available_days) && !empty($available_days) && !in_array($day_of_week, $available_days)) {
                    return new WP_Error('not_available_day', sprintf('Tour "%s" is not available on %s (%s)', get_the_title($tour_id), $date, ucfirst($day_of_week)));
                }

                // Check blackout dates
                $blackout_dates = get_field('tour_blackout_dates', $tour_id);
                if (is_array($blackout_dates)) {
                    foreach ($blackout_dates as $blackout) {
                        if (isset($blackout['blackout_date']) && $blackout['blackout_date'] === $date) {
                            return new WP_Error('blackout_date', sprintf('Tour "%s" is not available on %s (blocked date)', get_the_title($tour_id), $date));
                        }
                    }
                }

                // Check booking buffer
                $departure_time = get_field('tour_departure_time', $tour_id) ?: '09:00';
                $booking_cutoff = strtotime($date . ' ' . $departure_time) - ($buffer_hours * HOUR_IN_SECONDS);

                if (time() > $booking_cutoff) {
                    return new WP_Error('too_late', sprintf('Bookings for %s must be made at least %d hours in advance', $date, $buffer_hours));
                }
            }
        }

        // Validate consecutive dates if multi-date booking
        if (count($dates) > 1) {
            $sorted_dates = $dates;
            sort($sorted_dates);

            for ($i = 0; $i < count($sorted_dates) - 1; $i++) {
                $current_date = strtotime($sorted_dates[$i]);
                $next_date = strtotime($sorted_dates[$i + 1]);
                $diff_days = ($next_date - $current_date) / (60 * 60 * 24);

                if ($diff_days !== 1) {
                    return new WP_Error('non_consecutive_dates', 'Dates must be consecutive (no gaps allowed): ' . $sorted_dates[$i] . ' to ' . $sorted_dates[$i + 1]);
                }
            }
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
