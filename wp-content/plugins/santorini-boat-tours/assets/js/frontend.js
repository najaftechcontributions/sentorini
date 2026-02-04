/**
 * Santorini Boat Tours - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    const SBT = {
        init: function() {
            this.bindEvents();
            this.initBookingWidget();
            this.initCalendar();
            this.initURLFilters();
            this.initGalleryLightbox();
            this.initFormValidation();
        },

        bindEvents: function() {
            $(document).on('click', '.sbt-tour-card-archive.sbt-tour-option', this.selectTour);
            $(document).on('click', '.sbt-tour-option', this.selectTour);
            $(document).on('click', '.sbt-calendar-day:not(.disabled):not(.blocked)', this.selectDate);
            $(document).on('click', '.sbt-counter-btn', this.updatePassengerCount);
            $(document).on('submit', '.sbt-booking-form', this.submitBooking);
            $(document).on('click', '.sbt-step-next', this.nextStep);
            $(document).on('click', '.sbt-step-prev', this.prevStep);
            $(document).on('click', '.sbt-gallery-image', this.openLightbox);
            $(document).on('click', '.sbt-lightbox-close', this.closeLightbox);
            $(document).on('click', '.sbt-lightbox-overlay', this.closeLightbox);
            $(document).on('click', '.sbt-lightbox-prev', this.prevImage);
            $(document).on('click', '.sbt-lightbox-next', this.nextImage);
            $(document).on('keydown', this.handleKeyboard);
        },

        initGalleryLightbox: function() {
            // Initialize gallery images array
            SBT.galleryImages = [];
            $('.sbt-gallery-image').each(function(index) {
                SBT.galleryImages.push({
                    src: $(this).data('full'),
                    alt: $(this).attr('alt'),
                    index: index
                });
            });
        },

        openLightbox: function(e) {
            e.preventDefault();
            const $img = $(this);
            const fullSrc = $img.data('full');
            const index = $('.sbt-gallery-image').index($img);

            SBT.currentImageIndex = index;

            $('.sbt-lightbox-image').attr('src', fullSrc).attr('alt', $img.attr('alt'));
            $('.sbt-lightbox').fadeIn(300);
            $('body').css('overflow', 'hidden');
        },

        closeLightbox: function(e) {
            if (e.target === e.currentTarget) {
                $('.sbt-lightbox').fadeOut(300);
                $('body').css('overflow', '');
            }
        },

        prevImage: function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (SBT.currentImageIndex > 0) {
                SBT.currentImageIndex--;
                const img = SBT.galleryImages[SBT.currentImageIndex];
                $('.sbt-lightbox-image').attr('src', img.src).attr('alt', img.alt);
            }
        },

        nextImage: function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (SBT.currentImageIndex < SBT.galleryImages.length - 1) {
                SBT.currentImageIndex++;
                const img = SBT.galleryImages[SBT.currentImageIndex];
                $('.sbt-lightbox-image').attr('src', img.src).attr('alt', img.alt);
            }
        },

        handleKeyboard: function(e) {
            if ($('.sbt-lightbox').is(':visible')) {
                if (e.key === 'Escape') {
                    SBT.closeLightbox({ target: $('.sbt-lightbox-overlay')[0], currentTarget: $('.sbt-lightbox-overlay')[0] });
                } else if (e.key === 'ArrowLeft') {
                    SBT.prevImage(e);
                } else if (e.key === 'ArrowRight') {
                    SBT.nextImage(e);
                }
            }
        },

        initFormValidation: function() {
            // Real-time validation for form fields
            $('.sbt-form-input, .sbt-form-textarea, .sbt-form-select').on('blur', function() {
                SBT.validateField($(this));
            });

            // Clear error on focus
            $('.sbt-form-input, .sbt-form-textarea, .sbt-form-select').on('focus', function() {
                $(this).removeClass('error').siblings('.sbt-field-error').hide();
            });
        },

        validateField: function($field) {
            const value = $field.val().trim();
            const isRequired = $field.prop('required');
            let isValid = true;
            let errorMessage = '';

            if (isRequired && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            } else if ($field.attr('type') === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
            } else if ($field.attr('type') === 'tel' && value) {
                if (value.length < 10) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }

            if (!isValid) {
                $field.addClass('error');
                $field.siblings('.sbt-field-error').text(errorMessage).show();
            } else {
                $field.removeClass('error');
                $field.siblings('.sbt-field-error').hide();
            }

            return isValid;
        },

        validateForm: function($form) {
            let isValid = true;

            $form.find('[required]').each(function() {
                if (!SBT.validateField($(this))) {
                    isValid = false;
                }
            });

            // Check terms checkbox
            const $terms = $('#sbt-terms');
            if ($terms.length && !$terms.is(':checked')) {
                $terms.siblings('.sbt-field-error').show();
                isValid = false;
            }

            // Check payment method
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            if (!paymentMethod) {
                $('input[name="payment_method"]').closest('.sbt-form-group').find('.sbt-field-error').show();
                isValid = false;
            }

            return isValid;
        },
        
        initBookingWidget: function() {
            const $widget = $('.sbt-booking-widget');
            if (!$widget.length) return;
            
            $widget.data('currentStep', 1);
            this.showStep(1);
        },
        
        initCalendar: function() {
            const $calendar = $('.sbt-calendar');
            if (!$calendar.length) return;

            // Check for tour ID from data attribute or URL params
            const tourId = $calendar.data('tour-id') || (window.sbtUrlParams && window.sbtUrlParams.id) || 0;
            if (tourId) {
                SBT.selectedTourId = tourId;
            }

            // Check for preselected date from data attribute
            const preselectDate = $calendar.data('preselect-date');
            if (preselectDate) {
                SBT.selectedDate = preselectDate;
                const dateObj = new Date(preselectDate);
                // Load availability first, then render calendar
                if (tourId) {
                    SBT.loadTourAvailability(tourId, dateObj.getFullYear(), dateObj.getMonth());
                } else {
                    SBT.renderCalendar(dateObj.getFullYear(), dateObj.getMonth());
                }
            } else {
                const today = new Date();
                // Load availability first if we have a tour ID
                if (tourId) {
                    SBT.loadTourAvailability(tourId, today.getFullYear(), today.getMonth());
                } else {
                    SBT.renderCalendar(today.getFullYear(), today.getMonth());
                }
            }

            // Add event listeners for calendar navigation
            $('.sbt-calendar-prev').on('click', () => {
                SBT.currentMonth = SBT.currentMonth || new Date().getMonth();
                SBT.currentYear = SBT.currentYear || new Date().getFullYear();
                SBT.currentMonth--;
                if (SBT.currentMonth < 0) {
                    SBT.currentMonth = 11;
                    SBT.currentYear--;
                }
                // Reload availability for new month
                if (SBT.selectedTourId) {
                    SBT.loadTourAvailability(SBT.selectedTourId, SBT.currentYear, SBT.currentMonth);
                } else {
                    SBT.renderCalendar(SBT.currentYear, SBT.currentMonth);
                }
            });

            $('.sbt-calendar-next').on('click', () => {
                SBT.currentMonth = SBT.currentMonth || new Date().getMonth();
                SBT.currentYear = SBT.currentYear || new Date().getFullYear();
                SBT.currentMonth++;
                if (SBT.currentMonth > 11) {
                    SBT.currentMonth = 0;
                    SBT.currentYear++;
                }
                // Reload availability for new month
                if (SBT.selectedTourId) {
                    SBT.loadTourAvailability(SBT.selectedTourId, SBT.currentYear, SBT.currentMonth);
                } else {
                    SBT.renderCalendar(SBT.currentYear, SBT.currentMonth);
                }
            });
        },
        
        initURLFilters: function() {
            // Check for URL parameters from localized script
            if (typeof window.sbtUrlParams !== 'undefined') {
                const params = window.sbtUrlParams;

                // Auto-select tour by tour_id
                if (params.tour_id) {
                    const $tourOption = $(`.sbt-tour-option[data-tour-id="${params.tour_id}"]`);
                    if ($tourOption.length) {
                        $tourOption.trigger('click');
                    }
                }

                // Auto-select tour by tour_type (for backward compatibility)
                if (!params.tour_id && (params.tour || params.tour_type)) {
                    const tourType = params.tour || params.tour_type;
                    const $tourOption = $(`.sbt-tour-option[data-tour-type="${tourType}"]`);
                    if ($tourOption.length) {
                        $tourOption.trigger('click');
                    }
                }

                // Auto-select date if set
                if (params.date) {
                    this.selectedDate = params.date;
                }

                // Auto-set passenger count if set
                if (params.passengers && params.passengers > 0) {
                    this.passengerCount = parseInt(params.passengers);
                    $('.sbt-counter-value').text(this.passengerCount);
                    // Update button states
                    $('.sbt-counter-btn[data-action="decrement"]').prop('disabled', this.passengerCount <= 1);
                }
            }

            // Check for data attributes from shortcode
            const $widget = $('.sbt-booking-widget');
            if ($widget.length) {
                const preselectedTourId = $widget.data('preselect-tour');
                const preselectedDate = $widget.data('preselect-date');
                const preselectedPassengers = $widget.data('preselect-passengers');

                if (preselectedTourId) {
                    const $tourOption = $(`.sbt-tour-option[data-tour-id="${preselectedTourId}"]`);
                    if ($tourOption.length) {
                        setTimeout(() => $tourOption.trigger('click'), 100);
                    }
                }

                if (preselectedDate) {
                    this.selectedDate = preselectedDate;
                }

                if (preselectedPassengers && preselectedPassengers > 0) {
                    this.passengerCount = parseInt(preselectedPassengers);
                    $('.sbt-counter-value').text(this.passengerCount);
                    $('.sbt-counter-btn[data-action="decrement"]').prop('disabled', this.passengerCount <= 1);
                }
            }

            // Initialize tour type filter dropdown
            $('.sbt-tour-type-filter').on('change', function() {
                const tourType = $(this).val();
                const currentUrl = window.location.pathname;
                const newUrl = tourType ? `${currentUrl}?tour=${tourType}` : currentUrl;
                window.location.href = newUrl;
            });
        },
        
        selectTour: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $option = $(this).closest('.sbt-tour-card-archive, .sbt-tour-option');
            const tourId = $option.data('tour-id');

            if (!tourId) return;

            // Remove selected class from all tour options
            $('.sbt-tour-card-archive.sbt-tour-option').removeClass('selected');
            $('.sbt-tour-option').removeClass('selected');

            // Add selected class to clicked option
            $option.addClass('selected');

            // Check radio if exists
            $option.find('input[type="radio"]').prop('checked', true);

            SBT.selectedTourId = tourId;

            // Load availability for this tour
            SBT.loadTourAvailability(tourId);

            // Enable next button
            $('.sbt-step-content[data-step="1"] .sbt-step-next').prop('disabled', false);
        },
        
        selectDate: function(e) {
            const $day = $(this);
            const date = $day.data('date');
            
            $('.sbt-calendar-day').removeClass('selected');
            $day.addClass('selected');
            
            SBT.selectedDate = date;
            
            // Check availability for this date
            if (SBT.selectedTourId && SBT.passengerCount) {
                SBT.checkAvailability(SBT.selectedTourId, date, SBT.passengerCount);
            }
        },
        
        updatePassengerCount: function(e) {
            const $btn = $(this);
            const action = $btn.data('action');
            const $counter = $btn.siblings('.sbt-counter-value');
            let count = parseInt($counter.text());
            const maxCapacity = parseInt($btn.closest('.sbt-passenger-counter').data('max-capacity')) || 20;
            
            if (action === 'increment' && count < maxCapacity) {
                count++;
            } else if (action === 'decrement' && count > 1) {
                count--;
            }
            
            $counter.text(count);
            SBT.passengerCount = count;
            
            // Update button states
            $btn.siblings('[data-action="decrement"]').prop('disabled', count <= 1);
            $btn.siblings('[data-action="increment"]').prop('disabled', count >= maxCapacity);
        },
        
        loadTourAvailability: function(tourId, year, month) {
            // Use provided year/month or default to current
            const now = new Date();
            const targetYear = year !== undefined ? year : now.getFullYear();
            const targetMonth = month !== undefined ? month : now.getMonth();

            const monthString = `${targetYear}-${String(targetMonth + 1).padStart(2, '0')}`;

            $.ajax({
                url: `${sbtData.restUrl}tours/${tourId}/available-dates`,
                method: 'GET',
                data: { month: monthString },
                success: function(response) {
                    SBT.availabilityData = response;
                    SBT.renderCalendar(targetYear, targetMonth);
                },
                error: function(xhr) {
                    console.error('Failed to load availability:', xhr);
                    // Still render calendar even if availability fails
                    SBT.renderCalendar(targetYear, targetMonth);
                }
            });
        },
        
        checkAvailability: function(tourId, date, passengers) {
            const $availabilityMessage = $('.sbt-availability-message');
            
            $.ajax({
                url: `${sbtData.restUrl}availability`,
                method: 'POST',
                data: {
                    tour_id: tourId,
                    date: date,
                    passengers: passengers
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', sbtData.nonce);
                },
                success: function(response) {
                    if (response.available) {
                        const message = response.almost_full 
                            ? `<span class="sbt-badge sbt-badge-warning">Almost Full - ${response.remaining_capacity} spots left!</span>`
                            : `<span class="sbt-badge sbt-badge-success">Available</span>`;
                        $availabilityMessage.html(message);
                    } else {
                        $availabilityMessage.html('<span class="sbt-badge sbt-badge-danger">Not Available</span>');
                    }
                }
            });
        },
        
        renderCalendar: function(year, month) {
            const $calendar = $('.sbt-calendar-grid');
            if (!$calendar.length) return;

            // Store current month/year
            this.currentMonth = month;
            this.currentYear = year;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();

            $('.sbt-calendar-title').text(
                new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
            );
            
            let html = '';
            
            // Day headers
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                html += `<div class="sbt-calendar-day-header">${day}</div>`;
            });
            
            // Empty cells before first day
            for (let i = 0; i < firstDay; i++) {
                html += '<div class="sbt-calendar-day disabled"></div>';
            }
            
            // Days of month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                
                let classes = 'sbt-calendar-day';
                let disabled = false;
                
                // Check if date is in the past
                if (date < today.setHours(0, 0, 0, 0)) {
                    classes += ' disabled';
                    disabled = true;
                }
                
                // Check availability data
                if (SBT.availabilityData) {
                    const availability = SBT.availabilityData.find(a => a.date === dateString);
                    if (availability) {
                        if (availability.status === 'blocked' || availability.status === 'full') {
                            classes += ' blocked';
                            disabled = true;
                        } else if (availability.almost_full) {
                            classes += ' almost-full';
                        }
                    }
                }
                
                // Check if selected
                if (SBT.selectedDate === dateString) {
                    classes += ' selected';
                }
                
                const dataAttr = disabled ? '' : `data-date="${dateString}"`;
                html += `<div class="${classes}" ${dataAttr}>${day}</div>`;
            }
            
            $calendar.html(html);
        },
        
        nextStep: function(e) {
            e.preventDefault();
            
            const $widget = $('.sbt-booking-widget');
            const currentStep = $widget.data('currentStep');
            
            // Validate current step
            if (!SBT.validateStep(currentStep)) {
                return;
            }
            
            const nextStep = currentStep + 1;
            if (nextStep <= 4) {
                $widget.data('currentStep', nextStep);
                SBT.showStep(nextStep);
            }
        },
        
        prevStep: function(e) {
            e.preventDefault();
            
            const $widget = $('.sbt-booking-widget');
            const currentStep = $widget.data('currentStep');
            const prevStep = currentStep - 1;
            
            if (prevStep >= 1) {
                $widget.data('currentStep', prevStep);
                SBT.showStep(prevStep);
            }
        },
        
        showStep: function(step) {
            // Update step indicators
            $('.sbt-booking-step').each(function(index) {
                const stepNum = index + 1;
                $(this).removeClass('active completed');
                
                if (stepNum === step) {
                    $(this).addClass('active');
                } else if (stepNum < step) {
                    $(this).addClass('completed');
                }
            });
            
            // Show content for step
            $('.sbt-step-content').hide();
            $(`.sbt-step-content[data-step="${step}"]`).show();
            
            // Update summary if on final step
            if (step === 4) {
                SBT.updateBookingSummary();
            }
        },
        
        validateStep: function(step) {
            let isValid = true;
            let message = '';
            
            switch(step) {
                case 1:
                    if (!SBT.selectedTourId) {
                        message = 'Please select a tour';
                        isValid = false;
                    }
                    break;
                case 2:
                    if (!SBT.selectedDate) {
                        message = 'Please select a date';
                        isValid = false;
                    }
                    break;
                case 3:
                    if (!SBT.passengerCount || SBT.passengerCount < 1) {
                        message = 'Please select number of passengers';
                        isValid = false;
                    }
                    break;
            }
            
            if (!isValid) {
                SBT.showError(message);
            }
            
            return isValid;
        },
        
        updateBookingSummary: function() {
            const tourId = SBT.selectedTourId;
            const date = SBT.selectedDate;
            const passengers = SBT.passengerCount;
            
            // Fetch tour details and update summary
            $.ajax({
                url: `${sbtData.restUrl}tours/${tourId}`,
                method: 'GET',
                success: function(tour) {
                    const totalAmount = tour.price_per_person 
                        ? tour.price * passengers 
                        : tour.price;
                    
                    $('.sbt-summary-tour').text(tour.title);
                    $('.sbt-summary-date').text(new Date(date).toLocaleDateString('en-US', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    }));
                    $('.sbt-summary-passengers').text(passengers);
                    $('.sbt-summary-total').text(`€${totalAmount.toFixed(2)}`);
                }
            });
        },
        
        submitBooking: function(e) {
            e.preventDefault();

            const $form = $(this);

            // Validate form first
            if (!SBT.validateForm($form)) {
                SBT.showError('Please fill in all required fields correctly');
                // Scroll to first error
                const $firstError = $form.find('.error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 300);
                }
                return;
            }

            const formData = $form.serializeArray();
            const bookingData = {};

            formData.forEach(field => {
                bookingData[field.name] = field.value;
            });

            // Add tour, date, and passenger data
            bookingData.tour_id = SBT.selectedTourId;
            bookingData.date = SBT.selectedDate;
            bookingData.passengers = SBT.passengerCount;

            // Add reCAPTCHA token if enabled
            if (sbtData.recaptchaSiteKey) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(sbtData.recaptchaSiteKey, {action: 'booking'}).then(function(token) {
                        bookingData.recaptcha_token = token;
                        SBT.processBooking(bookingData);
                    });
                });
            } else {
                SBT.processBooking(bookingData);
            }
        },
        
        processBooking: function(bookingData) {
            const $submitBtn = $('.sbt-submit-booking');
            
            $.ajax({
                url: `${sbtData.restUrl}bookings`,
                method: 'POST',
                data: bookingData,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', sbtData.nonce);
                    $submitBtn.prop('disabled', true).html('<span class="sbt-loading"></span> Processing...');
                },
                success: function(response) {
                    // Redirect to payment page
                    window.location.href = `/booking-payment?code=${response.confirmation_code}`;
                },
                error: function(xhr) {
                    const error = xhr.responseJSON || {};
                    SBT.showError(error.message || 'Booking failed. Please try again.');
                    $submitBtn.prop('disabled', false).text('Complete Booking');
                }
            });
        },
        
        showError: function(message) {
            const $errorContainer = $('.sbt-error-message');
            
            if ($errorContainer.length) {
                $errorContainer.text(message).fadeIn();
                setTimeout(() => $errorContainer.fadeOut(), 5000);
            } else {
                alert(message);
            }
        },
        
        // Payment processing
        initStripePayment: function(bookingId, amount) {
            if (!window.Stripe || !sbtData.stripeKey) return;
            
            const stripe = Stripe(sbtData.stripeKey);
            const elements = stripe.elements();
            const cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#111827',
                        '::placeholder': {
                            color: '#9ca3af'
                        }
                    }
                }
            });
            
            cardElement.mount('#sbt-card-element');
            
            $('.sbt-payment-form').on('submit', function(e) {
                e.preventDefault();
                
                stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement
                }).then(function(result) {
                    if (result.error) {
                        SBT.showError(result.error.message);
                    } else {
                        SBT.confirmPayment(bookingId, result.paymentMethod.id, 'stripe');
                    }
                });
            });
        },
        
        confirmPayment: function(bookingId, paymentToken, method) {
            $.ajax({
                url: `${sbtData.restUrl}payment/process`,
                method: 'POST',
                data: {
                    booking_id: bookingId,
                    payment_method: method,
                    payment_token: paymentToken
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', sbtData.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = `/booking-confirmation?code=${response.confirmation_code}`;
                    } else if (response.requires_action) {
                        // Handle 3D Secure
                        SBT.handle3DSecure(response.client_secret);
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON || {};
                    SBT.showError(error.message || 'Payment failed. Please try again.');
                }
            });
        },
        
        handle3DSecure: function(clientSecret) {
            const stripe = Stripe(sbtData.stripeKey);
            stripe.confirmCardPayment(clientSecret).then(function(result) {
                if (result.error) {
                    SBT.showError(result.error.message);
                } else {
                    // Payment succeeded
                    location.reload();
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        SBT.init();
    });
    
    // Expose SBT globally
    window.SBT = SBT;
    
})(jQuery);
