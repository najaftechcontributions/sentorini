<?php
/**
 * Custom Post Types for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Post_Types {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('acf/init', [$this, 'register_acf_fields']);
    }
    
    public function register_post_types() {
        // Register Tours post type
        register_post_type('sbt_tour', [
            'labels' => [
                'name' => __('Tours', 'santorini-boat-tours'),
                'singular_name' => __('Tour', 'santorini-boat-tours'),
                'add_new' => __('Add New Tour', 'santorini-boat-tours'),
                'add_new_item' => __('Add New Tour', 'santorini-boat-tours'),
                'edit_item' => __('Edit Tour', 'santorini-boat-tours'),
                'new_item' => __('New Tour', 'santorini-boat-tours'),
                'view_item' => __('View Tour', 'santorini-boat-tours'),
                'search_items' => __('Search Tours', 'santorini-boat-tours'),
                'not_found' => __('No tours found', 'santorini-boat-tours'),
                'not_found_in_trash' => __('No tours found in trash', 'santorini-boat-tours')
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'tours'],
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'menu_icon' => 'dashicons-palmtree',
            'show_in_rest' => true,
            'capability_type' => 'post',
            'hierarchical' => false
        ]);
        
        // Register Bookings post type
        register_post_type('sbt_booking', [
            'labels' => [
                'name' => __('Bookings', 'santorini-boat-tours'),
                'singular_name' => __('Booking', 'santorini-boat-tours'),
                'add_new' => __('Add New Booking', 'santorini-boat-tours'),
                'add_new_item' => __('Add New Booking', 'santorini-boat-tours'),
                'edit_item' => __('Edit Booking', 'santorini-boat-tours'),
                'new_item' => __('New Booking', 'santorini-boat-tours'),
                'view_item' => __('View Booking', 'santorini-boat-tours'),
                'search_items' => __('Search Bookings', 'santorini-boat-tours'),
                'not_found' => __('No bookings found', 'santorini-boat-tours'),
                'not_found_in_trash' => __('No bookings found in trash', 'santorini-boat-tours')
            ],
            'public' => false,
            'show_ui' => true,
            'has_archive' => false,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_rest' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'capabilities' => [
                'create_posts' => 'manage_options'
            ],
            'map_meta_cap' => true
        ]);
    }
    
    public function register_taxonomies() {
        // Register Tour Type taxonomy
        register_taxonomy('sbt_tour_type', 'sbt_tour', [
            'labels' => [
                'name' => __('Tour Types', 'santorini-boat-tours'),
                'singular_name' => __('Tour Type', 'santorini-boat-tours'),
                'search_items' => __('Search Tour Types', 'santorini-boat-tours'),
                'all_items' => __('All Tour Types', 'santorini-boat-tours'),
                'edit_item' => __('Edit Tour Type', 'santorini-boat-tours'),
                'update_item' => __('Update Tour Type', 'santorini-boat-tours'),
                'add_new_item' => __('Add New Tour Type', 'santorini-boat-tours'),
                'new_item_name' => __('New Tour Type Name', 'santorini-boat-tours'),
                'menu_name' => __('Tour Types', 'santorini-boat-tours')
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'tour-type']
        ]);
    }
    
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        // Tour Fields
        acf_add_local_field_group([
            'key' => 'group_sbt_tour',
            'title' => 'Tour Details',
            'fields' => [
                [
                    'key' => 'field_tour_type',
                    'label' => 'Tour Type',
                    'name' => 'tour_type',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => [
                        'morning' => 'Morning Boat Tour',
                        'sunset' => 'Sunset Boat Tour',
                        'private' => 'Private Group Cruise',
                        'water_taxi_ios' => 'Water Taxi to Ios',
                        'water_taxi_mykonos' => 'Water Taxi to Mykonos'
                    ]
                ],
                [
                    'key' => 'field_tour_duration',
                    'label' => 'Duration (hours)',
                    'name' => 'tour_duration',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 5
                ],
                [
                    'key' => 'field_tour_max_capacity',
                    'label' => 'Maximum Capacity',
                    'name' => 'tour_max_capacity',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 20
                ],
                [
                    'key' => 'field_tour_price',
                    'label' => 'Price (EUR)',
                    'name' => 'tour_price',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                    'step' => 0.01
                ],
                [
                    'key' => 'field_tour_price_per_person',
                    'label' => 'Price is per person',
                    'name' => 'tour_price_per_person',
                    'type' => 'true_false',
                    'default_value' => 1
                ],
                [
                    'key' => 'field_tour_departure_time',
                    'label' => 'Departure Time',
                    'name' => 'tour_departure_time',
                    'type' => 'time_picker',
                    'required' => 0
                ],
                [
                    'key' => 'field_tour_departure_location',
                    'label' => 'Departure Location',
                    'name' => 'tour_departure_location',
                    'type' => 'text',
                    'default_value' => 'Santorini Port'
                ],
                [
                    'key' => 'field_tour_highlights',
                    'label' => 'Tour Highlights',
                    'name' => 'tour_highlights',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Highlight',
                    'sub_fields' => [
                        [
                            'key' => 'field_highlight_text',
                            'label' => 'Highlight',
                            'name' => 'highlight_text',
                            'type' => 'text'
                        ]
                    ]
                ],
                [
                    'key' => 'field_tour_included',
                    'label' => 'What\'s Included',
                    'name' => 'tour_included',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Item',
                    'sub_fields' => [
                        [
                            'key' => 'field_included_item',
                            'label' => 'Item',
                            'name' => 'included_item',
                            'type' => 'text'
                        ]
                    ]
                ],
                [
                    'key' => 'field_tour_gallery',
                    'label' => 'Tour Gallery',
                    'name' => 'tour_gallery',
                    'type' => 'gallery',
                    'return_format' => 'array'
                ],
                [
                    'key' => 'field_tour_available_days',
                    'label' => 'Available Days',
                    'name' => 'tour_available_days',
                    'type' => 'checkbox',
                    'choices' => [
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday'
                    ],
                    'default_value' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                ],
                [
                    'key' => 'field_tour_season_start',
                    'label' => 'Season Start Date',
                    'name' => 'tour_season_start',
                    'type' => 'date_picker',
                    'instructions' => 'First date this tour is available for booking',
                    'required' => 0,
                    'return_format' => 'Y-m-d',
                    'display_format' => 'd/m/Y'
                ],
                [
                    'key' => 'field_tour_season_end',
                    'label' => 'Season End Date',
                    'name' => 'tour_season_end',
                    'type' => 'date_picker',
                    'instructions' => 'Last date this tour is available for booking',
                    'required' => 0,
                    'return_format' => 'Y-m-d',
                    'display_format' => 'd/m/Y'
                ],
                [
                    'key' => 'field_tour_availability_ranges',
                    'label' => 'Custom Availability Ranges',
                    'name' => 'tour_availability_ranges',
                    'type' => 'repeater',
                    'instructions' => 'Add specific date ranges when this tour is available (optional)',
                    'layout' => 'row',
                    'button_label' => 'Add Date Range',
                    'sub_fields' => [
                        [
                            'key' => 'field_range_start',
                            'label' => 'Start Date',
                            'name' => 'range_start',
                            'type' => 'date_picker',
                            'return_format' => 'Y-m-d',
                            'display_format' => 'd/m/Y',
                            'required' => 1
                        ],
                        [
                            'key' => 'field_range_end',
                            'label' => 'End Date',
                            'name' => 'range_end',
                            'type' => 'date_picker',
                            'return_format' => 'Y-m-d',
                            'display_format' => 'd/m/Y',
                            'required' => 1
                        ],
                        [
                            'key' => 'field_range_notes',
                            'label' => 'Notes',
                            'name' => 'range_notes',
                            'type' => 'text',
                            'placeholder' => 'e.g., Summer Season, Peak Hours'
                        ]
                    ]
                ],
                [
                    'key' => 'field_tour_blackout_dates',
                    'label' => 'Blackout Dates',
                    'name' => 'tour_blackout_dates',
                    'type' => 'repeater',
                    'instructions' => 'Specific dates when this tour is NOT available',
                    'layout' => 'table',
                    'button_label' => 'Add Blackout Date',
                    'sub_fields' => [
                        [
                            'key' => 'field_blackout_date',
                            'label' => 'Date',
                            'name' => 'blackout_date',
                            'type' => 'date_picker',
                            'return_format' => 'Y-m-d'
                        ],
                        [
                            'key' => 'field_blackout_reason',
                            'label' => 'Reason',
                            'name' => 'blackout_reason',
                            'type' => 'text'
                        ]
                    ]
                ]
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sbt_tour'
                    ]
                ]
            ]
        ]);
        
        // Booking Fields
        acf_add_local_field_group([
            'key' => 'group_sbt_booking',
            'title' => 'Booking Details',
            'fields' => [
                [
                    'key' => 'field_booking_tour',
                    'label' => 'Tour',
                    'name' => 'booking_tour',
                    'type' => 'post_object',
                    'required' => 1,
                    'post_type' => ['sbt_tour'],
                    'return_format' => 'id'
                ],
                [
                    'key' => 'field_booking_date',
                    'label' => 'Booking Date',
                    'name' => 'booking_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'return_format' => 'Y-m-d'
                ],
                [
                    'key' => 'field_booking_time',
                    'label' => 'Booking Time',
                    'name' => 'booking_time',
                    'type' => 'time_picker',
                    'required' => 0
                ],
                [
                    'key' => 'field_booking_passengers',
                    'label' => 'Number of Passengers',
                    'name' => 'booking_passengers',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 1
                ],
                [
                    'key' => 'field_booking_customer_email',
                    'label' => 'Customer Email',
                    'name' => 'booking_customer_email',
                    'type' => 'email',
                    'required' => 1
                ],
                [
                    'key' => 'field_booking_customer_first_name',
                    'label' => 'First Name',
                    'name' => 'booking_customer_first_name',
                    'type' => 'text',
                    'required' => 1
                ],
                [
                    'key' => 'field_booking_customer_last_name',
                    'label' => 'Last Name',
                    'name' => 'booking_customer_last_name',
                    'type' => 'text',
                    'required' => 1
                ],
                [
                    'key' => 'field_booking_customer_phone',
                    'label' => 'Phone',
                    'name' => 'booking_customer_phone',
                    'type' => 'text',
                    'required' => 1
                ],
                [
                    'key' => 'field_booking_customer_country',
                    'label' => 'Country',
                    'name' => 'booking_customer_country',
                    'type' => 'text',
                    'required' => 0
                ],
                [
                    'key' => 'field_booking_special_requests',
                    'label' => 'Special Requests',
                    'name' => 'booking_special_requests',
                    'type' => 'textarea',
                    'required' => 0
                ],
                [
                    'key' => 'field_booking_status',
                    'label' => 'Booking Status',
                    'name' => 'booking_status',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => [
                        'pending' => 'Pending Payment',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                        'refunded' => 'Refunded'
                    ],
                    'default_value' => 'pending'
                ],
                [
                    'key' => 'field_booking_payment_method',
                    'label' => 'Payment Method',
                    'name' => 'booking_payment_method',
                    'type' => 'select',
                    'choices' => [
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal'
                    ]
                ],
                [
                    'key' => 'field_booking_payment_id',
                    'label' => 'Payment ID',
                    'name' => 'booking_payment_id',
                    'type' => 'text',
                    'readonly' => 1
                ],
                [
                    'key' => 'field_booking_total_amount',
                    'label' => 'Total Amount (EUR)',
                    'name' => 'booking_total_amount',
                    'type' => 'number',
                    'readonly' => 1,
                    'step' => 0.01
                ],
                [
                    'key' => 'field_booking_confirmation_code',
                    'label' => 'Confirmation Code',
                    'name' => 'booking_confirmation_code',
                    'type' => 'text',
                    'readonly' => 1
                ],
                [
                    'key' => 'field_booking_notes',
                    'label' => 'Admin Notes',
                    'name' => 'booking_notes',
                    'type' => 'textarea'
                ]
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'sbt_booking'
                    ]
                ]
            ]
        ]);
    }
}
