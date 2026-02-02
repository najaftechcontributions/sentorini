import React, { useState, useEffect } from 'react';
import { Builder } from '@builder.io/react';

/**
 * TestimonialSlider Component for Santorini Boat Tours
 * Displays customer testimonials in a slider format
 */
const TestimonialSlider = ({ 
    testimonials = [], 
    autoPlay = true, 
    autoPlayInterval = 5000,
    showStars = true 
}) => {
    const [currentIndex, setCurrentIndex] = useState(0);
    
    // Default testimonials if none provided
    const defaultTestimonials = [
        {
            text: "The sunset tour was absolutely breathtaking! The crew was professional and friendly. We saw the most amazing views of Santorini from the water. Highly recommend!",
            author: "Sarah Johnson",
            location: "New York, USA",
            rating: 5
        },
        {
            text: "Best experience in Santorini! The boat was clean and comfortable, and the captain took us to the most beautiful spots. Swimming in the hot springs was unforgettable.",
            author: "Marco Rossi",
            location: "Rome, Italy",
            rating: 5
        },
        {
            text: "We booked the private cruise for our anniversary and it was perfect. The staff went above and beyond to make our day special. Thank you for the memories!",
            author: "Emily & David Chen",
            location: "London, UK",
            rating: 5
        },
        {
            text: "The water taxi to Ios was convenient and comfortable. Much better than the ferry! Will definitely use this service again on our next trip.",
            author: "Anna Schmidt",
            location: "Berlin, Germany",
            rating: 5
        }
    ];
    
    const slides = testimonials.length > 0 ? testimonials : defaultTestimonials;
    
    useEffect(() => {
        if (!autoPlay || slides.length <= 1) return;
        
        const interval = setInterval(() => {
            setCurrentIndex((prevIndex) => (prevIndex + 1) % slides.length);
        }, autoPlayInterval);
        
        return () => clearInterval(interval);
    }, [autoPlay, autoPlayInterval, slides.length]);
    
    const goToSlide = (index) => {
        setCurrentIndex(index);
    };
    
    const nextSlide = () => {
        setCurrentIndex((prevIndex) => (prevIndex + 1) % slides.length);
    };
    
    const prevSlide = () => {
        setCurrentIndex((prevIndex) => (prevIndex - 1 + slides.length) % slides.length);
    };
    
    const renderStars = (rating) => {
        if (!showStars) return null;
        
        return (
            <div className="sbt-testimonial-stars">
                {[...Array(5)].map((_, i) => (
                    <span key={i} style={{ color: i < rating ? '#f59e0b' : '#d1d5db' }}>★</span>
                ))}
            </div>
        );
    };
    
    if (slides.length === 0) {
        return null;
    }
    
    return (
        <div className="sbt-testimonials">
            <div className="sbt-container">
                <h2 className="sbt-section-title">What Our Guests Say</h2>
                <p className="sbt-section-subtitle">
                    Hear from travelers who experienced the magic of Santorini with us
                </p>
                
                <div className="sbt-testimonial-slider">
                    <div className="sbt-testimonial">
                        {renderStars(slides[currentIndex].rating || 5)}
                        
                        <p className="sbt-testimonial-text">
                            "{slides[currentIndex].text}"
                        </p>
                        
                        <div className="sbt-testimonial-author">
                            {slides[currentIndex].author}
                        </div>
                        
                        {slides[currentIndex].location && (
                            <div className="sbt-testimonial-location">
                                {slides[currentIndex].location}
                            </div>
                        )}
                    </div>
                    
                    {slides.length > 1 && (
                        <>
                            <div className="sbt-slider-controls" style={{ 
                                display: 'flex', 
                                justifyContent: 'center', 
                                gap: '10px', 
                                marginTop: '30px' 
                            }}>
                                <button
                                    onClick={prevSlide}
                                    className="sbt-slider-arrow"
                                    style={{
                                        background: 'white',
                                        border: '2px solid #3b82f6',
                                        borderRadius: '50%',
                                        width: '40px',
                                        height: '40px',
                                        cursor: 'pointer',
                                        fontSize: '20px',
                                        color: '#3b82f6'
                                    }}
                                >
                                    ‹
                                </button>
                                
                                <div className="sbt-slider-dots" style={{ 
                                    display: 'flex', 
                                    alignItems: 'center', 
                                    gap: '8px' 
                                }}>
                                    {slides.map((_, index) => (
                                        <button
                                            key={index}
                                            onClick={() => goToSlide(index)}
                                            style={{
                                                width: '12px',
                                                height: '12px',
                                                borderRadius: '50%',
                                                border: 'none',
                                                background: currentIndex === index ? '#3b82f6' : '#d1d5db',
                                                cursor: 'pointer',
                                                padding: 0
                                            }}
                                            aria-label={`Go to testimonial ${index + 1}`}
                                        />
                                    ))}
                                </div>
                                
                                <button
                                    onClick={nextSlide}
                                    className="sbt-slider-arrow"
                                    style={{
                                        background: 'white',
                                        border: '2px solid #3b82f6',
                                        borderRadius: '50%',
                                        width: '40px',
                                        height: '40px',
                                        cursor: 'pointer',
                                        fontSize: '20px',
                                        color: '#3b82f6'
                                    }}
                                >
                                    ›
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

// Register with Builder.io
Builder.registerComponent(TestimonialSlider, {
    name: 'SBT Testimonial Slider',
    inputs: [
        {
            name: 'testimonials',
            type: 'list',
            defaultValue: [],
            subFields: [
                {
                    name: 'text',
                    type: 'longText',
                    required: true,
                    helperText: 'The testimonial text'
                },
                {
                    name: 'author',
                    type: 'string',
                    required: true,
                    helperText: 'Name of the person'
                },
                {
                    name: 'location',
                    type: 'string',
                    helperText: 'Location (e.g., "Paris, France")'
                },
                {
                    name: 'rating',
                    type: 'number',
                    defaultValue: 5,
                    helperText: 'Star rating (1-5)'
                }
            ],
            helperText: 'Add custom testimonials. Leave empty to use default testimonials.'
        },
        {
            name: 'autoPlay',
            type: 'boolean',
            defaultValue: true,
            helperText: 'Auto-advance testimonials'
        },
        {
            name: 'autoPlayInterval',
            type: 'number',
            defaultValue: 5000,
            helperText: 'Auto-play interval in milliseconds'
        },
        {
            name: 'showStars',
            type: 'boolean',
            defaultValue: true,
            helperText: 'Show star ratings'
        }
    ],
    image: 'https://cdn.builder.io/api/v1/image/assets%2Fpwgjf0RoYWbdnJSbpBAjXNRMe9F2%2Ffb27a7c790324294af8be1c35fe30f4d'
});

export default TestimonialSlider;
