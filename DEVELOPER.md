# Power Reservations - Developer Documentation

## Table of Contents

1. [Architecture](#architecture)
2. [Database Schema](#database-schema)
3. [Hooks & Filters](#hooks--filters)
4. [Classes & Methods](#classes--methods)
5. [Custom Development](#custom-development)
6. [Code Examples](#code-examples)
7. [Testing](#testing)
8. [Contributing](#contributing)

---

## Architecture

### Plugin Structure

```
powerReservations/
├── power-reservations.php          # Main plugin file
├── includes/
│   ├── elementor-widget.php        # Elementor integration
│   ├── class-reservations-table.php # List table class
│   └── functions.php               # Helper functions
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin styling
│   │   └── frontend.css           # Frontend styling
│   └── js/
│       ├── admin.js               # Admin JavaScript
│       └── frontend.js            # Frontend JavaScript
├── templates/
│   ├── reservation-form.php       # Main form template
│   ├── email/                     # Email templates
│   └── admin/                     # Admin templates
├── languages/                      # Translation files
├── docs/                          # Documentation
├── README.md                      # GitHub README
├── HELP.md                        # User help guide
└── LICENSE                        # GPL-2.0 License
```

### Core Components

#### 1. Main Plugin Class

**File:** `power-reservations.php`
**Class:** `PowerReservations`

Singleton pattern implementation that:

- Initializes plugin
- Registers hooks
- Manages settings
- Handles database operations

#### 2. Reservations List Table

**File:** `power-reservations.php`
**Class:** `PR_Reservations_List_Table`

Extends `WP_List_Table` to:

- Display reservations in admin
- Handle bulk actions
- Provide sorting and filtering
- Manage pagination

#### 3. Elementor Widget

**File:** `includes/elementor-widget.php`
**Class:** `Power_Reservations_Widget`

Extends `\Elementor\Widget_Base` to:

- Register widget with Elementor
- Provide visual controls
- Render reservation form
- Handle widget settings

---

## Database Schema

### Tables Created

The plugin creates three custom tables:

#### 1. Reservations Table (`wp_pr_reservations`)

```sql
CREATE TABLE IF NOT EXISTS `wp_pr_reservations` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `customer_name` varchar(255) NOT NULL,
    `customer_email` varchar(255) NOT NULL,
    `customer_phone` varchar(50) DEFAULT NULL,
    `reservation_date` date NOT NULL,
    `reservation_time` time NOT NULL,
    `party_size` int(11) NOT NULL,
    `special_requests` text,
    `status` varchar(20) NOT NULL DEFAULT 'pending',
    `confirmation_code` varchar(50) NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `reservation_date` (`reservation_date`),
    KEY `customer_email` (`customer_email`),
    KEY `confirmation_code` (`confirmation_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Fields:**

- `id`: Unique reservation identifier
- `customer_name`: Customer's full name
- `customer_email`: Customer's email address
- `customer_phone`: Optional phone number
- `reservation_date`: Date of reservation (YYYY-MM-DD)
- `reservation_time`: Time of reservation (HH:MM:SS)
- `party_size`: Number of guests
- `special_requests`: Customer notes/requests
- `status`: Reservation status (pending, approved, declined, cancelled)
- `confirmation_code`: Unique confirmation identifier
- `created_at`: Timestamp when created
- `updated_at`: Timestamp when last modified

#### 2. Settings Table (`wp_pr_settings`)

```sql
CREATE TABLE IF NOT EXISTS `wp_pr_settings` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(255) NOT NULL,
    `setting_value` longtext,
    `autoload` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Fields:**

- `id`: Unique setting identifier
- `setting_key`: Setting name (unique)
- `setting_value`: Serialized setting data
- `autoload`: Whether to load on plugin init

#### 3. Email Templates Table (`wp_pr_email_templates`)

```sql
CREATE TABLE IF NOT EXISTS `wp_pr_email_templates` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `template_name` varchar(255) NOT NULL,
    `subject` varchar(500) NOT NULL,
    `body` longtext NOT NULL,
    `enabled` tinyint(1) DEFAULT 1,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `template_name` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Fields:**

- `id`: Unique template identifier
- `template_name`: Template identifier (confirmation, reminder, etc.)
- `subject`: Email subject line (supports variables)
- `body`: Email body HTML (supports variables)
- `enabled`: Whether template is active
- `created_at`: Timestamp when created
- `updated_at`: Timestamp when last modified

### Database Queries

#### Retrieving Reservations

```php
global $wpdb;
$table_name = $wpdb->prefix . 'pr_reservations';

// Get all pending reservations
$pending = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE status = %s ORDER BY reservation_date ASC",
    'pending'
));

// Get reservations for specific date
$date_reservations = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE reservation_date = %s",
    '2025-10-15'
));

// Count reservations for time slot
$count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_name
    WHERE reservation_date = %s
    AND reservation_time = %s
    AND status IN ('pending', 'approved')",
    '2025-10-15',
    '18:00:00'
));
```

#### Inserting Reservations

```php
global $wpdb;
$table_name = $wpdb->prefix . 'pr_reservations';

$data = array(
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '555-1234',
    'reservation_date' => '2025-10-15',
    'reservation_time' => '18:00:00',
    'party_size' => 4,
    'special_requests' => 'Window seat please',
    'status' => 'pending',
    'confirmation_code' => wp_generate_password(12, false),
    'created_at' => current_time('mysql')
);

$wpdb->insert($table_name, $data, array(
    '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
));

$reservation_id = $wpdb->insert_id;
```

---

## Hooks & Filters

### Action Hooks

#### Plugin Initialization

```php
// Fires when plugin is activated
do_action('pr_plugin_activated');

// Fires when plugin is deactivated
do_action('pr_plugin_deactivated');

// Fires when plugin is initialized
do_action('pr_plugin_init');
```

#### Reservation Actions

```php
// Before reservation is created
do_action('pr_before_create_reservation', $data);

// After reservation is created
do_action('pr_after_create_reservation', $reservation_id, $data);

// Before reservation is updated
do_action('pr_before_update_reservation', $reservation_id, $data);

// After reservation is updated
do_action('pr_after_update_reservation', $reservation_id, $data);

// Before reservation is deleted
do_action('pr_before_delete_reservation', $reservation_id);

// After reservation is deleted
do_action('pr_after_delete_reservation', $reservation_id);

// When reservation status changes
do_action('pr_reservation_status_changed', $reservation_id, $old_status, $new_status);
```

#### Email Actions

```php
// Before email is sent
do_action('pr_before_send_email', $to, $subject, $message);

// After email is sent
do_action('pr_after_send_email', $to, $subject, $sent);

// When confirmation email is sent
do_action('pr_confirmation_email_sent', $reservation_id);

// When reminder email is sent
do_action('pr_reminder_email_sent', $reservation_id);
```

### Filter Hooks

#### Reservation Filters

```php
// Filter reservation data before saving
$data = apply_filters('pr_reservation_data', $data);

// Filter reservation validation
$is_valid = apply_filters('pr_validate_reservation', true, $data);

// Filter available time slots
$time_slots = apply_filters('pr_available_time_slots', $time_slots, $date);

// Filter party size options
$party_sizes = apply_filters('pr_party_size_options', $party_sizes);

// Filter max reservations per slot
$max = apply_filters('pr_max_reservations_per_slot', $max, $date, $time);
```

#### Form Filters

```php
// Filter form fields
$fields = apply_filters('pr_form_fields', $fields);

// Filter form attributes
$attributes = apply_filters('pr_form_attributes', $attributes);

// Filter submit button text
$button_text = apply_filters('pr_submit_button_text', $button_text);

// Filter form HTML output
$form_html = apply_filters('pr_form_html', $form_html);
```

#### Email Filters

```php
// Filter email subject
$subject = apply_filters('pr_email_subject', $subject, $template_name);

// Filter email body
$body = apply_filters('pr_email_body', $body, $template_name);

// Filter email headers
$headers = apply_filters('pr_email_headers', $headers);

// Filter email variables
$variables = apply_filters('pr_email_variables', $variables, $reservation_id);
```

#### Settings Filters

```php
// Filter default settings
$defaults = apply_filters('pr_default_settings', $defaults);

// Filter setting value
$value = apply_filters('pr_setting_value', $value, $setting_key);

// Filter business hours
$hours = apply_filters('pr_business_hours', $hours);

// Filter blackout dates
$blackout_dates = apply_filters('pr_blackout_dates', $blackout_dates);
```

---

## Classes & Methods

### PowerReservations Class

#### Core Methods

```php
// Get singleton instance
PowerReservations::get_instance()

// Initialize plugin
public function init()

// Create database tables
private function create_tables()

// Register settings
private function register_settings()

// Enqueue scripts and styles
public function enqueue_scripts()
public function enqueue_admin_scripts()
```

#### Reservation Methods

```php
// Create new reservation
public function create_reservation($data)

// Update reservation
public function update_reservation($reservation_id, $data)

// Delete reservation
public function delete_reservation($reservation_id)

// Get reservation by ID
public function get_reservation($reservation_id)

// Get reservations with filters
public function get_reservations($args = array())

// Check availability
public function check_availability($date, $time, $party_size)
```

#### Email Methods

```php
// Send email
public function send_email($to, $subject, $message, $headers = '')

// Send confirmation email
public function send_confirmation_email($reservation_id)

// Send reminder email
public function send_reminder_email($reservation_id)

// Get email template
public function get_email_template($template_name)

// Parse email variables
private function parse_email_variables($content, $reservation_id)
```

### PR_Reservations_List_Table Class

#### Required Methods

```php
// Prepare items for display
public function prepare_items()

// Get columns
public function get_columns()

// Get sortable columns
public function get_sortable_columns()

// Get bulk actions
public function get_bulk_actions()

// Process bulk actions
public function process_bulk_action()

// Render column content
public function column_default($item, $column_name)
```

### Power_Reservations_Widget Class

#### Widget Methods

```php
// Get widget name
public function get_name()

// Get widget title
public function get_title()

// Get widget icon
public function get_icon()

// Get widget categories
public function get_categories()

// Register widget controls
protected function register_controls()

// Render widget
protected function render()
```

---

## Custom Development

### Adding Custom Fields

#### Step 1: Register Field

```php
add_filter('pr_form_fields', function($fields) {
    $fields['dietary_restrictions'] = array(
        'type' => 'textarea',
        'label' => 'Dietary Restrictions',
        'placeholder' => 'Any allergies or dietary requirements?',
        'required' => false,
        'enabled' => true
    );
    return $fields;
});
```

#### Step 2: Handle Field Submission

```php
add_action('pr_after_create_reservation', function($reservation_id, $data) {
    if (isset($data['dietary_restrictions'])) {
        update_post_meta($reservation_id, 'dietary_restrictions',
            sanitize_textarea_field($data['dietary_restrictions']));
    }
}, 10, 2);
```

#### Step 3: Display in Admin

```php
add_filter('pr_reservation_meta', function($meta, $reservation_id) {
    $dietary = get_post_meta($reservation_id, 'dietary_restrictions', true);
    if ($dietary) {
        $meta['Dietary Restrictions'] = $dietary;
    }
    return $meta;
}, 10, 2);
```

### Custom Validation

```php
add_filter('pr_validate_reservation', function($is_valid, $data) {
    // Require phone for large parties
    if ($data['party_size'] > 10 && empty($data['customer_phone'])) {
        return new WP_Error('phone_required',
            'Phone number required for parties over 10');
    }

    // Block specific email domains
    $blocked_domains = array('spam.com', 'fake.com');
    $email_domain = substr(strrchr($data['customer_email'], "@"), 1);
    if (in_array($email_domain, $blocked_domains)) {
        return new WP_Error('blocked_domain',
            'Email domain not accepted');
    }

    return $is_valid;
}, 10, 2);
```

### Custom Time Slots

```php
add_filter('pr_available_time_slots', function($time_slots, $date) {
    // Different times for weekends
    $day_of_week = date('N', strtotime($date));

    if ($day_of_week >= 6) { // Saturday or Sunday
        $time_slots = array(
            '11:00' => '11:00 AM (Brunch)',
            '12:00' => '12:00 PM (Brunch)',
            '13:00' => '1:00 PM (Lunch)',
            '14:00' => '2:00 PM (Lunch)',
            '17:00' => '5:00 PM (Dinner)',
            '18:00' => '6:00 PM (Dinner)',
            '19:00' => '7:00 PM (Dinner)',
            '20:00' => '8:00 PM (Dinner)'
        );
    }

    return $time_slots;
}, 10, 2);
```

### Custom Notification Recipients

```php
add_filter('pr_notification_recipients', function($recipients, $reservation_id) {
    $reservation = pr_get_reservation($reservation_id);

    // Alert VIP coordinator for large parties
    if ($reservation['party_size'] >= 15) {
        $recipients[] = 'vip@restaurant.com';
    }

    // Alert specific server based on time
    $hour = date('H', strtotime($reservation['reservation_time']));
    if ($hour >= 18) {
        $recipients[] = 'evening-manager@restaurant.com';
    }

    return $recipients;
}, 10, 2);
```

### Dynamic Pricing

```php
add_filter('pr_reservation_data', function($data) {
    // Add deposit for prime time slots
    $prime_times = array('18:00', '18:30', '19:00', '19:30');
    $day_of_week = date('N', strtotime($data['reservation_date']));

    if (in_array($data['reservation_time'], $prime_times) && $day_of_week >= 5) {
        $data['requires_deposit'] = true;
        $data['deposit_amount'] = 25.00;
    }

    return $data;
});
```

### Integration with WooCommerce

```php
add_action('pr_after_create_reservation', function($reservation_id, $data) {
    // Create WooCommerce order for deposit
    if (!empty($data['requires_deposit'])) {
        $order = wc_create_order();
        $product_id = 123; // Deposit product ID
        $order->add_product(wc_get_product($product_id), 1);
        $order->set_customer_id($data['user_id']);
        $order->calculate_totals();

        // Link order to reservation
        update_post_meta($reservation_id, 'deposit_order_id', $order->get_id());
        update_post_meta($order->get_id(), 'reservation_id', $reservation_id);
    }
}, 10, 2);
```

---

## Code Examples

### Complete Custom Field Example

```php
/**
 * Add "Occasion" field for special events
 */

// 1. Add field to form
add_filter('pr_form_fields', function($fields) {
    $fields['occasion'] = array(
        'type' => 'select',
        'label' => 'Special Occasion',
        'placeholder' => 'Select an occasion',
        'options' => array(
            '' => 'Select...',
            'birthday' => 'Birthday',
            'anniversary' => 'Anniversary',
            'proposal' => 'Proposal',
            'business' => 'Business Dinner',
            'other' => 'Other'
        ),
        'required' => false,
        'enabled' => true,
        'order' => 15
    );
    return $fields;
});

// 2. Validate field
add_filter('pr_validate_reservation', function($is_valid, $data) {
    if (!empty($data['occasion'])) {
        $allowed = array('birthday', 'anniversary', 'proposal', 'business', 'other');
        if (!in_array($data['occasion'], $allowed)) {
            return new WP_Error('invalid_occasion', 'Invalid occasion selected');
        }
    }
    return $is_valid;
}, 10, 2);

// 3. Save field data
add_action('pr_after_create_reservation', function($reservation_id, $data) {
    if (!empty($data['occasion'])) {
        update_post_meta($reservation_id, 'occasion', sanitize_text_field($data['occasion']));
    }
}, 10, 2);

// 4. Display in admin
add_action('pr_reservation_details_after', function($reservation) {
    $occasion = get_post_meta($reservation->id, 'occasion', true);
    if ($occasion) {
        echo '<div class="pr-meta-field">';
        echo '<strong>Special Occasion:</strong> ';
        echo esc_html(ucfirst($occasion));
        echo '</div>';
    }
});

// 5. Include in emails
add_filter('pr_email_variables', function($variables, $reservation_id) {
    $occasion = get_post_meta($reservation_id, 'occasion', true);
    if ($occasion) {
        $variables['occasion'] = ucfirst($occasion);
    }
    return $variables;
}, 10, 2);
```

### REST API Endpoint Example

```php
/**
 * Add custom REST API endpoint for checking availability
 */

add_action('rest_api_init', function() {
    register_rest_route('power-reservations/v1', '/availability', array(
        'methods' => 'GET',
        'callback' => 'pr_rest_check_availability',
        'permission_callback' => '__return_true',
        'args' => array(
            'date' => array(
                'required' => true,
                'validate_callback' => function($param) {
                    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
                }
            ),
            'party_size' => array(
                'required' => false,
                'default' => 2,
                'validate_callback' => function($param) {
                    return is_numeric($param) && $param > 0;
                }
            )
        )
    ));
});

function pr_rest_check_availability($request) {
    $date = $request->get_param('date');
    $party_size = $request->get_param('party_size');

    $plugin = PowerReservations::get_instance();
    $time_slots = $plugin->get_available_time_slots($date, $party_size);

    return new WP_REST_Response(array(
        'date' => $date,
        'party_size' => $party_size,
        'available_slots' => $time_slots,
        'total_available' => count($time_slots)
    ), 200);
}

// Usage: GET /wp-json/power-reservations/v1/availability?date=2025-10-15&party_size=4
```

### Admin Dashboard Widget

```php
/**
 * Add custom dashboard widget showing stats
 */

add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'pr_stats_widget',
        'Reservation Statistics',
        'pr_render_stats_widget'
    );
});

function pr_render_stats_widget() {
    global $wpdb;
    $table = $wpdb->prefix . 'pr_reservations';

    // Today's stats
    $today = current_time('Y-m-d');
    $today_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE reservation_date = %s AND status = 'approved'",
        $today
    ));

    // This week's stats
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE reservation_date >= %s AND status = 'approved'",
        $week_start
    ));

    // Pending approvals
    $pending = $wpdb->get_var(
        "SELECT COUNT(*) FROM $table WHERE status = 'pending'"
    );

    // Display stats
    echo '<div class="pr-dashboard-stats">';
    echo '<div class="stat-box">';
    echo '<h3>' . esc_html($today_count) . '</h3>';
    echo '<p>Today\'s Reservations</p>';
    echo '</div>';
    echo '<div class="stat-box">';
    echo '<h3>' . esc_html($week_count) . '</h3>';
    echo '<p>This Week</p>';
    echo '</div>';
    echo '<div class="stat-box alert">';
    echo '<h3>' . esc_html($pending) . '</h3>';
    echo '<p>Pending Approval</p>';
    echo '</div>';
    echo '</div>';

    if ($pending > 0) {
        echo '<p><a href="' . admin_url('admin.php?page=reservations&status=pending') . '" class="button button-primary">Review Pending</a></p>';
    }
}
```

---

## Testing

### Unit Testing Setup

```php
/**
 * PHPUnit test example
 */

class Test_Power_Reservations extends WP_UnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->plugin = PowerReservations::get_instance();
    }

    public function test_create_reservation() {
        $data = array(
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'reservation_date' => date('Y-m-d', strtotime('+7 days')),
            'reservation_time' => '18:00:00',
            'party_size' => 4
        );

        $reservation_id = $this->plugin->create_reservation($data);

        $this->assertIsInt($reservation_id);
        $this->assertGreaterThan(0, $reservation_id);
    }

    public function test_check_availability() {
        $date = date('Y-m-d', strtotime('+7 days'));
        $time = '18:00';
        $party_size = 4;

        $available = $this->plugin->check_availability($date, $time, $party_size);

        $this->assertTrue($available);
    }

    public function test_send_confirmation_email() {
        // Create test reservation
        $reservation_id = $this->factory->post->create(array(
            'post_type' => 'pr_reservation'
        ));

        $result = $this->plugin->send_confirmation_email($reservation_id);

        $this->assertTrue($result);
    }
}
```

### Manual Testing Checklist

- [ ] Installation and activation
- [ ] Database tables created
- [ ] Settings page accessible
- [ ] Time slots configuration
- [ ] Form displays correctly
- [ ] Reservation submission works
- [ ] Emails are sent
- [ ] Admin can approve/decline
- [ ] Bulk actions work
- [ ] Export to CSV works
- [ ] Elementor widget displays
- [ ] Shortcode works
- [ ] Mobile responsive
- [ ] Browser compatibility
- [ ] Deactivation cleanup

---

## Contributing

### Development Workflow

1. Fork the repository
2. Create feature branch
3. Make changes with proper documentation
4. Test thoroughly
5. Submit pull request

### Code Standards

Follow WordPress Coding Standards:

- Use tabs for indentation
- Use single quotes for strings
- Add proper documentation blocks
- Escape all output
- Sanitize all input
- Use prepared statements for queries

### Pull Request Guidelines

- Clear description of changes
- Link to related issues
- Include test results
- Update documentation
- Follow semantic versioning

---

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Elementor Developer Docs](https://developers.elementor.com/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

---

_Last Updated: October 2025_
_Plugin Version: 1.0.0_
