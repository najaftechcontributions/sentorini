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
        $this->register_shortcodes();
    }
    
    public function register_shortcodes() {
        add_shortcode('sbt_booking_widget', [$this, 'booking_widget']);
        add_shortcode('sbt_booking_form', [$this, 'booking_widget']); // Alias
        add_shortcode('sbt_tour_list', [$this, 'tour_list']);
        add_shortcode('sbt_tour_archive', [$this, 'tour_list']); // Alias for archive
        add_shortcode('sbt_tour_card', [$this, 'tour_card']);
        add_shortcode('sbt_single_tour', [$this, 'tour_card']); // Alias
        add_shortcode('sbt_availability_calendar', [$this, 'availability_calendar']);
    }

    /**
     * Get URL parameter value
     */
    private function get_url_param($key, $default = '') {
        if (isset($_GET[$key])) {
            return sanitize_text_field($_GET[$key]);
        }
        return $default;
    }
    
    /**
     * Booking Widget Shortcode
     * Usage: [sbt_booking_widget] or [sbt_booking_form]
     * URL Parameters: ?id=123 or ?tour_id=123 or ?tour=morning&date=2024-01-15&passengers=2
     */
    public function booking_widget($atts) {
        $atts = shortcode_atts([
            'show_steps' => 'true',
            'tour' => '',
            'tour_id' => '',
            'date' => '',
            'passengers' => '1'
        ], $atts);

        // Get values from shortcode attributes or URL parameters
        if (empty($atts['tour'])) {
            $atts['tour'] = $this->get_url_param('tour');
        }
        if (empty($atts['tour_id'])) {
            // Check 'id' parameter first, then 'tour_id' for backward compatibility
            $atts['tour_id'] = $this->get_url_param('id', $this->get_url_param('tour_id'));
        }
        if (empty($atts['date'])) {
            $atts['date'] = $this->get_url_param('date');
        }
        if ($atts['passengers'] === '1') {
            $atts['passengers'] = $this->get_url_param('passengers', '1');
        }

        // Convert tour type to tour ID if provided
        $preselected_tour_id = 0;
        if (!empty($atts['tour_id'])) {
            $preselected_tour_id = intval($atts['tour_id']);
        } elseif (!empty($atts['tour'])) {
            // Find tour by tour_type meta
            $tours = get_posts([
                'post_type' => 'sbt_tour',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'tour_type',
                        'value' => $atts['tour']
                    ]
                ]
            ]);
            if (!empty($tours)) {
                $preselected_tour_id = $tours[0]->ID;
            }
        }

        ob_start();
        ?>
        <div class="sbt-booking-widget"
             data-preselect-tour="<?php echo esc_attr($preselected_tour_id); ?>"
             data-preselect-date="<?php echo esc_attr($atts['date']); ?>"
             data-preselect-passengers="<?php echo esc_attr($atts['passengers']); ?>">
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
            <div class="sbt-step-content sbt-step-section" data-step="1">
                <div class="sbt-step-header">
                    <h3>Select Your Tour</h3>
                    <p class="sbt-step-description">Choose one tour for your booking</p>
                </div>
                <div class="sbt-step-body">
                    <?php echo do_shortcode('[sbt_tour_list selection_mode="radio"]'); ?>
                </div>
                <div class="sbt-step-footer">
                    <button type="button" class="sbt-btn sbt-btn-primary sbt-step-next">Next</button>
                </div>
            </div>
            
            <!-- Step 2: Choose Date(s) -->
            <div class="sbt-step-content sbt-step-section" data-step="2" style="display: none;">
                <div class="sbt-step-header">
                    <h3>Choose Your Date(s)</h3>
                    <p class="sbt-step-description">Select one or multiple consecutive dates for your booking</p>
                </div>
                <div class="sbt-step-body">
                    <?php echo do_shortcode('[sbt_availability_calendar multi_select="consecutive"]'); ?>
                    <div class="sbt-availability-message"></div>
                    <div class="sbt-selected-dates-summary"></div>
                </div>
                <div class="sbt-step-footer">
                    <button type="button" class="sbt-btn sbt-btn-secondary sbt-step-prev">Back</button>
                    <button type="button" class="sbt-btn sbt-btn-primary sbt-step-next">Next</button>
                </div>
            </div>
            
            <!-- Step 3: Passengers & Destinations -->
            <div class="sbt-step-content sbt-step-section" data-step="3" style="display: none;">
                <div class="sbt-step-header">
                    <h3>Passengers & Destinations</h3>
                    <p class="sbt-step-description">Specify number of passengers and route</p>
                </div>
                <div class="sbt-step-body">
                    <div class="sbt-form-section">
                        <h4 class="sbt-form-section-title">Number of Passengers</h4>
                        <div class="sbt-passenger-counter" data-max-capacity="20">
                            <button type="button" class="sbt-counter-btn" data-action="decrement" disabled>-</button>
                            <span class="sbt-counter-value">1</span>
                            <button type="button" class="sbt-counter-btn" data-action="increment">+</button>
                        </div>
                    </div>

                    <div class="sbt-form-section sbt-destinations-section">
                        <h4 class="sbt-form-section-title">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                            </svg>
                            Multi-Destination Route
                        </h4>
                        <p class="sbt-section-description">Add stops along your route (e.g., Santorini → Ios → Mykonos)</p>

                        <div class="sbt-destinations-list">
                            <div class="sbt-destination-item">
                                <label class="sbt-form-label">Starting Point</label>
                                <input type="text" class="sbt-form-input sbt-destination-input" data-index="0" placeholder="e.g., Santorini" />
                            </div>
                        </div>

                        <button type="button" class="sbt-btn sbt-btn-secondary sbt-add-destination">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                            Add Stop
                        </button>
                    </div>
                </div>
                <div class="sbt-step-footer">
                    <button type="button" class="sbt-btn sbt-btn-secondary sbt-step-prev">Back</button>
                    <button type="button" class="sbt-btn sbt-btn-primary sbt-step-next">Next</button>
                </div>
            </div>
            
            <!-- Step 4: Customer Details & Summary -->
            <div class="sbt-step-content sbt-step-section" data-step="4" style="display: none;">
                <div class="sbt-booking-final-step">
                    <div class="sbt-booking-summary-card">
                        <h3 class="sbt-summary-title">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>
                            </svg>
                            Detailed Booking Summary
                        </h3>

                        <div class="sbt-summary-content">
                            <div class="sbt-summary-item">
                                <span class="sbt-summary-label">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                    </svg>
                                    Selected Tours
                                </span>
                                <span class="sbt-summary-value sbt-summary-tours">-</span>
                            </div>

                            <div class="sbt-summary-item">
                                <span class="sbt-summary-label">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                    </svg>
                                    Dates
                                </span>
                                <span class="sbt-summary-value sbt-summary-dates">-</span>
                            </div>

                            <div class="sbt-summary-item">
                                <span class="sbt-summary-label">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                        <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                                        <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                    </svg>
                                    Passengers
                                </span>
                                <span class="sbt-summary-value sbt-summary-passengers">-</span>
                            </div>

                            <div class="sbt-summary-item sbt-summary-destinations-row" style="display: none;">
                                <span class="sbt-summary-label">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                    </svg>
                                    Route
                                </span>
                                <span class="sbt-summary-value sbt-summary-destinations">-</span>
                            </div>

                            <div class="sbt-summary-divider"></div>

                            <div class="sbt-summary-item sbt-summary-total-row">
                                <span class="sbt-summary-label">Total Price</span>
                                <span class="sbt-summary-value sbt-summary-total">€0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="sbt-booking-form-card">
                        <h3 class="sbt-form-title">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            </svg>
                            Your Information
                        </h3>

                        <form class="sbt-booking-form" novalidate>
                            <div class="sbt-form-row">
                                <div class="sbt-form-group">
                                    <label for="sbt-first-name" class="sbt-form-label">
                                        First Name <span class="sbt-required">*</span>
                                    </label>
                                    <input type="text" id="sbt-first-name" name="first_name" class="sbt-form-input" required placeholder="John">
                                    <span class="sbt-field-error" style="display: none;">Please enter your first name</span>
                                </div>

                                <div class="sbt-form-group">
                                    <label for="sbt-last-name" class="sbt-form-label">
                                        Last Name <span class="sbt-required">*</span>
                                    </label>
                                    <input type="text" id="sbt-last-name" name="last_name" class="sbt-form-input" required placeholder="Doe">
                                    <span class="sbt-field-error" style="display: none;">Please enter your last name</span>
                                </div>
                            </div>

                            <div class="sbt-form-row">
                                <div class="sbt-form-group">
                                    <label for="sbt-email" class="sbt-form-label">
                                        Email Address <span class="sbt-required">*</span>
                                    </label>
                                    <input type="email" id="sbt-email" name="email" class="sbt-form-input" required placeholder="john.doe@example.com">
                                    <span class="sbt-field-error" style="display: none;">Please enter a valid email address</span>
                                </div>

                                <div class="sbt-form-group">
                                    <label for="sbt-phone" class="sbt-form-label">
                                        Phone Number <span class="sbt-required">*</span>
                                    </label>
                                    <input type="tel" id="sbt-phone" name="phone" class="sbt-form-input" required placeholder="+30 123 456 7890">
                                    <span class="sbt-field-error" style="display: none;">Please enter your phone number</span>
                                </div>
                            </div>

                            <div class="sbt-form-group">
                                <label for="sbt-country" class="sbt-form-label">
                                    Country
                                </label>
                                <input type="text" id="sbt-country" name="country" class="sbt-form-input" placeholder="Greece">
                            </div>

                            <div class="sbt-form-group">
                                <label for="sbt-special-requests" class="sbt-form-label">
                                    Special Requests or Dietary Requirements
                                </label>
                                <textarea id="sbt-special-requests" name="special_requests" class="sbt-form-textarea" rows="4" placeholder="Let us know if you have any special requests, dietary requirements, or accessibility needs..."></textarea>
                            </div>

                            <?php if (get_option('sbt_require_payment', true)): ?>
                            <div class="sbt-form-group">
                                <label for="sbt-payment-method" class="sbt-form-label">
                                    Payment Method <span class="sbt-required">*</span>
                                </label>
                                <div class="sbt-payment-methods">
                                    <?php if (get_option('sbt_enable_stripe')): ?>
                                    <label class="sbt-payment-option">
                                        <input type="radio" name="payment_method" value="stripe" required>
                                        <span class="sbt-payment-option-content">
                                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                                            </svg>
                                            <span>
                                                <strong>Credit/Debit Card</strong>
                                                <small>Secure payment via Stripe</small>
                                            </span>
                                        </span>
                                    </label>
                                    <?php endif; ?>

                                    <?php if (get_option('sbt_enable_paypal')): ?>
                                    <label class="sbt-payment-option">
                                        <input type="radio" name="payment_method" value="paypal" required>
                                        <span class="sbt-payment-option-content">
                                            <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M14.06 3.713c.12-1.071-.093-1.832-.702-2.526C12.628.356 11.312 0 9.626 0H4.734a.7.7 0 0 0-.691.59L2.005 13.509a.42.42 0 0 0 .415.486h2.756l-.202 1.28a.628.628 0 0 0 .62.726H8.14c.429 0 .793-.31.862-.731l.025-.13.48-3.043.03-.164.001-.007a.351.351 0 0 1 .348-.297h.38c1.266 0 2.425-.256 3.345-.91.379-.27.712-.603.993-.991a4.047 4.047 0 0 0 .88-2.195c.242-1.246.13-2.356-.57-3.154a2.687 2.687 0 0 0-.76-.59l-.094-.061zM6.543 8.82a.695.695 0 0 1 .321-.079H8.3c2.82 0 5.027-1.144 5.672-4.456l.003-.016c.217.124.4.27.548.438.546.623.679 1.535.45 2.71-.272 1.397-.866 2.307-1.663 2.874-.802.571-1.84.852-3.043.852h-.38a.873.873 0 0 0-.863.734l-.03.164-.48 3.043-.024.13-.001.004a.352.352 0 0 1-.348.296H5.595a.106.106 0 0 1-.105-.123l.208-1.32.845-5.214z"/>
                                            </svg>
                                            <span>
                                                <strong>PayPal</strong>
                                                <small>Pay with PayPal account</small>
                                            </span>
                                        </span>
                                    </label>
                                    <?php endif; ?>
                                </div>
                                <span class="sbt-field-error" style="display: none;">Please select a payment method</span>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="payment_method" value="free">
                            <div class="sbt-info-notice" style="padding: 16px; background: #dbeafe; border-left: 4px solid var(--sbt-primary); border-radius: 8px; margin: 20px 0;">
                                <p style="margin: 0; color: var(--sbt-gray-700); font-size: 15px;">
                                    <strong>Free Booking:</strong> No payment required. Your booking will be confirmed after submission.
                                </p>
                            </div>
                            <?php endif; ?>

                            <div class="sbt-form-group">
                                <label class="sbt-checkbox-label">
                                    <input type="checkbox" id="sbt-terms" name="terms" required>
                                    <span>I agree to the <a href="#" target="_blank">terms and conditions</a> and <a href="#" target="_blank">privacy policy</a> <span class="sbt-required">*</span></span>
                                </label>
                                <span class="sbt-field-error" style="display: none;">You must agree to the terms and conditions</span>
                            </div>

                            <div class="sbt-error-message" style="display: none;"></div>

                            <div class="sbt-form-actions">
                                <button type="button" class="sbt-btn sbt-btn-secondary sbt-step-prev">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                                    </svg>
                                    Back
                                </button>
                                <button type="submit" class="sbt-btn sbt-btn-primary sbt-btn-large sbt-submit-booking">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499L12.136.326zM5.562 3H13V1.78a.5.5 0 0 0-.621-.484L5.562 3zM1.5 4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-13z"/>
                                    </svg>
                                    <?php echo get_option('sbt_require_payment', true) ? 'Complete Booking & Pay' : 'Complete Booking'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Tour List Shortcode (Archive)
     * Usage: [sbt_tour_list type="morning" columns="3"] or [sbt_tour_archive]
     * URL Parameters: ?tour=morning or ?tour_type=sunset
     */
    public function tour_list($atts) {
        $atts = shortcode_atts([
            'type' => '',
            'columns' => '3',
            'limit' => -1,
            'show_filters' => 'true',
            'view_mode' => 'grid', // grid or list
            'selection_mode' => 'none' // none, radio, or checkbox
        ], $atts);

        // If no type from shortcode attribute, check URL parameters
        if (empty($atts['type'])) {
            $atts['type'] = $this->get_url_param('tour', $this->get_url_param('tour_type', ''));
        }

        $args = [
            'post_type' => 'sbt_tour',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish',
            'orderby' => 'menu_order title',
            'order' => 'ASC'
        ];

        // Filter by tour type if specified
        if (!empty($atts['type'])) {
            $args['meta_query'] = [
                [
                    'key' => 'tour_type',
                    'value' => $atts['type'],
                    'compare' => '='
                ]
            ];
        }

        $tours = new WP_Query($args);

        if (!$tours->have_posts()) {
            return '<div class="sbt-no-tours"><p>No tours available at the moment. Please check back later!</p></div>';
        }

        // Get all tour types for filter
        $tour_types = [];
        if ($atts['show_filters'] === 'true') {
            $all_tours = get_posts([
                'post_type' => 'sbt_tour',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ]);
            foreach ($all_tours as $tour) {
                $type = get_field('tour_type', $tour->ID);
                if ($type && !isset($tour_types[$type])) {
                    $tour_types[$type] = ucwords(str_replace('_', ' ', $type));
                }
            }
        }

        ob_start();
        ?>
        <div class="sbt-tour-archive">
            <?php if (!empty($tour_types) && $atts['show_filters'] === 'true'): ?>
            <div class="sbt-tour-archive-header">
                <div class="sbt-tour-count">
                    <span class="sbt-count-number"><?php echo $tours->found_posts; ?></span>
                    <span class="sbt-count-label">tour<?php echo $tours->found_posts !== 1 ? 's' : ''; ?> available</span>
                </div>

                <div class="sbt-tour-filters">
                    <div class="sbt-filter-group">
                        <label for="sbt-tour-filter" class="sbt-filter-label">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                            Filter by:
                        </label>
                        <select id="sbt-tour-filter" class="sbt-tour-type-filter">
                            <option value="">All Tours</option>
                            <?php foreach ($tour_types as $type_value => $type_label): ?>
                            <option value="<?php echo esc_attr($type_value); ?>" <?php selected($atts['type'], $type_value); ?>>
                                <?php echo esc_html($type_label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="sbt-tours-grid sbt-tours-columns-<?php echo esc_attr($atts['columns']); ?> sbt-view-<?php echo esc_attr($atts['view_mode']); ?> <?php echo $atts['selection_mode'] !== 'none' ? 'sbt-tour-selection' : ''; ?>">
                <?php while ($tours->have_posts()): $tours->the_post();
                    $tour_id = get_the_ID();
                    $tour_type = get_field('tour_type', $tour_id);
                    $duration = get_field('tour_duration', $tour_id);
                    $max_capacity = get_field('tour_max_capacity', $tour_id);
                    $price = get_field('tour_price', $tour_id);
                    $price_per_person = get_field('tour_price_per_person', $tour_id);
                    $departure_location = get_field('tour_departure_location', $tour_id);

                    $card_classes = 'sbt-tour-card-archive';
                    if ($atts['selection_mode'] !== 'none') {
                        $card_classes .= ' sbt-tour-option';
                    }
                ?>
                    <article class="<?php echo $card_classes; ?>" data-tour-id="<?php echo $tour_id; ?>" data-tour-type="<?php echo esc_attr($tour_type); ?>" data-tour-price="<?php echo esc_attr($price); ?>" data-price-per-person="<?php echo $price_per_person ? 'true' : 'false'; ?>">
                        <?php if ($atts['selection_mode'] === 'checkbox'): ?>
                        <div class="sbt-tour-checkbox-wrapper">
                            <input type="checkbox" name="selected_tours[]" value="<?php echo $tour_id; ?>" class="sbt-tour-checkbox" id="tour-<?php echo $tour_id; ?>" />
                            <label for="tour-<?php echo $tour_id; ?>" class="sbt-tour-checkbox-label"></label>
                        </div>
                        <?php elseif ($atts['selection_mode'] === 'radio'): ?>
                        <div class="sbt-tour-radio-wrapper">
                            <input type="radio" name="selected_tour" value="<?php echo $tour_id; ?>" class="sbt-tour-radio" id="tour-<?php echo $tour_id; ?>" />
                            <label for="tour-<?php echo $tour_id; ?>" class="sbt-tour-radio-label"></label>
                        </div>
                        <?php endif; ?>

                        <?php if (has_post_thumbnail()): ?>
                        <div class="sbt-tour-card-image">
                            <?php if ($atts['selection_mode'] === 'none'): ?>
                            <a href="<?php echo esc_url(home_url('/tour/?id=' . $tour_id)); ?>">
                                <?php the_post_thumbnail('large'); ?>
                            </a>
                            <?php else: ?>
                                <?php the_post_thumbnail('large'); ?>
                            <?php endif; ?>
                            <?php if ($tour_type): ?>
                            <span class="sbt-tour-type-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $tour_type))); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="sbt-tour-card-content">
                            <h3 class="sbt-tour-card-title">
                                <a href="<?php echo esc_url(home_url('/tour/?id=' . $tour_id)); ?>"><?php the_title(); ?></a>
                            </h3>

                            <?php if (get_the_excerpt()): ?>
                            <p class="sbt-tour-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                            <?php endif; ?>

                            <div class="sbt-tour-card-meta-grid">
                                <?php if ($duration): ?>
                                <div class="sbt-meta-item">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                    <span><?php echo esc_html($duration); ?> hours</span>
                                </div>
                                <?php endif; ?>

                                <?php if ($max_capacity): ?>
                                <div class="sbt-meta-item">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                        <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                                        <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                    </svg>
                                    <span>Up to <?php echo esc_html($max_capacity); ?></span>
                                </div>
                                <?php endif; ?>

                                <?php if ($departure_location): ?>
                                <div class="sbt-meta-item">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                    </svg>
                                    <span><?php echo esc_html($departure_location); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="sbt-tour-card-footer">
                                <?php if ($price): ?>
                                <div class="sbt-tour-card-price-block">
                                    <span class="sbt-price-label">From</span>
                                    <span class="sbt-price-amount">€<?php echo number_format($price, 0); ?></span>
                                    <?php if ($price_per_person): ?>
                                    <span class="sbt-price-per">/person</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <div class="sbt-tour-card-actions">
                                    <a href="<?php echo esc_url(home_url('/tour/?id=' . $tour_id)); ?>" class="sbt-btn sbt-btn-primary sbt-btn-tour-detail" target="_blank">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                                        </svg>
                                        Details
                                    </a>
                                    <?php if ($atts['selection_mode'] === 'none'): ?>
                                    <a href="<?php echo esc_url(home_url('/book/?id=' . $tour_id)); ?>" class="sbt-btn sbt-btn-secondary">
                                        Book Now
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }   
    
    /**
     * Single Tour Card Shortcode
     * Usage: [sbt_tour_card id="123"] or [sbt_single_tour]
     * URL Parameters: ?id=123 or ?tour_id=123 or ?tour=morning
     */
    public function tour_card($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'tour_type' => ''
        ], $atts);

        $tour_id = intval($atts['id']);

        // If no tour_id from shortcode attribute, check URL parameters
        if (!$tour_id) {
            // Check for 'id' parameter first, then 'tour_id' for backward compatibility
            $tour_id = intval($this->get_url_param('id', $this->get_url_param('tour_id', 0)));
        }

        // If still no tour_id, check for tour_type
        if (!$tour_id) {
            $tour_type = !empty($atts['tour_type']) ? $atts['tour_type'] : $this->get_url_param('tour', '');

            if (!empty($tour_type)) {
                $tours = get_posts([
                    'post_type' => 'sbt_tour',
                    'posts_per_page' => 1,
                    'meta_query' => [
                        [
                            'key' => 'tour_type',
                            'value' => $tour_type
                        ]
                    ]
                ]);
                if (!empty($tours)) {
                    $tour_id = $tours[0]->ID;
                }
            }
        }

        if (!$tour_id) {
            return '<p>No tour specified. Please provide a tour_id or tour parameter.</p>';
        }

        $tour = get_post($tour_id);
        if (!$tour || $tour->post_type !== 'sbt_tour') {
            return '<p>Tour not found.</p>';
        }

        // Get tour fields
        $tour_type = get_field('tour_type', $tour_id);
        $duration = get_field('tour_duration', $tour_id);
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        $price = get_field('tour_price', $tour_id);
        $price_per_person = get_field('tour_price_per_person', $tour_id);
        $departure_time = get_field('tour_departure_time', $tour_id);
        $departure_location = get_field('tour_departure_location', $tour_id);
        $highlights = get_field('tour_highlights', $tour_id);
        $included = get_field('tour_included', $tour_id);
        $gallery = get_field('tour_gallery', $tour_id);

        ob_start();
        ?>
        <div class="sbt-single-tour-detail">
            <!-- Tour Header -->
            <div class="sbt-tour-header">
                <?php if (has_post_thumbnail($tour_id)): ?>
                <div class="sbt-tour-hero-image">
                    <?php echo get_the_post_thumbnail($tour_id, 'full'); ?>
                    <div class="sbt-tour-hero-overlay">
                        <h1 class="sbt-tour-hero-title"><?php echo get_the_title($tour_id); ?></h1>
                        <?php if ($tour_type): ?>
                        <span class="sbt-tour-type-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $tour_type))); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="sbt-tour-main-content">
                <!-- Quick Info Bar -->
                <div class="sbt-tour-quick-info">
                    <?php if ($duration): ?>
                    <div class="sbt-quick-info-item">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                        </svg>
                        <span><strong>Duration:</strong> <?php echo esc_html($duration); ?> hours</span>
                    </div>
                    <?php endif; ?>

                    <?php if ($max_capacity): ?>
                    <div class="sbt-quick-info-item">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                            <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                        </svg>
                        <span><strong>Max Group:</strong> <?php echo esc_html($max_capacity); ?> people</span>
                    </div>
                    <?php endif; ?>

                    <?php if ($departure_location): ?>
                    <div class="sbt-quick-info-item">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        <span><strong>Departs from:</strong> <?php echo esc_html($departure_location); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($departure_time): ?>
                    <div class="sbt-quick-info-item">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6 .5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1H9v1.07a7.001 7.001 0 0 1 3.274 12.474l.601.602a.5.5 0 0 1-.707.708l-.746-.746A6.97 6.97 0 0 1 8 16a6.97 6.97 0 0 1-3.422-.892l-.746.746a.5.5 0 0 1-.707-.708l.602-.602A7.001 7.001 0 0 1 7 2.07V1h-.5A.5.5 0 0 1 6 .5zm2.5 5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5zM.86 5.387A2.5 2.5 0 1 1 4.387 1.86 8.035 8.035 0 0 0 .86 5.387zM11.613 1.86a2.5 2.5 0 1 1 3.527 3.527 8.035 8.035 0 0 0-3.527-3.527z"/>
                        </svg>
                        <span><strong>Departure:</strong> <?php echo esc_html($departure_time); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Two Column Layout -->
                <div class="sbt-tour-two-column">
                    <!-- Left Column: Details -->
                    <div class="sbt-tour-left-column">
                        <!-- Description -->
                        <?php if ($tour->post_content): ?>
                        <div class="sbt-tour-section sbt-tour-description">
                            <h2 class="sbt-section-heading">About This Tour</h2>
                            <div class="sbt-section-content">
                                <?php echo wpautop($tour->post_content); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Tour Highlights -->
                        <?php if ($highlights && is_array($highlights)): ?>
                        <div class="sbt-tour-section sbt-tour-highlights">
                            <h2 class="sbt-section-heading">
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                </svg>
                                Tour Highlights
                            </h2>
                            <ul class="sbt-highlights-list">
                                <?php foreach ($highlights as $highlight): ?>
                                    <?php if (isset($highlight['highlight_text']) && !empty($highlight['highlight_text'])): ?>
                                    <li>
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                        </svg>
                                        <?php echo esc_html($highlight['highlight_text']); ?>
                                    </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- What's Included -->
                        <?php if ($included && is_array($included)): ?>
                        <div class="sbt-tour-section sbt-tour-included">
                            <h2 class="sbt-section-heading">
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z"/>
                                </svg>
                                What's Included
                            </h2>
                            <ul class="sbt-included-list">
                                <?php foreach ($included as $item): ?>
                                    <?php if (isset($item['included_item']) && !empty($item['included_item'])): ?>
                                    <li>
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        <?php echo esc_html($item['included_item']); ?>
                                    </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Tour Gallery -->
                        <?php if ($gallery && is_array($gallery) && count($gallery) > 0): ?>
                        <div class="sbt-tour-section sbt-tour-gallery-section">
                            <h2 class="sbt-section-heading">
                                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                </svg>
                                Photo Gallery
                            </h2>
                            <div class="sbt-tour-gallery">
                                <?php foreach ($gallery as $image): ?>
                                    <div class="sbt-gallery-item">
                                        <img src="<?php echo esc_url($image['sizes']['medium'] ?? $image['url']); ?>"
                                             alt="<?php echo esc_attr($image['alt'] ?? 'Tour image'); ?>"
                                             data-full="<?php echo esc_url($image['url']); ?>"
                                             class="sbt-gallery-image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column: Booking Widget -->
                    <div class="sbt-tour-right-column">
                        <div class="sbt-booking-card-sticky">
                            <div class="sbt-booking-card">
                                <?php if ($price): ?>
                                <div class="sbt-booking-price">
                                    <span class="sbt-price-amount">€<?php echo number_format($price, 2); ?></span>
                                    <?php if ($price_per_person): ?>
                                    <span class="sbt-price-label">per person</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <!-- Availability Calendar -->
                                <div class="sbt-tour-availability-section">
                                    <h4 class="sbt-availability-title">
                                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                        </svg>
                                        Check Availability
                                    </h4>
                                    <?php echo do_shortcode('[sbt_availability_calendar tour_id="' . $tour_id . '" full_width="true"]'); ?>
                                </div>

                                <a href="<?php echo esc_url(home_url('/book/?id=' . $tour_id)); ?>" class="sbt-btn sbt-btn-primary sbt-btn-large sbt-book-now-btn" data-tour-id="<?php echo esc_attr($tour_id); ?>">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                    </svg>
                                    Book This Tour
                                </a>

                                <div class="sbt-booking-card-features">
                                    <div class="sbt-feature-item">
                                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Instant Confirmation
                                    </div>
                                    <div class="sbt-feature-item">
                                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Free Cancellation
                                    </div>
                                    <div class="sbt-feature-item">
                                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Secure Payment
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox for Gallery -->
        <div class="sbt-lightbox" style="display: none;">
            <div class="sbt-lightbox-overlay"></div>
            <div class="sbt-lightbox-content">
                <button class="sbt-lightbox-close">&times;</button>
                <button class="sbt-lightbox-prev">&lt;</button>
                <button class="sbt-lightbox-next">&gt;</button>
                <img src="" alt="" class="sbt-lightbox-image">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Availability Calendar Shortcode
     * Usage: [sbt_availability_calendar]
     * URL Parameters: ?date=2024-01-15&id=123
     */
    public function availability_calendar($atts) {
        $atts = shortcode_atts([
            'date' => '',
            'tour_id' => '',
            'multi_select' => 'false',
            'full_width' => 'false'
        ], $atts);

        // If no values from shortcode attributes, check URL parameters
        if (empty($atts['date'])) {
            $atts['date'] = $this->get_url_param('date');
        }
        if (empty($atts['tour_id'])) {
            // Check 'id' parameter first, then 'tour_id' for backward compatibility
            $atts['tour_id'] = $this->get_url_param('id', $this->get_url_param('tour_id'));
        }

        $calendar_class = 'sbt-calendar';
        if ($atts['full_width'] === 'true') {
            $calendar_class .= ' sbt-calendar-full-width';
        }
        if ($atts['multi_select'] === 'true') {
            $calendar_class .= ' sbt-calendar-multi-select';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr($calendar_class); ?>"
             data-preselect-date="<?php echo esc_attr($atts['date']); ?>"
             data-tour-id="<?php echo esc_attr($atts['tour_id']); ?>"
             data-multi-select="<?php echo esc_attr($atts['multi_select']); ?>">
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
