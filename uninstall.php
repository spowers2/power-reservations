<?php
/**
 * Power Reservations Uninstall Handler
 * 
 * This file is executed when the plugin is uninstalled (deleted) from WordPress.
 * It completely removes all plugin data from the database.
 * 
 * IMPORTANT: This only runs when the plugin is DELETED, not deactivated.
 * 
 * @package PowerReservations
 * @version 2.0.0
 */

// Security check - only run if called by WordPress uninstall process
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/* ========================================
   DATABASE CLEANUP
   ======================================== */

// Get WordPress database connection
global $wpdb;

// Remove all plugin tables
$tables_to_remove = array(
    $wpdb->prefix . 'pr_reservations',    // Main reservations table
    $wpdb->prefix . 'pr_settings',        // Plugin settings table  
    $wpdb->prefix . 'pr_email_templates'  // Email templates table
);

foreach ($tables_to_remove as $table_name) {
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

/* ========================================
   OPTIONS CLEANUP
   ======================================== */

// Remove all plugin options from wp_options table
$options_to_remove = array(
    // Core settings
    'pr_db_version',                    // Database version
    'pr_plugin_migrated',              // Plugin migration flag
    'pr_business_name',                // Business name setting
    'pr_business_email',               // Business email setting
    'pr_max_party_size',               // Maximum party size setting
    'pr_booking_window',               // Booking window (days) setting
    'pr_time_slots',                   // Available time slots setting
    
    // Form builder settings
    'pr_styling_mode',                 // Form styling mode
    'pr_form_fields',                  // Enabled form fields
    'pr_form_field_order',             // Form field order
    'pr_form_field_settings',          // Form field settings
    'pr_form_colors',                  // Form color scheme
    'pr_custom_css',                   // Custom CSS styles
    
    // Advanced settings
    'pr_max_reservations_per_slot',    // Max reservations per time slot
    'pr_edit_window_hours',            // Hours window for editing reservations
    'pr_require_approval',             // Require admin approval
    'pr_blackout_dates',               // Blackout dates
    'pr_opening_hours',                // Business opening hours
    'pr_time_slot_duration'            // Time slot duration
);

foreach ($options_to_remove as $option_name) {
    delete_option($option_name);
}

/* ========================================
   SCHEDULED EVENTS CLEANUP
   ======================================== */

// Remove scheduled cron events
wp_clear_scheduled_hook('pr_send_reminder');
wp_clear_scheduled_hook('pr_daily_cleanup');

/* ========================================
   TRANSIENTS CLEANUP
   ======================================== */

// Remove any cached transients
delete_transient('pr_dashboard_stats');
delete_transient('pr_email_template_cache');

/* ========================================
   USER META CLEANUP
   ======================================== */

// Remove user meta data (if any was stored)
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'pr_%'");

/* ========================================
   ELEMENTOR WIDGETS CLEANUP
   ======================================== */

// Clear Elementor cache if it exists
if (class_exists('\Elementor\Plugin')) {
    try {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    } catch (Exception $e) {
        // Silently handle any Elementor cleanup errors
    }
}

/* ========================================
   FINAL CLEANUP
   ======================================== */

// Remove any remaining plugin-specific options that might have been missed
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pr_%'");

// Log the uninstall for debugging purposes
if (function_exists('error_log')) {
    error_log('Power Reservations plugin successfully uninstalled and all data removed.');
}