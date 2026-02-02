# Santorini Boat Tours Booking System - Setup Guide

## Quick Start

### Prerequisites
- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Advanced Custom Fields (Free version)
- Builder.io account (free tier works)
- Stripe account (for payments)

### Installation Steps

#### 1. Install WordPress Plugin

```bash
# Upload the entire 'santorini-boat-tours' folder to wp-content/plugins/
# OR zip the folder and upload via WordPress admin
```

**Via WordPress Admin:**
1. Go to Plugins → Add New → Upload Plugin
2. Upload `santorini-boat-tours.zip`
3. Click "Activate Plugin"

#### 2. Install Required Plugin

**Advanced Custom Fields (ACF):**
1. Go to Plugins → Add New
2. Search for "Advanced Custom Fields"
3. Install and activate the FREE version

#### 3. Configure ACF Fields

Go to Custom Fields → Add New and create these field groups:

**Tour Details (assign to Tours post type):**
- Field Group Name: `Tour Details`
- Location: Post Type = `sbt_tour`

**Fields:**
```
tour_type (Select)
  - morning
  - sunset  
  - private
  - water_taxi_ios
  - water_taxi_mykonos

tour_duration (Number) - default: 5

tour_max_capacity (Number) - default: 20

tour_price (Number) - required

tour_price_per_person (True/False) - default: true

tour_departure_time (Time Picker)

tour_departure_location (Text)

tour_highlights (Repeater)
  - highlight_text (Text)

tour_included (Repeater)
  - included_item (Text)

tour_gallery (Gallery)

tour_available_days (Checkbox)
  - Monday
  - Tuesday
  - Wednesday
  - Thursday
  - Friday
  - Saturday
  - Sunday
```

**Booking Details (assign to Bookings post type):**
- Field Group Name: `Booking Details`
- Location: Post Type = `sbt_booking`

**Fields:**
```
booking_tour (Post Object) - Post Type: sbt_tour
booking_date (Date Picker)
booking_passengers (Number)
booking_status (Select)
  - pending
  - confirmed
  - paid
  - cancelled
booking_confirmation_code (Text)
booking_total_amount (Number)
booking_customer_email (Email)
booking_customer_name (Text)
booking_customer_phone (Text)
booking_payment_method (Select)
  - stripe
  - paypal
booking_payment_id (Text)
```

#### 4. Plugin Settings

Go to **Boat Tours → Settings** and configure:

**General Settings:**
- Currency: EUR
- Time Format: 24h
- Booking Buffer: 24 hours

**Payment Settings:**
- Enable Stripe: Yes
- Stripe Publishable Key: `pk_live_...` or `pk_test_...`
- Stripe Secret Key: `sk_live_...` or `sk_test_...`
- Enable PayPal: (Optional)

**Email Settings:**
- From Email: bookings@santorini-boattours.com
- From Name: Santorini Boat Tours
- Admin Email: admin@santorini-boattours.com

**Security Settings:**
- Enable reCAPTCHA v3: Yes (recommended)
- reCAPTCHA Site Key: (from Google reCAPTCHA)
- reCAPTCHA Secret Key: (from Google reCAPTCHA)

#### 5. Create Tours

Go to **Boat Tours → Add New Tour**

**Example Tour:**
- Title: "Santorini Morning Cruise"
- Content: (Full tour description)
- Tour Type: morning
- Duration: 5 hours
- Max Capacity: 20 passengers
- Price: 50
- Price Per Person: Yes
- Departure Time: 09:00
- Departure Location: "Ammoudi Bay"
- Available Days: Monday through Sunday

**Create these tours:**
1. Morning Boat Tour
2. Sunset Boat Tour
3. Private Group Cruise
4. Water Taxi to Ios
5. Water Taxi to Mykonos

#### 6. Configure Permalinks

Go to **Settings → Permalinks**
- Select "Post name" structure
- Click "Save Changes" (this activates custom rewrite rules)

**Test URLs (these should now work):**
- `/santorini-to-ios/`
- `/santorini-to-mykonos/`
- `/morning-tour/`
- `/sunset-tour/`
- `/private-cruise/`
- `/book/?tour=morning`

#### 7. Builder.io Integration

**Setup Builder.io:**

1. Create account at builder.io (free tier works)
2. Create new Space
3. Get API Key from Settings → API Keys

**Install Builder.io Plugin:**

```bash
npm install @builder.io/react
# OR add via CDN in WordPress theme
```

**Register Components:**

Create file in your theme: `functions.php` or custom plugin:

```php
function enqueue_builder_components() {
    wp_enqueue_script(
        'sbt-builder-components',
        get_stylesheet_directory_uri() . '/builder-components/index.js',
        ['react', 'react-dom'],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_builder_components');
```

**Import Components in Builder.io:**

Copy all files from `/builder-components/` to your theme or upload to Builder.io:

```javascript
// In your Builder.io integration file
import {
    TourCard,
    BookingWidget,
    AvailabilityCalendar,
    TestimonialSlider,
    ServiceGrid
} from './builder-components';
```

#### 8. Create Pages

**Homepage (using Builder.io or Elementor):**
- Add ServiceGrid component
- Add featured TourCard components
- Add TestimonialSlider component

**Booking Page:**
- Create page with slug: `/book/`
- Add BookingWidget component

**Tours Page:**
- Add multiple TourCard components
- OR add ServiceGrid with tour listings

**Sample Page Structure:**
```
Homepage:
  - Hero Section
  - ServiceGrid (4 services)
  - Featured Tours (3 TourCards)
  - Why Choose Us
  - TestimonialSlider
  - Contact Section

Booking Page:
  - BookingWidget (full width)

Tours Page:
  - TourCard (Morning Tour)
  - TourCard (Sunset Tour)
  - TourCard (Private Cruise)
  - etc.
```

#### 9. Test Booking Flow

1. Go to `/book/` or `/morning-tour/`
2. Select a tour (should auto-select if coming from direct URL)
3. Choose date from calendar
4. Select passengers (1-20)
5. Fill in contact details
6. Complete booking

**Test with Stripe:**
- Use test card: `4242 4242 4242 4242`
- Expiry: Any future date
- CVC: Any 3 digits

#### 10. Email Templates

Email templates are in `/includes/class-email-notifications.php`

**Customize templates:**
1. Edit the HTML in the class methods
2. Or create templates in `/templates/emails/`
3. Use filters: `sbt_email_template_[type]`

#### 11. URL Filter Testing

**Test these URLs work correctly:**

```
/santorini-to-ios/          → Auto-selects Water Taxi to Ios
/santorini-to-mykonos/      → Auto-selects Water Taxi to Mykonos  
/morning-tour/              → Auto-selects Morning Tour
/sunset-tour/               → Auto-selects Sunset Tour
/private-cruise/            → Auto-selects Private Cruise
/book/?tour=morning         → Auto-selects by slug
/book/?tour=sunset&date=2024-08-15  → Pre-fills date too
```

## Configuration

### Stripe Setup

1. Create Stripe account: https://stripe.com
2. Get API keys from Dashboard → Developers → API Keys
3. For testing: Use test keys (pk_test_... and sk_test_...)
4. For production: Use live keys (pk_live_... and sk_live_...)
5. Configure webhook endpoint: `/wp-json/sbt/v1/webhooks/stripe`

### reCAPTCHA Setup

1. Go to: https://www.google.com/recaptcha/admin
2. Create new site
3. Select reCAPTCHA v3
4. Add your domain
5. Copy Site Key and Secret Key
6. Paste in plugin settings

### PayPal Setup (Optional)

1. Create PayPal Business account
2. Get API credentials from Developer Dashboard
3. Configure in plugin settings
4. Set webhook: `/wp-json/sbt/v1/webhooks/paypal`

## Customization

### Styling

**Override CSS:**
Create file: `/wp-content/themes/your-theme/sbt-custom.css`

```css
/* Override primary color */
:root {
    --sbt-primary: #your-color;
    --sbt-primary-light: #your-light-color;
}

/* Custom button style */
.sbt-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

Enqueue in theme:
```php
wp_enqueue_style('sbt-custom', get_stylesheet_directory_uri() . '/sbt-custom.css', ['sbt-frontend']);
```

### Add Custom Tour Types

```php
// In functions.php
add_filter('sbt_tour_types', function($types) {
    $types['custom_type'] = 'Custom Tour Name';
    return $types;
});

// Add rewrite rule
add_action('init', function() {
    add_rewrite_rule(
        '^custom-tour/?$',
        'index.php?tour_filter=custom_type',
        'top'
    );
});
```

## Troubleshooting

### URLs not working
- Go to Settings → Permalinks
- Click "Save Changes" without making changes
- This flushes and regenerates rewrite rules

### Calendar not showing availability
- Check tour has Available Days set
- Verify tour_max_capacity is set
- Check browser console for API errors
- Verify REST API accessible: `/wp-json/sbt/v1/tours`

### Booking emails not sending
- Install WP Mail SMTP plugin
- Configure SMTP settings
- Test email delivery
- Check spam folder

### Payment errors
- Verify Stripe keys are correct (test vs live)
- Check Stripe Dashboard → Logs for errors
- Enable WordPress debug: `define('WP_DEBUG', true);`
- Check browser console for JavaScript errors

### Builder.io components not appearing
- Clear Builder.io cache
- Verify components registered correctly
- Check browser console for import errors
- Ensure React is loaded before components

## Support & Updates

- Documentation: `/wp-admin/admin.php?page=sbt-docs`
- Support: support@santorini-boattours.com
- Updates: Enable auto-updates in Plugins page

## Security Checklist

- ✅ reCAPTCHA v3 enabled
- ✅ Rate limiting active
- ✅ Nonce verification on AJAX
- ✅ SQL injection protection (prepared statements)
- ✅ XSS prevention (sanitization)
- ✅ HTTPS required for payments
- ✅ Payment tokenization (no card storage)

## Performance Optimization

1. **Caching:** Install WP Rocket or W3 Total Cache
2. **CDN:** Use Cloudflare for static assets
3. **Images:** Compress tour images (max 1200px width)
4. **Database:** Add indexes for availability lookups
5. **API:** Enable object caching for tour queries

## Going Live Checklist

- [ ] Switch Stripe to live keys
- [ ] Configure production domain in Builder.io
- [ ] Update email templates with real branding
- [ ] Test booking flow end-to-end
- [ ] Set up email notifications
- [ ] Configure SSL certificate
- [ ] Enable caching
- [ ] Submit sitemap to Google
- [ ] Add schema markup for tours
- [ ] Test mobile responsiveness
- [ ] Train staff on admin dashboard

---

**Setup Time:** 30-60 minutes
**Difficulty:** Medium (requires basic WordPress & Builder.io knowledge)
**Cost:** Free (except Stripe fees: 2.9% + $0.30 per transaction)

For questions: Read inline code comments or contact support.
