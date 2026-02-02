import React, { useState, useEffect } from 'react';
import { Builder } from '@builder.io/react';

/**
 * AvailabilityCalendar Component for Santorini Boat Tours
 * Displays tour availability in a calendar format
 */
const AvailabilityCalendar = ({ 
    tourId = null,
    selectedDate = null,
    onDateSelect = null,
    passengerCount = 1,
    showLegend = true 
}) => {
    const [currentMonth, setCurrentMonth] = useState(new Date());
    const [availability, setAvailability] = useState([]);
    const [loading, setLoading] = useState(false);
    const [internalSelectedDate, setInternalSelectedDate] = useState(selectedDate);
    
    useEffect(() => {
        if (tourId) {
            loadAvailability();
        }
    }, [tourId, currentMonth]);
    
    useEffect(() => {
        setInternalSelectedDate(selectedDate);
    }, [selectedDate]);
    
    const loadAvailability = async () => {
        setLoading(true);
        const month = `${currentMonth.getFullYear()}-${String(currentMonth.getMonth() + 1).padStart(2, '0')}`;
        
        try {
            const response = await fetch(`/wp-json/sbt/v1/tours/${tourId}/available-dates?month=${month}`);
            const data = await response.json();
            setAvailability(data);
        } catch (error) {
            console.error('Error loading availability:', error);
        } finally {
            setLoading(false);
        }
    };
    
    const handleDateClick = (dateString) => {
        setInternalSelectedDate(dateString);
        
        if (onDateSelect) {
            onDateSelect(dateString);
        }
        
        // Check availability for this date
        if (tourId && passengerCount) {
            checkDateAvailability(dateString);
        }
    };
    
    const checkDateAvailability = async (date) => {
        try {
            const response = await fetch('/wp-json/sbt/v1/availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.sbtData?.nonce || ''
                },
                body: JSON.stringify({
                    tour_id: tourId,
                    date: date,
                    passengers: passengerCount
                })
            });
            
            const result = await response.json();
            
            // Dispatch custom event with availability info
            const event = new CustomEvent('sbt-availability-checked', {
                detail: result
            });
            window.dispatchEvent(event);
        } catch (error) {
            console.error('Error checking availability:', error);
        }
    };
    
    const prevMonth = () => {
        setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1));
    };
    
    const nextMonth = () => {
        setCurrentMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1));
    };
    
    const renderCalendar = () => {
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const days = [];
        
        // Day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            days.push(
                <div key={`header-${day}`} className="sbt-calendar-day-header" style={{
                    textAlign: 'center',
                    fontWeight: '600',
                    color: '#4b5563',
                    padding: '8px 0',
                    fontSize: '14px'
                }}>
                    {day}
                </div>
            );
        });
        
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
            let title = '';
            
            // Check if date is in the past
            if (date < today) {
                classes += ' disabled';
                disabled = true;
                title = 'Past date';
            }
            
            // Check availability data
            const avail = availability.find(a => a.date === dateString);
            if (avail) {
                if (avail.status === 'blocked') {
                    classes += ' blocked';
                    disabled = true;
                    title = 'Not available';
                } else if (avail.status === 'full') {
                    classes += ' blocked';
                    disabled = true;
                    title = 'Fully booked';
                } else if (avail.almost_full) {
                    classes += ' almost-full';
                    title = `Only ${avail.remaining} spots left`;
                } else {
                    title = `${avail.remaining} spots available`;
                }
            }
            
            // Check if selected
            if (internalSelectedDate === dateString) {
                classes += ' selected';
            }
            
            days.push(
                <div
                    key={day}
                    className={classes}
                    data-date={dateString}
                    title={title}
                    onClick={() => !disabled && handleDateClick(dateString)}
                    style={{ position: 'relative' }}
                >
                    {day}
                </div>
            );
        }
        
        return days;
    };
    
    const renderLegend = () => {
        if (!showLegend) return null;
        
        return (
            <div className="sbt-calendar-legend" style={{
                display: 'flex',
                justifyContent: 'center',
                gap: '20px',
                marginTop: '20px',
                fontSize: '14px',
                flexWrap: 'wrap'
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        background: '#3b82f6'
                    }}></div>
                    <span>Selected</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        background: '#f3f4f6',
                        border: '2px solid #e5e7eb'
                    }}></div>
                    <span>Available</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        background: '#fef3c7',
                        position: 'relative'
                    }}>
                        <span style={{
                            position: 'absolute',
                            top: '-2px',
                            right: '-2px',
                            color: '#f59e0b',
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }}>!</span>
                    </div>
                    <span>Almost Full</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <div style={{
                        width: '16px',
                        height: '16px',
                        borderRadius: '4px',
                        background: '#e5e7eb',
                        textDecoration: 'line-through'
                    }}></div>
                    <span>Unavailable</span>
                </div>
            </div>
        );
    };
    
    if (!tourId) {
        return (
            <div className="sbt-calendar" style={{ 
                background: 'white', 
                borderRadius: '12px', 
                padding: '20px',
                textAlign: 'center'
            }}>
                <p style={{ color: '#6b7280' }}>Please select a tour to view availability</p>
            </div>
        );
    }
    
    return (
        <div className="sbt-calendar">
            <div className="sbt-calendar-header">
                <button
                    className="sbt-calendar-nav"
                    onClick={prevMonth}
                    disabled={loading}
                >
                    ‹
                </button>
                
                <div className="sbt-calendar-title">
                    {currentMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
                </div>
                
                <button
                    className="sbt-calendar-nav"
                    onClick={nextMonth}
                    disabled={loading}
                >
                    ›
                </button>
            </div>
            
            {loading && (
                <div style={{ textAlign: 'center', padding: '20px' }}>
                    <div className="sbt-loading"></div>
                </div>
            )}
            
            {!loading && (
                <>
                    <div className="sbt-calendar-grid">
                        {renderCalendar()}
                    </div>
                    {renderLegend()}
                </>
            )}
        </div>
    );
};

// Register with Builder.io
Builder.registerComponent(AvailabilityCalendar, {
    name: 'SBT Availability Calendar',
    inputs: [
        {
            name: 'tourId',
            type: 'number',
            helperText: 'WordPress Tour ID to show availability for'
        },
        {
            name: 'passengerCount',
            type: 'number',
            defaultValue: 1,
            helperText: 'Number of passengers for availability check'
        },
        {
            name: 'showLegend',
            type: 'boolean',
            defaultValue: true,
            helperText: 'Show calendar legend'
        }
    ],
    image: 'https://cdn.builder.io/api/v1/image/assets%2Fpwgjf0RoYWbdnJSbpBAjXNRMe9F2%2Ffb27a7c790324294af8be1c35fe30f4d'
});

export default AvailabilityCalendar;
