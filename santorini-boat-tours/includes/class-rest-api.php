<?php
/**
 * REST API Endpoints for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_REST_API {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes() {
        $namespace = 'sbt/v1';
        
        // Get all tours
        register_rest_route($namespace, '/tours', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tours'],
            'permission_callback' => '__return_true'
        ]);
        
        // Get single tour
        register_rest_route($namespace, '/tours/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_tour'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);
        
        // Check availability
        register_rest_route($namespace, '/availability', [
            'methods' => 'POST',
            'callback' => [$this, 'check_availability'],
            'permission_callback' => '__return_true',
            'args' => [
                'tour_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'date' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
                    }
                ],
                'passengers' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ]
        ]);
        
        // Get available dates for a tour
        register_rest_route($namespace, '/tours/(?P<id>\d+)/available-dates', [
            'methods' => 'GET',
            'callback' => [$this, 'get_available_dates'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'month' => [
                    'required' => false,
                    'validate_callback' => function($param) {
                        return preg_match('/^\d{4}-\d{2}$/', $param);
                    }
                ]
            ]
        ]);
        
        // Create booking
        register_rest_route($namespace, '/bookings', [
            'methods' => 'POST',
            'callback' => [$this, 'create_booking'],
            'permission_callback' => '__return_true',
            'args' => $this->get_booking_args()
        ]);
        
        // Get booking by confirmation code
        register_rest_route($namespace, '/bookings/(?P<code>[a-zA-Z0-9]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_booking_by_code'],
            'permission_callback' => '__return_true'
        ]);
        
        // Cancel booking
        register_rest_route($namespace, '/bookings/(?P<id>\d+)/cancel', [
            'methods' => 'POST',
            'callback' => [$this, 'cancel_booking'],
            'permission_callback' => function() {
                return current_user_can('manage_options') || $this->verify_booking_owner();
            }
        ]);
        
        // Process payment
        register_rest_route($namespace, '/payment/process', [
            'methods' => 'POST',
            'callback' => [$this, 'process_payment'],
            'permission_callback' => '__return_true',
            'args' => [
                'booking_id' => ['required' => true],
                'payment_method' => ['required' => true],
                'payment_token' => ['required' => false]
            ]
        ]);
        
        // Stripe webhook
        register_rest_route($namespace, '/webhooks/stripe', [
            'methods' => 'POST',
            'callback' => [$this, 'stripe_webhook'],
            'permission_callback' => '__return_true'
        ]);
        
        // PayPal webhook
        register_rest_route($namespace, '/webhooks/paypal', [
            'methods' => 'POST',
            'callback' => [$this, 'paypal_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    private function get_booking_args() {
        return [
            'tour_id' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param);
                },
                'sanitize_callback' => 'absint'
            ],
            'date' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
                },
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'passengers' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param > 0;
                },
                'sanitize_callback' => 'absint'
            ],
            'first_name' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'last_name' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'email' => [
                'required' => true,
                'validate_callback' => 'is_email',
                'sanitize_callback' => 'sanitize_email'
            ],
            'phone' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'country' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'special_requests' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_textarea_field'
            ],
            'recaptcha_token' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field'
            ]
        ];
    }
    
    public function get_tours($request) {
        $args = [
            'post_type' => 'sbt_tour',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ];
        
        // Filter by tour type if provided
        if ($request->get_param('type')) {
            $args['meta_query'] = [
                [
                    'key' => 'tour_type',
                    'value' => sanitize_text_field($request->get_param('type')),
                    'compare' => '='
                ]
            ];
        }
        
        $tours = get_posts($args);
        $result = [];
        
        foreach ($tours as $tour) {
            $result[] = $this->format_tour_data($tour->ID);
        }
        
        return rest_ensure_response($result);
    }
    
    public function get_tour($request) {
        $tour_id = $request->get_param('id');
        $tour = get_post($tour_id);
        
        if (!$tour || $tour->post_type !== 'sbt_tour') {
            return new WP_Error('not_found', 'Tour not found', ['status' => 404]);
        }
        
        return rest_ensure_response($this->format_tour_data($tour_id));
    }
    
    private function format_tour_data($tour_id) {
        $tour = get_post($tour_id);
        
        return [
            'id' => $tour_id,
            'title' => get_the_title($tour_id),
            'description' => get_the_content(null, false, $tour),
            'excerpt' => get_the_excerpt($tour),
            'featured_image' => get_the_post_thumbnail_url($tour_id, 'large'),
            'type' => get_field('tour_type', $tour_id),
            'duration' => get_field('tour_duration', $tour_id),
            'max_capacity' => get_field('tour_max_capacity', $tour_id),
            'price' => get_field('tour_price', $tour_id),
            'price_per_person' => get_field('tour_price_per_person', $tour_id),
            'departure_time' => get_field('tour_departure_time', $tour_id),
            'departure_location' => get_field('tour_departure_location', $tour_id),
            'highlights' => get_field('tour_highlights', $tour_id),
            'included' => get_field('tour_included', $tour_id),
            'gallery' => get_field('tour_gallery', $tour_id),
            'available_days' => get_field('tour_available_days', $tour_id),
            'slug' => $tour->post_name
        ];
    }
    
    public function check_availability($request) {
        $tour_id = $request->get_param('tour_id');
        $date = $request->get_param('date');
        $passengers = $request->get_param('passengers');
        
        $availability = SBT_Availability::instance();
        $is_available = $availability->check_availability($tour_id, $date, $passengers);
        
        if (!$is_available) {
            return rest_ensure_response([
                'available' => false,
                'message' => 'This tour is not available for the selected date and passenger count.'
            ]);
        }
        
        $remaining = $availability->get_remaining_capacity($tour_id, $date);
        
        return rest_ensure_response([
            'available' => true,
            'remaining_capacity' => $remaining,
            'almost_full' => $remaining <= 5
        ]);
    }
    
    public function get_available_dates($request) {
        $tour_id = $request->get_param('id');
        $month = $request->get_param('month');
        
        if (!$month) {
            $month = date('Y-m');
        }
        
        $availability = SBT_Availability::instance();
        $dates = $availability->get_available_dates($tour_id, $month);
        
        return rest_ensure_response($dates);
    }
    
    public function create_booking($request) {
        // Verify reCAPTCHA if enabled
        if (get_option('sbt_enable_recaptcha')) {
            $recaptcha_token = $request->get_param('recaptcha_token');
            if (!$this->verify_recaptcha($recaptcha_token)) {
                return new WP_Error('recaptcha_failed', 'reCAPTCHA verification failed', ['status' => 403]);
            }
        }
        
        // Rate limiting check
        if (!$this->check_rate_limit($request->get_param('email'))) {
            return new WP_Error('rate_limit', 'Too many booking attempts. Please try again later.', ['status' => 429]);
        }
        
        $booking_manager = SBT_Booking_Manager::instance();
        $result = $booking_manager->create_booking($request->get_params());
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response($result);
    }
    
    public function get_booking_by_code($request) {
        $code = $request->get_param('code');
        
        $args = [
            'post_type' => 'sbt_booking',
            'meta_query' => [
                [
                    'key' => 'booking_confirmation_code',
                    'value' => $code,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1
        ];
        
        $bookings = get_posts($args);
        
        if (empty($bookings)) {
            return new WP_Error('not_found', 'Booking not found', ['status' => 404]);
        }
        
        $booking = $bookings[0];
        
        return rest_ensure_response([
            'id' => $booking->ID,
            'tour' => get_field('booking_tour', $booking->ID),
            'date' => get_field('booking_date', $booking->ID),
            'passengers' => get_field('booking_passengers', $booking->ID),
            'status' => get_field('booking_status', $booking->ID),
            'confirmation_code' => get_field('booking_confirmation_code', $booking->ID),
            'total_amount' => get_field('booking_total_amount', $booking->ID)
        ]);
    }
    
    public function cancel_booking($request) {
        $booking_id = $request->get_param('id');
        $booking_manager = SBT_Booking_Manager::instance();
        
        $result = $booking_manager->cancel_booking($booking_id);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response(['success' => true, 'message' => 'Booking cancelled successfully']);
    }
    
    public function process_payment($request) {
        $payment_handler = SBT_Payment_Handler::instance();
        $result = $payment_handler->process_payment($request->get_params());
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response($result);
    }
    
    public function stripe_webhook($request) {
        $payment_handler = SBT_Payment_Handler::instance();
        return $payment_handler->handle_stripe_webhook($request);
    }
    
    public function paypal_webhook($request) {
        $payment_handler = SBT_Payment_Handler::instance();
        return $payment_handler->handle_paypal_webhook($request);
    }
    
    private function verify_recaptcha($token) {
        if (empty($token)) {
            return false;
        }
        
        $secret_key = get_option('sbt_recaptcha_secret_key');
        
        if (empty($secret_key)) {
            return true; // Skip if not configured
        }
        
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $token
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return isset($body['success']) && $body['success'] === true && $body['score'] >= 0.5;
    }
    
    private function check_rate_limit($email) {
        $transient_key = 'sbt_rate_limit_' . md5($email);
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            set_transient($transient_key, 1, HOUR_IN_SECONDS);
            return true;
        }
        
        if ($attempts >= 5) {
            return false;
        }
        
        set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
        return true;
    }
    
    private function verify_booking_owner() {
        // Additional verification can be added here
        return true;
    }
}
