<?php
/**
 * Plugin Name: Power Reservations
 * Plugin URI: https://github.com/scottpowers/power-reservations
 * Description: Simple restaurant reservation management system for WordPress.
 * Version: 2.0.4
 * Author: Scott Powers
 * Author URI: https://scottpowers.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: power-reservations
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * 
 * Plugin Structure:
 * ================
 * 1. Security checks and constants
 * 2. PR_Reservations_List_Table - Admin table display
 * 3. PowerReservations - Main plugin class with:
 *    - Database table creation and management
 *    - Admin pages (dashboard, settings, form builder, styling)
 *    - Frontend shortcodes and AJAX handlers
 *    - Email system with templates
 *    - Elementor integration
 * 4. Plugin initialization and hooks
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Ensure WordPress functions are available
if (!function_exists('add_action')) {
    exit;
}

// Plugin constants
define('PR_VERSION', '2.0.4'); // Rev4: Fixed form builder validation issues and preview styling
define('PR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PR_PLUGIN_FILE', __FILE__);
define('PR_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PR_PLUGIN_SLUG', 'power-reservations');

// Include WordPress List Table class
if (!class_exists('WP_List_Table')) {
    $wp_list_table_path = ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    if (file_exists($wp_list_table_path)) {
        require_once($wp_list_table_path);
    } else {
        // Log the issue but don't fail
        if (function_exists('error_log')) {
            error_log('Power Reservations: WP_List_Table class file not found at: ' . $wp_list_table_path);
        }
        // Create a dummy class to prevent fatal errors
        if (!class_exists('WP_List_Table')) {
            class WP_List_Table {
                public function __construct() {}
                public function prepare_items() {}
                public function display() {}
                protected function get_pagenum() { return 1; }
                protected function get_columns() { return array(); }
                protected function get_sortable_columns() { return array(); }
                protected function get_bulk_actions() { return array(); }
                protected function process_bulk_action() {}
                protected function column_default($item, $column_name) { return ''; }
            }
        }
    }
}

/**
 * Power Reservations List Table Class
 */
if (!class_exists('PR_Reservations_List_Table')) {
class PR_Reservations_List_Table extends WP_List_Table {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'reservation',
            'plural' => 'reservations',
            'ajax' => false
        ));
    }
    
    /**
     * Get columns
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'reservation_id' => __('ID', 'power-reservations'),
            'name' => __('Name', 'power-reservations'),
            'email' => __('Email', 'power-reservations'),
            'phone' => __('Phone', 'power-reservations'),
            'reservation_date' => __('Date', 'power-reservations'),
            'reservation_time' => __('Time', 'power-reservations'),
            'party_size' => __('Party Size', 'power-reservations'),
            'status' => __('Status', 'power-reservations'),
            'created_at' => __('Created', 'power-reservations'),
            'actions' => __('Actions', 'power-reservations')
        );
    }
    
    /**
     * Get sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'reservation_id' => array('reservation_id', false),
            'name' => array('name', false),
            'email' => array('email', false),
            'reservation_date' => array('reservation_date', true),
            'reservation_time' => array('reservation_time', false),
            'party_size' => array('party_size', false),
            'status' => array('status', false),
            'created_at' => array('created_at', true)
        );
    }
    
    /**
     * Get bulk actions
     */
    public function get_bulk_actions() {
        return array(
            'approve' => __('Approve', 'power-reservations'),
            'decline' => __('Decline', 'power-reservations'),
            'delete' => __('Delete', 'power-reservations')
        );
    }
    
    /**
     * Column default
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'reservation_id':
            case 'name':
            case 'email':
            case 'phone':
            case 'reservation_date':
            case 'reservation_time':
            case 'party_size':
            case 'created_at':
                return $item->$column_name;
            default:
                return print_r($item, true);
        }
    }
    
    /**
     * Column checkbox
     */
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="reservations[]" value="%s" />', $item->id);
    }
    
    /**
     * Column status
     */
    public function column_status($item) {
        $status_labels = array(
            'pending' => '<span class="status-pending">' . __('Pending', 'power-reservations') . '</span>',
            'approved' => '<span class="status-approved">' . __('Approved', 'power-reservations') . '</span>',
            'declined' => '<span class="status-declined">' . __('Declined', 'power-reservations') . '</span>',
            'cancelled' => '<span class="status-cancelled">' . __('Cancelled', 'power-reservations') . '</span>'
        );
        
        return isset($status_labels[$item->status]) ? $status_labels[$item->status] : $item->status;
    }
    
    /**
     * Column actions
     */
    public function column_actions($item) {
        $actions = array();
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 'power-reservations';
        
        if ($item->status === 'pending') {
            $actions['approve'] = sprintf(
                '<a href="?page=%s&action=approve&reservation=%s&_wpnonce=%s" class="button button-small button-primary">%s</a>',
                $page,
                $item->id,
                wp_create_nonce('approve_reservation_' . $item->id),
                __('Approve', 'power-reservations')
            );
            $actions['decline'] = sprintf(
                '<a href="?page=%s&action=decline&reservation=%s&_wpnonce=%s" class="button button-small">%s</a>',
                $page,
                $item->id,
                wp_create_nonce('decline_reservation_' . $item->id),
                __('Decline', 'power-reservations')
            );
        }
        
        $actions['edit'] = sprintf(
            '<a href="?page=%s&action=edit&reservation=%s" class="button button-small">%s</a>',
            $page,
            $item->id,
            __('Edit', 'power-reservations')
        );
        
        $actions['delete'] = sprintf(
            '<a href="?page=%s&action=delete&reservation=%s&_wpnonce=%s" class="button button-small" onclick="return confirm(\'%s\')">%s</a>',
            $page,
            $item->id,
            wp_create_nonce('delete_reservation_' . $item->id),
            __('Are you sure you want to delete this reservation?', 'power-reservations'),
            __('Delete', 'power-reservations')
        );
        
        return implode(' ', $actions);
    }
    
    /**
     * Prepare items
     */
    public function prepare_items() {
        global $wpdb;
        
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        // Handle bulk actions
        $this->process_bulk_action();
        
        // Get data
        $table_name = $this->get_reservations_table();
        
        // Build query
        $where = '1=1';
        $where_args = array();
        
        // Search
        if (!empty($_REQUEST['s'])) {
            $search = '%' . $wpdb->esc_like($_REQUEST['s']) . '%';
            $where .= " AND (name LIKE %s OR email LIKE %s OR reservation_id LIKE %s)";
            $where_args = array_merge($where_args, array($search, $search, $search));
        }
        
        // Status filter
        if (!empty($_REQUEST['status']) && $_REQUEST['status'] !== 'all') {
            $where .= " AND status = %s";
            $where_args[] = sanitize_text_field($_REQUEST['status']);
        }
        
        // Date filter
        if (!empty($_REQUEST['date_from'])) {
            $where .= " AND reservation_date >= %s";
            $where_args[] = sanitize_text_field($_REQUEST['date_from']);
        }
        
        if (!empty($_REQUEST['date_to'])) {
            $where .= " AND reservation_date <= %s";
            $where_args[] = sanitize_text_field($_REQUEST['date_to']);
        }
        
        // Ordering with whitelist validation
        $allowed_orderby = array('id', 'name', 'email', 'reservation_date', 'reservation_time', 'party_size', 'status', 'created_at');
        $orderby = !empty($_REQUEST['orderby']) && in_array(sanitize_text_field($_REQUEST['orderby']), $allowed_orderby) ? sanitize_text_field($_REQUEST['orderby']) : 'reservation_date';
        
        $allowed_order = array('ASC', 'DESC');
        $order = !empty($_REQUEST['order']) && in_array(strtoupper(sanitize_text_field($_REQUEST['order'])), $allowed_order) ? strtoupper(sanitize_text_field($_REQUEST['order'])) : 'DESC';
        
        // Get total count
        $total_query = "SELECT COUNT(*) FROM $table_name WHERE $where";
        if (!empty($where_args)) {
            $total_query = $wpdb->prepare($total_query, $where_args);
        }
        $total_items = $wpdb->get_var($total_query);
        
        // Get data
        $offset = ($this->get_pagenum() - 1) * $per_page;
        $data_query = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby $order LIMIT %d OFFSET %d";
        $query_args = array_merge($where_args, array($per_page, $offset));
        $data = $wpdb->get_results($wpdb->prepare($data_query, $query_args));
        
        $this->items = $data;
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
    
    /**
     * Process bulk actions
     */
    public function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            // Handle single and bulk delete
            $reservations = isset($_REQUEST['reservations']) ? $_REQUEST['reservations'] : (isset($_REQUEST['reservation']) ? array($_REQUEST['reservation']) : array());
            
            foreach ($reservations as $reservation_id) {
                if (isset($_REQUEST['_wpnonce']) && (wp_verify_nonce($_REQUEST['_wpnonce'], 'delete_reservation_' . $reservation_id) || 
                    wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-reservations'))) {
                    $this->delete_reservation($reservation_id);
                }
            }
        }
        
        if ('approve' === $this->current_action()) {
            $reservations = isset($_REQUEST['reservations']) ? $_REQUEST['reservations'] : (isset($_REQUEST['reservation']) ? array($_REQUEST['reservation']) : array());
            
            foreach ($reservations as $reservation_id) {
                if (isset($_REQUEST['_wpnonce']) && (wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_reservation_' . $reservation_id) || 
                    wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-reservations'))) {
                    $this->update_reservation_status($reservation_id, 'approved');
                }
            }
        }
        
        if ('decline' === $this->current_action()) {
            $reservations = isset($_REQUEST['reservations']) ? $_REQUEST['reservations'] : (isset($_REQUEST['reservation']) ? array($_REQUEST['reservation']) : array());
            
            foreach ($reservations as $reservation_id) {
                if (isset($_REQUEST['_wpnonce']) && (wp_verify_nonce($_REQUEST['_wpnonce'], 'decline_reservation_' . $reservation_id) || 
                    wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-reservations'))) {
                    $this->update_reservation_status($reservation_id, 'declined');
                }
            }
        }
    }
    
    /**
     * Get reservations table name
     */
    private function get_reservations_table() {
        global $wpdb;
        return $wpdb->prefix . 'pr_reservations';
    }
    
    /**
     * Delete reservation
     */
    private function delete_reservation($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $result = $wpdb->delete($table_name, array('id' => $reservation_id), array('%d'));
        
        if ($result === false) {
            error_log('Power Reservations: Failed to delete reservation ID ' . $reservation_id);
            return false;
        }
        
        return true;
    }
    
    /**
     * Update reservation status
     */
    private function update_reservation_status($reservation_id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $reservation_id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            error_log('Power Reservations: Failed to update reservation status for ID ' . $reservation_id);
            return false;
        }
        
        return true;
    }
    
    /**
     * Extra table navigation
     */
    public function extra_tablenav($which) {
        if ('top' === $which) {
            echo '<div class="alignleft actions">';
            
            // Status filter
            $statuses = array(
                'all' => __('All Statuses', 'power-reservations'),
                'pending' => __('Pending', 'power-reservations'),
                'approved' => __('Approved', 'power-reservations'),
                'declined' => __('Declined', 'power-reservations'),
                'cancelled' => __('Cancelled', 'power-reservations')
            );
            
            $current_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'all';
            
            echo '<select name="status">';
            foreach ($statuses as $value => $label) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    $value,
                    selected($current_status, $value, false),
                    $label
                );
            }
            echo '</select>';
            
            // Date filters
            echo '<input type="date" name="date_from" value="' . esc_attr(isset($_REQUEST['date_from']) ? $_REQUEST['date_from'] : '') . '" placeholder="' . __('From Date', 'power-reservations') . '">';
            echo '<input type="date" name="date_to" value="' . esc_attr(isset($_REQUEST['date_to']) ? $_REQUEST['date_to'] : '') . '" placeholder="' . __('To Date', 'power-reservations') . '">';
            
            submit_button(__('Filter', 'power-reservations'), 'secondary', 'filter_action', false);
            
            echo '</div>';
        }
    }
}
} // End if (!class_exists('PR_Reservations_List_Table'))

/* ========================================
   MAIN PLUGIN CLASS
   ======================================== */

/**
 * Main Power Reservations Class
 * 
 * Handles all plugin functionality including:
 * - Database table creation and management
 * - Admin interface and pages
 * - Frontend shortcodes and forms
 * - AJAX handlers for reservations
 * - Email system with templates
 * - Elementor widget integration
 * - Settings and customization options
 * 
 * Uses singleton pattern to ensure single instance
 */
if (!class_exists('PowerReservations')) {
class PowerReservations {
    
    /**
     * Single instance of the class
     * @var PowerReservations|null
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Get database table names
     */
    private function get_reservations_table() {
        global $wpdb;
        return $wpdb->prefix . 'pr_reservations';
    }
    
    private function get_settings_table() {
        global $wpdb;
        return $wpdb->prefix . 'pr_settings';
    }
    
    private function get_templates_table() {
        global $wpdb;
        return $wpdb->prefix . 'pr_email_templates';
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check for plugin migration on init
        $this->check_plugin_migration();
        
        load_plugin_textdomain('power-reservations', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Check email templates status
        if (is_admin() && current_user_can('manage_options')) {
            add_action('admin_init', array($this, 'check_email_templates_status'));
        }
        
        // Elementor Integration
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'register_elementor_category'));
        
        // Shortcodes
        add_shortcode('power_reservations', array($this, 'reservation_shortcode'));
        add_shortcode('my_power_reservations', array($this, 'my_reservations_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_pr_submit_reservation', array($this, 'handle_reservation_submission'));
        add_action('wp_ajax_nopriv_pr_submit_reservation', array($this, 'handle_reservation_submission'));
        add_action('wp_ajax_pr_check_availability', array($this, 'check_availability'));
        add_action('wp_ajax_nopriv_pr_check_availability', array($this, 'check_availability'));
        add_action('wp_ajax_pr_cancel_reservation', array($this, 'handle_reservation_cancellation'));
        add_action('wp_ajax_pr_edit_reservation', array($this, 'handle_reservation_edit'));
        
        // Cron hooks
        add_action('pr_send_reminder', array($this, 'send_reminder_email'));
        add_action('pr_daily_cleanup', array($this, 'daily_cleanup'));
        
        // Query vars for reservation management
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_reservation_actions'));
        
        // Dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        
        // Schedule cron events if not already scheduled
        if (!wp_next_scheduled('pr_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'pr_daily_cleanup');
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Handle plugin folder name migration
        $this->handle_plugin_migration();
        
        $this->create_database_tables();
        $this->set_default_options();
        $this->insert_default_email_templates();
        
        flush_rewrite_rules();
    }
    
    /**
     * Handle plugin migration from different folder names
     */
    private function handle_plugin_migration() {
        $old_plugin_paths = array(
            'power-reservations/power-reservations.php',
            'powerReservations/power-reservations.php', 
            'power_reservations/power-reservations.php'
        );
        
        $current_plugin = plugin_basename(__FILE__);
        
        foreach ($old_plugin_paths as $old_path) {
            if ($old_path !== $current_plugin) {
                $old_option = get_option('active_plugins', array());
                if (is_array($old_option) && in_array($old_path, $old_option)) {
                    // Remove old plugin path and add new one
                    $old_option = array_diff($old_option, array($old_path));
                    if (!in_array($current_plugin, $old_option)) {
                        $old_option[] = $current_plugin;
                    }
                    update_option('active_plugins', $old_option);
                    
                    // Mark migration as complete
                    update_option('pr_plugin_migrated', true);
                    break;
                }
            }
        }
    }
    
    /**
     * Check for plugin migration
     */
    private function check_plugin_migration() {
        if (!get_option('pr_plugin_migrated') && !get_option('pr_db_version')) {
            // Check if this looks like a fresh install vs migration
            global $wpdb;
            $table_name = $wpdb->prefix . 'pr_reservations';
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;
            
            if ($table_exists) {
                // Tables exist but no migration flag - this is likely a folder rename
                update_option('pr_plugin_migrated', true);
                update_option('pr_db_version', '1.0');
                
                // Add admin notice about successful migration
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p><strong>Power Reservations:</strong> Plugin successfully migrated to new version. All your data has been preserved.</p>';
                    echo '</div>';
                });
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create all required database tables for the plugin
     * 
     * Creates three main tables:
     * 1. pr_reservations - stores reservation data with foreign key to users
     * 2. pr_settings - stores plugin configuration settings
     * 3. pr_email_templates - stores customizable email templates
     * 
     * Includes proper indexes for performance and foreign key constraints
     * for data integrity. Handles creation errors gracefully with logging.
     */
    
    /**
     * Check email templates status on admin pages
     */
    public function check_email_templates_status() {
        // Only run this check on our plugin pages
        if (!function_exists('get_current_screen')) {
            return;
        }
        
        $current_screen = get_current_screen();
        if (!$current_screen || strpos($current_screen->id, 'power-reservations') === false) {
            return;
        }
        
        // Check if templates exist and create them if missing
        $stats = $this->get_template_stats();
        if (empty($stats['templates']) || $stats['total_templates'] == 0) {
            $this->create_database_tables();
            $this->insert_default_email_templates();
        }
    }
    

    
    /**
     * Create necessary database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main reservations table
        $table_name = $wpdb->prefix . 'pr_reservations';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            reservation_id varchar(32) NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT '',
            reservation_date date NOT NULL,
            reservation_time time NOT NULL,
            party_size int(2) NOT NULL,
            special_requests text DEFAULT '',
            status varchar(20) DEFAULT 'pending',
            admin_notes text DEFAULT '',
            edit_token varchar(64) DEFAULT '',
            reminder_sent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY reservation_id (reservation_id),
            UNIQUE KEY edit_token (edit_token),
            KEY user_id (user_id),
            KEY reservation_date (reservation_date),
            KEY status (status),
            KEY email (email)
        ) $charset_collate;";
        
        // Settings table for enhanced configuration
        $settings_table = $wpdb->prefix . 'pr_settings';
        $sql2 = "CREATE TABLE $settings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_name varchar(100) NOT NULL,
            setting_value longtext DEFAULT '',
            setting_type varchar(50) DEFAULT 'text',
            autoload tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_name (setting_name)
        ) $charset_collate;";
        
        // Email templates table
        $templates_table = $wpdb->prefix . 'pr_email_templates';
        $sql3 = "CREATE TABLE $templates_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_name varchar(100) NOT NULL,
            template_subject varchar(255) NOT NULL,
            template_content longtext NOT NULL,
            template_type varchar(50) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY template_name (template_name),
            KEY template_type (template_type)
        ) $charset_collate;";
        
        // Include upgrade functions if available
        $upgrade_file = ABSPATH . 'wp-admin/includes/upgrade.php';
        if (file_exists($upgrade_file)) {
            require_once($upgrade_file);
        } else {
            error_log('Power Reservations: WordPress upgrade.php file not found');
            return false;
        }
        
        // Ensure dbDelta function exists
        if (!function_exists('dbDelta')) {
            error_log('Power Reservations: dbDelta function not available');
            return false;
        }
        
        // Create tables 
        dbDelta($sql);
        dbDelta($sql2);
        dbDelta($sql3);
        
        // Check if all tables were created successfully
        $table_names = array(
            $wpdb->prefix . 'pr_reservations',
            $wpdb->prefix . 'pr_settings',
            $wpdb->prefix . 'pr_email_templates'
        );
        
        foreach ($table_names as $table_name) {
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;
            error_log("Power Reservations: Table $table_name exists: " . ($table_exists ? 'YES' : 'NO'));
            if (!$table_exists) {
                error_log("Power Reservations: Failed to create table $table_name");
                // Don't completely fail activation, but log the error
            }
        }
        
        // Insert default email templates
        $this->insert_default_email_templates();
        
        add_option('pr_db_version', '2.0');
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'pr_business_name' => get_bloginfo('name'),
            'pr_business_email' => get_option('admin_email'),
            'pr_max_party_size' => 8,
            'pr_booking_window' => 30,
            'pr_time_slots' => array(
                '17:00' => '5:00 PM',
                '17:30' => '5:30 PM',
                '18:00' => '6:00 PM',
                '18:30' => '6:30 PM',
                '19:00' => '7:00 PM',
                '19:30' => '7:30 PM',
                '20:00' => '8:00 PM',
                '20:30' => '8:30 PM',
                '21:00' => '9:00 PM'
            )
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
        
        // Add enhanced default settings
        $enhanced_defaults = array(
            'pr_max_reservations_per_slot' => 5,
            'pr_edit_window_hours' => 24,
            'pr_require_approval' => false,
            'pr_blackout_dates' => array(),
            'pr_opening_hours' => array(
                'monday' => array('open' => '17:00', 'close' => '22:00'),
                'tuesday' => array('open' => '17:00', 'close' => '22:00'),
                'wednesday' => array('open' => '17:00', 'close' => '22:00'),
                'thursday' => array('open' => '17:00', 'close' => '22:00'),
                'friday' => array('open' => '17:00', 'close' => '22:00'),
                'saturday' => array('open' => '17:00', 'close' => '22:00'),
                'sunday' => array('open' => '17:00', 'close' => '22:00')
            ),
            'pr_time_slot_duration' => 30,
            'pr_form_fields' => array('name', 'email', 'phone', 'date', 'time', 'party_size', 'special_requests'),
            'pr_form_colors' => array(
                'primary' => '#007cba',
                'secondary' => '#50575e',
                'background' => '#ffffff',
                'text' => '#1d2327'
            )
        );
        
        foreach ($enhanced_defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Insert default email templates
     */
    private function insert_default_email_templates() {
        global $wpdb;
        
        $templates_table = $this->get_templates_table();
        
        // Check if table exists before inserting
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $templates_table)) == $templates_table;
        if (!$table_exists) {
            return false;
        }
        
        // Check if default templates already exist by name
        $required_templates = array('customer_confirmation', 'customer_reminder', 'admin_notification');
        $missing_templates = array();
        
        foreach ($required_templates as $template_name) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $templates_table WHERE template_name = %s",
                $template_name
            ));
            if ($exists == 0) {
                $missing_templates[] = $template_name;
            }
        }
        
        // If all templates exist, return early
        if (empty($missing_templates)) {
            return true;
        }
        
        $default_templates = array(
            array(
                'template_name' => 'customer_confirmation',
                'template_subject' => __('Reservation Confirmed - {business_name}', 'power-reservations'),
                'template_content' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
                    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <h1 style="color: #2c3e50; margin: 0; font-size: 28px;">Reservation Confirmed!</h1>
                            <div style="width: 50px; height: 3px; background-color: #007cba; margin: 15px auto;"></div>
                        </div>
                        
                        <p style="font-size: 16px; color: #333; margin-bottom: 20px;">Dear <strong>{name}</strong>,</p>
                        
                        <p style="font-size: 16px; color: #333; line-height: 1.6;">
                            Thank you for choosing {business_name}! We are excited to confirm your reservation. 
                            Here are your reservation details:
                        </p>
                        
                        <div style="background-color: #f8f9fa; padding: 25px; border-radius: 6px; margin: 25px 0;">
                            <h3 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 18px;">Reservation Details</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Reservation ID:</td>
                                    <td style="padding: 8px 0; color: #007cba; font-weight: bold;">{reservation_id}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Date:</td>
                                    <td style="padding: 8px 0; color: #333;">{date}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Time:</td>
                                    <td style="padding: 8px 0; color: #333;">{time}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Party Size:</td>
                                    <td style="padding: 8px 0; color: #333;">{party_size} guests</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Contact Email:</td>
                                    <td style="padding: 8px 0; color: #333;">{email}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{edit_link}" style="background-color: #007cba; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                                Manage Your Reservation
                            </a>
                        </div>
                        
                        <div style="background-color: #e8f4f8; padding: 20px; border-radius: 6px; margin: 25px 0;">
                            <h4 style="color: #1a5673; margin: 0 0 10px 0;">Important Information:</h4>
                            <ul style="margin: 0; padding-left: 20px; color: #333;">
                                <li>Please arrive on time for your reservation</li>
                                <li>If you need to cancel or modify, please do so at least 2 hours in advance</li>
                                <li>Contact us if you have any special dietary requirements</li>
                            </ul>
                        </div>
                        
                        <p style="font-size: 16px; color: #333; line-height: 1.6; margin-top: 25px;">
                            We look forward to providing you with an exceptional dining experience!
                        </p>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <p style="color: #666; font-size: 14px; margin: 0;">
                                Best regards,<br>
                                <strong>{business_name}</strong>
                            </p>
                        </div>
                    </div>
                </div>',
                'template_type' => 'customer',
                'is_active' => 1
            ),
            array(
                'template_name' => 'customer_reminder',
                'template_subject' => __('Reservation Reminder - Today at {time}', 'power-reservations'),
                'template_content' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;">
                    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <h1 style="color: #e67e22; margin: 0; font-size: 28px;">Reservation Reminder</h1>
                            <div style="width: 50px; height: 3px; background-color: #e67e22; margin: 15px auto;"></div>
                        </div>
                        
                        <p style="font-size: 16px; color: #333; margin-bottom: 20px;">Dear <strong>{name}</strong>,</p>
                        
                        <p style="font-size: 16px; color: #333; line-height: 1.6;">
                            This is a friendly reminder about your reservation with us today!
                        </p>
                        
                        <div style="background-color: #fff3cd; padding: 25px; border-radius: 6px; margin: 25px 0; border-left: 4px solid #e67e22;">
                            <h3 style="color: #e67e22; margin: 0 0 15px 0; font-size: 18px;">Today\'s Reservation</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Time:</td>
                                    <td style="padding: 8px 0; color: #e67e22; font-weight: bold; font-size: 18px;">{time}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Party Size:</td>
                                    <td style="padding: 8px 0; color: #333;">{party_size} guests</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; color: #495057;">Reservation ID:</td>
                                    <td style="padding: 8px 0; color: #333;">{reservation_id}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <p style="font-size: 16px; color: #333; line-height: 1.6;">
                            We can\'t wait to see you today! Please arrive a few minutes early to ensure we can seat you promptly.
                        </p>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{edit_link}" style="background-color: #e67e22; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                                View Reservation Details
                            </a>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <p style="color: #666; font-size: 14px; margin: 0;">
                                See you soon!<br>
                                <strong>{business_name}</strong>
                            </p>
                        </div>
                    </div>
                </div>',
                'template_type' => 'customer',
                'is_active' => 1
            ),
            array(
                'template_name' => 'admin_notification',
                'template_subject' => __('New Reservation Alert - {name} for {date} at {time}', 'power-reservations'),
                'template_content' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
                    <div style="background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <h1 style="color: #28a745; margin: 0; font-size: 28px;">New Reservation Alert</h1>
                            <div style="width: 50px; height: 3px; background-color: #28a745; margin: 15px auto;"></div>
                        </div>
                        
                        <div style="background-color: #d4edda; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #28a745;">
                            <p style="font-size: 16px; color: #155724; margin: 0; font-weight: bold;">
                                A new reservation has been submitted and is awaiting your review.
                            </p>
                        </div>
                        
                        <div style="background-color: #f8f9fa; padding: 25px; border-radius: 6px; margin: 25px 0;">
                            <h3 style="color: #2c3e50; margin: 0 0 20px 0; font-size: 20px;">Customer Information</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057; width: 35%;">Name:</td>
                                    <td style="padding: 10px 0; color: #333; font-weight: bold;">{name}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057;">Email:</td>
                                    <td style="padding: 10px 0; color: #007cba;"><a href="mailto:{email}" style="color: #007cba;">{email}</a></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057;">Phone:</td>
                                    <td style="padding: 10px 0; color: #333;"><a href="tel:{phone}" style="color: #333;">{phone}</a></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 25px; border-radius: 6px; margin: 25px 0;">
                            <h3 style="color: #856404; margin: 0 0 20px 0; font-size: 20px;">Reservation Details</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057; width: 35%;">Reservation ID:</td>
                                    <td style="padding: 10px 0; color: #856404; font-weight: bold;">{reservation_id}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057;">Date:</td>
                                    <td style="padding: 10px 0; color: #333; font-weight: bold; font-size: 16px;">{date}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057;">Time:</td>
                                    <td style="padding: 10px 0; color: #333; font-weight: bold; font-size: 16px;">{time}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057;">Party Size:</td>
                                    <td style="padding: 10px 0; color: #333; font-weight: bold;">{party_size} guests</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold; color: #495057; vertical-align: top;">Special Requests:</td>
                                    <td style="padding: 10px 0; color: #333;">{special_requests}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{admin_link}" style="background-color: #28a745; color: white; padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                                Manage This Reservation
                            </a>
                        </div>
                        
                        <div style="background-color: #e8f4f8; padding: 20px; border-radius: 6px; margin: 25px 0;">
                            <h4 style="color: #1a5673; margin: 0 0 10px 0;">Quick Actions Needed:</h4>
                            <ul style="margin: 0; padding-left: 20px; color: #333;">
                                <li>Review and approve/decline the reservation</li>
                                <li>Check table availability for the requested time</li>
                                <li>Note any special requests or dietary requirements</li>
                                <li>Send confirmation to the customer if approved</li>
                            </ul>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <p style="color: #666; font-size: 14px; margin: 0;">
                                <strong>{business_name}</strong> Reservation Management System<br>
                                <em>This is an automated notification</em>
                            </p>
                        </div>
                    </div>
                </div>',
                'template_type' => 'admin',
                'is_active' => 1
            )
        );
        
        $success_count = 0;
        $total_missing = count($missing_templates);
        
        foreach ($default_templates as $template) {
            // Only insert if this template is missing
            if (in_array($template['template_name'], $missing_templates)) {
                $result = $wpdb->replace(
                    $templates_table,
                    $template,
                    array('%s', '%s', '%s', '%s', '%d')
                );
                
                if ($result === false) {
                    error_log('Power Reservations: Failed to insert default email template: ' . $template['template_name']);
                    if (!empty($wpdb->last_error)) {
                        error_log('Power Reservations: Database error: ' . $wpdb->last_error);
                    }
                } else {
                    $success_count++;
                }
            } else {
                error_log('Power Reservations: Skipping template ' . $template['template_name'] . ' - already exists');
            }
        }
        
        return ($success_count == $total_missing);
    }
    
    /**
     * Manually restore default email templates
     * This can be called from admin to restore missing templates
     */
    public function restore_default_email_templates() {
        // First, let's check if the table exists
        global $wpdb;
        $templates_table = $this->get_templates_table();
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $templates_table)) == $templates_table;
        
        if (!$table_exists) {
            error_log('Power Reservations: Attempting to create email templates table');
            $this->create_database_tables();
            
            // Check again
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $templates_table)) == $templates_table;
            error_log('Power Reservations: Templates table exists after creation attempt: ' . ($table_exists ? 'YES' : 'NO'));
        }
        
        // Call the insert function which now checks for missing templates
        $result = $this->insert_default_email_templates();
        
        if ($result) {
            return array(
                'success' => true,
                'message' => __('Default email templates have been restored successfully.', 'power-reservations')
            );
        } else {
            return array(
                'success' => false,
                'message' => __('Failed to restore default email templates. Please check your database.', 'power-reservations')
            );
        }
    }
    
    /**
     * Get template statistics for debugging
     */
    public function get_template_stats() {
        global $wpdb;
        $templates_table = $this->get_templates_table();
        
        // Check if table exists first
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $templates_table)) == $templates_table;
        if (!$table_exists) {
            error_log('Power Reservations: Email templates table does not exist in get_template_stats()');
            return array(
                'customer_confirmation' => false,
                'customer_reminder' => false,
                'admin_notification' => false
            );
        }
        
        $stats = array();
        $required_templates = array('customer_confirmation', 'customer_reminder', 'admin_notification');
        
        foreach ($required_templates as $template_name) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $templates_table WHERE template_name = %s",
                $template_name
            ));
            $stats[$template_name] = ($exists > 0);
            

        }
        
        return $stats;
    }

    /* ========================================
       ADMIN INTERFACE FUNCTIONS
       ======================================== */    /**
     * Add admin menu pages
     * 
     * Creates the main reservations menu with subpages:
     * - Dashboard (main page)
     * - Settings 
     * - Email Templates
     * - Form Builder
     * - Form Styling
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('Power Reservations', 'power-reservations'),
            __('Reservations', 'power-reservations'),
            'manage_options',
            'power-reservations',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );
        
        // All Reservations (uses WP_List_Table)
        add_submenu_page(
            'power-reservations',
            __('All Reservations', 'power-reservations'),
            __('All Reservations', 'power-reservations'),
            'manage_options',
            'power-reservations',
            array($this, 'admin_page')
        );
        
        // Settings
        add_submenu_page(
            'power-reservations',
            __('Settings', 'power-reservations'),
            __('Settings', 'power-reservations'),
            'manage_options',
            'pr-settings',
            array($this, 'settings_page')
        );
        
        // Email Templates
        add_submenu_page(
            'power-reservations',
            __('Email Templates', 'power-reservations'),
            __('Email Templates', 'power-reservations'),
            'manage_options',
            'pr-email-templates',
            array($this, 'email_templates_page')
        );
        
        // Form Builder
        add_submenu_page(
            'power-reservations',
            __('Form Builder', 'power-reservations'),
            __('Form Builder', 'power-reservations'),
            'manage_options',
            'pr-form-builder',
            array($this, 'form_builder_page')
        );
        
        // Form Styling
        add_submenu_page(
            'power-reservations',
            __('Form Styling', 'power-reservations'),
            __('Form Styling', 'power-reservations'),
            'manage_options',
            'pr-form-styling',
            array($this, 'form_styling_page')
        );
    }
    
    /**
     * Display the main admin dashboard page
     * 
     * Handles:
     * - Individual reservation actions (edit, view, delete)
     * - Displays reservations list table with filtering/sorting
     * - Shows admin header with navigation buttons
     * - Processes bulk actions and individual actions
     * - Displays success/error messages
     */
    public function admin_page() {
        // Handle individual reservation actions first (edit, view, delete)
        if (isset($_GET['action']) && isset($_GET['reservation'])) {
            $this->handle_admin_action();
            return;
        }
        
        // Initialize and prepare the reservations list table
        $list_table = new PR_Reservations_List_Table();
        $list_table->prepare_items();
        
        echo '<div class="wrap pr-admin-page">';
        echo '<div class="pr-admin-container">';
        echo '<div class="pr-admin-header">';
        echo '<h1 class="pr-page-title">' . __('Reservations Dashboard', 'power-reservations') . '</h1>';
        echo '<div class="pr-header-actions">';
        echo '<a href="' . admin_url('admin.php?page=pr-form-builder') . '" class="pr-btn pr-btn-primary">';
        echo '<span class="dashicons dashicons-plus-alt2"></span>' . __('Form Builder', 'power-reservations') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=pr-settings') . '" class="pr-btn pr-btn-secondary">';
        echo '<span class="dashicons dashicons-admin-generic"></span>' . __('Settings', 'power-reservations') . '</a>';
        echo '</div>';
        echo '</div>';
        
        // Show notices
        if (isset($_GET['message'])) {
            $message = '';
            $type = 'success';
            switch ($_GET['message']) {
                case 'approved':
                    $message = __('Reservation approved successfully.', 'power-reservations');
                    break;
                case 'declined':
                    $message = __('Reservation declined successfully.', 'power-reservations');
                    break;
                case 'deleted':
                    $message = __('Reservation deleted successfully.', 'power-reservations');
                    break;
                case 'updated':
                    $message = __('Reservation updated successfully.', 'power-reservations');
                    break;
            }
            if ($message) {
                echo '<div class="pr-notification pr-notification-' . esc_attr($type) . '">';
                echo '<span class="dashicons dashicons-yes-alt"></span>';
                echo '<span>' . esc_html($message) . '</span>';
                echo '<button class="pr-notification-close">&times;</button>';
                echo '</div>';
            }
        }
        
        // Stats overview
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $today = date('Y-m-d');
        
        $stats = array(
            'total' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
            'pending' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE status = %s", 'pending')),
            'today' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE reservation_date = %s", $today)),
            'upcoming' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE reservation_date > %s AND status = %s", $today, 'approved'))
        );
        
        echo '<div class="pr-stats-grid">';
        echo '<div class="pr-stat-card">';
        echo '<div class="pr-stat-icon pr-stat-icon-total"><span class="dashicons dashicons-calendar-alt"></span></div>';
        echo '<div class="pr-stat-content">';
        echo '<div class="pr-stat-number">' . number_format($stats['total']) . '</div>';
        echo '<div class="pr-stat-label">' . __('Total Reservations', 'power-reservations') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-stat-card pr-stat-card-warning">';
        echo '<div class="pr-stat-icon pr-stat-icon-pending"><span class="dashicons dashicons-clock"></span></div>';
        echo '<div class="pr-stat-content">';
        echo '<div class="pr-stat-number">' . number_format($stats['pending']) . '</div>';
        echo '<div class="pr-stat-label">' . __('Pending Approval', 'power-reservations') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-stat-card pr-stat-card-info">';
        echo '<div class="pr-stat-icon pr-stat-icon-today"><span class="dashicons dashicons-star-filled"></span></div>';
        echo '<div class="pr-stat-content">';
        echo '<div class="pr-stat-number">' . number_format($stats['today']) . '</div>';
        echo '<div class="pr-stat-label">' . __('Today\'s Reservations', 'power-reservations') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-stat-card pr-stat-card-success">';
        echo '<div class="pr-stat-icon pr-stat-icon-upcoming"><span class="dashicons dashicons-yes-alt"></span></div>';
        echo '<div class="pr-stat-content">';
        echo '<div class="pr-stat-number">' . number_format($stats['upcoming']) . '</div>';
        echo '<div class="pr-stat-label">' . __('Upcoming Approved', 'power-reservations') . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Full width reservations table
        echo '<div class="pr-card pr-reservations-table-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">' . __('All Reservations', 'power-reservations') . '</h2>';
        echo '<div class="pr-card-actions">';
        
        // Search form
        echo '<form method="get" class="pr-search-form">';
        echo '<input type="hidden" name="page" value="' . (isset($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : 'power-reservations') . '">';
        echo '<div class="pr-search-wrapper">';
        echo '<input type="search" name="s" value="' . (isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '') . '" placeholder="' . __('Search reservations...', 'power-reservations') . '" class="pr-search-input">';
        echo '<button type="submit" class="pr-search-btn"><span class="dashicons dashicons-search"></span></button>';
        echo '</div>';
        echo '</form>';
        
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-card-content pr-table-wrapper">';
        // List table
        echo '<form method="post">';
        $list_table->display();
        echo '</form>';
        echo '</div>';
        echo '</div>';
        
        // Bottom section with Quick Actions and Shortcodes
        echo '<div class="pr-bottom-section">';
        echo '<div class="pr-bottom-grid">';
        
        echo '<div class="pr-card pr-bottom-card">';
        echo '<div class="pr-card-header">';
        echo '<h3 class="pr-card-title"><span class="dashicons dashicons-admin-tools"></span> ' . __('Quick Actions', 'power-reservations') . '</h3>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<div class="pr-action-grid">';
        echo '<a href="' . admin_url('admin.php?page=pr-form-builder') . '" class="pr-action-card">';
        echo '<span class="dashicons dashicons-admin-page"></span>';
        echo '<span>' . __('Form Builder', 'power-reservations') . '</span>';
        echo '</a>';
        echo '<a href="' . admin_url('admin.php?page=pr-email-templates') . '" class="pr-action-card">';
        echo '<span class="dashicons dashicons-email-alt"></span>';
        echo '<span>' . __('Email Templates', 'power-reservations') . '</span>';
        echo '</a>';
        echo '<a href="' . admin_url('admin.php?page=pr-form-styling') . '" class="pr-action-card">';
        echo '<span class="dashicons dashicons-art"></span>';
        echo '<span>' . __('Form Styling', 'power-reservations') . '</span>';
        echo '</a>';
        echo '<a href="' . admin_url('admin.php?page=pr-settings') . '" class="pr-action-card">';
        echo '<span class="dashicons dashicons-admin-settings"></span>';
        echo '<span>' . __('Settings', 'power-reservations') . '</span>';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-card pr-bottom-card">';
        echo '<div class="pr-card-header">';
        echo '<h3 class="pr-card-title"><span class="dashicons dashicons-shortcode"></span> ' . __('Shortcodes', 'power-reservations') . '</h3>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<div class="pr-shortcode-item">';
        echo '<label>' . __('Reservation Form:', 'power-reservations') . '</label>';
        echo '<div class="pr-shortcode-copy">';
        echo '<code>[power_reservations]</code>';
        echo '<button class="pr-copy-btn" data-copy="[power_reservations]"><span class="dashicons dashicons-admin-page"></span></button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="pr-shortcode-item">';
        echo '<label>' . __('User Reservations:', 'power-reservations') . '</label>';
        echo '<div class="pr-shortcode-copy">';
        echo '<code>[my_power_reservations]</code>';
        echo '<button class="pr-copy-btn" data-copy="[my_power_reservations]"><span class="dashicons dashicons-admin-page"></span></button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // Close .pr-bottom-grid
        echo '</div>'; // Close .pr-bottom-section
        
        echo '</div>'; // .pr-admin-container
        echo '</div>'; // .wrap.pr-admin-page
    }
    
    /**
     * Handle admin actions
     */
    private function handle_admin_action() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'power-reservations'));
        }
        
        $action = $_GET['action'];
        $reservation_id = intval($_GET['reservation']);
        
        if ($action === 'edit') {
            $this->edit_reservation_page($reservation_id);
        } elseif ($action === 'view') {
            $this->view_reservation_page($reservation_id);
        } else {
            wp_redirect(admin_url('admin.php?page=power-reservations'));
            exit;
        }
    }
    
    /**
     * Edit reservation page
     */
    private function edit_reservation_page($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $reservation_id));
        
        if (!$reservation) {
            wp_die(__('Reservation not found.', 'power-reservations'));
        }
        
        // Handle form submission
        if (isset($_POST['submit'])) {
            // Verify nonce
            if (!isset($_POST['pr_edit_reservation_nonce']) || !wp_verify_nonce($_POST['pr_edit_reservation_nonce'], 'pr_edit_reservation_action')) {
                wp_die(__('Security check failed', 'power-reservations'));
            }
            
            // Validate required fields
            $required_fields = array('name', 'email', 'reservation_date', 'reservation_time', 'party_size', 'status');
            $validation_errors = array();
            
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    /* translators: %s: Field name (e.g., Name, Email, Party size) */
                    $validation_errors[] = sprintf(__('%s is required', 'power-reservations'), ucfirst(str_replace('_', ' ', $field)));
                }
            }
            
            if (!empty($validation_errors)) {
                echo '<div class="notice notice-error"><p>' . esc_html(implode(', ', $validation_errors)) . '</p></div>';
            } else {
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'name' => sanitize_text_field($_POST['name']),
                        'email' => sanitize_email($_POST['email']),
                        'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
                        'reservation_date' => sanitize_text_field($_POST['reservation_date']),
                        'reservation_time' => sanitize_text_field($_POST['reservation_time']),
                        'party_size' => intval($_POST['party_size']),
                        'special_requests' => isset($_POST['special_requests']) ? sanitize_textarea_field($_POST['special_requests']) : '',
                        'status' => sanitize_text_field($_POST['status']),
                        'admin_notes' => isset($_POST['admin_notes']) ? sanitize_textarea_field($_POST['admin_notes']) : ''
                    ),
                    array('id' => $reservation_id),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s'),
                    array('%d')
                );
                
                if ($result === false) {
                    echo '<div class="notice notice-error"><p>' . __('Failed to update reservation. Please try again.', 'power-reservations') . '</p></div>';
                    error_log('Power Reservations: Failed to update reservation ID ' . $reservation_id);
                } else {
                    wp_redirect(admin_url('admin.php?page=power-reservations&message=updated'));
                    exit;
                }
            }
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . __('Edit Reservation', 'power-reservations') . '</h1>';
        echo '<form method="post">';
        wp_nonce_field('pr_edit_reservation_action', 'pr_edit_reservation_nonce');
        echo '<table class="form-table">';
        
        echo '<tr><th scope="row">' . __('Name', 'power-reservations') . '</th>';
        echo '<td><input type="text" name="name" value="' . esc_attr($reservation->name) . '" class="regular-text" required /></td></tr>';
        
        echo '<tr><th scope="row">' . __('Email', 'power-reservations') . '</th>';
        echo '<td><input type="email" name="email" value="' . esc_attr($reservation->email) . '" class="regular-text" required /></td></tr>';
        
        echo '<tr><th scope="row">' . __('Phone', 'power-reservations') . '</th>';
        echo '<td><input type="text" name="phone" value="' . esc_attr($reservation->phone) . '" class="regular-text" /></td></tr>';
        
        echo '<tr><th scope="row">' . __('Date', 'power-reservations') . '</th>';
        echo '<td><input type="date" name="reservation_date" value="' . esc_attr($reservation->reservation_date) . '" required /></td></tr>';
        
        echo '<tr><th scope="row">' . __('Time', 'power-reservations') . '</th>';
        echo '<td><input type="time" name="reservation_time" value="' . esc_attr($reservation->reservation_time) . '" required /></td></tr>';
        
        echo '<tr><th scope="row">' . __('Party Size', 'power-reservations') . '</th>';
        echo '<td><input type="number" name="party_size" value="' . esc_attr($reservation->party_size) . '" min="1" max="20" required /></td></tr>';
        
        echo '<tr><th scope="row">' . __('Special Requests', 'power-reservations') . '</th>';
        echo '<td><textarea name="special_requests" rows="3" class="large-text">' . esc_textarea($reservation->special_requests) . '</textarea></td></tr>';
        
        echo '<tr><th scope="row">' . __('Status', 'power-reservations') . '</th>';
        echo '<td><select name="status">';
        $statuses = array('pending', 'approved', 'declined', 'cancelled');
        foreach ($statuses as $status) {
            echo '<option value="' . esc_attr($status) . '"' . selected($reservation->status, $status, false) . '>' . esc_html(ucfirst($status)) . '</option>';
        }
        echo '</select></td></tr>';
        
        echo '<tr><th scope="row">' . __('Admin Notes', 'power-reservations') . '</th>';
        echo '<td><textarea name="admin_notes" rows="3" class="large-text">' . esc_textarea($reservation->admin_notes) . '</textarea></td></tr>';
        
        echo '</table>';
        submit_button(__('Update Reservation', 'power-reservations'));
        echo '</form>';
        echo '<p><a href="' . admin_url('admin.php?page=power-reservations') . '">' . __('← Back to Reservations', 'power-reservations') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * View reservation page
     */
    private function view_reservation_page($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $reservation_id));
        
        if (!$reservation) {
            wp_die(__('Reservation not found.', 'power-reservations'));
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . __('View Reservation', 'power-reservations') . '</h1>';
        echo '<div class="card">';
        echo '<h2>' . esc_html($reservation->name) . '</h2>';
        echo '<p><strong>' . __('Reservation ID:', 'power-reservations') . '</strong> ' . esc_html($reservation->reservation_id) . '</p>';
        echo '<p><strong>' . __('Email:', 'power-reservations') . '</strong> ' . esc_html($reservation->email) . '</p>';
        echo '<p><strong>' . __('Phone:', 'power-reservations') . '</strong> ' . esc_html($reservation->phone) . '</p>';
        echo '<p><strong>' . __('Date:', 'power-reservations') . '</strong> ' . esc_html($reservation->reservation_date) . '</p>';
        echo '<p><strong>' . __('Time:', 'power-reservations') . '</strong> ' . esc_html($reservation->reservation_time) . '</p>';
        echo '<p><strong>' . __('Party Size:', 'power-reservations') . '</strong> ' . esc_html($reservation->party_size) . '</p>';
        if ($reservation->special_requests) {
            echo '<p><strong>' . __('Special Requests:', 'power-reservations') . '</strong><br>' . esc_html($reservation->special_requests) . '</p>';
        }
        if ($reservation->admin_notes) {
            echo '<p><strong>' . __('Admin Notes:', 'power-reservations') . '</strong><br>' . esc_html($reservation->admin_notes) . '</p>';
        }
        echo '<p><strong>' . __('Created:', 'power-reservations') . '</strong> ' . esc_html($reservation->created_at) . '</p>';
        echo '</div>';
        echo '<p><a href="' . admin_url('admin.php?page=power-reservations&action=edit&reservation=' . $reservation_id) . '" class="button button-primary">' . __('Edit Reservation', 'power-reservations') . '</a></p>';
        echo '<p><a href="' . admin_url('admin.php?page=power-reservations') . '">' . __('← Back to Reservations', 'power-reservations') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['submit'])) {
            // Verify nonce
            if (!isset($_POST['pr_settings_nonce']) || !wp_verify_nonce($_POST['pr_settings_nonce'], 'pr_settings_action')) {
                wp_die(__('Security check failed', 'power-reservations'));
            }
            
            // Validate and update settings with proper checking
            if (isset($_POST['pr_business_name'])) {
                update_option('pr_business_name', sanitize_text_field($_POST['pr_business_name']));
            }
            if (isset($_POST['pr_business_email'])) {
                update_option('pr_business_email', sanitize_email($_POST['pr_business_email']));
            }
            if (isset($_POST['pr_max_party_size'])) {
                update_option('pr_max_party_size', intval($_POST['pr_max_party_size']));
            }
            if (isset($_POST['pr_booking_window'])) {
                update_option('pr_booking_window', intval($_POST['pr_booking_window']));
            }
            
            // Save time slots with validation
            if (isset($_POST['pr_time_slots']) && is_array($_POST['pr_time_slots'])) {
                $time_slots = array();
                foreach ($_POST['pr_time_slots'] as $slot) {
                    if (!empty(trim($slot))) {
                        $time_slots[] = sanitize_text_field($slot);
                    }
                }
                update_option('pr_time_slots', $time_slots);
            }
        }
        
        $business_name = get_option('pr_business_name', get_bloginfo('name'));
        $business_email = get_option('pr_business_email', get_option('admin_email'));
        $max_party_size = get_option('pr_max_party_size', 8);
        $booking_window = get_option('pr_booking_window', 30);
        $time_slots = get_option('pr_time_slots', array(
            '6:00 PM', '6:30 PM', '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM', '9:00 PM', '9:30 PM'
        ));
        
        $settings_saved = isset($_POST['submit']);
        
        echo '<div class="wrap pr-admin-page">';
        echo '<div class="pr-admin-container">';
        
        // Show notification inside container
        if ($settings_saved) {
            echo '<div class="pr-notification pr-notification-success">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo '<span>' . __('Settings saved successfully!', 'power-reservations') . '</span>';
            echo '<button class="pr-notification-close">&times;</button>';
            echo '</div>';
        }
        
        echo '<div class="pr-admin-header">';
        echo '<h1 class="pr-page-title">' . __('Reservation Settings', 'power-reservations') . '</h1>';
        echo '<p class="pr-page-subtitle">' . __('Configure your reservation system settings', 'power-reservations') . '</p>';
        echo '</div>';
        
        echo '<div class="pr-admin-content pr-settings-layout">';
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">' . __('General Settings', 'power-reservations') . '</h2>';
        echo '<p class="pr-card-description">' . __('Basic configuration for your reservation system', 'power-reservations') . '</p>';
        echo '</div>';
        
        echo '<form method="post" action="" class="pr-form">';
        wp_nonce_field('pr_settings_action', 'pr_settings_nonce');
        echo '<div class="pr-card-content">';
        
        echo '<div class="pr-form-grid">';
        echo '<div class="pr-form-group">';
        echo '<label for="pr_business_name" class="pr-label">' . __('Business Name', 'power-reservations') . '</label>';
        echo '<input type="text" id="pr_business_name" name="pr_business_name" value="' . esc_attr($business_name) . '" class="pr-input" placeholder="' . __('Enter your business name', 'power-reservations') . '" />';
        echo '<small class="pr-help-text">' . __('This will appear in emails and form headers', 'power-reservations') . '</small>';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label for="pr_business_email" class="pr-label">' . __('Business Email', 'power-reservations') . '</label>';
        echo '<input type="email" id="pr_business_email" name="pr_business_email" value="' . esc_attr($business_email) . '" class="pr-input" placeholder="' . __('admin@yourbusiness.com', 'power-reservations') . '" />';
        echo '<small class="pr-help-text">' . __('Email address for receiving reservation notifications', 'power-reservations') . '</small>';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label for="pr_max_party_size" class="pr-label">' . __('Maximum Party Size', 'power-reservations') . '</label>';
        echo '<div class="pr-input-group">';
        echo '<input type="number" id="pr_max_party_size" name="pr_max_party_size" value="' . esc_attr($max_party_size) . '" min="1" max="20" class="pr-input" />';
        echo '<span class="pr-input-suffix">' . __('people', 'power-reservations') . '</span>';
        echo '</div>';
        echo '<small class="pr-help-text">' . __('Maximum number of people per reservation', 'power-reservations') . '</small>';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label for="pr_booking_window" class="pr-label">' . __('Booking Window', 'power-reservations') . '</label>';
        echo '<div class="pr-input-group">';
        echo '<input type="number" id="pr_booking_window" name="pr_booking_window" value="' . esc_attr($booking_window) . '" min="1" max="365" class="pr-input" />';
        echo '<span class="pr-input-suffix">' . __('days', 'power-reservations') . '</span>';
        echo '</div>';
        echo '<small class="pr-help-text">' . __('How far in advance customers can make reservations', 'power-reservations') . '</small>';
        echo '</div>';
        
        echo '<div class="pr-form-group pr-time-slots-section">';
    echo '<label class="pr-label">' . __('Available Time Slots', 'power-reservations') . '</label>';
    echo '<small class="pr-help-text pr-help-text-top">' . __('Drag and drop to reorder time slots', 'power-reservations') . '</small>';
    echo '<div class="pr-time-slots-container" id="pr-time-slots">';
        foreach ($time_slots as $index => $slot) {
            echo '<div class="pr-time-slot-item">';
            echo '<span class="pr-time-drag-handle dashicons dashicons-menu" title="' . __('Drag to reorder', 'power-reservations') . '"></span>';
            echo '<input type="text" name="pr_time_slots[]" value="' . esc_attr($slot) . '" placeholder="' . __('e.g. 6:00 PM', 'power-reservations') . '" class="pr-input pr-time-input" />';
            echo '<button type="button" class="pr-btn pr-btn-sm pr-btn-danger pr-remove-time-slot" title="' . __('Remove time slot', 'power-reservations') . '">';
            echo '<span class="dashicons dashicons-no-alt"></span>';
            echo '</button>';
            echo '</div>';
        }
        echo '</div>';
        echo '<button type="button" id="pr-add-time-slot" class="pr-btn pr-btn-sm pr-btn-outline">';
    echo '<span class="dashicons dashicons-plus-alt"></span>' . __('Add Time Slot', 'power-reservations');
    echo '</button>';
        echo '<small class="pr-help-text">' . __('Time slots will be displayed to customers in the order shown above', 'power-reservations') . '</small>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        echo '<div class="pr-card-footer">';
        echo '<button type="submit" name="submit" class="pr-btn pr-btn-primary">';
        echo '<span class="dashicons dashicons-saved"></span>' . __('Save Settings', 'power-reservations');
        echo '</button>';
        echo '<a href="' . admin_url('admin.php?page=power-reservations') . '" class="pr-btn pr-btn-secondary">' . __('Back to Dashboard', 'power-reservations') . '</a>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        
        echo '<div class="pr-sidebar">';
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h3 class="pr-card-title">' . __('Quick Links', 'power-reservations') . '</h3>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<div class="pr-action-list">';
        echo '<a href="' . admin_url('admin.php?page=pr-email-templates') . '" class="pr-action-item">';
        echo '<span class="dashicons dashicons-email-alt"></span>' . __('Email Templates', 'power-reservations');
        echo '</a>';
        echo '<a href="' . admin_url('admin.php?page=pr-form-builder') . '" class="pr-action-item">';
        echo '<span class="dashicons dashicons-admin-page"></span>' . __('Form Builder', 'power-reservations');
        echo '</a>';
        echo '<a href="' . admin_url('admin.php?page=pr-form-styling') . '" class="pr-action-item">';
        echo '<span class="dashicons dashicons-art"></span>' . __('Form Styling', 'power-reservations');
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h3 class="pr-card-title">' . __('Need Help?', 'power-reservations') . '</h3>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<p class="pr-help-text">' . __('Visit our documentation for detailed setup instructions and troubleshooting guides.', 'power-reservations') . '</p>';
        echo '<a href="#" class="pr-btn pr-btn-outline pr-btn-sm">' . __('View Documentation', 'power-reservations') . '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // .pr-admin-container
        echo '</div>'; // .wrap.pr-admin-page
    }
    
    /**
     * Email Templates page
     */
    public function email_templates_page() {
        echo '<div class="wrap pr-admin-page">';
        echo '<div class="pr-admin-container">';
        echo '<div class="pr-admin-header">';
        echo '<h1 class="pr-page-title">' . __('Email Templates', 'power-reservations') . '</h1>';
        echo '<p class="pr-page-subtitle">' . __('Customize email templates sent to customers and administrators', 'power-reservations') . '</p>';
        echo '</div>';
        
        // Check template status and show info
        $template_stats = $this->get_template_stats();
        $missing_templates = array();
        foreach ($template_stats as $template_name => $exists) {
            if (!$exists) {
                $missing_templates[] = ucwords(str_replace('_', ' ', $template_name));
            }
        }
        
        if (!empty($missing_templates)) {
            echo '<div class="pr-notification pr-notification-warning">';
            echo '<span class="dashicons dashicons-info"></span>';
            echo '<span>';
            echo esc_html(sprintf(
                /* translators: %s: Comma-separated list of missing template names */
                __('Missing default templates: %s. Use the "Restore Defaults" button to restore them.', 'power-reservations'),
                implode(', ', $missing_templates)
            ));
            echo '</span>';
            echo '</div>';
        }
        
        // Handle restore default templates action
        if (isset($_GET['action']) && $_GET['action'] === 'restore_defaults' && isset($_GET['_wpnonce'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'restore_default_templates')) {
                $result = $this->restore_default_email_templates();
                if ($result['success']) {
                    echo '<div class="pr-notification pr-notification-success">';
                    echo '<span class="dashicons dashicons-yes-alt"></span>';
                    echo '<span>' . esc_html($result['message']) . '</span>';
                } else {
                    echo '<div class="pr-notification pr-notification-error">';
                    echo '<span class="dashicons dashicons-warning"></span>';
                    echo '<span>' . esc_html($result['message']) . '</span>';
                }
                echo '</div>';
            }
        }
        
        // Handle form submission
        if (isset($_POST['submit_template'])) {
            // Verify nonce
            if (!wp_verify_nonce($_POST['pr_email_templates_nonce'], 'pr_email_templates_action')) {
                wp_die(__('Security check failed', 'power-reservations'));
            }
            
            // Validate required fields
            $required_fields = array('template_name', 'template_subject', 'template_content', 'template_type');
            $validation_errors = array();
            
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    /* translators: %s: Field name (e.g., Template name, Template subject) */
                    $validation_errors[] = sprintf(__('%s is required', 'power-reservations'), ucfirst(str_replace('_', ' ', $field)));
                }
            }
            
            if (!empty($validation_errors)) {
                echo '<div class="pr-notification pr-notification-error">';
                echo '<span class="dashicons dashicons-warning"></span>';
                echo '<span>' . esc_html(implode(', ', $validation_errors)) . '</span>';
                echo '</div>';
            } else {
                global $wpdb;
                $templates_table = $wpdb->prefix . 'pr_email_templates';
                
                $template_data = array(
                    'template_name' => sanitize_text_field($_POST['template_name']),
                    'template_subject' => sanitize_text_field($_POST['template_subject']),
                    'template_content' => wp_kses_post($_POST['template_content']),
                    'template_type' => sanitize_text_field($_POST['template_type']),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                );
                
                $result = false;
                if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
                    // Update existing template
                    $result = $wpdb->update(
                        $templates_table,
                        $template_data,
                        array('id' => intval($_POST['template_id'])),
                        array('%s', '%s', '%s', '%s', '%d'),
                        array('%d')
                    );
                } else {
                    // Insert new template
                    $result = $wpdb->insert($templates_table, $template_data, array('%s', '%s', '%s', '%s', '%d'));
                }
                
                if ($result === false) {
                    echo '<div class="pr-notification pr-notification-error">';
                    echo '<span class="dashicons dashicons-warning"></span>';
                    echo '<span>' . __('Failed to save template. Please try again.', 'power-reservations') . '</span>';
                    echo '</div>';
                    error_log('Power Reservations: Failed to save email template');
                } else {
                    echo '<div class="pr-notification pr-notification-success">';
                    echo '<span class="dashicons dashicons-yes-alt"></span>';
                    echo '<span>' . __('Template saved successfully!', 'power-reservations') . '</span>';
                    echo '</div>';
                }
            }
        }
        
        // Handle template deletion
        if (isset($_GET['delete']) && isset($_GET['_wpnonce'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'delete_template_' . $_GET['delete'])) {
                global $wpdb;
                $templates_table = $wpdb->prefix . 'pr_email_templates';
                $result = $wpdb->delete($templates_table, array('id' => intval($_GET['delete'])), array('%d'));
                
                if ($result === false) {
                    echo '<div class="pr-notification pr-notification-error">';
                    echo '<span class="dashicons dashicons-warning"></span>';
                    echo '<span>' . __('Failed to delete template. Please try again.', 'power-reservations') . '</span>';
                    echo '</div>';
                    error_log('Power Reservations: Failed to delete email template ID ' . intval($_GET['delete']));
                } else {
                    echo '<div class="pr-notification pr-notification-success">';
                    echo '<span class="dashicons dashicons-yes-alt"></span>';
                    echo '<span>' . __('Template deleted successfully!', 'power-reservations') . '</span>';
                    echo '</div>';
                }
            }
        }
        
        // Check if we're adding or editing a template
        $editing = isset($_GET['edit']) || isset($_GET['add']);
        $edit_template = null;
        
        if (isset($_GET['edit'])) {
            global $wpdb;
            $templates_table = $wpdb->prefix . 'pr_email_templates';
            $edit_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $templates_table WHERE id = %d", intval($_GET['edit'])));
            
            // If editing but template not found, redirect back to list
            if (!$edit_template) {
                wp_redirect(admin_url('admin.php?page=pr-email-templates'));
                exit;
            }
        }
        
        if ($editing) {
            // Show add/edit form
            echo '<div class="pr-admin-content">';
            echo '<div class="pr-card">';
            echo '<div class="pr-card-header">';
            echo '<h2 class="pr-card-title">';
            echo esc_html($edit_template ? __('Edit Email Template', 'power-reservations') : __('Add New Email Template', 'power-reservations'));
            echo '</h2>';
            echo '<div class="pr-card-actions">';
            echo '<a href="?page=pr-email-templates" class="pr-btn pr-btn-secondary">';
            echo '<span class="dashicons dashicons-arrow-left-alt2"></span>' . __('Back to Templates', 'power-reservations');
            echo '</a>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="pr-card-content">';
            echo '<form method="post" class="pr-form">';
            wp_nonce_field('pr_email_templates_action', 'pr_email_templates_nonce');
            
            if ($edit_template) {
                echo '<input type="hidden" name="template_id" value="' . esc_attr($edit_template->id) . '">';
            }
            
            echo '<div class="pr-form-row">';
            echo '<div class="pr-form-group">';
            echo '<label for="template_name">' . __('Template Name', 'power-reservations') . '</label>';
            echo '<input type="text" id="template_name" name="template_name" class="pr-form-control" required ';
            echo 'value="' . esc_attr($edit_template ? $edit_template->template_name : '') . '">';
            echo '<small class="pr-form-help">' . __('Internal name for this template (e.g., booking_confirmation)', 'power-reservations') . '</small>';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label for="template_type">' . __('Template Type', 'power-reservations') . '</label>';
            echo '<select id="template_type" name="template_type" class="pr-form-control" required>';
            echo '<option value="customer"' . ($edit_template && $edit_template->template_type === 'customer' ? ' selected' : '') . '>' . __('Customer Email', 'power-reservations') . '</option>';
            echo '<option value="admin"' . ($edit_template && $edit_template->template_type === 'admin' ? ' selected' : '') . '>' . __('Admin Notification', 'power-reservations') . '</option>';
            echo '</select>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label for="template_subject">' . __('Email Subject', 'power-reservations') . '</label>';
            echo '<input type="text" id="template_subject" name="template_subject" class="pr-form-control" required ';
            echo 'value="' . esc_attr($edit_template ? $edit_template->template_subject : '') . '">';
            echo '<small class="pr-form-help">' . __('You can use template variables like {name} and {business_name}', 'power-reservations') . '</small>';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label for="template_content">' . __('Email Content', 'power-reservations') . '</label>';
            wp_editor(
                $edit_template ? $edit_template->template_content : '',
                'template_content',
                array(
                    'textarea_name' => 'template_content',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny' => true,
                    'quicktags' => true
                )
            );
            echo '<small class="pr-form-help">' . __('Use the template variables shown below to personalize emails', 'power-reservations') . '</small>';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-checkbox-label">';
            echo '<input type="checkbox" name="is_active" value="1"' . ($edit_template && $edit_template->is_active ? ' checked' : (!$edit_template ? ' checked' : '')) . '>';
            echo '<span class="pr-checkbox-custom"></span>';
            esc_html_e('Template is active', 'power-reservations');
            echo '</label>';
            echo '</div>';
            
            echo '<div class="pr-form-actions">';
            echo '<button type="submit" name="submit_template" class="pr-btn pr-btn-primary">';
            echo '<span class="dashicons dashicons-yes"></span>';
            echo esc_html($edit_template ? __('Update Template', 'power-reservations') : __('Create Template', 'power-reservations'));
            echo '</button>';
            echo '<a href="?page=pr-email-templates" class="pr-btn pr-btn-secondary">' . __('Cancel', 'power-reservations') . '</a>';
            echo '</div>';
            
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        } else {
            // Show templates list
            global $wpdb;
            $templates_table = $wpdb->prefix . 'pr_email_templates';
            $templates = $wpdb->get_results("SELECT * FROM $templates_table ORDER BY template_type, template_name");
            
            echo '<div class="pr-admin-content">';
            echo '<div class="pr-card">';
            echo '<div class="pr-card-header">';
            echo '<h2 class="pr-card-title">' . __('Email Templates', 'power-reservations') . '</h2>';
            echo '<div class="pr-card-actions">';
            $restore_nonce = wp_create_nonce('restore_default_templates');
            echo '<a href="?page=pr-email-templates&action=restore_defaults&_wpnonce=' . $restore_nonce . '" class="pr-btn pr-btn-secondary" ';
            echo 'onclick="return confirm(\'' . esc_js(__('This will restore missing default email templates. Continue?', 'power-reservations')) . '\')">';
            echo '<span class="dashicons dashicons-backup"></span>' . __('Restore Defaults', 'power-reservations');
            echo '</a>';
            echo '<a href="?page=pr-email-templates&add=1" class="pr-btn pr-btn-primary">';
            echo '<span class="dashicons dashicons-plus-alt2"></span>' . __('Add Template', 'power-reservations');
            echo '</a>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="pr-card-content">';
            
            if (empty($templates)) {
                echo '<div class="pr-empty-state">';
                echo '<span class="dashicons dashicons-email-alt"></span>';
                echo '<h3>' . __('No Email Templates Found', 'power-reservations') . '</h3>';
                echo '<p>' . __('It looks like your email templates are missing. You can restore the default templates or create custom ones.', 'power-reservations') . '</p>';
                echo '<div class="pr-empty-actions">';
                $restore_nonce = wp_create_nonce('restore_default_templates');
                echo '<a href="?page=pr-email-templates&action=restore_defaults&_wpnonce=' . $restore_nonce . '" class="pr-btn pr-btn-primary">';
                echo '<span class="dashicons dashicons-backup"></span>' . __('Restore Default Templates', 'power-reservations');
                echo '</a>';
                echo '<a href="?page=pr-email-templates&add=1" class="pr-btn pr-btn-secondary">';
                echo '<span class="dashicons dashicons-plus-alt2"></span>' . __('Create Custom Template', 'power-reservations');
                echo '</a>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="pr-templates-grid">';
                foreach ($templates as $template) {
                    $status_class = $template->is_active ? 'pr-status-active' : 'pr-status-inactive';
                    $type_icon = $template->template_type === 'customer' ? 'admin-users' : 'admin-tools';
                    
                    echo '<div class="pr-template-card">';
                    echo '<div class="pr-template-header">';
                    echo '<div class="pr-template-icon">';
                    echo '<span class="dashicons dashicons-' . $type_icon . '"></span>';
                    echo '</div>';
                    echo '<div class="pr-template-meta">';
                    echo '<h3 class="pr-template-title">' . esc_html(ucwords(str_replace('_', ' ', $template->template_name))) . '</h3>';
                    echo '<div class="pr-template-type">' . esc_html(ucfirst($template->template_type)) . '</div>';
                    echo '</div>';
                    echo '<div class="pr-template-status ' . $status_class . '">';
                    echo esc_html($template->is_active ? __('Active', 'power-reservations') : __('Inactive', 'power-reservations'));
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="pr-template-content">';
                    echo '<div class="pr-template-subject">';
                    echo '<strong>' . __('Subject:', 'power-reservations') . '</strong> ' . esc_html($template->template_subject);
                    echo '</div>';
                    echo '<div class="pr-template-preview">';
                    echo wp_trim_words(strip_tags($template->template_content), 20, '...');
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="pr-template-actions">';
                    echo '<a href="?page=pr-email-templates&edit=' . $template->id . '" class="pr-btn pr-btn-secondary pr-btn-sm">';
                    echo '<span class="dashicons dashicons-edit"></span>' . __('Edit', 'power-reservations');
                    echo '</a>';
                    $delete_nonce = wp_create_nonce('delete_template_' . $template->id);
                    echo '<a href="?page=pr-email-templates&delete=' . $template->id . '&_wpnonce=' . $delete_nonce . '" ';
                    echo 'class="pr-btn pr-btn-danger pr-btn-sm" onclick="return confirm(\'' . __('Are you sure you want to delete this template?', 'power-reservations') . '\')">';
                    echo '<span class="dashicons dashicons-trash"></span>' . __('Delete', 'power-reservations');
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Show template variables help section (always visible)
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h3 class="pr-card-title">' . __('Template Variables', 'power-reservations') . '</h3>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<p class="pr-help-text">' . __('Use these placeholders in your email templates to dynamically insert reservation information:', 'power-reservations') . '</p>';
        echo '<div class="pr-variables-grid">';
        
        $variables = array(
            '{name}' => __('Customer name', 'power-reservations'),
            '{email}' => __('Customer email', 'power-reservations'),
            '{phone}' => __('Customer phone', 'power-reservations'),
            '{date}' => __('Reservation date', 'power-reservations'),
            '{time}' => __('Reservation time', 'power-reservations'),
            '{party_size}' => __('Party size', 'power-reservations'),
            '{special_requests}' => __('Special requests', 'power-reservations'),
            '{business_name}' => __('Business name', 'power-reservations'),
            '{reservation_id}' => __('Reservation ID', 'power-reservations'),
            '{edit_link}' => __('Edit reservation link', 'power-reservations'),
            '{admin_link}' => __('Admin management link', 'power-reservations')
        );
        
        foreach ($variables as $variable => $description) {
            echo '<div class="pr-variable-item">';
            echo '<code class="pr-variable-code">' . esc_html($variable) . '</code>';
            echo '<span class="pr-variable-desc">' . esc_html($description) . '</span>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // .pr-admin-container
        echo '</div>'; // .wrap.pr-admin-page
    }
    
    /**
     * Form Builder page
     */
    public function form_builder_page() {
        echo '<div class="wrap pr-admin-page">';
        echo '<div class="pr-admin-container">';
        echo '<div class="pr-admin-header">';
        echo '<h1 class="pr-page-title">' . __('Form Builder', 'power-reservations') . '</h1>';
        echo '<p class="pr-page-subtitle">' . __('Customize your reservation form fields and styling', 'power-reservations') . '</p>';
        echo '</div>';
        
        // Handle form submission
        if (isset($_POST['submit_form_builder'])) {
            // Verify nonce
            if (!wp_verify_nonce($_POST['pr_form_builder_nonce'], 'pr_form_builder')) {
                wp_die(__('Security check failed', 'power-reservations'));
            }
            
            // Save styling mode
            update_option('pr_styling_mode', sanitize_text_field($_POST['styling_mode']));
            
            // Save form fields (enabled fields)
            $enabled_fields = isset($_POST['form_fields']) ? array_map('sanitize_text_field', $_POST['form_fields']) : array();
            update_option('pr_form_fields', $enabled_fields);
            
            // Save field order (if provided via JavaScript)
            if (isset($_POST['field_order'])) {
                $field_order = array_map('sanitize_text_field', explode(',', $_POST['field_order']));
                update_option('pr_form_field_order', $field_order);
            }
            
            // Save field settings
            $field_settings = array();
            $available_fields = array('name', 'email', 'phone', 'date', 'time', 'party_size', 'special_requests');
            
            foreach ($available_fields as $field) {
                if (isset($_POST[$field . '_label'])) {
                    $field_settings[$field]['label'] = sanitize_text_field($_POST[$field . '_label']);
                }
                if (isset($_POST[$field . '_placeholder'])) {
                    $field_settings[$field]['placeholder'] = sanitize_text_field($_POST[$field . '_placeholder']);
                }
                $field_settings[$field]['required'] = isset($_POST[$field . '_required']);
            }
            update_option('pr_form_field_settings', $field_settings);
            
            echo '<div class="pr-notification pr-notification-success">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo '<span>' . __('Form configuration saved successfully!', 'power-reservations') . '</span>';
            echo '<button class="pr-notification-close">&times;</button>';
            echo '</div>';
        }
        
        // Get current settings
        $styling_mode = get_option('pr_styling_mode', 'custom');
        $available_fields = array(
            'name' => array(
                'label' => __('Name', 'power-reservations'),
                'icon' => '👤',
                'type' => 'text',
                'required_default' => true
            ),
            'email' => array(
                'label' => __('Email', 'power-reservations'),
                'icon' => '📧',
                'type' => 'email',
                'required_default' => true
            ),
            'phone' => array(
                'label' => __('Phone', 'power-reservations'),
                'icon' => '📱',
                'type' => 'tel',
                'required_default' => false
            ),
            'date' => array(
                'label' => __('Date', 'power-reservations'),
                'icon' => '📅',
                'type' => 'date',
                'required_default' => true
            ),
            'time' => array(
                'label' => __('Time', 'power-reservations'),
                'icon' => '🕐',
                'type' => 'select',
                'required_default' => true
            ),
            'party_size' => array(
                'label' => __('Party Size', 'power-reservations'),
                'icon' => '👥',
                'type' => 'select',
                'required_default' => true
            ),
            'special_requests' => array(
                'label' => __('Special Requests', 'power-reservations'),
                'icon' => '💬',
                'type' => 'textarea',
                'required_default' => false
            )
        );
        
        $current_fields = get_option('pr_form_fields', array_keys($available_fields));
        $field_order = get_option('pr_form_field_order', array_keys($available_fields));
        $field_settings = get_option('pr_form_field_settings', array());
        
        $booking_window = get_option('pr_booking_window', 30);
        $min_date = date('Y-m-d', strtotime('+1 day'));
        $max_date = date('Y-m-d', strtotime("+{$booking_window} days"));
        
        echo '<div class="pr-admin-content">';
        
        echo '<form method="post" id="pr-form-builder-form" class="pr-form">';
        wp_nonce_field('pr_form_builder', 'pr_form_builder_nonce');
        
        // Hidden field to store field order
        echo '<input type="hidden" id="field_order" name="field_order" value="' . esc_attr(implode(',', $field_order)) . '">';
        
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">' . __('Form Styling Mode', 'power-reservations') . '</h2>';
        echo '<p class="pr-card-description">' . __('Choose how you want to style your reservation form', 'power-reservations') . '</p>';
        echo '</div>';
        
        echo '<div class="pr-card-content">';
        echo '<div class="pr-styling-options">';
        
        // Use Current Theme Option
        echo '<div class="pr-styling-option ' . ($styling_mode === 'theme' ? 'active' : '') . '" onclick="selectStylingMode(\'theme\')">';
        echo '<input type="radio" name="styling_mode" value="theme" id="styling_theme"' . checked($styling_mode, 'theme', false) . '>';
        echo '<h5>' . __('🎨 Use Current Theme', 'power-reservations') . '</h5>';
        echo '<p>' . __('Automatically inherit colors, fonts, and styling from your active WordPress theme. Perfect for seamless integration.', 'power-reservations') . '</p>';
        echo '</div>';
        
        // Custom Styling Option
        echo '<div class="pr-styling-option ' . ($styling_mode === 'custom' ? 'active' : '') . '" onclick="selectStylingMode(\'custom\')">';
        echo '<input type="radio" name="styling_mode" value="custom" id="styling_custom"' . checked($styling_mode, 'custom', false) . '>';
        echo '<h5>' . __('🎛️ Custom Styling', 'power-reservations') . '</h5>';
        echo '<p>' . __('Use the Form Styling page to customize colors, fonts, and appearance exactly how you want them.', 'power-reservations') . '</p>';
        echo '</div>';
        
        // Minimal Styling Option
        echo '<div class="pr-styling-option ' . ($styling_mode === 'minimal' ? 'active' : '') . '" onclick="selectStylingMode(\'minimal\')">';
        echo '<input type="radio" name="styling_mode" value="minimal" id="styling_minimal"' . checked($styling_mode, 'minimal', false) . '>';
        echo '<h5>' . __('🎯 Minimal Style', 'power-reservations') . '</h5>';
        echo '<p>' . __('Clean, minimal styling that works with any theme. Great for maximum compatibility.', 'power-reservations') . '</p>';
        echo '</div>';
        
        echo '</div>'; // .pr-styling-options
        echo '</div>'; // .pr-card-content
        echo '</div>'; // .pr-card
        
        // Live Preview Card - right after styling options
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">' . __('Live Preview & Field Configuration', 'power-reservations') . '</h2>';
        echo '<p class="pr-card-description">' . __('See how your form appears and configure field settings', 'power-reservations') . '</p>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        
        // Live Preview Section
        echo '<div class="pr-preview-section">';
        echo '<h3 class="pr-section-title">' . __('Form Preview', 'power-reservations') . '</h3>';
        echo '<div class="pr-form-preview" id="pr-live-form-preview">';
        
        // Render preview form (non-functional, for display only)
        $this->render_preview_form();
        
        echo '</div>';
        echo '</div>';
        
        // Field Configuration Section - Now under preview
        echo '<div class="pr-fields-section">';
        echo '<h3 class="pr-section-title">' . __('Field Configuration', 'power-reservations') . '</h3>';
        echo '<p class="pr-section-description">' . __('Drag to reorder, toggle to enable/disable, and customize each field', 'power-reservations') . '</p>';
        echo '<div class="pr-field-items" id="pr-sortable-fields">';
        
        // Display fields in saved order
        foreach ($field_order as $field_key) {
            if (!isset($available_fields[$field_key])) continue; // Skip invalid fields
            
            $field_info = $available_fields[$field_key];
            $is_enabled = in_array($field_key, $current_fields);
            $is_required = isset($field_settings[$field_key]['required']) ? $field_settings[$field_key]['required'] : $field_info['required_default'];
            $custom_label = isset($field_settings[$field_key]['label']) ? $field_settings[$field_key]['label'] : $field_info['label'];
            $placeholder = isset($field_settings[$field_key]['placeholder']) ? $field_settings[$field_key]['placeholder'] : '';
            
            echo '<div class="pr-field-config ' . ($is_enabled ? 'pr-field-enabled' : 'pr-field-disabled') . '" data-field="' . $field_key . '">';
            echo '<div class="pr-drag-handle" title="' . __('Drag to reorder', 'power-reservations') . '">⋮⋮</div>';
            
            // Field Header
            echo '<div class="pr-field-header">';
            echo '<div class="pr-field-title">';
            echo '<span class="pr-field-icon">' . esc_html($field_info['icon']) . '</span>';
            echo '<span>' . esc_html($field_info['label']) . ' (' . esc_html(ucfirst($field_info['type'])) . ')</span>';
            if ($field_info['required_default']) {
                echo '<span class="pr-required-field">*</span>';
            }
            echo '</div>';
            
            echo '<label class="pr-field-toggle" for="toggle_' . $field_key . '">';
            echo '<input type="checkbox" name="form_fields[]" value="' . $field_key . '" id="toggle_' . $field_key . '" data-field-key="' . $field_key . '"' . checked($is_enabled, true, false) . '>';
            echo '<span class="pr-toggle-slider"></span>';
            echo '</label>';
            
            echo '</div>'; // .pr-field-header
            
            // Field Options
            echo '<div class="pr-field-options">';
            echo '<div class="pr-field-options-grid">';
            
            echo '<div>';
            echo '<label for="' . $field_key . '_label">' . __('Field Label', 'power-reservations') . '</label>';
            echo '<input type="text" name="' . $field_key . '_label" id="' . $field_key . '_label" value="' . esc_attr($custom_label) . '" placeholder="' . esc_attr($field_info['label']) . '">';
            echo '</div>';
            
            echo '<div>';
            echo '<label for="' . $field_key . '_placeholder">' . __('Placeholder Text', 'power-reservations') . '</label>';
            echo '<input type="text" name="' . $field_key . '_placeholder" id="' . $field_key . '_placeholder" value="' . esc_attr($placeholder) . '" placeholder="' . __('Enter placeholder...', 'power-reservations') . '">';
            echo '</div>';
            
            echo '</div>'; // .pr-field-options-grid
            
            if (!$field_info['required_default']) {
                echo '<div style="margin-top: 10px;">';
                echo '<label>';
                echo '<input type="checkbox" name="' . $field_key . '_required" value="1"' . checked($is_required, true, false) . '>';
                esc_html_e('Required Field', 'power-reservations');
                echo '</label>';
                echo '</div>';
            }
            
            echo '</div>'; // .pr-field-options
            echo '</div>'; // .pr-field-config
        }
        
        echo '</div>'; // .pr-field-items
        echo '</div>'; // .pr-fields-section
        echo '</div>'; // .pr-card-content
        
        echo '<div class="pr-card-footer">';
        echo '<button type="submit" name="submit_form_builder" class="pr-btn pr-btn-primary">';
        echo '<span class="dashicons dashicons-saved"></span>' . __('Save Configuration', 'power-reservations');
        echo '</button>';
        echo '<a href="' . admin_url('admin.php?page=pr-form-styling') . '" class="pr-btn pr-btn-secondary">';
        echo '<span class="dashicons dashicons-art"></span>' . __('Advanced Styling', 'power-reservations');
        echo '</a>';
        echo '</div>';
        echo '</div>'; // .pr-card
        
        echo '</form>';
        
        echo '</div>'; // .pr-admin-content
        echo '</div>'; // .pr-admin-container
        echo '</div>'; // .wrap.pr-admin-page
    }
    
    /**
     * Form Styling admin page
     */
    public function form_styling_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle save
        $styling_saved = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pr_form_styling_nonce']) && wp_verify_nonce($_POST['pr_form_styling_nonce'], 'pr_save_form_styling')) {
            $styling_mode = isset($_POST['pr_styling_mode']) ? sanitize_text_field($_POST['pr_styling_mode']) : 'custom';
            update_option('pr_styling_mode', $styling_mode);
            
            // Always save custom styling options (even if not in custom mode, so they're preserved)
            $primary_color = isset($_POST['pr_primary_color']) ? sanitize_hex_color($_POST['pr_primary_color']) : '#667eea';
            $secondary_color = isset($_POST['pr_secondary_color']) ? sanitize_hex_color($_POST['pr_secondary_color']) : '#764ba2';
            $button_color = isset($_POST['pr_button_color']) ? sanitize_hex_color($_POST['pr_button_color']) : '#667eea';
            $input_border_color = isset($_POST['pr_input_border_color']) ? sanitize_hex_color($_POST['pr_input_border_color']) : '#ddd';
            $label_color = isset($_POST['pr_label_color']) ? sanitize_hex_color($_POST['pr_label_color']) : '#888';
            $font_size = isset($_POST['pr_font_size']) ? absint($_POST['pr_font_size']) : 16;
            $border_radius = isset($_POST['pr_border_radius']) ? absint($_POST['pr_border_radius']) : 8;
            $input_padding = isset($_POST['pr_input_padding']) ? absint($_POST['pr_input_padding']) : 12;
            $form_width = isset($_POST['pr_form_width']) ? absint($_POST['pr_form_width']) : 600;
            
            update_option('pr_primary_color', $primary_color);
            update_option('pr_secondary_color', $secondary_color);
            update_option('pr_button_color', $button_color);
            update_option('pr_input_border_color', $input_border_color);
            update_option('pr_label_color', $label_color);
            update_option('pr_font_size', $font_size);
            update_option('pr_border_radius', $border_radius);
            update_option('pr_input_padding', $input_padding);
            update_option('pr_form_width', $form_width);
            
            $styling_saved = true;
        }

        $current_mode = get_option('pr_styling_mode', 'custom');
        $primary_color = get_option('pr_primary_color', '#667eea');
        $secondary_color = get_option('pr_secondary_color', '#764ba2');
        $button_color = get_option('pr_button_color', '#667eea');
        $input_border_color = get_option('pr_input_border_color', '#ddd');
        $label_color = get_option('pr_label_color', '#888');
        $font_size = get_option('pr_font_size', 16);
        $border_radius = get_option('pr_border_radius', 8);
        $input_padding = get_option('pr_input_padding', 12);
        $form_width = get_option('pr_form_width', 600);

        echo '<div class="wrap pr-admin-page">';
        echo '<div class="pr-admin-container">';
        
        // Show notification inside container
        if ($styling_saved) {
            echo '<div class="pr-notification pr-notification-success">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo '<span>' . __('Form styling saved successfully!', 'power-reservations') . '</span>';
            echo '<button class="pr-notification-close">&times;</button>';
            echo '</div>';
        }
        
        echo '<div class="pr-admin-header">';
        echo '<h1 class="pr-page-title">' . esc_html__('Form Styling', 'power-reservations') . '</h1>';
        echo '<p class="pr-page-subtitle">' . esc_html__('Customize the appearance of your reservation form', 'power-reservations') . '</p>';
        echo '</div>';
        
        echo '<div class="pr-admin-content">';
        echo '<div class="pr-two-column-layout">';
        
        // Left column - Styling controls
        echo '<div class="pr-styling-controls">';
        echo '<form method="post" action="" id="pr-styling-form">';
        wp_nonce_field('pr_save_form_styling', 'pr_form_styling_nonce');
        
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">' . esc_html__('Styling Mode', 'power-reservations') . '</h2>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<div class="pr-radio-group">';
        echo '<label class="pr-radio-option"><input type="radio" name="pr_styling_mode" value="custom" ' . checked($current_mode, 'custom', false) . ' /> Custom</label>';
        echo '<label class="pr-radio-option"><input type="radio" name="pr_styling_mode" value="theme" ' . checked($current_mode, 'theme', false) . ' /> Use Theme</label>';
        echo '<label class="pr-radio-option"><input type="radio" name="pr_styling_mode" value="minimal" ' . checked($current_mode, 'minimal', false) . ' /> Minimal</label>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Custom styling options (show only if custom mode)
        echo '<div id="custom-styling-options" style="' . ($current_mode !== 'custom' ? 'display:none;' : '') . '">';
        
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">🎨 ' . esc_html__('Colors', 'power-reservations') . '</h2>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Primary Color', 'power-reservations') . '</label>';
        echo '<input type="color" name="pr_primary_color" value="' . esc_attr($primary_color) . '" class="pr-color-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Secondary Color', 'power-reservations') . '</label>';
        echo '<input type="color" name="pr_secondary_color" value="' . esc_attr($secondary_color) . '" class="pr-color-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Button Color', 'power-reservations') . '</label>';
        echo '<input type="color" name="pr_button_color" value="' . esc_attr($button_color) . '" class="pr-color-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Input Border Color', 'power-reservations') . '</label>';
        echo '<input type="color" name="pr_input_border_color" value="' . esc_attr($input_border_color) . '" class="pr-color-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Label Color', 'power-reservations') . '</label>';
        echo '<input type="color" name="pr_label_color" value="' . esc_attr($label_color) . '" class="pr-color-input" />';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">📐 ' . esc_html__('Dimensions', 'power-reservations') . '</h2>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Font Size (px)', 'power-reservations') . '</label>';
        echo '<input type="number" name="pr_font_size" value="' . esc_attr($font_size) . '" min="12" max="24" class="pr-number-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Border Radius (px)', 'power-reservations') . '</label>';
        echo '<input type="number" name="pr_border_radius" value="' . esc_attr($border_radius) . '" min="0" max="50" class="pr-number-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Input Padding (px)', 'power-reservations') . '</label>';
        echo '<input type="number" name="pr_input_padding" value="' . esc_attr($input_padding) . '" min="8" max="24" class="pr-number-input" />';
        echo '</div>';
        
        echo '<div class="pr-form-group">';
        echo '<label>' . esc_html__('Form Max Width (px)', 'power-reservations') . '</label>';
        echo '<input type="number" name="pr_form_width" value="' . esc_attr($form_width) . '" min="400" max="1200" step="50" class="pr-number-input" />';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // #custom-styling-options
        
        echo '<div class="pr-card-footer">';
        echo '<button type="submit" class="pr-btn pr-btn-primary">' . esc_html__('Save Styling', 'power-reservations') . '</button>';
        echo '<a href="' . admin_url('admin.php?page=pr-form-builder') . '" class="pr-btn pr-btn-secondary">' . esc_html__('Back to Form Builder', 'power-reservations') . '</a>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>'; // .pr-styling-controls
        
        // Right column - Live preview
        echo '<div class="pr-live-preview">';
        echo '<div class="pr-card">';
        echo '<div class="pr-card-header">';
        echo '<h2 class="pr-card-title">👁️ ' . esc_html__('Live Preview', 'power-reservations') . '</h2>';
        echo '</div>';
        echo '<div class="pr-card-content">';
        echo '<div id="pr-form-preview-container" style="background:#f5f5f5; padding:2em; border-radius:8px;">';
        
        // Render preview form (non-functional, for display only)
        $this->render_preview_form();
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>'; // .pr-live-preview
        
        echo '</div>'; // .pr-two-column-layout
        echo '</div>'; // .pr-admin-content
        echo '</div>'; // .pr-admin-container
        echo '</div>'; // .wrap
        
        // Add inline JavaScript for toggling custom options and live preview
        echo '<script>
        jQuery(document).ready(function($) {
            $("input[name=pr_styling_mode]").on("change", function() {
                if ($(this).val() === "custom") {
                    $("#custom-styling-options").slideDown();
                } else {
                    $("#custom-styling-options").slideUp();
                }
            });
            
            // Live preview updates
            $(".pr-color-input, .pr-number-input").on("input change", function() {
                var primaryColor = $("input[name=pr_primary_color]").val();
                var borderRadius = $("input[name=pr_border_radius]").val() + "px";
                var fontSize = $("input[name=pr_font_size]").val() + "px";
                
                $("#pr-form-preview-container .pr-input").css({
                    "border-radius": borderRadius,
                    "font-size": fontSize
                });
                $("#pr-form-preview-container .pr-label").css({
                    "color": $("input[name=pr_label_color]").val()
                });
                $("#pr-form-preview-container .pr-input").css({
                    "border-color": $("input[name=pr_input_border_color]").val()
                });
                $("#pr-form-preview-container .pr-submit-btn").css({
                    "background": "linear-gradient(135deg, " + primaryColor + ", " + $("input[name=pr_secondary_color]").val() + ")",
                    "border-radius": borderRadius
                });
            });
        });
        </script>';
        
        echo '<style>
        .pr-two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2em;
            margin-top: 2em;
        }
        .pr-color-input {
            width: 100%;
            height: 50px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
        }
        .pr-number-input {
            width: 100%;
            padding: 0.8em;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }
        .pr-radio-group {
            display: flex;
            gap: 1em;
            flex-direction: column;
        }
        .pr-radio-option {
            padding: 1em;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .pr-radio-option:has(input:checked) {
            border-color: #667eea;
            background: #f0f4ff;
        }
        @media (max-width: 1200px) {
            .pr-two-column-layout {
                grid-template-columns: 1fr;
            }
        }
        </style>';
    }
    
    /**
     * Send confirmation email to customer
     */
    private function send_confirmation_email($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $templates_table = $wpdb->prefix . 'pr_email_templates';
        
        $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $reservation_id));
        if (!$reservation) return;
        
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $templates_table WHERE template_name = %s AND is_active = 1",
            'customer_confirmation'
        ));
        
        if (!$template) return;
        
        $edit_link = home_url('?pr_action=edit&token=' . $reservation->edit_token);
        
        $placeholders = array(
            '{name}' => $reservation->name,
            '{email}' => $reservation->email,
            '{phone}' => $reservation->phone,
            '{date}' => date('F j, Y', strtotime($reservation->reservation_date)),
            '{time}' => date('g:i A', strtotime($reservation->reservation_time)),
            '{party_size}' => $reservation->party_size,
            '{special_requests}' => $reservation->special_requests,
            '{business_name}' => get_option('pr_business_name', get_bloginfo('name')),
            '{reservation_id}' => $reservation->reservation_id,
            '{edit_link}' => $edit_link
        );
        
        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->template_subject);
        $message = str_replace(array_keys($placeholders), array_values($placeholders), $template->template_content);
        
        $result = wp_mail($reservation->email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
        
        if (!$result) {
            error_log('Power Reservations: Failed to send confirmation email to ' . $reservation->email . ' for reservation ID ' . $reservation_id);
        }
        
        return $result;
    }
    
    /**
     * Send notification email to admin
     */
    private function send_admin_notification_email($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $templates_table = $wpdb->prefix . 'pr_email_templates';
        
        $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $reservation_id));
        if (!$reservation) return;
        
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $templates_table WHERE template_name = %s AND is_active = 1",
            'admin_notification'
        ));
        
        if (!$template) return;
        
        $admin_link = admin_url('admin.php?page=power-reservations&action=view&reservation=' . $reservation->id);
        
        $placeholders = array(
            '{name}' => $reservation->name,
            '{email}' => $reservation->email,
            '{phone}' => $reservation->phone,
            '{date}' => date('F j, Y', strtotime($reservation->reservation_date)),
            '{time}' => date('g:i A', strtotime($reservation->reservation_time)),
            '{party_size}' => $reservation->party_size,
            '{special_requests}' => $reservation->special_requests,
            '{business_name}' => get_option('pr_business_name', get_bloginfo('name')),
            '{reservation_id}' => $reservation->reservation_id,
            '{admin_link}' => $admin_link
        );
        
        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->template_subject);
        $message = str_replace(array_keys($placeholders), array_values($placeholders), $template->template_content);
        
        $admin_email = get_option('pr_business_email', get_option('admin_email'));
        $result = wp_mail($admin_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
        
        if (!$result) {
            error_log('Power Reservations: Failed to send admin notification email to ' . $admin_email . ' for reservation ID ' . $reservation_id);
        }
        
        return $result;
    }
    
    /**
     * My Reservations shortcode
     */
    public function my_reservations_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your reservations.', 'power-reservations') . '</p>';
        }
        
        $user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        
        $reservations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY reservation_date DESC, reservation_time DESC",
            $user_id
        ));
        
        if (empty($reservations)) {
            return '<p>' . __('You have no reservations yet.', 'power-reservations') . '</p>';
        }
        
        ob_start();
        echo '<div class="pr-my-reservations">';
        echo '<h3>' . __('My Reservations', 'power-reservations') . '</h3>';
        
        foreach ($reservations as $reservation) {
            $can_edit = strtotime($reservation->reservation_date . ' ' . $reservation->reservation_time) > (time() + (get_option('pr_edit_window_hours', 24) * 3600));
            
            echo '<div class="pr-reservation-item status-' . esc_attr($reservation->status) . '">';
            echo '<div class="pr-reservation-header">';
            /* translators: %s: Reservation ID code */
            echo '<h4>' . sprintf(__('Reservation #%s', 'power-reservations'), esc_html($reservation->reservation_id)) . '</h4>';
            echo '<span class="pr-reservation-status">' . esc_html(ucfirst($reservation->status)) . '</span>';
            echo '</div>';
            
            echo '<div class="pr-reservation-details">';
            echo '<p><strong>' . __('Date:', 'power-reservations') . '</strong> ' . esc_html(date('F j, Y', strtotime($reservation->reservation_date))) . '</p>';
            echo '<p><strong>' . __('Time:', 'power-reservations') . '</strong> ' . esc_html(date('g:i A', strtotime($reservation->reservation_time))) . '</p>';
            echo '<p><strong>' . __('Party Size:', 'power-reservations') . '</strong> ' . esc_html($reservation->party_size) . '</p>';
            if ($reservation->special_requests) {
                echo '<p><strong>' . __('Special Requests:', 'power-reservations') . '</strong> ' . esc_html($reservation->special_requests) . '</p>';
            }
            echo '</div>';
            
            if ($can_edit && in_array($reservation->status, array('pending', 'approved'))) {
                echo '<div class="pr-reservation-actions">';
                echo '<a href="?pr_action=edit&token=' . esc_attr($reservation->edit_token) . '" class="button">' . __('Edit', 'power-reservations') . '</a>';
                echo '<a href="?pr_action=cancel&token=' . esc_attr($reservation->edit_token) . '" class="button button-secondary" onclick="return confirm(\'' . __('Are you sure you want to cancel this reservation?', 'power-reservations') . '\')">' . __('Cancel', 'power-reservations') . '</a>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        return ob_get_clean();
    }
    
    /* ========================================
       AJAX HANDLERS
       ======================================== */
    
    /**
     * Check time slot availability via AJAX
     * 
     * Handles frontend requests to check if a specific
     * date/time/party size combination is available
     * 
     * @return void (outputs JSON response)
     */
    public function check_availability() {
        check_ajax_referer('pr_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date']);
        $party_size = intval($_POST['party_size']);
        
        if (empty($date) || empty($party_size)) {
            wp_send_json_error(__('Invalid parameters.', 'power-reservations'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $max_per_slot = get_option('pr_max_reservations_per_slot', 5);
        $time_slots = get_option('pr_time_slots', array());
        
        $available_times = array();
        
        foreach ($time_slots as $time => $label) {
            $existing_count = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(party_size) FROM $table_name WHERE reservation_date = %s AND reservation_time = %s AND status IN ('pending', 'approved')",
                $date, $time
            ));
            
            $remaining_capacity = $max_per_slot - intval($existing_count);
            
            if ($remaining_capacity >= $party_size) {
                $available_times[] = array(
                    'value' => $time,
                    'label' => $label,
                    'remaining' => $remaining_capacity
                );
            }
        }
        
        wp_send_json_success($available_times);
    }
    
    /**
     * Handle reservation cancellation
     */
    public function handle_reservation_cancellation() {
        check_ajax_referer('pr_nonce', 'nonce');
        
        $token = sanitize_text_field($_POST['token']);
        
        if (empty($token)) {
            wp_send_json_error(__('Invalid token.', 'power-reservations'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        
        $reservation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE edit_token = %s",
            $token
        ));
        
        if (!$reservation) {
            wp_send_json_error(__('Reservation not found.', 'power-reservations'));
        }
        
        // Check if within edit window
        $edit_window_hours = get_option('pr_edit_window_hours', 24);
        $reservation_time = strtotime($reservation->reservation_date . ' ' . $reservation->reservation_time);
        
        if ($reservation_time <= (time() + ($edit_window_hours * 3600))) {
            wp_send_json_error(__('Cannot cancel reservation within the edit window.', 'power-reservations'));
        }
        
        $wpdb->update(
            $table_name,
            array('status' => 'cancelled'),
            array('id' => $reservation->id),
            array('%s'),
            array('%d')
        );
        
        wp_send_json_success(__('Reservation cancelled successfully.', 'power-reservations'));
    }
    
    /**
     * Handle reservation edit
     */
    public function handle_reservation_edit() {
        check_ajax_referer('pr_nonce', 'nonce');
        
        $token = sanitize_text_field($_POST['token']);
        
        if (empty($token)) {
            wp_send_json_error(__('Invalid token.', 'power-reservations'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        
        $reservation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE edit_token = %s",
            $token
        ));
        
        if (!$reservation) {
            wp_send_json_error(__('Reservation not found.', 'power-reservations'));
        }
        
        // Check if within edit window
        $edit_window_hours = get_option('pr_edit_window_hours', 24);
        $reservation_time = strtotime($reservation->reservation_date . ' ' . $reservation->reservation_time);
        
        if ($reservation_time <= (time() + ($edit_window_hours * 3600))) {
            wp_send_json_error(__('Cannot edit reservation within the edit window.', 'power-reservations'));
        }
        
        // Update reservation
        $wpdb->update(
            $table_name,
            array(
                'reservation_date' => sanitize_text_field($_POST['date']),
                'reservation_time' => sanitize_text_field($_POST['time']),
                'party_size' => intval($_POST['party_size']),
                'special_requests' => sanitize_textarea_field($_POST['special_requests'])
            ),
            array('id' => $reservation->id),
            array('%s', '%s', '%d', '%s'),
            array('%d')
        );
        
        wp_send_json_success(__('Reservation updated successfully.', 'power-reservations'));
    }
    
    /**
     * Add query vars for reservation management
     */
    public function add_query_vars($vars) {
        $vars[] = 'pr_action';
        $vars[] = 'token';
        return $vars;
    }
    
    /**
     * Handle reservation actions from URL
     */
    public function handle_reservation_actions() {
        $action = get_query_var('pr_action');
        $token = get_query_var('token');
        
        if (!$action || !$token) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        
        $reservation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE edit_token = %s",
            $token
        ));
        
        if (!$reservation) {
            wp_die(__('Invalid reservation link.', 'power-reservations'));
        }
        
        if ($action === 'cancel') {
            $wpdb->update(
                $table_name,
                array('status' => 'cancelled'),
                array('id' => $reservation->id),
                array('%s'),
                array('%d')
            );
            
            // Redirect to the edit page with a success message
            wp_redirect(home_url('?pr_action=edit&token=' . $token . '&cancelled=1'));
            exit;
        }
        
        if ($action === 'edit') {
            // Render the full page
            $this->render_reservation_page($reservation, $token);
            exit;
        }
    }
    
    /**
     * Render full reservation management page
     */
    private function render_reservation_page($reservation, $token) {
        // Get site info for header
        $site_name = get_bloginfo('name');
        $cancelled = isset($_GET['cancelled']) ? true : false;
        
        // Output full HTML page
        ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html__('Manage Your Reservation', 'power-reservations') . ' - ' . esc_html($site_name); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: #f0f0f1;
            margin: 0;
            padding: 20px;
        }
        .pr-reservation-page {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .pr-reservation-page h1 {
            margin: 0 0 30px 0;
            padding-bottom: 20px;
            border-bottom: 2px solid #2271b1;
            color: #1d2327;
        }
        .pr-success-message {
            background: #00a32a;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .pr-reservation-details {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        .pr-detail-label {
            font-weight: 600;
            color: #1d2327;
        }
        .pr-detail-value {
            color: #50575e;
        }
        .pr-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
        }
        .pr-status.pending {
            background: #f0f6fc;
            color: #0969da;
        }
        .pr-status.confirmed {
            background: #dafbe1;
            color: #1a7f37;
        }
        .pr-status.cancelled {
            background: #ffebe9;
            color: #cf222e;
        }
        .pr-actions {
            padding-top: 30px;
            border-top: 1px solid #dcdcde;
            display: flex;
            gap: 10px;
        }
        .pr-button {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .pr-button-primary {
            background: #2271b1;
            color: #fff;
        }
        .pr-button-primary:hover {
            background: #135e96;
            color: #fff;
        }
        .pr-button-danger {
            background: #dc3232;
            color: #fff;
        }
        .pr-button-danger:hover {
            background: #a00;
            color: #fff;
        }
        .pr-back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2271b1;
            text-decoration: none;
        }
        .pr-back-link:hover {
            color: #135e96;
        }
    </style>
</head>
<body class="pr-reservation-management">
    <div class="pr-reservation-page">
        <h1><?php esc_html_e('Manage Your Reservation', 'power-reservations'); ?></h1>
        
        <?php if ($cancelled): ?>
        <div class="pr-success-message">
            <?php esc_html_e('Your reservation has been cancelled successfully.', 'power-reservations'); ?>
        </div>
        <?php endif; ?>
        
        <div class="pr-reservation-details">
            <div class="pr-detail-label"><?php esc_html_e('Reservation ID:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->reservation_id); ?></div>
            
            <div class="pr-detail-label"><?php esc_html_e('Status:', 'power-reservations'); ?></div>
            <div class="pr-detail-value">
                <span class="pr-status <?php echo esc_attr($reservation->status); ?>">
                    <?php echo esc_html(ucfirst($reservation->status)); ?>
                </span>
            </div>
            
            <div class="pr-detail-label"><?php esc_html_e('Name:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->name); ?></div>
            
            <div class="pr-detail-label"><?php esc_html_e('Email:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->email); ?></div>
            
            <div class="pr-detail-label"><?php esc_html_e('Phone:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->phone); ?></div>
            
            <div class="pr-detail-label"><?php esc_html_e('Date:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($reservation->reservation_date))); ?></div>
            
            <div class="pr-detail-label"><?php esc_html_e('Time:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->reservation_time); ?></div>
            
            <div class="pr-detail-label"><?php esc_html_e('Party Size:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->party_size); ?></div>
            
            <?php if (!empty($reservation->special_requests)): ?>
            <div class="pr-detail-label"><?php esc_html_e('Special Requests:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html($reservation->special_requests); ?></div>
            <?php endif; ?>
            
            <div class="pr-detail-label"><?php esc_html_e('Created:', 'power-reservations'); ?></div>
            <div class="pr-detail-value"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reservation->created_at))); ?></div>
        </div>
        
        <?php if ($reservation->status !== 'cancelled'): ?>
        <div class="pr-actions">
            <a href="<?php echo esc_url(home_url('?pr_action=cancel&token=' . $token)); ?>" 
               class="pr-button pr-button-danger" 
               onclick="return confirm('<?php esc_attr_e('Are you sure you want to cancel this reservation?', 'power-reservations'); ?>')">
                <?php esc_html_e('Cancel Reservation', 'power-reservations'); ?>
            </a>
        </div>
        <?php endif; ?>
        
        <a href="<?php echo esc_url(home_url()); ?>" class="pr-back-link">
            ← <?php esc_html_e('Back to Home', 'power-reservations'); ?>
        </a>
    </div>
    <?php wp_footer(); ?>
</body>
</html><?php
    }
    
    /**
     * Send reminder email
     */
    public function send_reminder_email($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        
        $reservation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND reminder_sent = 0",
            $reservation_id
        ));
        
        if (!$reservation) {
            return;
        }
        
        // Use email template
        $templates_table = $wpdb->prefix . 'pr_email_templates';
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $templates_table WHERE template_name = %s AND is_active = 1",
            'customer_reminder'
        ));
        
        if ($template) {
            $edit_link = home_url('?pr_action=edit&token=' . $reservation->edit_token);
            
            $placeholders = array(
                '{name}' => $reservation->name,
                '{date}' => date('F j, Y', strtotime($reservation->reservation_date)),
                '{time}' => date('g:i A', strtotime($reservation->reservation_time)),
                '{party_size}' => $reservation->party_size,
                '{business_name}' => get_option('pr_business_name', get_bloginfo('name')),
                '{reservation_id}' => $reservation->reservation_id,
                '{edit_link}' => $edit_link
            );
            
            $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->template_subject);
            $message = str_replace(array_keys($placeholders), array_values($placeholders), $template->template_content);
            
            $result = wp_mail($reservation->email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
            if ($result) {
                $wpdb->update(
                    $table_name,
                    array('reminder_sent' => 1),
                    array('id' => $reservation_id),
                    array('%d'),
                    array('%d')
                );
            } else {
                error_log('Power Reservations: Failed to send reminder email to ' . $reservation->email . ' for reservation ID ' . $reservation_id);
            }
            
            return $result;
        }
    }
    
    /**
     * Daily cleanup
     */
    public function daily_cleanup() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        
        // Schedule reminder emails for today's reservations
        $today = date('Y-m-d');
        $reservations = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM $table_name WHERE reservation_date = %s AND status = 'approved' AND reminder_sent = 0",
            $today
        ));
        
        foreach ($reservations as $reservation) {
            wp_schedule_single_event(time(), 'pr_send_reminder', array($reservation->id));
        }
        
        // Clean up old cancelled reservations (older than 30 days)
        $wpdb->delete(
            $table_name,
            array(
                'status' => 'cancelled',
            ),
            array('%s'),
            'created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)'
        );
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'pr_dashboard_widget',
            __('Reservation Summary', 'power-reservations'),
            array($this, 'dashboard_widget_content')
        );
    }
    
    /**
     * Dashboard widget content
     */
    public function dashboard_widget_content() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pr_reservations';
        $today = date('Y-m-d');
        
        $stats = array(
            'today' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE reservation_date = %s", $today)),
            'pending' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE status = %s", 'pending')),
            'this_week' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE reservation_date BETWEEN %s AND %s", $today, date('Y-m-d', strtotime('+7 days'))))
        );
        
        echo '<div class="pr-dashboard-stats">';
        echo '<p><strong>' . $stats['today'] . '</strong> ' . __('reservations today', 'power-reservations') . '</p>';
        echo '<p><strong>' . $stats['pending'] . '</strong> ' . __('pending approval', 'power-reservations') . '</p>';
        echo '<p><strong>' . $stats['this_week'] . '</strong> ' . __('reservations this week', 'power-reservations') . '</p>';
        echo '<p><a href="' . admin_url('admin.php?page=power-reservations') . '" class="button">' . __('Manage Reservations', 'power-reservations') . '</a></p>';
        echo '</div>';
    }
    
    /* ========================================
       ELEMENTOR INTEGRATION
       ======================================== */
    
    /**
     * Register custom Elementor category for Power Reservations widgets
     * 
     * Creates a dedicated category in Elementor's widget panel
     * for easy organization of reservation-related widgets
     * 
     * @param object $elements_manager Elementor elements manager instance
     * @return void
     */
    public function register_elementor_category($elements_manager) {
        // Verify elements manager exists and has add_category method
        if (!$elements_manager || !method_exists($elements_manager, 'add_category')) {
            error_log('Power Reservations: Invalid elements manager for Elementor category registration');
            return;
        }
        
        try {
            $elements_manager->add_category(
                'power-reservations',
                array(
                    'title' => __('Power Reservations', 'power-reservations'),
                    'icon' => 'fa fa-calendar-alt',
                )
            );
        } catch (Exception $e) {
            error_log('Power Reservations: Failed to register Elementor category: ' . $e->getMessage());
        }
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_elementor_widgets() {
        // Check if Elementor is active and loaded
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Check if Elementor Plugin class exists
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        
        // Check if widget file exists
        $widget_file = PR_PLUGIN_PATH . 'includes/elementor-widget.php';
        if (!file_exists($widget_file)) {
            error_log('Power Reservations: Elementor widget file not found: ' . $widget_file);
            return;
        }
        
        // Include the widget file
        require_once $widget_file;
        
        // Check if widget class exists after including
        if (!class_exists('PowerReservations_Elementor_Widget')) {
            error_log('Power Reservations: PowerReservations_Elementor_Widget class not found');
            return;
        }
        
        try {
            // Register the widget
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \PowerReservations_Elementor_Widget());
        } catch (Exception $e) {
            error_log('Power Reservations: Failed to register Elementor widget: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle AJAX reservation submission
     */
    public function handle_reservation_submission() {
        // Check if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die(json_encode(['success' => false, 'message' => __('Invalid request method', 'power-reservations')]));
        }
        
        // Check if POST data exists
        if (empty($_POST)) {
            wp_die(json_encode(['success' => false, 'message' => __('No data received', 'power-reservations')]));
        }
        
        // Verify nonce
        if (!isset($_POST['pr_nonce']) || !wp_verify_nonce($_POST['pr_nonce'], 'pr_nonce')) {
            wp_die(json_encode(['success' => false, 'message' => __('Security check failed', 'power-reservations')]));
        }
        
        // Validate required fields
        $required_fields = ['name', 'email', 'date', 'time', 'party_size'];
        $errors = array();
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                /* translators: %s: Field name (e.g., Name, Email, Party size) */
                $errors[] = sprintf(__('%s is required', 'power-reservations'), ucfirst(str_replace('_', ' ', $field)));
            }
        }
        
        if (!empty($errors)) {
            wp_die(json_encode(['success' => false, 'message' => implode('<br>', $errors)]));
        }
        
        // Generate unique reservation ID
        $reservation_id_code = strtoupper(wp_generate_password(12, false));
        
        // Generate edit token for customer self-service
        $edit_token = wp_generate_password(32, false);
        
        // Sanitize data
        $reservation_data = array(
            'reservation_id' => $reservation_id_code,
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'reservation_date' => sanitize_text_field($_POST['date']),
            'reservation_time' => sanitize_text_field($_POST['time']),
            'party_size' => intval($_POST['party_size']),
            'special_requests' => isset($_POST['special_requests']) ? sanitize_textarea_field($_POST['special_requests']) : '',
            'status' => 'pending',
            'edit_token' => $edit_token,
            'created_at' => current_time('mysql')
        );
        
        // Insert into database
        global $wpdb;
        $table_name = $this->get_reservations_table();
        
        $result = $wpdb->insert(
            $table_name,
            $reservation_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            // Log the database error for debugging
            if (!empty($wpdb->last_error)) {
                error_log('Power Reservations: Database error during reservation submission: ' . $wpdb->last_error);
            }
            wp_die(json_encode(['success' => false, 'message' => __('Failed to save reservation. Please try again.', 'power-reservations')]));
        }
        
        // Get the inserted reservation ID
        $reservation_id = $wpdb->insert_id;
        
        // Send confirmation email to customer
        $this->send_confirmation_email($reservation_id);
        
        // Send notification email to admin
        $this->send_admin_notification_email($reservation_id);
        
        // Success response
        /* translators: %s: Reservation confirmation code (e.g., A1B2C3D4E5F6) */
        wp_die(json_encode([
            'success' => true,
            'message' => sprintf(
                __('Reservation submitted successfully! Your confirmation code is: %s. We will contact you shortly to confirm.', 'power-reservations'),
                $reservation_id_code
            )
        ]));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on plugin admin pages
        if (strpos($hook, 'power-reservations') !== false ||
            strpos($hook, 'pr-settings') !== false ||
            strpos($hook, 'pr-email-templates') !== false ||
            strpos($hook, 'pr-form-builder') !== false ||
            strpos($hook, 'pr-form-styling') !== false) {

            wp_enqueue_style('pr-admin', PR_PLUGIN_URL . 'assets/admin.css', array(), PR_VERSION);
            
            // Apply custom styling to admin previews
            if (get_option('pr_styling_mode', 'custom') === 'custom') {
                $primary_color = get_option('pr_primary_color', '#667eea');
                $secondary_color = get_option('pr_secondary_color', '#764ba2');
                $button_color = get_option('pr_button_color', '#667eea');
                $input_border_color = get_option('pr_input_border_color', '#ddd');
                $label_color = get_option('pr_label_color', '#888');
                $font_size = get_option('pr_font_size', 16);
                $border_radius = get_option('pr_border_radius', 8);
                $input_padding = get_option('pr_input_padding', 12);
                $form_width = get_option('pr_form_width', 600);
                
                $custom_css = "
                    :root {
                        --pr-primary: {$primary_color};
                        --pr-secondary: {$secondary_color};
                    }
                    .pr-reservation-form-wrapper {
                        max-width: {$form_width}px;
                        margin: 0 auto;
                    }
                    .pr-reservation-form .pr-label {
                        display: block;
                        margin-bottom: 0.5rem;
                        color: {$label_color};
                        font-size: {$font_size}px;
                        font-weight: 500;
                    }
                    .pr-reservation-form .pr-input {
                        width: 100%;
                        font-size: {$font_size}px;
                        border-radius: {$border_radius}px;
                        padding: {$input_padding}px;
                        border: 2px solid {$input_border_color};
                    }
                    .pr-reservation-form .pr-input:focus {
                        border-color: {$primary_color};
                        outline: none;
                    }
                    .pr-reservation-form .pr-submit-btn {
                        background: linear-gradient(135deg, {$primary_color}, {$secondary_color});
                        border-radius: {$border_radius}px;
                    }
                ";
                wp_add_inline_style('pr-admin', $custom_css);
            }
            
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', array(), '1.13.2');

            // Form builder GUI
            if (strpos($hook, 'pr-form-builder') !== false) {
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('jquery-ui-droppable');
                wp_enqueue_script('pr-form-builder-gui', PR_PLUGIN_URL . 'assets/form-builder-gui.js', array('jquery', 'jquery-ui-sortable'), PR_VERSION, true);
            }
            
            // Main admin JS
            wp_enqueue_script('pr-admin-js', PR_PLUGIN_URL . 'assets/admin.js', array('jquery', 'jquery-ui-sortable'), PR_VERSION, true);
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue frontend styles and scripts
        wp_enqueue_style('pr-frontend', PR_PLUGIN_URL . 'assets/frontend.css', array(), PR_VERSION);
        wp_enqueue_script('pr-frontend', PR_PLUGIN_URL . 'assets/frontend.js', array('jquery'), PR_VERSION, true);
        
        // Apply custom styling if mode is custom
        if (get_option('pr_styling_mode', 'custom') === 'custom') {
            $primary_color = get_option('pr_primary_color', '#667eea');
            $secondary_color = get_option('pr_secondary_color', '#764ba2');
            $button_color = get_option('pr_button_color', '#667eea');
            $input_border_color = get_option('pr_input_border_color', '#ddd');
            $label_color = get_option('pr_label_color', '#888');
            $font_size = get_option('pr_font_size', 16);
            $border_radius = get_option('pr_border_radius', 8);
            $input_padding = get_option('pr_input_padding', 12);
            $form_width = get_option('pr_form_width', 600);
            
            $custom_css = "
                :root {
                    --pr-primary: {$primary_color};
                    --pr-secondary: {$secondary_color};
                }
                .pr-reservation-form-wrapper {
                    max-width: {$form_width}px;
                    margin: 0 auto;
                }
                .pr-reservation-form .pr-label {
                    display: block;
                    margin-bottom: 0.5rem;
                    color: {$label_color};
                    font-size: {$font_size}px;
                    font-weight: 500;
                }
                .pr-reservation-form .pr-input {
                    width: 100%;
                    font-size: {$font_size}px;
                    border-radius: {$border_radius}px;
                    padding: {$input_padding}px;
                    border: 2px solid {$input_border_color};
                }
                .pr-reservation-form .pr-input:focus {
                    border-color: {$primary_color};
                    outline: none;
                }
                .pr-reservation-form .pr-submit-btn {
                    background: linear-gradient(135deg, {$primary_color}, {$secondary_color});
                    border-radius: {$border_radius}px;
                }
            ";
            wp_add_inline_style('pr-frontend', $custom_css);
        }
        
        // Localize script for AJAX
        wp_localize_script('pr-frontend', 'pr_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pr_ajax_nonce')
        ));
    }
    
    /**
     * Reservation form shortcode
     */
    public function reservation_shortcode($atts) {
        // Get form fields from form builder
        $form_fields = get_option('pr_form_fields', array());
        
        // Start output buffering
        ob_start();
        
        // Get styling mode
        $styling_mode = get_option('pr_styling_mode', 'custom');
        
        echo '<div class="pr-reservation-form-wrapper pr-styling-' . esc_attr($styling_mode) . '">';
        echo '<form id="pr-reservation-form" class="pr-reservation-form" method="post">';
        echo wp_nonce_field('pr_reservation_nonce', 'pr_nonce', true, false);
        
        // Render form fields
        if (!empty($form_fields)) {
            foreach ($form_fields as $field) {
                $field_type = isset($field['type']) ? $field['type'] : 'text';
                $field_label = isset($field['label']) ? $field['label'] : '';
                $field_name = isset($field['name']) ? $field['name'] : '';
                $field_required = isset($field['required']) ? $field['required'] : false;
                
                echo '<div class="pr-form-group">';
                
                // LABEL FIRST for traditional form layout
                echo '<label class="pr-label" for="' . esc_attr($field_name) . '">' . esc_html($field_label);
                if ($field_required) {
                    echo ' <span class="pr-required">*</span>';
                }
                echo '</label>';
                
                // INPUT AFTER label
                if ($field_type === 'textarea') {
                    echo '<textarea id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" class="pr-input" ' . ($field_required ? 'required' : '') . '></textarea>';
                } elseif ($field_type === 'select') {
                    echo '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" class="pr-input" ' . ($field_required ? 'required' : '') . '>';
                    echo '<option value="">Select...</option>';
                    if (isset($field['options']) && is_array($field['options'])) {
                        foreach ($field['options'] as $option) {
                            echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                        }
                    }
                    echo '</select>';
                } else {
                    echo '<input type="' . esc_attr($field_type) . '" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" class="pr-input" ' . ($field_required ? 'required' : '') . ' />';
                }
                
                echo '</div>';
            }
        } else {
            // Default form if no custom fields - traditional labels above inputs
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="pr_name">Name <span class="pr-required">*</span></label>';
            echo '<input type="text" id="pr_name" name="pr_name" class="pr-input" required />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="pr_email">Email <span class="pr-required">*</span></label>';
            echo '<input type="email" id="pr_email" name="pr_email" class="pr-input" required />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="pr_phone">Phone <span class="pr-required">*</span></label>';
            echo '<input type="tel" id="pr_phone" name="pr_phone" class="pr-input" required />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="pr_date">Date <span class="pr-required">*</span></label>';
            echo '<input type="date" id="pr_date" name="pr_date" class="pr-input" required />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="pr_time">Time <span class="pr-required">*</span></label>';
            echo '<select id="pr_time" name="pr_time" class="pr-input" required>';
            echo '<option value="">Select time...</option>';
            $time_slots = get_option('pr_time_slots', array('6:00 PM', '7:00 PM', '8:00 PM'));
            foreach ($time_slots as $slot) {
                echo '<option value="' . esc_attr($slot) . '">' . esc_html($slot) . '</option>';
            }
            echo '</select>';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="pr_party_size">Party Size <span class="pr-required">*</span></label>';
            echo '<input type="number" id="pr_party_size" name="pr_party_size" class="pr-input" min="1" required />';
            echo '</div>';
        }
        
        echo '<div class="pr-form-group">';
        echo '<button type="submit" class="pr-btn pr-btn-primary pr-submit-btn">Make Reservation</button>';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        
        return ob_get_clean();
    }
    
    /**
     * Render a non-functional preview form for the admin Form Builder
     * This prevents HTML5 validation from interfering with the builder's save button
     */
    private function render_preview_form() {
        // Get form fields from form builder
        $form_fields = get_option('pr_form_fields', array());
        
        // Get styling mode
        $styling_mode = get_option('pr_styling_mode', 'custom');
        
        echo '<div class="pr-reservation-form-wrapper pr-styling-' . esc_attr($styling_mode) . '">';
        // Use a div instead of form to prevent validation issues
        echo '<div class="pr-reservation-form pr-preview-only">';
        
        // Render form fields
        if (!empty($form_fields)) {
            foreach ($form_fields as $field) {
                $field_type = isset($field['type']) ? $field['type'] : 'text';
                $field_label = isset($field['label']) ? $field['label'] : '';
                $field_name = isset($field['name']) ? $field['name'] : '';
                $field_required = isset($field['required']) ? $field['required'] : false;
                
                echo '<div class="pr-form-group">';
                
                // LABEL FIRST for traditional form layout
                echo '<label class="pr-label" for="preview_' . esc_attr($field_name) . '">' . esc_html($field_label);
                if ($field_required) {
                    echo ' <span class="pr-required">*</span>';
                }
                echo '</label>';
                
                // INPUT AFTER label (disabled to prevent interaction)
                if ($field_type === 'textarea') {
                    echo '<textarea id="preview_' . esc_attr($field_name) . '" class="pr-input" disabled></textarea>';
                } elseif ($field_type === 'select') {
                    echo '<select id="preview_' . esc_attr($field_name) . '" class="pr-input" disabled>';
                    echo '<option value="">Select...</option>';
                    if (isset($field['options']) && is_array($field['options'])) {
                        foreach ($field['options'] as $option) {
                            echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                        }
                    }
                    echo '</select>';
                } else {
                    echo '<input type="' . esc_attr($field_type) . '" id="preview_' . esc_attr($field_name) . '" class="pr-input" disabled />';
                }
                
                echo '</div>';
            }
        } else {
            // Default form if no custom fields
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="preview_pr_name">Name <span class="pr-required">*</span></label>';
            echo '<input type="text" id="preview_pr_name" class="pr-input" disabled />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="preview_pr_email">Email <span class="pr-required">*</span></label>';
            echo '<input type="email" id="preview_pr_email" class="pr-input" disabled />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="preview_pr_phone">Phone <span class="pr-required">*</span></label>';
            echo '<input type="tel" id="preview_pr_phone" class="pr-input" disabled />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="preview_pr_date">Date <span class="pr-required">*</span></label>';
            echo '<input type="date" id="preview_pr_date" class="pr-input" disabled />';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="preview_pr_time">Time <span class="pr-required">*</span></label>';
            echo '<select id="preview_pr_time" class="pr-input" disabled>';
            echo '<option value="">Select time...</option>';
            $time_slots = get_option('pr_time_slots', array('6:00 PM', '7:00 PM', '8:00 PM'));
            foreach ($time_slots as $slot) {
                echo '<option value="' . esc_attr($slot) . '">' . esc_html($slot) . '</option>';
            }
            echo '</select>';
            echo '</div>';
            
            echo '<div class="pr-form-group">';
            echo '<label class="pr-label" for="preview_pr_party_size">Party Size <span class="pr-required">*</span></label>';
            echo '<input type="number" id="preview_pr_party_size" class="pr-input" min="1" disabled />';
            echo '</div>';
        }
        
        echo '<div class="pr-form-group">';
        echo '<button type="button" class="pr-btn pr-btn-primary pr-submit-btn" disabled>Make Reservation</button>';
        echo '</div>';
        
        echo '</div>'; // .pr-reservation-form
        echo '</div>'; // .pr-reservation-form-wrapper
    }
    
    /**
     * Admin init
     */
    public function admin_init() {
        // Register settings
        register_setting('pr_settings', 'pr_business_name');
        register_setting('pr_settings', 'pr_business_email');
        register_setting('pr_settings', 'pr_max_party_size');
        register_setting('pr_settings', 'pr_booking_window');
        register_setting('pr_settings', 'pr_max_reservations_per_slot');
        register_setting('pr_settings', 'pr_edit_window_hours');
        register_setting('pr_settings', 'pr_require_approval');
        register_setting('pr_settings', 'pr_blackout_dates');
        register_setting('pr_settings', 'pr_opening_hours');
        register_setting('pr_settings', 'pr_time_slot_duration');
    }
    
    /**
     * Helper function to darken a hex color
     * 
     * @param string $hex_color The hex color to darken
     * @param int $percent The percentage to darken (0-100)
     * @return string The darkened hex color
     */
    private function darken_color($hex_color, $percent) {
        // Remove # if present
        $hex_color = ltrim($hex_color, '#');
        
        // Convert hex to RGB
        if (strlen($hex_color) == 3) {
            $hex_color = $hex_color[0] . $hex_color[0] . $hex_color[1] . $hex_color[1] . $hex_color[2] . $hex_color[2];
        }
        
        if (strlen($hex_color) != 6) {
            return '#000000'; // Fallback to black
        }
        
        $rgb = array_map('hexdec', str_split($hex_color, 2));
        
        // Darken each component
        foreach ($rgb as &$component) {
            $component = max(0, min(255, $component - ($component * $percent / 100)));
        }
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
    }
} // End class PowerReservations
} // End if (!class_exists('PowerReservations'))

/**
 * Initialize the plugin
 */
function power_reservations_init() {
    if (class_exists('PowerReservations')) {
        return PowerReservations::get_instance();
    }
    return false;
}

// Only add hooks if we're in WordPress environment
if (function_exists('add_action')) {
    add_action('plugins_loaded', 'power_reservations_init');
}

/**
 * Uninstall hook (only register if function exists)
 */
if (function_exists('register_uninstall_hook')) {
    register_uninstall_hook(__FILE__, 'power_reservations_uninstall');
}

function power_reservations_uninstall() {
    global $wpdb;
    
    // Drop all plugin tables
    $tables = array(
        $wpdb->prefix . 'pr_reservations',
        $wpdb->prefix . 'pr_settings', 
        $wpdb->prefix . 'pr_email_templates'
    );
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    // Delete all plugin options
    $options = array(
        'pr_db_version',
        'pr_business_name',
        'pr_business_email', 
        'pr_max_party_size',
        'pr_booking_window',
        'pr_max_reservations_per_slot',
        'pr_edit_window_hours',
        'pr_require_approval',
        'pr_blackout_dates',
        'pr_opening_hours',
        'pr_time_slot_duration',
        'pr_time_slots'
    );
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Clear scheduled events
    wp_clear_scheduled_hook('pr_daily_cleanup');
    wp_clear_scheduled_hook('pr_send_reminder');
}
