import React, { useState, useEffect } from 'react';
import { Builder } from '@builder.io/react';

/**
 * BookingWidget Component for Santorini Boat Tours
 * Complete 4-step booking flow
 */
const BookingWidget = ({ autoSelectTour = null, showSteps = true }) => {
    const [currentStep, setCurrentStep] = useState(1);
    const [selectedTour, setSelectedTour] = useState(null);
    const [selectedDate, setSelectedDate] = useState(null);
    const [passengerCount, setPassengerCount] = useState(2);
    const [tours, setTours] = useState([]);
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        country: '',
        specialRequests: ''
    });
    const [errors, setErrors] = useState({});
    
    useEffect(() => {
        loadTours();
        
        // Check for URL parameters
        if (window.sbtUrlParams) {
            const params = window.sbtUrlParams;
            
            if (params.tour_filter || autoSelectTour) {
                const tourType = params.tour_filter || autoSelectTour;
                // Auto-select tour after tours are loaded
                setTimeout(() => {
                    const tour = tours.find(t => t.type === tourType || t.slug === tourType);
                    if (tour) {
                        setSelectedTour(tour);
                        setCurrentStep(2);
                    }
                }, 500);
            }
            
            if (params.tour_date) {
                setSelectedDate(params.tour_date);
            }
            
            if (params.tour_passengers) {
                setPassengerCount(parseInt(params.tour_passengers));
            }
        }
    }, [autoSelectTour]);
    
    const loadTours = async () => {
        try {
            const response = await fetch('/wp-json/sbt/v1/tours');
            const data = await response.json();
            setTours(data);
        } catch (error) {
            console.error('Error loading tours:', error);
        }
    };
    
    const validateStep = (step) => {
        let stepErrors = {};
        
        switch(step) {
            case 1:
                if (!selectedTour) {
                    stepErrors.tour = 'Please select a tour';
                }
                break;
            case 2:
                if (!selectedDate) {
                    stepErrors.date = 'Please select a date';
                }
                break;
            case 3:
                if (!passengerCount || passengerCount < 1) {
                    stepErrors.passengers = 'Please select number of passengers';
                }
                break;
            case 4:
                if (!formData.firstName) stepErrors.firstName = 'First name is required';
                if (!formData.lastName) stepErrors.lastName = 'Last name is required';
                if (!formData.email) stepErrors.email = 'Email is required';
                if (!formData.phone) stepErrors.phone = 'Phone is required';
                break;
        }
        
        setErrors(stepErrors);
        return Object.keys(stepErrors).length === 0;
    };
    
    const nextStep = () => {
        if (validateStep(currentStep)) {
            setCurrentStep(currentStep + 1);
        }
    };
    
    const prevStep = () => {
        setCurrentStep(currentStep - 1);
    };
    
    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validateStep(4)) return;
        
        setLoading(true);
        
        try {
            const bookingData = {
                tour_id: selectedTour.id,
                date: selectedDate,
                passengers: passengerCount,
                first_name: formData.firstName,
                last_name: formData.lastName,
                email: formData.email,
                phone: formData.phone,
                country: formData.country,
                special_requests: formData.specialRequests
            };
            
            const response = await fetch('/wp-json/sbt/v1/bookings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.sbtData?.nonce || ''
                },
                body: JSON.stringify(bookingData)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Redirect to payment page
                window.location.href = `/booking-payment?code=${result.confirmation_code}`;
            } else {
                setErrors({ submit: result.message || 'Booking failed. Please try again.' });
            }
        } catch (error) {
            setErrors({ submit: 'An error occurred. Please try again.' });
        } finally {
            setLoading(false);
        }
    };
    
    const renderStepIndicators = () => {
        if (!showSteps) return null;
        
        const steps = [
            { number: 1, label: 'Select Tour' },
            { number: 2, label: 'Choose Date' },
            { number: 3, label: 'Passengers' },
            { number: 4, label: 'Your Details' }
        ];
        
        return (
            <div className="sbt-booking-steps">
                {steps.map(step => (
                    <div 
                        key={step.number}
                        className={`sbt-booking-step ${currentStep === step.number ? 'active' : ''} ${currentStep > step.number ? 'completed' : ''}`}
                    >
                        <div className="sbt-booking-step-number">{step.number}</div>
                        <div className="sbt-booking-step-label">{step.label}</div>
                    </div>
                ))}
            </div>
        );
    };
    
    const renderStep1 = () => (
        <div className="sbt-step-content" data-step="1">
            <h3 className="sbt-section-title">Select Your Tour</h3>
            <div className="sbt-tour-selection">
                {tours.map(tour => (
                    <div
                        key={tour.id}
                        className={`sbt-tour-option ${selectedTour?.id === tour.id ? 'selected' : ''}`}
                        onClick={() => setSelectedTour(tour)}
                        data-tour-type={tour.type}
                    >
                        <input type="radio" name="tour" value={tour.id} checked={selectedTour?.id === tour.id} readOnly />
                        <h4>{tour.title}</h4>
                        <p className="sbt-tour-card-type">{tour.type.replace('_', ' ').toUpperCase()}</p>
                        <div className="sbt-tour-card-meta">
                            <span>⏱ {tour.duration}h</span>
                            <span>👥 Max {tour.max_capacity}</span>
                        </div>
                        <div className="sbt-tour-card-price">€{tour.price}</div>
                    </div>
                ))}
            </div>
            {errors.tour && <p className="sbt-form-error">{errors.tour}</p>}
            <button className="sbt-btn sbt-btn-primary sbt-step-next" onClick={nextStep}>
                Continue to Date Selection
            </button>
        </div>
    );
    
    const renderStep2 = () => (
        <div className="sbt-step-content" data-step="2">
            <h3 className="sbt-section-title">Choose Your Date</h3>
            <div className="sbt-availability-message"></div>
            <AvailabilityCalendar
                tourId={selectedTour?.id}
                selectedDate={selectedDate}
                onDateSelect={setSelectedDate}
                passengerCount={passengerCount}
            />
            {errors.date && <p className="sbt-form-error">{errors.date}</p>}
            <div style={{ display: 'flex', gap: '10px', marginTop: '20px' }}>
                <button className="sbt-btn sbt-btn-secondary sbt-step-prev" onClick={prevStep}>
                    Back
                </button>
                <button className="sbt-btn sbt-btn-primary sbt-step-next" onClick={nextStep}>
                    Continue to Passengers
                </button>
            </div>
        </div>
    );
    
    const renderStep3 = () => (
        <div className="sbt-step-content" data-step="3">
            <h3 className="sbt-section-title">Number of Passengers</h3>
            <div className="sbt-passenger-counter" data-max-capacity={selectedTour?.max_capacity}>
                <button
                    className="sbt-counter-btn"
                    data-action="decrement"
                    onClick={() => setPassengerCount(Math.max(1, passengerCount - 1))}
                    disabled={passengerCount <= 1}
                >
                    −
                </button>
                <div className="sbt-counter-value">{passengerCount}</div>
                <button
                    className="sbt-counter-btn"
                    data-action="increment"
                    onClick={() => setPassengerCount(Math.min(selectedTour?.max_capacity || 20, passengerCount + 1))}
                    disabled={passengerCount >= (selectedTour?.max_capacity || 20)}
                >
                    +
                </button>
            </div>
            {errors.passengers && <p className="sbt-form-error">{errors.passengers}</p>}
            <div style={{ display: 'flex', gap: '10px', marginTop: '40px' }}>
                <button className="sbt-btn sbt-btn-secondary sbt-step-prev" onClick={prevStep}>
                    Back
                </button>
                <button className="sbt-btn sbt-btn-primary sbt-step-next" onClick={nextStep}>
                    Continue to Details
                </button>
            </div>
        </div>
    );
    
    const renderStep4 = () => {
        const totalAmount = selectedTour?.price_per_person 
            ? selectedTour.price * passengerCount 
            : selectedTour?.price || 0;
        
        return (
            <div className="sbt-step-content" data-step="4">
                <h3 className="sbt-section-title">Your Details</h3>
                
                <div className="sbt-booking-summary" style={{ background: '#f0f9ff', padding: '20px', borderRadius: '8px', marginBottom: '30px' }}>
                    <h4 style={{ marginTop: 0 }}>Booking Summary</h4>
                    <p><strong>Tour:</strong> <span className="sbt-summary-tour">{selectedTour?.title}</span></p>
                    <p><strong>Date:</strong> <span className="sbt-summary-date">{new Date(selectedDate).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span></p>
                    <p><strong>Passengers:</strong> <span className="sbt-summary-passengers">{passengerCount}</span></p>
                    <p><strong>Total:</strong> <span className="sbt-summary-total">€{totalAmount.toFixed(2)}</span></p>
                </div>
                
                <form className="sbt-booking-form" onSubmit={handleSubmit}>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                        <div className="sbt-form-group">
                            <label className="sbt-form-label">First Name *</label>
                            <input
                                type="text"
                                className="sbt-form-input"
                                value={formData.firstName}
                                onChange={(e) => setFormData({...formData, firstName: e.target.value})}
                            />
                            {errors.firstName && <p className="sbt-form-error">{errors.firstName}</p>}
                        </div>
                        
                        <div className="sbt-form-group">
                            <label className="sbt-form-label">Last Name *</label>
                            <input
                                type="text"
                                className="sbt-form-input"
                                value={formData.lastName}
                                onChange={(e) => setFormData({...formData, lastName: e.target.value})}
                            />
                            {errors.lastName && <p className="sbt-form-error">{errors.lastName}</p>}
                        </div>
                    </div>
                    
                    <div className="sbt-form-group">
                        <label className="sbt-form-label">Email *</label>
                        <input
                            type="email"
                            className="sbt-form-input"
                            value={formData.email}
                            onChange={(e) => setFormData({...formData, email: e.target.value})}
                        />
                        {errors.email && <p className="sbt-form-error">{errors.email}</p>}
                    </div>
                    
                    <div className="sbt-form-group">
                        <label className="sbt-form-label">Phone *</label>
                        <input
                            type="tel"
                            className="sbt-form-input"
                            value={formData.phone}
                            onChange={(e) => setFormData({...formData, phone: e.target.value})}
                        />
                        {errors.phone && <p className="sbt-form-error">{errors.phone}</p>}
                    </div>
                    
                    <div className="sbt-form-group">
                        <label className="sbt-form-label">Country</label>
                        <input
                            type="text"
                            className="sbt-form-input"
                            value={formData.country}
                            onChange={(e) => setFormData({...formData, country: e.target.value})}
                        />
                    </div>
                    
                    <div className="sbt-form-group">
                        <label className="sbt-form-label">Special Requests</label>
                        <textarea
                            className="sbt-form-textarea"
                            rows="4"
                            value={formData.specialRequests}
                            onChange={(e) => setFormData({...formData, specialRequests: e.target.value})}
                        ></textarea>
                    </div>
                    
                    {errors.submit && (
                        <div className="sbt-error-message" style={{ color: 'red', marginBottom: '20px' }}>
                            {errors.submit}
                        </div>
                    )}
                    
                    <div style={{ display: 'flex', gap: '10px' }}>
                        <button type="button" className="sbt-btn sbt-btn-secondary sbt-step-prev" onClick={prevStep}>
                            Back
                        </button>
                        <button type="submit" className="sbt-btn sbt-btn-primary sbt-btn-large sbt-submit-booking" disabled={loading}>
                            {loading ? 'Processing...' : 'Complete Booking'}
                        </button>
                    </div>
                </form>
            </div>
        );
    };
    
    return (
        <div className="sbt-booking-widget">
            {renderStepIndicators()}
            <div className="sbt-booking-content">
                {currentStep === 1 && renderStep1()}
                {currentStep === 2 && renderStep2()}
                {currentStep === 3 && renderStep3()}
                {currentStep === 4 && renderStep4()}
            </div>
        </div>
    );
};

// Simple AvailabilityCalendar component embedded
const AvailabilityCalendar = ({ tourId, selectedDate, onDateSelect, passengerCount }) => {
    const [currentMonth, setCurrentMonth] = useState(new Date());
    const [availability, setAvailability] = useState([]);
    
    useEffect(() => {
        if (tourId) {
            loadAvailability();
        }
    }, [tourId, currentMonth]);
    
    const loadAvailability = async () => {
        const month = `${currentMonth.getFullYear()}-${String(currentMonth.getMonth() + 1).padStart(2, '0')}`;
        try {
            const response = await fetch(`/wp-json/sbt/v1/tours/${tourId}/available-dates?month=${month}`);
            const data = await response.json();
            setAvailability(data);
        } catch (error) {
            console.error('Error loading availability:', error);
        }
    };
    
    const renderCalendar = () => {
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const days = [];
        
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            days.push(<div key={`empty-${i}`} className="sbt-calendar-day disabled"></div>);
        }
        
        // Days of month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            let classes = 'sbt-calendar-day';
            let disabled = false;
            
            if (date < today) {
                classes += ' disabled';
                disabled = true;
            }
            
            const avail = availability.find(a => a.date === dateString);
            if (avail) {
                if (avail.status === 'blocked' || avail.status === 'full') {
                    classes += ' blocked';
                    disabled = true;
                } else if (avail.almost_full) {
                    classes += ' almost-full';
                }
            }
            
            if (selectedDate === dateString) {
                classes += ' selected';
            }
            
            days.push(
                <div
                    key={day}
                    className={classes}
                    data-date={dateString}
                    onClick={() => !disabled && onDateSelect(dateString)}
                >
                    {day}
                </div>
            );
        }
        
        return days;
    };
    
    return (
        <div className="sbt-calendar">
            <div className="sbt-calendar-header">
                <button
                    className="sbt-calendar-nav"
                    onClick={() => setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1))}
                >
                    ‹
                </button>
                <div className="sbt-calendar-title">
                    {currentMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
                </div>
                <button
                    className="sbt-calendar-nav"
                    onClick={() => setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1))}
                >
                    ›
                </button>
            </div>
            <div className="sbt-calendar-grid">
                {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => (
                    <div key={day} className="sbt-calendar-day-header">{day}</div>
                ))}
                {renderCalendar()}
            </div>
        </div>
    );
};

// Register with Builder.io
Builder.registerComponent(BookingWidget, {
    name: 'SBT Booking Widget',
    inputs: [
        {
            name: 'autoSelectTour',
            type: 'string',
            helperText: 'Auto-select a tour by slug or type (e.g., "morning", "sunset", "water_taxi_ios")',
            defaultValue: null
        },
        {
            name: 'showSteps',
            type: 'boolean',
            defaultValue: true,
            helperText: 'Show step indicators at the top'
        }
    ],
    image: 'https://cdn.builder.io/api/v1/image/assets%2Fpwgjf0RoYWbdnJSbpBAjXNRMe9F2%2Ffb27a7c790324294af8be1c35fe30f4d'
});

export default BookingWidget;
