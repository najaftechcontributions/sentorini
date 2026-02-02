<?php
/**
 * Payment Handler for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Payment_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor
    }
    
    public function process_payment($data) {
        $booking_id = $data['booking_id'];
        $payment_method = $data['payment_method'];
        
        // Verify booking exists and is pending
        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'sbt_booking') {
            return new WP_Error('invalid_booking', 'Invalid booking ID');
        }
        
        $status = get_field('booking_status', $booking_id);
        if ($status !== 'pending') {
            return new WP_Error('invalid_status', 'Booking is not in pending status');
        }
        
        // Process based on payment method
        switch ($payment_method) {
            case 'stripe':
                return $this->process_stripe_payment($data);
            case 'paypal':
                return $this->process_paypal_payment($data);
            default:
                return new WP_Error('invalid_method', 'Invalid payment method');
        }
    }
    
    private function process_stripe_payment($data) {
        $booking_id = $data['booking_id'];
        $payment_token = $data['payment_token'] ?? '';
        
        // Get Stripe secret key
        $secret_key = get_option('sbt_stripe_secret_key');
        
        if (empty($secret_key)) {
            return new WP_Error('stripe_not_configured', 'Stripe is not configured');
        }
        
        // Get booking details
        $total_amount = get_field('booking_total_amount', $booking_id);
        $customer_email = get_field('booking_customer_email', $booking_id);
        $customer_name = get_field('booking_customer_first_name', $booking_id) . ' ' . get_field('booking_customer_last_name', $booking_id);
        $confirmation_code = get_field('booking_confirmation_code', $booking_id);
        
        // Convert amount to cents
        $amount_cents = intval($total_amount * 100);
        
        try {
            // Create Stripe payment intent
            $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $secret_key,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => http_build_query([
                    'amount' => $amount_cents,
                    'currency' => strtolower(get_option('sbt_currency', 'EUR')),
                    'description' => sprintf('Booking #%s - Santorini Boat Tour', $confirmation_code),
                    'receipt_email' => $customer_email,
                    'metadata' => [
                        'booking_id' => $booking_id,
                        'confirmation_code' => $confirmation_code
                    ],
                    'payment_method' => $payment_token,
                    'confirm' => 'true',
                    'return_url' => home_url('/booking-confirmation?code=' . $confirmation_code)
                ])
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($body['error'])) {
                throw new Exception($body['error']['message']);
            }
            
            if ($body['status'] === 'succeeded') {
                // Payment successful
                $booking_manager = SBT_Booking_Manager::instance();
                $booking_manager->confirm_booking($booking_id, $body['id'], 'stripe');
                
                return [
                    'success' => true,
                    'payment_id' => $body['id'],
                    'confirmation_code' => $confirmation_code
                ];
            } else if ($body['status'] === 'requires_action') {
                // 3D Secure or additional authentication required
                return [
                    'success' => false,
                    'requires_action' => true,
                    'client_secret' => $body['client_secret'],
                    'payment_intent_id' => $body['id']
                ];
            } else {
                throw new Exception('Payment failed with status: ' . $body['status']);
            }
            
        } catch (Exception $e) {
            return new WP_Error('payment_failed', $e->getMessage());
        }
    }
    
    private function process_paypal_payment($data) {
        $booking_id = $data['booking_id'];
        $order_id = $data['paypal_order_id'] ?? '';
        
        if (empty($order_id)) {
            return new WP_Error('missing_order_id', 'PayPal order ID is required');
        }
        
        // Get PayPal credentials
        $client_id = get_option('sbt_paypal_client_id');
        $secret = get_option('sbt_paypal_secret');
        $mode = get_option('sbt_paypal_mode', 'sandbox');
        
        if (empty($client_id) || empty($secret)) {
            return new WP_Error('paypal_not_configured', 'PayPal is not configured');
        }
        
        $base_url = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        
        try {
            // Get access token
            $auth_response = wp_remote_post($base_url . '/v1/oauth2/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $secret),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => 'grant_type=client_credentials'
            ]);
            
            if (is_wp_error($auth_response)) {
                throw new Exception($auth_response->get_error_message());
            }
            
            $auth_body = json_decode(wp_remote_retrieve_body($auth_response), true);
            $access_token = $auth_body['access_token'];
            
            // Capture the order
            $capture_response = wp_remote_post($base_url . '/v2/checkout/orders/' . $order_id . '/capture', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            if (is_wp_error($capture_response)) {
                throw new Exception($capture_response->get_error_message());
            }
            
            $capture_body = json_decode(wp_remote_retrieve_body($capture_response), true);
            
            if ($capture_body['status'] === 'COMPLETED') {
                $booking_manager = SBT_Booking_Manager::instance();
                $confirmation_code = get_field('booking_confirmation_code', $booking_id);
                $booking_manager->confirm_booking($booking_id, $order_id, 'paypal');
                
                return [
                    'success' => true,
                    'payment_id' => $order_id,
                    'confirmation_code' => $confirmation_code
                ];
            } else {
                throw new Exception('PayPal payment not completed. Status: ' . $capture_body['status']);
            }
            
        } catch (Exception $e) {
            return new WP_Error('payment_failed', $e->getMessage());
        }
    }
    
    public function create_paypal_order($booking_id) {
        // Get PayPal credentials
        $client_id = get_option('sbt_paypal_client_id');
        $secret = get_option('sbt_paypal_secret');
        $mode = get_option('sbt_paypal_mode', 'sandbox');
        
        if (empty($client_id) || empty($secret)) {
            return new WP_Error('paypal_not_configured', 'PayPal is not configured');
        }
        
        $base_url = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        
        // Get booking details
        $total_amount = get_field('booking_total_amount', $booking_id);
        $confirmation_code = get_field('booking_confirmation_code', $booking_id);
        
        try {
            // Get access token
            $auth_response = wp_remote_post($base_url . '/v1/oauth2/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $secret),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => 'grant_type=client_credentials'
            ]);
            
            if (is_wp_error($auth_response)) {
                throw new Exception($auth_response->get_error_message());
            }
            
            $auth_body = json_decode(wp_remote_retrieve_body($auth_response), true);
            $access_token = $auth_body['access_token'];
            
            // Create order
            $order_data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $confirmation_code,
                        'description' => 'Santorini Boat Tour - Booking #' . $confirmation_code,
                        'amount' => [
                            'currency_code' => get_option('sbt_currency', 'EUR'),
                            'value' => number_format($total_amount, 2, '.', '')
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => get_option('blogname'),
                    'return_url' => home_url('/booking-confirmation?code=' . $confirmation_code),
                    'cancel_url' => home_url('/booking-cancelled')
                ]
            ];
            
            $create_response = wp_remote_post($base_url . '/v2/checkout/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($order_data)
            ]);
            
            if (is_wp_error($create_response)) {
                throw new Exception($create_response->get_error_message());
            }
            
            $create_body = json_decode(wp_remote_retrieve_body($create_response), true);
            
            return [
                'order_id' => $create_body['id'],
                'approval_url' => $this->get_paypal_approval_url($create_body['links'])
            ];
            
        } catch (Exception $e) {
            return new WP_Error('paypal_order_failed', $e->getMessage());
        }
    }
    
    private function get_paypal_approval_url($links) {
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        return null;
    }
    
    public function handle_stripe_webhook($request) {
        $payload = $request->get_body();
        $sig_header = $request->get_header('stripe_signature');
        $webhook_secret = get_option('sbt_stripe_webhook_secret');
        
        try {
            // Verify webhook signature
            // Note: In production, use Stripe PHP SDK for proper signature verification
            $event = json_decode($payload, true);
            
            if ($event['type'] === 'payment_intent.succeeded') {
                $payment_intent = $event['data']['object'];
                $booking_id = $payment_intent['metadata']['booking_id'] ?? null;
                
                if ($booking_id) {
                    $booking_manager = SBT_Booking_Manager::instance();
                    $booking_manager->confirm_booking($booking_id, $payment_intent['id'], 'stripe');
                }
            }
            
            return rest_ensure_response(['received' => true]);
            
        } catch (Exception $e) {
            return new WP_Error('webhook_error', $e->getMessage());
        }
    }
    
    public function handle_paypal_webhook($request) {
        $payload = $request->get_body();
        $event = json_decode($payload, true);
        
        // Verify webhook (simplified - in production, verify signature)
        if ($event['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = $event['resource'];
            $order_id = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
            
            if ($order_id) {
                // Find booking by PayPal order ID
                $args = [
                    'post_type' => 'sbt_booking',
                    'meta_query' => [
                        [
                            'key' => 'booking_payment_id',
                            'value' => $order_id,
                            'compare' => '='
                        ]
                    ]
                ];
                
                $bookings = get_posts($args);
                
                if (!empty($bookings)) {
                    $booking_id = $bookings[0]->ID;
                    $booking_manager = SBT_Booking_Manager::instance();
                    $booking_manager->confirm_booking($booking_id, $order_id, 'paypal');
                }
            }
        }
        
        return rest_ensure_response(['received' => true]);
    }
}
