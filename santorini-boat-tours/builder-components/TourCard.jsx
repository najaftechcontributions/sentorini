import React, { useEffect, useState } from 'react';
import { Builder } from '@builder.io/react';

/**
 * TourCard Component for Santorini Boat Tours
 * Displays individual tour information with booking CTA
 */
const TourCard = ({ tourId, showExcerpt = true, showHighlights = false }) => {
    const [tour, setTour] = useState(null);
    const [loading, setLoading] = useState(true);
    
    useEffect(() => {
        if (!tourId) return;
        
        // Fetch tour data from WordPress REST API
        fetch(`/wp-json/sbt/v1/tours/${tourId}`)
            .then(res => res.json())
            .then(data => {
                setTour(data);
                setLoading(false);
            })
            .catch(err => {
                console.error('Error loading tour:', err);
                setLoading(false);
            });
    }, [tourId]);
    
    if (loading) {
        return (
            <div className="sbt-tour-card">
                <div className="sbt-loading"></div>
            </div>
        );
    }
    
    if (!tour) {
        return (
            <div className="sbt-tour-card">
                <p>Tour not found</p>
            </div>
        );
    }
    
    const formatPrice = (price) => {
        return `€${parseFloat(price).toFixed(2)}`;
    };
    
    const getTourTypeLabel = (type) => {
        const labels = {
            'morning': 'Morning Tour',
            'sunset': 'Sunset Tour',
            'private': 'Private Cruise',
            'water_taxi_ios': 'Water Taxi to Ios',
            'water_taxi_mykonos': 'Water Taxi to Mykonos'
        };
        return labels[type] || type;
    };
    
    const handleBookNow = () => {
        window.location.href = `/book/?tour=${tour.slug}`;
    };
    
    return (
        <div className="sbt-tour-card">
            {tour.featured_image && (
                <img 
                    src={tour.featured_image} 
                    alt={tour.title}
                    className="sbt-tour-card-image"
                />
            )}
            
            <div className="sbt-tour-card-content">
                <span className="sbt-tour-card-type">
                    {getTourTypeLabel(tour.type)}
                </span>
                
                <h3 className="sbt-tour-card-title">{tour.title}</h3>
                
                {showExcerpt && tour.excerpt && (
                    <p className="sbt-tour-card-description">
                        {tour.excerpt}
                    </p>
                )}
                
                <div className="sbt-tour-card-meta">
                    <div className="sbt-tour-card-meta-item">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                        </svg>
                        {tour.duration} hours
                    </div>
                    
                    <div className="sbt-tour-card-meta-item">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                        </svg>
                        Max {tour.max_capacity}
                    </div>
                </div>
                
                {showHighlights && tour.highlights && tour.highlights.length > 0 && (
                    <div className="sbt-tour-card-highlights">
                        <h4>Highlights:</h4>
                        <ul>
                            {tour.highlights.slice(0, 3).map((highlight, index) => (
                                <li key={index}>{highlight.highlight_text}</li>
                            ))}
                        </ul>
                    </div>
                )}
                
                <div className="sbt-tour-card-footer">
                    <div className="sbt-tour-card-price">
                        {formatPrice(tour.price)}
                        {tour.price_per_person && (
                            <span className="sbt-tour-card-price-label"> /person</span>
                        )}
                    </div>
                    
                    <button 
                        className="sbt-btn sbt-btn-primary"
                        onClick={handleBookNow}
                    >
                        Book Now
                    </button>
                </div>
            </div>
        </div>
    );
};

// Register with Builder.io
Builder.registerComponent(TourCard, {
    name: 'SBT Tour Card',
    inputs: [
        {
            name: 'tourId',
            type: 'number',
            required: true,
            helperText: 'Enter the WordPress Tour ID'
        },
        {
            name: 'showExcerpt',
            type: 'boolean',
            defaultValue: true,
            helperText: 'Show tour excerpt/description'
        },
        {
            name: 'showHighlights',
            type: 'boolean',
            defaultValue: false,
            helperText: 'Show tour highlights list'
        }
    ],
    image: 'https://cdn.builder.io/api/v1/image/assets%2Fpwgjf0RoYWbdnJSbpBAjXNRMe9F2%2Ffb27a7c790324294af8be1c35fe30f4d'
});

export default TourCard;
