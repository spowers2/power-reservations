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
    'pr_styling_mode',                 // Form styling mode (custom/theme/minimal)
    'pr_form_fields',                  // Enabled form fields
    'pr_form_field_order',             // Form field order
    'pr_form_field_settings',          // Form field settings
    'pr_form_colors',                  // Form color scheme
    'pr_custom_css',                   // Custom CSS styles
    
    // Form styling options (NEW)
    'pr_primary_color',                // Primary color for form
    'pr_secondary_color',              // Secondary color for form
    'pr_button_color',                 // Button color
    'pr_input_border_color',           // Input border color
    'pr_label_color',                  // Label text color
    'pr_font_size',                    // Form font size
    'pr_border_radius',                // Border radius for inputs
    'pr_input_padding',                // Input padding
    'pr_form_width',                   // Maximum form width
    
    // Advanced settings
    'pr_max_reservations_per_slot',    // Max reservations per time slot
    'pr_edit_window_hours',            // Hours window for editing reservations
    'pr_require_approval',             // Require admin approval
    'pr_blackout_dates',               // Blackout dates
    'pr_opening_hours',                // Business opening hours
    'pr_time_slot_duration',           // Time slot duration
    
    // Email template settings
    'pr_email_from_name',              // Email from name
    'pr_email_from_address',           // Email from address
    'pr_email_subject_customer',       // Customer email subject
    'pr_email_subject_admin',          // Admin email subject
    'pr_email_template_customer',      // Customer email template
    'pr_email_template_admin'          // Admin email template
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
   POST META CLEANUP
   ======================================== */

// Remove any post meta data associated with the plugin
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'pr_%'");

/* ========================================
   UPLOADED FILES CLEANUP
   ======================================== */

// Remove any uploaded files from the plugin directory
$upload_dir = wp_upload_dir();
$plugin_upload_dir = $upload_dir['basedir'] . '/power-reservations';

if (is_dir($plugin_upload_dir)) {
    // Recursively delete the directory and all its contents
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($plugin_upload_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        @$todo($fileinfo->getRealPath());
    }

    @rmdir($plugin_upload_dir);
}

/* ========================================
   CAPABILITIES CLEANUP
   ======================================== */

// Remove custom capabilities if any were added
$role = get_role('administrator');
if ($role) {
    $role->remove_cap('manage_reservations');
    $role->remove_cap('edit_reservations');
    $role->remove_cap('delete_reservations');
}

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
// This catches any options we didn't explicitly list above
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pr_%'");

// Remove any transients with plugin prefix
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pr_%' OR option_name LIKE '_transient_timeout_pr_%'");

// Remove any site transients (for multisite)
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_pr_%' OR option_name LIKE '_site_transient_timeout_pr_%'");

// Flush rewrite rules to remove any custom endpoints
flush_rewrite_rules();

// Clear WordPress object cache
wp_cache_flush();

/* ========================================
   LOGGING & CONFIRMATION
   ======================================== */

// Count of items removed for logging
$tables_removed = count($tables_to_remove);
$options_removed = count($options_to_remove);

// Log the uninstall for debugging purposes
if (function_exists('error_log')) {
    error_log(sprintf(
        'Power Reservations plugin successfully uninstalled. Removed: %d tables, %d options, all transients, user meta, post meta, and uploaded files.',
        $tables_removed,
        $options_removed
    ));
}

/* ========================================
   UNINSTALL COMPLETE
   ======================================== */
// All plugin data has been completely removed from the WordPress installation
// No remnants of the plugin remain in the database or file system