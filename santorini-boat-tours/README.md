# Santorini Boat Tours - Complete Booking System

A custom WordPress booking system with Builder.io integration for Santorini Boat Tours. Zero third-party commissions, full control, seamless design integration.

## 🚀 Features

### Core Functionality
- ✅ **4-Step Booking Flow** - Tour selection, date picking, passenger count, guest details
- ✅ **Real-time Availability** - Automatic capacity management with visual indicators
- ✅ **Smart URL Routing** - `/santorini-to-ios/`, `/morning-tour/` auto-filter tours
- ✅ **Payment Integration** - Direct Stripe & PayPal (no redirects)
- ✅ **Email Automation** - Confirmations, receipts, reminders, cancellations
- ✅ **Mobile Responsive** - Mobile-first design, touch-friendly
- ✅ **Security** - reCAPTCHA v3, rate limiting, SQL injection protection

### Tour Types Supported
- Morning Boat Tours (5h, max 20)
- Sunset Boat Tours (5h, max 20)
- Private Group Cruises (custom capacity)
- Water Taxi to Ios
- Water Taxi to Mykonos

### Admin Features
- Complete booking management dashboard
- Tour configuration (prices, capacity, schedules)
- Availability calendar with blackout dates
- CSV export functionality
- Customer database
- Payment tracking

## 📁 Project Structure

```
santorini-boat-tours/
├── builder-components/          # Builder.io React Components
│   ├── TourCard.jsx            # Individual tour display card
│   ├── BookingWidget.jsx       # Full 4-step booking flow
│   ├── AvailabilityCalendar.jsx # Date picker with availability
│   ├── TestimonialSlider.jsx   # Customer testimonials
│   ├── ServiceGrid.jsx         # Services overview grid
│   └── index.js                # Component export
├── includes/                    # WordPress Backend Classes
│   ├── class-admin-dashboard.php
│   ├── class-availability.php
│   ├── class-booking-manager.php
│   ├── class-email-notifications.php
│   ├── class-payment-handler.php
│   ├── class-post-types.php
│   ├── class-rest-api.php
│   └── class-url-handler.php
├── assets/
│   ├── css/
│   │   ├── admin.css           # Admin dashboard styles
│   │   └── frontend.css        # Greek-inspired blue/white theme
│   └── js/
│       ├── admin.js            # Admin functionality
│       └── frontend.js         # Booking flow logic
├── santorini-boat-tours.php    # Main plugin file
├── SETUP.md                    # Detailed setup guide
└── README.md                   # This file
```

## 🎨 Builder.io Components

### TourCard
Displays individual tour with image, details, pricing, and booking CTA.

**Props:**
- `tourId` (number, required) - WordPress tour post ID
- `showExcerpt` (boolean) - Show tour description
- `showHighlights` (boolean) - Show tour highlights

**Usage:**
```jsx
<TourCard tourId={123} showExcerpt={true} showHighlights={false} />
```

### BookingWidget
Complete 4-step booking process. Embeds anywhere on site.

**Props:**
- `autoSelectTour` (string) - Auto-select tour by slug/type
- `showSteps` (boolean) - Show step indicators

**Usage:**
```jsx
<BookingWidget autoSelectTour="morning" showSteps={true} />
```

**URL Integration:**
- `/book/?tour=morning` - Auto-selects Morning Tour
- `/book/?tour=sunset&date=2024-08-15` - Pre-fills date too
- `/santorini-to-ios/` - Auto-selects Water Taxi to Ios

### AvailabilityCalendar
Interactive calendar showing tour availability.

**Props:**
- `tourId` (number) - Tour to show availability for
- `passengerCount` (number) - For capacity checking
- `showLegend` (boolean) - Display color legend

**Usage:**
```jsx
<AvailabilityCalendar tourId={123} passengerCount={2} showLegend={true} />
```

### TestimonialSlider
Customer reviews with auto-play slider.

**Props:**
- `testimonials` (array) - Custom testimonials
- `autoPlay` (boolean) - Auto-advance slides
- `autoPlayInterval` (number) - Milliseconds between slides
- `showStars` (boolean) - Show star ratings

**Usage:**
```jsx
<TestimonialSlider autoPlay={true} autoPlayInterval={5000} showStars={true} />
```

### ServiceGrid
Display tour services in responsive grid.

**Props:**
- `services` (array) - Custom services
- `columns` (number) - Grid columns (2-5)
- `showIcons` (boolean) - Show service icons

**Usage:**
```jsx
<ServiceGrid columns={4} showIcons={true} />
```

## 🔌 REST API Endpoints

All endpoints prefixed with `/wp-json/sbt/v1/`

### Tours
- `GET /tours` - List all tours
- `GET /tours/{id}` - Get single tour
- `GET /tours/{id}/available-dates?month=2024-08` - Get available dates

### Availability
- `POST /availability` - Check availability for date/passengers

### Bookings
- `POST /bookings` - Create new booking
- `GET /bookings/{code}` - Get booking by confirmation code
- `POST /bookings/{id}/cancel` - Cancel booking

### Payment
- `POST /payment/process` - Process payment
- `POST /webhooks/stripe` - Stripe webhook
- `POST /webhooks/paypal` - PayPal webhook

## 🎯 URL Routing

Smart URLs automatically filter tours:

- `/santorini-to-ios/` → Water Taxi to Ios
- `/santorini-to-mykonos/` → Water Taxi to Mykonos
- `/morning-tour/` → Morning Boat Tour
- `/sunset-tour/` → Sunset Boat Tour
- `/private-cruise/` → Private Cruise
- `/book/?tour={slug}` → Any tour by slug
- `/book/?tour={slug}&date={date}&passengers={num}` → Pre-fill all fields

## 💳 Payment Flow

1. User completes booking form
2. Booking created with status "pending"
3. Redirected to payment page
4. Payment processed via Stripe/PayPal
5. Webhook confirms payment
6. Booking status → "confirmed"
7. Confirmation email sent
8. Reminder email 24h before tour

## 📧 Email Notifications

**Automated Emails:**
- Booking Pending (after reservation)
- Booking Confirmation (after payment)
- Payment Receipt
- Booking Reminder (24h before)
- Cancellation Notice

**Customize:** Edit `/includes/class-email-notifications.php`

## 🎨 Design System

**Colors:**
```css
--sbt-primary: #1e3a8a;          /* Deep blue */
--sbt-primary-light: #3b82f6;    /* Bright blue */
--sbt-secondary: #0ea5e9;        /* Sky blue */
--sbt-accent: #f59e0b;           /* Amber */
--sbt-success: #10b981;          /* Green */
--sbt-white: #ffffff;            /* Pure white */
```

**Greek-Inspired Aesthetic:**
- Clean blues and whites
- Rounded corners (8-12px)
- Soft shadows
- Mediterranean vibes

**Override:** Create `sbt-custom.css` in theme and enqueue after `sbt-frontend`

## 🔒 Security Features

- ✅ Google reCAPTCHA v3
- ✅ Rate limiting (5 attempts/hour)
- ✅ CSRF protection (nonces)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (sanitization)
- ✅ Payment tokenization (no card storage)
- ✅ HTTPS enforcement
- ✅ Input validation

## 📊 Database Tables

**Custom Tables:**
- `wp_sbt_availability` - Tour date availability
- `wp_sbt_customers` - Customer records

**Post Types:**
- `sbt_tour` - Tour listings
- `sbt_booking` - Booking records

## ⚙️ Configuration

**Required Options:**
- Stripe API keys (test or live)
- Email sender name/address
- reCAPTCHA keys (optional but recommended)
- Booking buffer hours (default: 24)

**Set via:** WP Admin → Boat Tours → Settings

## 🚀 Quick Installation

1. **Upload Plugin**
   ```bash
   wp-content/plugins/santorini-boat-tours/
   ```

2. **Activate Plugin**
   ```bash
   WP Admin → Plugins → Activate
   ```

3. **Install ACF Free**
   ```bash
   Plugins → Add New → Search "Advanced Custom Fields"
   ```

4. **Configure Settings**
   ```bash
   Boat Tours → Settings
   ```

5. **Create Tours**
   ```bash
   Boat Tours → Add New Tour
   ```

6. **Flush Permalinks**
   ```bash
   Settings → Permalinks → Save Changes
   ```

7. **Add to Pages**
   ```bash
   Use Builder.io components in page builder
   ```

## 📖 Documentation

- **Setup Guide:** `SETUP.md` (detailed installation)
- **Code Comments:** Inline documentation in all files
- **API Docs:** See `/includes/class-rest-api.php`

## 🧪 Testing

**Test Booking Flow:**
1. Go to `/book/` or `/morning-tour/`
2. Select tour → date → passengers → details
3. Use Stripe test card: `4242 4242 4242 4242`
4. Verify confirmation email received
5. Check WP Admin → Boat Tours → Bookings

**Test URL Filters:**
- Visit `/santorini-to-ios/` → Should auto-select Water Taxi to Ios
- Visit `/book/?tour=sunset&date=2024-08-15` → Should pre-fill form

## 🛠️ Customization

**Add New Tour Type:**
```php
// In functions.php
add_filter('sbt_tour_types', function($types) {
    $types['new_type'] = 'New Tour Name';
    return $types;
});
```

**Custom Email Template:**
```php
add_filter('sbt_email_template_confirmation', function($template, $booking_id) {
    // Customize $template HTML
    return $template;
}, 10, 2);
```

**Change Colors:**
```css
/* In theme CSS */
:root {
    --sbt-primary: #your-color;
    --sbt-primary-light: #your-light-color;
}
```

## 📱 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile Safari (iOS 12+)
- Chrome Mobile (Android 8+)

## ⚡ Performance

- **Page Load:** < 2 seconds
- **Booking Widget:** Lazy loaded
- **Images:** Optimized, lazy loaded
- **API:** Cached availability data
- **Database:** Indexed queries

## 🆘 Troubleshooting

**URLs not working?**
→ Settings → Permalinks → Save Changes

**Calendar not loading?**
→ Check browser console for errors
→ Verify tour has max_capacity set

**Emails not sending?**
→ Install WP Mail SMTP plugin
→ Check spam folder

**Payment failing?**
→ Verify Stripe keys (test vs live)
→ Check Stripe Dashboard logs

## 📞 Support

- Email: support@santorini-boattours.com
- Documentation: `SETUP.md`
- Code: Fully commented

## 📄 License

Proprietary - Santorini Boat Tours

## 🎉 Credits

Built with:
- WordPress
- Advanced Custom Fields
- Builder.io
- React
- Stripe API
- Greek island inspiration ☀️🌊

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**WordPress:** 5.8+  
**PHP:** 7.4+  
**Setup Time:** 30-60 minutes  

For detailed setup instructions, see `SETUP.md`
