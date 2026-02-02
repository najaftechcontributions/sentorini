<?php
/**
 * URL Handler for Santorini Boat Tours
 * Handles URL parameters and pretty permalinks for tour filtering
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_URL_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_tour_filters']);
        add_action('wp_enqueue_scripts', [$this, 'localize_url_params']);
    }
    
    public function add_rewrite_rules() {
        // Add rewrite rules for tour destination filters
        // Example: /santorini-to-ios/ or /santorini-to-mykonos/
        add_rewrite_rule(
            '^santorini-to-ios/?$',
            'index.php?tour_filter=water_taxi_ios',
            'top'
        );
        
        add_rewrite_rule(
            '^santorini-to-mykonos/?$',
            'index.php?tour_filter=water_taxi_mykonos',
            'top'
        );
        
        add_rewrite_rule(
            '^morning-tour/?$',
            'index.php?tour_filter=morning',
            'top'
        );
        
        add_rewrite_rule(
            '^sunset-tour/?$',
            'index.php?tour_filter=sunset',
            'top'
        );
        
        add_rewrite_rule(
            '^private-cruise/?$',
            'index.php?tour_filter=private',
            'top'
        );
        
        // Generic tour filter
        add_rewrite_rule(
            '^book/([^/]+)/?$',
            'index.php?tour_filter=$matches[1]',
            'top'
        );
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'tour_filter';
        $vars[] = 'tour_date';
        $vars[] = 'tour_passengers';
        return $vars;
    }
    
    public function handle_tour_filters() {
        $tour_filter = get_query_var('tour_filter');
        
        if ($tour_filter) {
            // Set a transient or session variable to pass to booking widget
            set_transient('sbt_active_filter_' . session_id(), $tour_filter, HOUR_IN_SECONDS);
        }
    }
    
    public function localize_url_params() {
        // Get URL parameters
        $tour_filter = get_query_var('tour_filter');
        $tour_date = get_query_var('tour_date');
        $tour_passengers = get_query_var('tour_passengers');
        
        // Also check for query string parameters
        if (!$tour_filter && isset($_GET['tour'])) {
            $tour_filter = sanitize_text_field($_GET['tour']);
        }
        
        if (!$tour_date && isset($_GET['date'])) {
            $tour_date = sanitize_text_field($_GET['date']);
        }
        
        if (!$tour_passengers && isset($_GET['passengers'])) {
            $tour_passengers = absint($_GET['passengers']);
        }
        
        // Add to existing sbtData localization
        wp_add_inline_script('sbt-frontend', sprintf(
            'window.sbtUrlParams = %s;',
            json_encode([
                'tour_filter' => $tour_filter,
                'tour_date' => $tour_date,
                'tour_passengers' => $tour_passengers
            ])
        ), 'before');
    }
    
    public static function get_tour_url($tour_type) {
        $urls = [
            'water_taxi_ios' => home_url('/santorini-to-ios/'),
            'water_taxi_mykonos' => home_url('/santorini-to-mykonos/'),
            'morning' => home_url('/morning-tour/'),
            'sunset' => home_url('/sunset-tour/'),
            'private' => home_url('/private-cruise/')
        ];
        
        return $urls[$tour_type] ?? home_url('/tours/');
    }
    
    public static function get_booking_url($params = []) {
        $url = home_url('/book/');
        
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        return $url;
    }
}
