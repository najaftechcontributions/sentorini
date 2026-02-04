<?php
/**
 * Plugin Name: Santorini Boat Tours Booking System
 * Plugin URI: https://santorini-boattours.com
 * Description: Custom booking system for boat tours with real-time availability, payment integration, and admin management
 * Version: 1.0.0
 * Author: Santorini Boat Tours
 * Text Domain: santorini-boat-tours
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SBT_VERSION', '1.0.0');
define('SBT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SBT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SBT_PLUGIN_FILE', __FILE__);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'SBT\\';
    $base_dir = SBT_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Include core files
require_once SBT_PLUGIN_DIR . 'includes/class-post-types.php';
require_once SBT_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once SBT_PLUGIN_DIR . 'includes/class-booking-manager.php';
require_once SBT_PLUGIN_DIR . 'includes/class-availability.php';
require_once SBT_PLUGIN_DIR . 'includes/class-payment-handler.php';
require_once SBT_PLUGIN_DIR . 'includes/class-email-notifications.php';
require_once SBT_PLUGIN_DIR . 'includes/class-admin-dashboard.php';
require_once SBT_PLUGIN_DIR . 'includes/class-url-handler.php';
require_once SBT_PLUGIN_DIR . 'includes/class-shortcodes.php';

/**
 * Main plugin class
 */
class Santorini_Boat_Tours {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        // Initialize post types early to ensure they exist
        add_action('plugins_loaded', [$this, 'init_post_types'], 5);
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function init_post_types() {
        // Initialize post types before everything else
        SBT_Post_Types::instance();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('santorini-boat-tours', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function init() {
        // Initialize REST API
        SBT_REST_API::instance();

        // Initialize booking manager
        SBT_Booking_Manager::instance();

        // Initialize availability system
        SBT_Availability::instance();

        // Initialize payment handler
        SBT_Payment_Handler::instance();

        // Initialize email notifications
        SBT_Email_Notifications::instance();

        // Initialize admin dashboard
        if (is_admin()) {
            SBT_Admin_Dashboard::instance();
        }

        // Initialize URL handler
        SBT_URL_Handler::instance();

        // Initialize shortcodes
        SBT_Shortcodes::instance();
    }
    
    public function enqueue_scripts() {
        // Enqueue frontend styles
        wp_enqueue_style(
            'sbt-frontend',
            SBT_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            SBT_VERSION
        );
        
        // Enqueue frontend scripts
        wp_enqueue_script(
            'sbt-frontend',
            SBT_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            SBT_VERSION,
            true
        );
        
        // Localize script with Ajax URL and nonce
        wp_localize_script('sbt-frontend', 'sbtData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('sbt/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'stripeKey' => get_option('sbt_stripe_publishable_key', ''),
            'recaptchaSiteKey' => get_option('sbt_recaptcha_site_key', '')
        ]);
    }
    
    public function admin_enqueue_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'santorini-boat-tours') === false && 
            get_post_type() !== 'sbt_tour' && 
            get_post_type() !== 'sbt_booking') {
            return;
        }
        
        wp_enqueue_style(
            'sbt-admin',
            SBT_PLUGIN_URL . 'assets/css/admin.css',
            [],
            SBT_VERSION
        );
        
        wp_enqueue_script(
            'sbt-admin',
            SBT_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'jquery-ui-datepicker'],
            SBT_VERSION,
            true
        );
        
        wp_localize_script('sbt-admin', 'sbtAdminData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sbt-admin-nonce')
        ]);
    }
    
    public function activate() {
        // Create custom tables
        $this->create_tables();

        // Set default options
        $this->set_default_options();

        // Initialize post types and URL handler before flushing
        $post_types = SBT_Post_Types::instance();
        // Manually trigger post type registration during activation
        $post_types->register_post_types();
        $post_types->register_taxonomies();

        SBT_URL_Handler::instance();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag for admin notice
        set_transient('sbt_activation_notice', true, 60);
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Availability table
        $availability_table = $wpdb->prefix . 'sbt_availability';
        $sql_availability = "CREATE TABLE IF NOT EXISTS $availability_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tour_id bigint(20) unsigned NOT NULL,
            tour_date date NOT NULL,
            booked_count int(11) NOT NULL DEFAULT 0,
            max_capacity int(11) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'available',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY tour_date_unique (tour_id, tour_date),
            KEY tour_id (tour_id),
            KEY tour_date (tour_date),
            KEY status (status)
        ) $charset_collate;";
        
        // Customer data table
        $customers_table = $wpdb->prefix . 'sbt_customers';
        $sql_customers = "CREATE TABLE IF NOT EXISTS $customers_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            phone varchar(50),
            country varchar(100),
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_availability);
        dbDelta($sql_customers);
    }
    
    private function set_default_options() {
        $defaults = [
            'sbt_currency' => 'EUR',
            'sbt_currency_symbol' => '€',
            'sbt_time_format' => '24h',
            'sbt_booking_buffer_hours' => 24,
            'sbt_enable_recaptcha' => false,
            'sbt_enable_stripe' => false,
            'sbt_enable_paypal' => false,
            'sbt_admin_email' => get_option('admin_email'),
            'sbt_from_email' => get_option('admin_email'),
            'sbt_from_name' => get_option('blogname')
        ];
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

// Initialize plugin
function sbt_init() {
    return Santorini_Boat_Tours::instance();
}

// Start the plugin
sbt_init();
