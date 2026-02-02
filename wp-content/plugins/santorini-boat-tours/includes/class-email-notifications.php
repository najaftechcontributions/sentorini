<?php
/**
 * Email Notifications for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Email_Notifications {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_filter('wp_mail_content_type', [$this, 'set_html_content_type']);
    }
    
    public function set_html_content_type() {
        return 'text/html';
    }
    
    public function send_booking_pending($booking_id) {
        $data = $this->get_booking_data($booking_id);
        
        $subject = sprintf('Booking Pending - %s', $data['confirmation_code']);
        $message = $this->get_pending_template($data);
        
        $this->send_email($data['customer_email'], $subject, $message);
        
        // Send copy to admin
        $admin_email = get_option('sbt_admin_email');
        if ($admin_email) {
            $admin_subject = sprintf('New Booking Pending - %s', $data['confirmation_code']);
            $this->send_email($admin_email, $admin_subject, $message);
        }
    }
    
    public function send_booking_confirmation($booking_id) {
        $data = $this->get_booking_data($booking_id);
        
        $subject = sprintf('Booking Confirmed - %s - Santorini Boat Tours', $data['confirmation_code']);
        $message = $this->get_confirmation_template($data);
        
        $this->send_email($data['customer_email'], $subject, $message);
        
        // Send copy to admin
        $admin_email = get_option('sbt_admin_email');
        if ($admin_email) {
            $admin_subject = sprintf('Booking Confirmed - %s', $data['confirmation_code']);
            $this->send_email($admin_email, $admin_subject, $message);
        }
    }
    
    public function send_booking_cancellation($booking_id) {
        $data = $this->get_booking_data($booking_id);
        
        $subject = sprintf('Booking Cancelled - %s', $data['confirmation_code']);
        $message = $this->get_cancellation_template($data);
        
        $this->send_email($data['customer_email'], $subject, $message);
        
        // Send copy to admin
        $admin_email = get_option('sbt_admin_email');
        if ($admin_email) {
            $this->send_email($admin_email, $subject, $message);
        }
    }
    
    public function send_booking_reminder($booking_id) {
        $data = $this->get_booking_data($booking_id);
        
        $subject = sprintf('Reminder: Your Boat Tour Tomorrow - %s', $data['confirmation_code']);
        $message = $this->get_reminder_template($data);
        
        $this->send_email($data['customer_email'], $subject, $message);
    }
    
    public function send_payment_receipt($booking_id) {
        $data = $this->get_booking_data($booking_id);
        
        $subject = sprintf('Payment Receipt - %s', $data['confirmation_code']);
        $message = $this->get_receipt_template($data);
        
        $this->send_email($data['customer_email'], $subject, $message);
    }
    
    private function send_email($to, $subject, $message) {
        $from_email = get_option('sbt_from_email', get_option('admin_email'));
        $from_name = get_option('sbt_from_name', get_option('blogname'));
        
        $headers = [
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email
        ];
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    private function get_booking_data($booking_id) {
        $tour_id = get_field('booking_tour', $booking_id);
        
        return [
            'booking_id' => $booking_id,
            'confirmation_code' => get_field('booking_confirmation_code', $booking_id),
            'tour_title' => get_the_title($tour_id),
            'tour_date' => get_field('booking_date', $booking_id),
            'tour_time' => get_field('tour_departure_time', $tour_id),
            'departure_location' => get_field('tour_departure_location', $tour_id),
            'passengers' => get_field('booking_passengers', $booking_id),
            'customer_name' => get_field('booking_customer_first_name', $booking_id) . ' ' . get_field('booking_customer_last_name', $booking_id),
            'customer_email' => get_field('booking_customer_email', $booking_id),
            'customer_phone' => get_field('booking_customer_phone', $booking_id),
            'total_amount' => get_field('booking_total_amount', $booking_id),
            'currency_symbol' => get_option('sbt_currency_symbol', '€'),
            'payment_method' => get_field('booking_payment_method', $booking_id),
            'payment_id' => get_field('booking_payment_id', $booking_id),
            'special_requests' => get_field('booking_special_requests', $booking_id),
            'status' => get_field('booking_status', $booking_id)
        ];
    }
    
    private function get_pending_template($data) {
        $template = $this->get_email_header();
        
        $template .= '
        <div style="background: #fff; padding: 40px; border-radius: 8px;">
            <h1 style="color: #1e3a8a; margin-bottom: 20px;">Booking Pending Payment</h1>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Dear ' . esc_html($data['customer_name']) . ',
            </p>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Your booking has been created and is pending payment. Please complete your payment to confirm your reservation.
            </p>
            
            <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0;">
                <h2 style="color: #1e3a8a; margin-top: 0;">Booking Details</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Confirmation Code:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #1e3a8a;">' . esc_html($data['confirmation_code']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Tour:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['tour_title']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Date:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html(date('l, F j, Y', strtotime($data['tour_date']))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Departure Time:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['tour_time']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Departure Location:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['departure_location']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Passengers:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['passengers']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Total Amount:</td>
                        <td style="padding: 8px 0; font-weight: bold; font-size: 20px; color: #059669;">' . esc_html($data['currency_symbol'] . number_format($data['total_amount'], 2)) . '</td>
                    </tr>
                </table>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . home_url('/booking-payment?code=' . $data['confirmation_code']) . '" 
                   style="display: inline-block; background: #3b82f6; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                    Complete Payment
                </a>
            </div>
            
            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                If you have any questions, please don\'t hesitate to contact us.
            </p>
        </div>';
        
        $template .= $this->get_email_footer();
        
        return $template;
    }
    
    private function get_confirmation_template($data) {
        $template = $this->get_email_header();
        
        $template .= '
        <div style="background: #fff; padding: 40px; border-radius: 8px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="display: inline-block; background: #10b981; color: #fff; padding: 10px 20px; border-radius: 50px; font-weight: bold;">
                    ✓ CONFIRMED
                </div>
            </div>
            
            <h1 style="color: #1e3a8a; margin-bottom: 20px; text-align: center;">Your Boat Tour is Confirmed!</h1>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Dear ' . esc_html($data['customer_name']) . ',
            </p>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                We\'re excited to have you join us! Your booking has been confirmed and we look forward to providing you with an unforgettable experience in Santorini.
            </p>
            
            <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 30px 0;">
                <h2 style="color: #1e3a8a; margin-top: 0;">Booking Details</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Confirmation Code:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #1e3a8a; font-size: 18px;">' . esc_html($data['confirmation_code']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Tour:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['tour_title']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Date:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html(date('l, F j, Y', strtotime($data['tour_date']))) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Departure Time:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['tour_time']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Departure Location:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['departure_location']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Passengers:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['passengers']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Total Paid:</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #059669;">' . esc_html($data['currency_symbol'] . number_format($data['total_amount'], 2)) . '</td>
                    </tr>
                </table>
            </div>
            
            <div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 20px; border-radius: 6px; margin: 30px 0;">
                <h3 style="color: #92400e; margin-top: 0;">Important Information</h3>
                <ul style="color: #78350f; margin: 0; padding-left: 20px;">
                    <li>Please arrive 15 minutes before departure time</li>
                    <li>Bring your confirmation code: <strong>' . esc_html($data['confirmation_code']) . '</strong></li>
                    <li>Don\'t forget sunscreen, hat, and camera</li>
                    <li>Comfortable shoes recommended</li>
                </ul>
            </div>
            
            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                We\'ll send you a reminder 24 hours before your tour. If you need to make any changes, please contact us as soon as possible.
            </p>
        </div>';
        
        $template .= $this->get_email_footer();
        
        return $template;
    }
    
    private function get_cancellation_template($data) {
        $template = $this->get_email_header();
        
        $template .= '
        <div style="background: #fff; padding: 40px; border-radius: 8px;">
            <h1 style="color: #dc2626; margin-bottom: 20px;">Booking Cancelled</h1>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Dear ' . esc_html($data['customer_name']) . ',
            </p>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Your booking has been cancelled as requested. We\'re sorry we won\'t be seeing you this time.
            </p>
            
            <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 20px; margin: 30px 0;">
                <h2 style="color: #991b1b; margin-top: 0;">Cancelled Booking Details</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Confirmation Code:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['confirmation_code']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Tour:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['tour_title']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Date:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html(date('l, F j, Y', strtotime($data['tour_date']))) . '</td>
                    </tr>
                </table>
            </div>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                We hope to see you in the future! You can book another tour anytime on our website.
            </p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . home_url('/tours') . '" 
                   style="display: inline-block; background: #3b82f6; color: #fff; padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                    Browse Tours
                </a>
            </div>
        </div>';
        
        $template .= $this->get_email_footer();
        
        return $template;
    }
    
    private function get_reminder_template($data) {
        $template = $this->get_email_header();
        
        $template .= '
        <div style="background: #fff; padding: 40px; border-radius: 8px;">
            <h1 style="color: #1e3a8a; margin-bottom: 20px;">🚤 Your Tour is Tomorrow!</h1>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Dear ' . esc_html($data['customer_name']) . ',
            </p>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                This is a friendly reminder that your boat tour is scheduled for tomorrow. We\'re excited to see you!
            </p>
            
            <div style="background: #dbeafe; border: 2px solid #3b82f6; padding: 30px; border-radius: 8px; margin: 30px 0; text-align: center;">
                <h2 style="color: #1e3a8a; margin-top: 0; font-size: 24px;">Tomorrow\'s Details</h2>
                <p style="font-size: 20px; font-weight: bold; color: #1e3a8a; margin: 10px 0;">' . esc_html(date('l, F j, Y', strtotime($data['tour_date']))) . '</p>
                <p style="font-size: 32px; font-weight: bold; color: #3b82f6; margin: 10px 0;">' . esc_html($data['tour_time']) . '</p>
                <p style="font-size: 16px; color: #1e40af; margin: 10px 0;">' . esc_html($data['departure_location']) . '</p>
                <p style="font-size: 14px; color: #1e40af; margin-top: 20px;">Confirmation Code: <strong>' . esc_html($data['confirmation_code']) . '</strong></p>
            </div>
            
            <div style="background: #fef3c7; border-left: 4px solid #fbbf24; padding: 20px; margin: 30px 0;">
                <h3 style="color: #92400e; margin-top: 0;">📋 Checklist</h3>
                <ul style="color: #78350f; margin: 0; padding-left: 20px;">
                    <li>✓ Arrive 15 minutes early</li>
                    <li>✓ Bring confirmation code: ' . esc_html($data['confirmation_code']) . '</li>
                    <li>✓ Sunscreen and hat</li>
                    <li>✓ Camera for amazing photos</li>
                    <li>✓ Comfortable footwear</li>
                    <li>✓ Light jacket (for boat breeze)</li>
                </ul>
            </div>
            
            <p style="font-size: 16px; color: #333; margin-top: 30px;">
                See you tomorrow! If you have any last-minute questions, please don\'t hesitate to contact us.
            </p>
        </div>';
        
        $template .= $this->get_email_footer();
        
        return $template;
    }
    
    private function get_receipt_template($data) {
        $template = $this->get_email_header();
        
        $template .= '
        <div style="background: #fff; padding: 40px; border-radius: 8px;">
            <h1 style="color: #1e3a8a; margin-bottom: 20px;">Payment Receipt</h1>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Dear ' . esc_html($data['customer_name']) . ',
            </p>
            
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Thank you for your payment. Here is your receipt for your records.
            </p>
            
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 30px; margin: 30px 0;">
                <h2 style="margin-top: 0;">Receipt Details</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Receipt Date:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html(date('F j, Y')) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Confirmation Code:</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . esc_html($data['confirmation_code']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Payment Method:</td>
                        <td style="padding: 8px 0; font-weight: bold; text-transform: capitalize;">' . esc_html($data['payment_method']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;">Transaction ID:</td>
                        <td style="padding: 8px 0; font-family: monospace; font-size: 12px;">' . esc_html($data['payment_id']) . '</td>
                    </tr>
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td style="padding: 15px 0; color: #666; font-size: 18px;">Total Paid:</td>
                        <td style="padding: 15px 0; font-weight: bold; font-size: 24px; color: #059669;">' . esc_html($data['currency_symbol'] . number_format($data['total_amount'], 2)) . '</td>
                    </tr>
                </table>
            </div>
        </div>';
        
        $template .= $this->get_email_footer();
        
        return $template;
    }
    
    private function get_email_header() {
        $logo_url = get_option('sbt_email_logo_url', '');
        $site_name = get_option('blogname');
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background: #f3f4f6;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background: #f3f4f6; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <tr>
                                <td style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 30px; text-align: center;">
                                    ' . (!empty($logo_url) ? '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($site_name) . '" style="max-width: 200px; height: auto;">' : '<h1 style="color: #fff; margin: 0; font-size: 28px;">' . esc_html($site_name) . '</h1>') . '
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0;">
        ';
    }
    
    private function get_email_footer() {
        $site_name = get_option('blogname');
        $site_url = home_url();
        $contact_email = get_option('sbt_admin_email', get_option('admin_email'));
        
        return '
                                </td>
                            </tr>
                            <tr>
                                <td style="background: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                                    <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                                        <strong>' . esc_html($site_name) . '</strong><br>
                                        Santorini, Greece
                                    </p>
                                    <p style="margin: 10px 0; color: #6b7280; font-size: 14px;">
                                        Contact us: <a href="mailto:' . esc_attr($contact_email) . '" style="color: #3b82f6; text-decoration: none;">' . esc_html($contact_email) . '</a>
                                    </p>
                                    <p style="margin: 10px 0; color: #9ca3af; font-size: 12px;">
                                        <a href="' . esc_url($site_url) . '" style="color: #3b82f6; text-decoration: none;">Visit our website</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }
}
