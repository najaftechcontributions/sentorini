# Shortcode Usage Guide - Santorini Boat Tours

## ✅ What Was Fixed

All shortcodes now properly support **URL parameters** for dynamic content. Everything works through shortcodes - no URL-based pages required. URLs only pass parameters to change data displayed by shortcodes.

## 🎯 How It Works

1. **Place shortcode** on any WordPress page/post
2. **Add URL parameters** to pre-populate or filter content
3. **JavaScript automatically** reads parameters and initializes shortcodes

## 📋 Quick Reference

### 1. Booking Form
**Use Case:** Main booking page that accepts pre-filled data from URLs

```
Shortcode: [sbt_booking_form]
Page URL: /book/
```

**Examples:**
```
/book/?tour=morning
  → Pre-selects Morning Tour

/book/?tour_id=123&date=2024-08-15
  → Pre-selects tour #123 and Aug 15, 2024

/book/?tour=sunset&date=2024-09-01&passengers=4
  → Pre-fills everything: Sunset tour, Sep 1, 2024, 4 passengers
```

---

### 2. Tour Archive
**Use Case:** Tour listing page with filtering

```
Shortcode: [sbt_tour_archive columns="3"]
Page URL: /tours/
```

**Examples:**
```
/tours/
  → Shows all tours with filter dropdown

/tours/?tour=morning
  → Shows only Morning tours

/tours/?tour_type=sunset
  → Shows only Sunset tours
```

**Filter Dropdown:** Automatically added when `show_all="true"` (default)

---

### 3. Single Tour Page
**Use Case:** Dynamic tour details page

```
Shortcode: [sbt_single_tour]
Page URL: /tour-details/
```

**Examples:**
```
/tour-details/?tour_id=123
  → Shows tour #123

/tour-details/?tour=morning
  → Shows first Morning tour found
```

**Book Now Button:** Automatically links to booking form with tour pre-selected

---

### 4. Availability Calendar
**Use Case:** Standalone calendar or embedded in forms

```
Shortcode: [sbt_availability_calendar]
Page URL: /availability/
```

**Examples:**
```
/availability/?tour_id=123
  → Shows availability for tour #123

/availability/?date=2024-08-15
  → Opens to Aug 2024 with 15th pre-selected
```

---

## 🔗 Linking Between Pages

### From Archive to Booking Form
```php
<a href="/book/?tour_id=<?php echo get_the_ID(); ?>">
    Book This Tour
</a>
```

### From Archive to Single Tour
```php
<a href="/tour-details/?tour_id=<?php echo get_the_ID(); ?>">
    View Details
</a>
```

### From Single Tour to Booking
```php
<a href="/book/?tour_id=<?php echo $tour_id; ?>&passengers=2">
    Book for 2 People
</a>
```

### External Link to Pre-filled Booking
```html
<a href="https://yoursite.com/book/?tour=sunset&date=2024-08-15&passengers=4">
    Book Sunset Tour - Aug 15 for 4 People
</a>
```

---

## 📄 Recommended Page Setup

### Page 1: Tours Archive
- **Slug:** `/tours/`
- **Shortcode:** `[sbt_tour_archive columns="3"]`
- **Purpose:** Browse all tours, filter by type

### Page 2: Tour Details (Optional)
- **Slug:** `/tour-details/`
- **Shortcode:** `[sbt_single_tour]`
- **Purpose:** View single tour details via `?tour_id=X`

### Page 3: Booking Form
- **Slug:** `/book/`
- **Shortcode:** `[sbt_booking_form]`
- **Purpose:** Complete booking process

### Page 4: Availability (Optional)
- **Slug:** `/availability/`
- **Shortcode:** `[sbt_availability_calendar]`
- **Purpose:** Check tour availability

---

## 🎨 Customization Examples

### Archive with 4 Columns, No Filter
```
[sbt_tour_archive columns="4" show_all="false"]
```

### Morning Tours Only
```
[sbt_tour_list type="morning" columns="2"]
```

### Booking Form Without Step Indicators
```
[sbt_booking_form show_steps="false"]
```

### Limited Tour List
```
[sbt_tour_list limit="6" columns="3"]
```

---

## 🌐 URL Parameter Combinations

All parameters can be combined:

```
?tour=morning&date=2024-08-15&passengers=2
?tour_id=123&date=2024-09-01
?tour_type=sunset&passengers=5
?tour_id=123
?date=2024-08-15
```

**Priority:**
- `tour_id` takes priority over `tour` and `tour_type`
- `tour` and `tour_type` are aliases (both work)

---

## ⚡ How Parameters Are Processed

1. **URL Parameters** are read from `$_GET`
2. **Sanitized** for security
3. **Passed to shortcodes** via attributes
4. **JavaScript reads** both URL params and shortcode data attributes
5. **Form pre-populated** automatically

---

## 🔧 Technical Details

### Shortcode Aliases
- `[sbt_booking_widget]` = `[sbt_booking_form]`
- `[sbt_tour_list]` = `[sbt_tour_archive]`
- `[sbt_tour_card]` = `[sbt_single_tour]`

### Data Attributes
Shortcodes output data attributes for JavaScript:
```html
<div class="sbt-booking-widget"
     data-preselect-tour="123"
     data-preselect-date="2024-08-15"
     data-preselect-passengers="2">
```

### JavaScript Global Object
URL params available in JS:
```javascript
window.sbtUrlParams = {
    tour: "morning",
    tour_id: 123,
    date: "2024-08-15",
    passengers: 2
}
```

---

## 🛠️ Developer: Adding Custom Parameters

### 1. Add to URL Handler
```php
// wp-content/plugins/santorini-boat-tours/includes/class-url-handler.php

$params = [
    'tour' => isset($_GET['tour']) ? sanitize_text_field($_GET['tour']) : '',
    'custom_param' => isset($_GET['custom']) ? sanitize_text_field($_GET['custom']) : '',
];
```

### 2. Add to Shortcode
```php
// wp-content/plugins/santorini-boat-tours/includes/class-shortcodes.php

$atts = shortcode_atts([
    'custom_param' => $this->get_url_param('custom', 'default'),
], $atts);
```

### 3. Use in JavaScript
```javascript
// wp-content/plugins/santorini-boat-tours/assets/js/frontend.js

const customValue = window.sbtUrlParams.custom_param;
```

---

## ✅ After Installation

1. **Deactivate and reactivate** the plugin (to remove old rewrite rules)
2. **Go to Settings → Permalinks** and click "Save Changes" (flush rewrite rules)
3. **Test each shortcode** on a page
4. **Test URL parameters** by adding `?tour=morning` to URLs

---

## 🎉 Benefits of This Approach

✅ **No URL-based pages** - Everything is shortcode-based  
✅ **Flexible placement** - Use shortcodes anywhere  
✅ **Dynamic content** - URL parameters change displayed data  
✅ **SEO friendly** - Clean, semantic URLs  
✅ **Easy sharing** - Share pre-filled booking links  
✅ **Marketing ready** - Create targeted campaign URLs  

---

## 📞 Need Help?

- Read full documentation: `README.md`
- Setup guide: `SETUP.md`
- Check browser console for JavaScript errors
- Verify shortcode syntax

---

**Last Updated:** 2024  
**Plugin Version:** 1.0.0
