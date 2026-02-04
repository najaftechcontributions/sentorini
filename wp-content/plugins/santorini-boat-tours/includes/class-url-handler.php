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
        // Only localize URL params for use in JavaScript
        add_action('wp_enqueue_scripts', [$this, 'localize_url_params']);
    }
    
    public function localize_url_params() {
        // Get URL parameters from query string
        $params = [
            'tour' => isset($_GET['tour']) ? sanitize_text_field($_GET['tour']) : '',
            'tour_type' => isset($_GET['tour_type']) ? sanitize_text_field($_GET['tour_type']) : '',
            'tour_id' => isset($_GET['tour_id']) ? absint($_GET['tour_id']) : 0,
            'date' => isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '',
            'passengers' => isset($_GET['passengers']) ? absint($_GET['passengers']) : 1
        ];

        // Add to existing sbtData localization
        wp_add_inline_script('sbt-frontend', sprintf(
            'window.sbtUrlParams = %s;',
            json_encode($params)
        ), 'before');
    }
    
    /**
     * Helper function to build URLs with parameters
     */
    public static function build_url($base_url, $params = []) {
        if (!empty($params)) {
            return add_query_arg($params, $base_url);
        }
        return $base_url;
    }
}
