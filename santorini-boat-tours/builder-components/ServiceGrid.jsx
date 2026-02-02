import React from 'react';
import { Builder } from '@builder.io/react';

/**
 * ServiceGrid Component for Santorini Boat Tours
 * Displays tour services in a grid layout
 */
const ServiceGrid = ({ 
    services = [],
    columns = 4,
    showIcons = true 
}) => {
    // Default services matching common boat tour offerings
    const defaultServices = [
        {
            title: 'Morning Boat Tours',
            description: 'Experience the tranquil beauty of Santorini at sunrise. 5-hour tour with swimming stops.',
            icon: '🌅',
            link: '/morning-tour',
            type: 'morning'
        },
        {
            title: 'Sunset Boat Tours',
            description: 'Witness the world-famous Santorini sunset from the sea. Includes dinner and drinks.',
            icon: '🌇',
            link: '/sunset-tour',
            type: 'sunset'
        },
        {
            title: 'Private Group Cruises',
            description: 'Customize your perfect day on the water. Ideal for families and celebrations.',
            icon: '⛵',
            link: '/private-cruise',
            type: 'private'
        },
        {
            title: 'Water Taxi to Ios',
            description: 'Fast and comfortable transport between Santorini and Ios island.',
            icon: '🚤',
            link: '/santorini-to-ios',
            type: 'water_taxi_ios'
        },
        {
            title: 'Water Taxi to Mykonos',
            description: 'Direct boat service to Mykonos. Skip the ferry and travel in style.',
            icon: '⚓',
            link: '/santorini-to-mykonos',
            type: 'water_taxi_mykonos'
        },
        {
            title: 'Hot Springs Visit',
            description: 'Swim in the volcanic hot springs and explore the colorful beaches.',
            icon: '♨️',
            link: '/book/?tour=morning',
            type: 'hot_springs'
        },
        {
            title: 'Caldera Cruise',
            description: 'Sail around the stunning caldera with stops at the best viewpoints.',
            icon: '🏔️',
            link: '/book/?tour=sunset',
            type: 'caldera'
        },
        {
            title: 'Snorkeling & Swimming',
            description: 'Discover the underwater world with provided snorkeling equipment.',
            icon: '🤿',
            link: '/book/',
            type: 'snorkeling'
        }
    ];
    
    const displayServices = services.length > 0 ? services : defaultServices.slice(0, columns);
    
    const handleServiceClick = (service) => {
        if (service.link) {
            window.location.href = service.link;
        } else if (service.type) {
            window.location.href = `/book/?tour=${service.type}`;
        }
    };
    
    const gridColumns = {
        2: 'repeat(auto-fit, minmax(300px, 1fr))',
        3: 'repeat(auto-fit, minmax(250px, 1fr))',
        4: 'repeat(auto-fit, minmax(250px, 1fr))',
        5: 'repeat(auto-fit, minmax(200px, 1fr))'
    };
    
    return (
        <div className="sbt-section">
            <div className="sbt-container">
                <h2 className="sbt-section-title">Our Services</h2>
                <p className="sbt-section-subtitle">
                    Explore the beauty of Santorini from the sea
                </p>
                
                <div 
                    className="sbt-services-grid"
                    style={{
                        display: 'grid',
                        gridTemplateColumns: gridColumns[columns] || gridColumns[4],
                        gap: '30px',
                        marginTop: '40px'
                    }}
                >
                    {displayServices.map((service, index) => (
                        <div
                            key={index}
                            className="sbt-service-item"
                            onClick={() => handleServiceClick(service)}
                            style={{ cursor: service.link || service.type ? 'pointer' : 'default' }}
                        >
                            {showIcons && service.icon && (
                                <div className="sbt-service-icon">
                                    {service.icon}
                                </div>
                            )}
                            
                            <h3 className="sbt-service-title">{service.title}</h3>
                            
                            {service.description && (
                                <p className="sbt-service-description">{service.description}</p>
                            )}
                            
                            {(service.link || service.type) && (
                                <div style={{ marginTop: '16px' }}>
                                    <span className="sbt-btn sbt-btn-primary" style={{ 
                                        display: 'inline-block',
                                        padding: '8px 20px',
                                        fontSize: '14px'
                                    }}>
                                        Learn More →
                                    </span>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};

// Register with Builder.io
Builder.registerComponent(ServiceGrid, {
    name: 'SBT Service Grid',
    inputs: [
        {
            name: 'services',
            type: 'list',
            defaultValue: [],
            subFields: [
                {
                    name: 'title',
                    type: 'string',
                    required: true,
                    helperText: 'Service title'
                },
                {
                    name: 'description',
                    type: 'longText',
                    helperText: 'Service description'
                },
                {
                    name: 'icon',
                    type: 'string',
                    helperText: 'Emoji or icon (e.g., 🌅, ⛵, 🚤)'
                },
                {
                    name: 'link',
                    type: 'string',
                    helperText: 'Link URL when clicked'
                },
                {
                    name: 'type',
                    type: 'string',
                    enum: ['morning', 'sunset', 'private', 'water_taxi_ios', 'water_taxi_mykonos', 'hot_springs', 'caldera', 'snorkeling'],
                    helperText: 'Tour type for automatic booking link'
                }
            ],
            helperText: 'Add custom services. Leave empty to use default services.'
        },
        {
            name: 'columns',
            type: 'number',
            enum: [2, 3, 4, 5],
            defaultValue: 4,
            helperText: 'Number of columns in the grid'
        },
        {
            name: 'showIcons',
            type: 'boolean',
            defaultValue: true,
            helperText: 'Show service icons'
        }
    ],
    image: 'https://cdn.builder.io/api/v1/image/assets%2Fpwgjf0RoYWbdnJSbpBAjXNRMe9F2%2Ffb27a7c790324294af8be1c35fe30f4d'
});

export default ServiceGrid;
