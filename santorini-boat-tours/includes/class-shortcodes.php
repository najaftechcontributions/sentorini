<?php
/**
 * Shortcodes for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Shortcodes {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_shortcodes']);
    }
    
    public function register_shortcodes() {
        add_shortcode('sbt_booking_widget', [$this, 'booking_widget']);
        add_shortcode('sbt_tour_list', [$this, 'tour_list']);
        add_shortcode('sbt_tour_card', [$this, 'tour_card']);
        add_shortcode('sbt_availability_calendar', [$this, 'availability_calendar']);
    }
    
    /**
     * Booking Widget Shortcode
     * Usage: [sbt_booking_widget]
     */
    public function booking_widget($atts) {
        $atts = shortcode_atts([
            'show_steps' => 'true'
        ], $atts);
        
        ob_start();
        ?>
        <div class="sbt-booking-widget">
            <?php if ($atts['show_steps'] === 'true'): ?>
            <div class="sbt-booking-steps">
                <div class="sbt-booking-step active" data-step="1">
                    <span class="sbt-step-number">1</span>
                    <span class="sbt-step-label">Select Tour</span>
                </div>
                <div class="sbt-booking-step" data-step="2">
                    <span class="sbt-step-number">2</span>
                    <span class="sbt-step-label">Choose Date</span>
                </div>
                <div class="sbt-booking-step" data-step="3">
                    <span class="sbt-step-number">3</span>
                    <span class="sbt-step-label">Passengers</span>
                </div>
                <div class="sbt-booking-step" data-step="4">
                    <span class="sbt-step-number">4</span>
                    <span class="sbt-step-label">Details</span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Step 1: Select Tour -->
            <div class="sbt-step-content" data-step="1">
                <h3>Select Your Tour</h3>
                <?php echo do_shortcode('[sbt_tour_list]'); ?>
                <button type="button" class="sbt-button sbt-step-next">Next</button>
            </div>
            
            <!-- Step 2: Choose Date -->
            <div class="sbt-step-content" data-step="2" style="display: none;">
                <h3>Choose Your Date</h3>
                <?php echo do_shortcode('[sbt_availability_calendar]'); ?>
                <div class="sbt-availability-message"></div>
                <button type="button" class="sbt-button sbt-button-secondary sbt-step-prev">Back</button>
                <button type="button" class="sbt-button sbt-step-next">Next</button>
            </div>
            
            <!-- Step 3: Passengers -->
            <div class="sbt-step-content" data-step="3" style="display: none;">
                <h3>Number of Passengers</h3>
                <div class="sbt-passenger-counter" data-max-capacity="20">
                    <button type="button" class="sbt-counter-btn" data-action="decrement" disabled>-</button>
                    <span class="sbt-counter-value">1</span>
                    <button type="button" class="sbt-counter-btn" data-action="increment">+</button>
                </div>
                <button type="button" class="sbt-button sbt-button-secondary sbt-step-prev">Back</button>
                <button type="button" class="sbt-button sbt-step-next">Next</button>
            </div>
            
            <!-- Step 4: Customer Details -->
            <div class="sbt-step-content" data-step="4" style="display: none;">
                <h3>Your Details</h3>
                
                <div class="sbt-booking-summary">
                    <h4>Booking Summary</h4>
                    <div class="sbt-summary-item">
                        <span>Tour:</span>
                        <span class="sbt-summary-tour">-</span>
                    </div>
                    <div class="sbt-summary-item">
                        <span>Date:</span>
                        <span class="sbt-summary-date">-</span>
                    </div>
                    <div class="sbt-summary-item">
                        <span>Passengers:</span>
                        <span class="sbt-summary-passengers">-</span>
                    </div>
                    <div class="sbt-summary-item sbt-summary-total-row">
                        <span>Total:</span>
                        <span class="sbt-summary-total">€0.00</span>
                    </div>
                </div>
                
                <form class="sbt-booking-form">
                    <div class="sbt-form-row">
                        <div class="sbt-form-group">
                            <label for="sbt-first-name">First Name *</label>
                            <input type="text" id="sbt-first-name" name="first_name" required>
                        </div>
                        <div class="sbt-form-group">
                            <label for="sbt-last-name">Last Name *</label>
                            <input type="text" id="sbt-last-name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="sbt-form-row">
                        <div class="sbt-form-group">
                            <label for="sbt-email">Email *</label>
                            <input type="email" id="sbt-email" name="email" required>
                        </div>
                        <div class="sbt-form-group">
                            <label for="sbt-phone">Phone *</label>
                            <input type="tel" id="sbt-phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="sbt-form-group">
                        <label for="sbt-country">Country</label>
                        <input type="text" id="sbt-country" name="country">
                    </div>
                    
                    <div class="sbt-form-group">
                        <label for="sbt-special-requests">Special Requests</label>
                        <textarea id="sbt-special-requests" name="special_requests" rows="3"></textarea>
                    </div>
                    
                    <div class="sbt-form-group">
                        <label for="sbt-payment-method">Payment Method *</label>
                        <select id="sbt-payment-method" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <?php if (get_option('sbt_enable_stripe')): ?>
                            <option value="stripe">Credit Card (Stripe)</option>
                            <?php endif; ?>
                            <?php if (get_option('sbt_enable_paypal')): ?>
                            <option value="paypal">PayPal</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="sbt-error-message" style="display: none;"></div>
                    
                    <button type="button" class="sbt-button sbt-button-secondary sbt-step-prev">Back</button>
                    <button type="submit" class="sbt-button sbt-submit-booking">Complete Booking</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Tour List Shortcode
     * Usage: [sbt_tour_list type="morning" columns="3"]
     */
    public function tour_list($atts) {
        $atts = shortcode_atts([
            'type' => '',
            'columns' => '2',
            'limit' => -1
        ], $atts);
        
        $args = [
            'post_type' => 'sbt_tour',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish'
        ];
        
        if (!empty($atts['type'])) {
            $args['meta_query'] = [
                [
                    'key' => 'tour_type',
                    'value' => $atts['type']
                ]
            ];
        }
        
        $tours = new WP_Query($args);
        
        if (!$tours->have_posts()) {
            return '<p>No tours available.</p>';
        }
        
        ob_start();
        ?>
        <div class="sbt-tour-list sbt-tour-columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php while ($tours->have_posts()): $tours->the_post(); ?>
                <div class="sbt-tour-option" data-tour-id="<?php echo get_the_ID(); ?>" data-tour-type="<?php echo esc_attr(get_field('tour_type')); ?>">
                    <input type="radio" name="tour" value="<?php echo get_the_ID(); ?>" id="tour-<?php echo get_the_ID(); ?>">
                    <label for="tour-<?php echo get_the_ID(); ?>">
                        <?php if (has_post_thumbnail()): ?>
                        <div class="sbt-tour-image">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                        <?php endif; ?>
                        <div class="sbt-tour-content">
                            <h4 class="sbt-tour-title"><?php the_title(); ?></h4>
                            <?php if (get_the_excerpt()): ?>
                            <p class="sbt-tour-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                            <?php endif; ?>
                            <div class="sbt-tour-meta">
                                <?php if (get_field('tour_duration')): ?>
                                <span class="sbt-tour-duration">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    <?php echo get_field('tour_duration'); ?> hours
                                </span>
                                <?php endif; ?>
                                <?php if (get_field('tour_price')): ?>
                                <span class="sbt-tour-price">
                                    €<?php echo number_format(get_field('tour_price'), 2); ?>
                                    <?php if (get_field('tour_price_per_person')): ?>
                                    <small>/person</small>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
    
    /**
     * Single Tour Card Shortcode
     * Usage: [sbt_tour_card id="123"]
     */
    public function tour_card($atts) {
        $atts = shortcode_atts([
            'id' => 0
        ], $atts);
        
        $tour_id = intval($atts['id']);
        if (!$tour_id) {
            return '<p>Invalid tour ID.</p>';
        }
        
        $tour = get_post($tour_id);
        if (!$tour || $tour->post_type !== 'sbt_tour') {
            return '<p>Tour not found.</p>';
        }
        
        ob_start();
        ?>
        <div class="sbt-tour-card">
            <?php if (has_post_thumbnail($tour_id)): ?>
            <div class="sbt-tour-card-image">
                <?php echo get_the_post_thumbnail($tour_id, 'large'); ?>
            </div>
            <?php endif; ?>
            
            <div class="sbt-tour-card-content">
                <h3><?php echo get_the_title($tour_id); ?></h3>
                
                <?php if (get_field('tour_price', $tour_id)): ?>
                <div class="sbt-tour-card-price">
                    €<?php echo number_format(get_field('tour_price', $tour_id), 2); ?>
                    <?php if (get_field('tour_price_per_person', $tour_id)): ?>
                    <small>/person</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="sbt-tour-card-description">
                    <?php echo wpautop($tour->post_content); ?>
                </div>
                
                <?php
                $highlights = get_field('tour_highlights', $tour_id);
                if ($highlights):
                ?>
                <div class="sbt-tour-card-highlights">
                    <h4>Highlights</h4>
                    <ul>
                        <?php foreach ($highlights as $highlight): ?>
                        <li><?php echo esc_html($highlight['highlight_text']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php
                $included = get_field('tour_included', $tour_id);
                if ($included):
                ?>
                <div class="sbt-tour-card-included">
                    <h4>What's Included</h4>
                    <ul>
                        <?php foreach ($included as $item): ?>
                        <li><?php echo esc_html($item['included_item']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <a href="<?php echo home_url('/book/?tour=' . get_field('tour_type', $tour_id)); ?>" class="sbt-button">
                    Book Now
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Availability Calendar Shortcode
     * Usage: [sbt_availability_calendar]
     */
    public function availability_calendar($atts) {
        ob_start();
        ?>
        <div class="sbt-calendar">
            <div class="sbt-calendar-header">
                <button type="button" class="sbt-calendar-nav sbt-calendar-prev">&lt;</button>
                <h4 class="sbt-calendar-title"></h4>
                <button type="button" class="sbt-calendar-nav sbt-calendar-next">&gt;</button>
            </div>
            <div class="sbt-calendar-grid"></div>
            <div class="sbt-calendar-legend">
                <span class="sbt-legend-item">
                    <span class="sbt-legend-box sbt-legend-available"></span> Available
                </span>
                <span class="sbt-legend-item">
                    <span class="sbt-legend-box sbt-legend-almost-full"></span> Almost Full
                </span>
                <span class="sbt-legend-item">
                    <span class="sbt-legend-box sbt-legend-blocked"></span> Unavailable
                </span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
