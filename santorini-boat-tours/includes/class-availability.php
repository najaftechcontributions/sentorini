<?php
/**
 * Availability Management for Santorini Boat Tours
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBT_Availability {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Constructor
    }
    
    public function check_availability($tour_id, $date, $passengers) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        // Get max capacity for tour
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        
        // Get current bookings for this date
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE tour_id = %d AND tour_date = %s",
            $tour_id,
            $date
        ));
        
        if (!$row) {
            // No bookings yet for this date, check if date is valid
            return $this->is_date_valid($tour_id, $date) && $passengers <= $max_capacity;
        }
        
        // Check if status is blocked
        if ($row->status === 'blocked') {
            return false;
        }
        
        // Check if there's enough capacity
        $remaining = $row->max_capacity - $row->booked_count;
        
        return $remaining >= $passengers;
    }
    
    public function get_remaining_capacity($tour_id, $date) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE tour_id = %d AND tour_date = %s",
            $tour_id,
            $date
        ));
        
        if (!$row) {
            return $max_capacity;
        }
        
        if ($row->status === 'blocked') {
            return 0;
        }
        
        return max(0, $row->max_capacity - $row->booked_count);
    }
    
    public function reserve_capacity($tour_id, $date, $passengers) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        
        // Check if row exists
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE tour_id = %d AND tour_date = %s",
            $tour_id,
            $date
        ));
        
        if (!$row) {
            // Create new row
            $result = $wpdb->insert(
                $table,
                [
                    'tour_id' => $tour_id,
                    'tour_date' => $date,
                    'booked_count' => $passengers,
                    'max_capacity' => $max_capacity,
                    'status' => $passengers >= $max_capacity ? 'full' : 'available'
                ],
                ['%d', '%s', '%d', '%d', '%s']
            );
            
            return $result !== false;
        }
        
        // Check if there's enough capacity
        $remaining = $row->max_capacity - $row->booked_count;
        
        if ($remaining < $passengers) {
            return false;
        }
        
        // Update booked count
        $new_count = $row->booked_count + $passengers;
        $new_status = $new_count >= $max_capacity ? 'full' : 'available';
        
        $result = $wpdb->update(
            $table,
            [
                'booked_count' => $new_count,
                'status' => $new_status
            ],
            [
                'tour_id' => $tour_id,
                'tour_date' => $date
            ],
            ['%d', '%s'],
            ['%d', '%s']
        );
        
        return $result !== false;
    }
    
    public function release_capacity($tour_id, $date, $passengers) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE tour_id = %d AND tour_date = %s",
            $tour_id,
            $date
        ));
        
        if (!$row) {
            return false;
        }
        
        $new_count = max(0, $row->booked_count - $passengers);
        $new_status = $new_count >= $row->max_capacity ? 'full' : 'available';
        
        $result = $wpdb->update(
            $table,
            [
                'booked_count' => $new_count,
                'status' => $new_status
            ],
            [
                'tour_id' => $tour_id,
                'tour_date' => $date
            ],
            ['%d', '%s'],
            ['%d', '%s']
        );
        
        return $result !== false;
    }
    
    public function get_available_dates($tour_id, $month) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        // Parse month (format: YYYY-MM)
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        
        // Get tour configuration
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        $available_days = get_field('tour_available_days', $tour_id);
        $blackout_dates = get_field('tour_blackout_dates', $tour_id) ?: [];
        
        // Get booked dates from database
        $booked_dates = $wpdb->get_results($wpdb->prepare(
            "SELECT tour_date, booked_count, status FROM $table 
             WHERE tour_id = %d AND tour_date BETWEEN %s AND %s",
            $tour_id,
            $start_date,
            $end_date
        ), OBJECT_K);
        
        $result = [];
        $current_date = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        
        while ($current_date <= $end_timestamp) {
            $date_string = date('Y-m-d', $current_date);
            $day_of_week = strtolower(date('l', $current_date));
            
            // Skip if not in future
            if ($current_date < strtotime('today')) {
                $current_date = strtotime('+1 day', $current_date);
                continue;
            }
            
            // Check if day of week is available
            if (!in_array($day_of_week, $available_days)) {
                $current_date = strtotime('+1 day', $current_date);
                continue;
            }
            
            // Check blackout dates
            $is_blackout = false;
            foreach ($blackout_dates as $blackout) {
                if ($blackout['blackout_date'] === $date_string) {
                    $is_blackout = true;
                    break;
                }
            }
            
            if ($is_blackout) {
                $result[$date_string] = [
                    'date' => $date_string,
                    'status' => 'blocked',
                    'remaining' => 0,
                    'almost_full' => false
                ];
                $current_date = strtotime('+1 day', $current_date);
                continue;
            }
            
            // Check database for booking status
            if (isset($booked_dates[$date_string])) {
                $row = $booked_dates[$date_string];
                $remaining = $row->max_capacity - $row->booked_count;
                
                $result[$date_string] = [
                    'date' => $date_string,
                    'status' => $row->status,
                    'remaining' => max(0, $remaining),
                    'almost_full' => $remaining > 0 && $remaining <= 5
                ];
            } else {
                $result[$date_string] = [
                    'date' => $date_string,
                    'status' => 'available',
                    'remaining' => $max_capacity,
                    'almost_full' => false
                ];
            }
            
            $current_date = strtotime('+1 day', $current_date);
        }
        
        return array_values($result);
    }
    
    public function block_date($tour_id, $date, $reason = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        
        // Check if row exists
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE tour_id = %d AND tour_date = %s",
            $tour_id,
            $date
        ));
        
        if (!$row) {
            $result = $wpdb->insert(
                $table,
                [
                    'tour_id' => $tour_id,
                    'tour_date' => $date,
                    'booked_count' => 0,
                    'max_capacity' => $max_capacity,
                    'status' => 'blocked'
                ],
                ['%d', '%s', '%d', '%d', '%s']
            );
        } else {
            $result = $wpdb->update(
                $table,
                ['status' => 'blocked'],
                [
                    'tour_id' => $tour_id,
                    'tour_date' => $date
                ],
                ['%s'],
                ['%d', '%s']
            );
        }
        
        return $result !== false;
    }
    
    public function unblock_date($tour_id, $date) {
        global $wpdb;
        $table = $wpdb->prefix . 'sbt_availability';
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE tour_id = %d AND tour_date = %s",
            $tour_id,
            $date
        ));
        
        if (!$row) {
            return false;
        }
        
        $max_capacity = get_field('tour_max_capacity', $tour_id);
        $new_status = $row->booked_count >= $max_capacity ? 'full' : 'available';
        
        $result = $wpdb->update(
            $table,
            ['status' => $new_status],
            [
                'tour_id' => $tour_id,
                'tour_date' => $date
            ],
            ['%s'],
            ['%d', '%s']
        );
        
        return $result !== false;
    }
    
    private function is_date_valid($tour_id, $date) {
        // Check if date is in the future
        if (strtotime($date) < strtotime('today')) {
            return false;
        }
        
        // Check if day of week is available
        $available_days = get_field('tour_available_days', $tour_id);
        $day_of_week = strtolower(date('l', strtotime($date)));
        
        if (!in_array($day_of_week, $available_days)) {
            return false;
        }
        
        // Check blackout dates
        $blackout_dates = get_field('tour_blackout_dates', $tour_id) ?: [];
        foreach ($blackout_dates as $blackout) {
            if ($blackout['blackout_date'] === $date) {
                return false;
            }
        }
        
        return true;
    }
}
