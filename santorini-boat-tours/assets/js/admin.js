/**
 * Santorini Boat Tours - Admin JavaScript
 */

(function($) {
    'use strict';
    
    const SBTAdmin = {
        init: function() {
            this.bindEvents();
            this.initCalendar();
            this.initBookingActions();
        },
        
        bindEvents: function() {
            $(document).on('click', '.sbt-cancel-booking', this.cancelBooking);
            $(document).on('click', '.sbt-confirm-booking', this.confirmBooking);
            $(document).on('click', '.sbt-refund-booking', this.refundBooking);
            $(document).on('change', '#sbt-booking-status', this.updateBookingStatus);
        },
        
        initCalendar: function() {
            const $container = $('#sbt-calendar-container');
            if (!$container.length) return;
            
            // Render admin calendar for availability management
            this.renderAdminCalendar();
        },
        
        renderAdminCalendar: function() {
            const $container = $('#sbt-calendar-container');
            const today = new Date();
            
            const html = `
                <div class="sbt-admin-calendar">
                    <div class="sbt-calendar-header">
                        <button class="button sbt-calendar-prev-month">Previous</button>
                        <h3 class="sbt-calendar-month-year"></h3>
                        <button class="button sbt-calendar-next-month">Next</button>
                    </div>
                    <div class="sbt-calendar-grid"></div>
                    <div class="sbt-calendar-legend">
                        <div><span class="sbt-status-dot confirmed"></span> Available</div>
                        <div><span class="sbt-status-dot pending"></span> Almost Full</div>
                        <div><span class="sbt-status-dot cancelled"></span> Fully Booked</div>
                    </div>
                </div>
            `;
            
            $container.html(html);
            this.currentCalendarDate = today;
            this.updateAdminCalendar();
            
            // Bind navigation events
            $(document).on('click', '.sbt-calendar-prev-month', () => {
                this.currentCalendarDate.setMonth(this.currentCalendarDate.getMonth() - 1);
                this.updateAdminCalendar();
            });
            
            $(document).on('click', '.sbt-calendar-next-month', () => {
                this.currentCalendarDate.setMonth(this.currentCalendarDate.getMonth() + 1);
                this.updateAdminCalendar();
            });
        },
        
        updateAdminCalendar: function() {
            const date = this.currentCalendarDate;
            const year = date.getFullYear();
            const month = date.getMonth();
            
            // Update month/year display
            $('.sbt-calendar-month-year').text(
                date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
            );
            
            // Render calendar days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            let html = '';
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            
            dayHeaders.forEach(day => {
                html += `<div class="sbt-calendar-day-header"><strong>${day}</strong></div>`;
            });
            
            for (let i = 0; i < firstDay; i++) {
                html += '<div class="sbt-calendar-day"></div>';
            }
            
            for (let day = 1; day <= daysInMonth; day++) {
                html += `
                    <div class="sbt-calendar-day-cell" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">
                        <div class="sbt-day-number">${day}</div>
                        <div class="sbt-day-bookings">-</div>
                    </div>
                `;
            }
            
            $('.sbt-calendar-grid').html(html);
            
            // Load booking counts
            this.loadBookingCounts(year, month);
        },
        
        loadBookingCounts: function(year, month) {
            // This would make an AJAX call to get booking counts per day
            // For now, just a placeholder
            console.log('Load booking counts for', year, month);
        },
        
        initBookingActions: function() {
            // Initialize any booking-specific actions
            this.loadBookingLog();
        },
        
        loadBookingLog: function() {
            const $logContainer = $('.sbt-booking-log');
            if (!$logContainer.length) return;
            
            const bookingId = $logContainer.data('booking-id');
            
            // Load booking log via AJAX
            // Placeholder for now
        },
        
        cancelBooking: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to cancel this booking?')) {
                return;
            }
            
            const $btn = $(this);
            const bookingId = $btn.data('booking-id');
            
            $.ajax({
                url: `${sbtAdminData.ajaxUrl}`,
                method: 'POST',
                data: {
                    action: 'sbt_cancel_booking',
                    booking_id: bookingId,
                    nonce: sbtAdminData.nonce
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).html('<span class="sbt-admin-loading"></span> Cancelling...');
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel booking: ' + (response.data || 'Unknown error'));
                        $btn.prop('disabled', false).text('Cancel Booking');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text('Cancel Booking');
                }
            });
        },
        
        confirmBooking: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const bookingId = $btn.data('booking-id');
            
            $.ajax({
                url: `${sbtAdminData.ajaxUrl}`,
                method: 'POST',
                data: {
                    action: 'sbt_confirm_booking',
                    booking_id: bookingId,
                    nonce: sbtAdminData.nonce
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).html('<span class="sbt-admin-loading"></span> Confirming...');
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to confirm booking: ' + (response.data || 'Unknown error'));
                        $btn.prop('disabled', false).text('Confirm Booking');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text('Confirm Booking');
                }
            });
        },
        
        refundBooking: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to refund this booking? This action cannot be undone.')) {
                return;
            }
            
            const $btn = $(this);
            const bookingId = $btn.data('booking-id');
            
            $.ajax({
                url: `${sbtAdminData.ajaxUrl}`,
                method: 'POST',
                data: {
                    action: 'sbt_refund_booking',
                    booking_id: bookingId,
                    nonce: sbtAdminData.nonce
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).html('<span class="sbt-admin-loading"></span> Processing Refund...');
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to process refund: ' + (response.data || 'Unknown error'));
                        $btn.prop('disabled', false).text('Refund Booking');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text('Refund Booking');
                }
            });
        },
        
        updateBookingStatus: function(e) {
            const $select = $(this);
            const bookingId = $select.data('booking-id');
            const newStatus = $select.val();
            
            $.ajax({
                url: `${sbtAdminData.ajaxUrl}`,
                method: 'POST',
                data: {
                    action: 'sbt_update_booking_status',
                    booking_id: bookingId,
                    status: newStatus,
                    nonce: sbtAdminData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        const $message = $('<div class="notice notice-success is-dismissible"><p>Booking status updated successfully.</p></div>');
                        $('.wrap h1').after($message);
                        setTimeout(() => $message.fadeOut(), 3000);
                    } else {
                        alert('Failed to update status: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        SBTAdmin.init();
    });
    
    // Expose globally
    window.SBTAdmin = SBTAdmin;
    
})(jQuery);
