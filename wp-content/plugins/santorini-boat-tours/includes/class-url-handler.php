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
        // Add rewrite rules for virtual pages
        add_action('init', [$this, 'add_rewrite_rules']);

        // Handle template redirects for virtual pages
        add_action('template_redirect', [$this, 'handle_virtual_pages']);

        // Localize URL params for use in JavaScript
        add_action('wp_enqueue_scripts', [$this, 'localize_url_params']);

        // Add query vars
        add_filter('query_vars', [$this, 'add_query_vars']);
    }

    /**
     * Add rewrite rules for /tour and /book pages
     */
    public function add_rewrite_rules() {
        // Add rewrite rule for /tour page
        add_rewrite_rule('^tour/?$', 'index.php?sbt_page=tour', 'top');

        // Add rewrite rule for /book page
        add_rewrite_rule('^book/?$', 'index.php?sbt_page=book', 'top');

        // Add rewrite rule for /tours (archive)
        add_rewrite_rule('^tours/?$', 'index.php?sbt_page=tours', 'top');
    }

    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'sbt_page';
        $vars[] = 'id';
        return $vars;
    }

    /**
     * Handle virtual pages for tour and booking
     */
    public function handle_virtual_pages() {
        $sbt_page = get_query_var('sbt_page');

        if (empty($sbt_page)) {
            return;
        }

        switch ($sbt_page) {
            case 'tour':
                $this->render_tour_page();
                break;
            case 'book':
                $this->render_booking_page();
                break;
            case 'tours':
                $this->render_tours_archive_page();
                break;
        }
    }

    /**
     * Render the single tour page
     */
    private function render_tour_page() {
        $tour_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        if (!$tour_id) {
            wp_redirect(home_url('/tours'));
            exit;
        }

        // Verify tour exists
        $tour = get_post($tour_id);
        if (!$tour || $tour->post_type !== 'sbt_tour') {
            wp_redirect(home_url('/tours'));
            exit;
        }

        // Set page title
        add_filter('pre_get_document_title', function() use ($tour) {
            return get_the_title($tour->ID) . ' - ' . get_bloginfo('name');
        });

        // Get header
        get_header();

        // Render tour card shortcode
        echo '<div class="sbt-page-container">';
        echo do_shortcode('[sbt_single_tour id="' . $tour_id . '"]');
        echo '</div>';

        // Get footer
        get_footer();
        exit;
    }

    /**
     * Render the booking form page
     */
    private function render_booking_page() {
        $tour_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        // Set page title
        add_filter('pre_get_document_title', function() {
            return 'Book Your Tour - ' . get_bloginfo('name');
        });

        // Get header
        get_header();

        // Render booking form
        echo '<div class="sbt-page-container">';
        echo '<h1 class="sbt-page-title">Book Your Tour</h1>';

        if ($tour_id) {
            echo do_shortcode('[sbt_booking_form tour_id="' . $tour_id . '"]');
        } else {
            echo do_shortcode('[sbt_booking_form]');
        }

        echo '</div>';

        // Get footer
        get_footer();
        exit;
    }

    /**
     * Render the tours archive page
     */
    private function render_tours_archive_page() {
        // Set page title
        add_filter('pre_get_document_title', function() {
            return 'All Tours - ' . get_bloginfo('name');
        });

        // Get header
        get_header();

        // Render tour list
        echo '<div class="sbt-page-container">';
        echo '<h1 class="sbt-page-title">Our Tours</h1>';
        echo do_shortcode('[sbt_tour_archive show_filters="true"]');
        echo '</div>';

        // Get footer
        get_footer();
        exit;
    }
    
    public function localize_url_params() {
        // Get URL parameters from query string
        $params = [
            'tour' => isset($_GET['tour']) ? sanitize_text_field($_GET['tour']) : '',
            'tour_type' => isset($_GET['tour_type']) ? sanitize_text_field($_GET['tour_type']) : '',
            'id' => isset($_GET['id']) ? absint($_GET['id']) : 0,
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
