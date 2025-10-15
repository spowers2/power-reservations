# Power Reservations

A powerful and flexible WordPress reservation system plugin with Elementor integration, perfect for restaurants, venues, and any business requiring appointment scheduling.

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-brightgreen.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-orange.svg)

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage](#usage)
- [Elementor Integration](#elementor-integration)
- [Email Notifications](#email-notifications)
- [Shortcodes](#shortcodes)
- [FAQ](#faq)
- [Support](#support)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Core Features

- **Easy Reservation Management** - Comprehensive admin interface for managing all reservations
- **Elementor Widget** - Drag-and-drop reservation form widget with live preview
- **Customizable Forms** - Configure which fields to display and make them required/optional
- **Email Notifications** - Automated emails for confirmations, reminders, and status updates
- **Approval Workflow** - Optional reservation approval system
- **Time Slot Management** - Configure available time slots and booking windows
- **Party Size Control** - Set minimum and maximum party sizes
- **Blackout Dates** - Block specific dates when reservations aren't available
- **Customer Management** - Track customer information and reservation history
- **Dashboard Widget** - Quick overview of today's reservations in WordPress dashboard

### Advanced Features

- **Custom Styling** - Multiple form themes with custom color options
- **Real-time Availability** - Check time slot availability before booking
- **Reservation Editing** - Allow customers to edit reservations within a specified window
- **Cancellation System** - Built-in reservation cancellation with email notifications
- **Export Functionality** - Export reservations to CSV for reporting
- **Responsive Design** - Mobile-friendly forms and admin interface
- **Security Features** - Nonce verification, SQL injection prevention, XSS protection
- **Developer Friendly** - Hooks and filters for customization

## 📋 Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **Elementor:** 3.0 or higher (optional, for widget integration)

## 🚀 Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin** button
5. Choose the downloaded ZIP file
6. Click **Install Now**
7. Activate the plugin

### Method 2: FTP Upload

1. Download and extract the plugin ZIP file
2. Upload the `powerReservations` folder to `/wp-content/plugins/`
3. Log in to WordPress admin
4. Navigate to **Plugins**
5. Find "Power Reservations" and click **Activate**

### Method 3: Manual Installation

1. Clone this repository or download the files
2. Place the `powerReservations` folder in your WordPress plugins directory
3. Ensure the main plugin file is named `power-reservations.php`
4. Activate through WordPress admin

## 🎯 Quick Start

### 1. Initial Setup

After activation, the plugin will:

- Create necessary database tables
- Set up default settings
- Generate default email templates

Navigate to **Reservations > Settings** to configure:

- Business name and email
- Time slots and booking windows
- Form fields and requirements
- Email templates

### 2. Add Reservation Form to Your Site

**Using Elementor (Recommended):**

1. Edit any page with Elementor
2. Search for "Power Reservations" widget
3. Drag widget to your page
4. Customize settings in the left panel
5. Publish your page

**Using Shortcode:**
Add this shortcode to any page, post, or widget:

```
[power_reservations]
```

### 3. Managing Reservations

Access your reservations dashboard:

- Navigate to **Reservations** in WordPress admin
- View all reservations in a sortable table
- Approve, decline, edit, or delete reservations
- Export to CSV for reporting

## ⚙️ Configuration

### Business Settings

**Location:** Reservations > Settings > General

- **Business Name:** Displayed in emails and forms
- **Business Email:** Receives notification emails
- **Contact Phone:** Displayed on forms (optional)
- **Address:** Displayed on forms (optional)

### Time Slot Configuration

**Location:** Reservations > Settings > Time Slots

Configure when customers can make reservations:

```
Example Time Slot Configuration:
- Start Time: 17:00 (5:00 PM)
- End Time: 22:00 (10:00 PM)
- Slot Duration: 30 minutes
- Max Reservations per Slot: 10
```

**Operating Hours:**

- Set different hours for each day of the week
- Close specific days by unchecking them
- Set seasonal hours or special event times

### Form Field Configuration

**Location:** Reservations > Settings > Form Builder

Customize which fields appear on your reservation form:

- **Name** - Customer's full name
- **Email** - Customer's email address
- **Phone** - Contact phone number
- **Date** - Reservation date picker
- **Time** - Available time slot selector
- **Party Size** - Number of guests
- **Special Requests** - Optional notes/requests

Each field can be:

- Enabled/Disabled
- Required/Optional
- Reordered via drag-and-drop
- Customized with labels and placeholders

### Email Templates

**Location:** Reservations > Settings > Email Templates

Customize automated emails sent to customers:

**Available Templates:**

1. **Confirmation Email** - Sent when reservation is created
2. **Approval Email** - Sent when admin approves a reservation
3. **Declined Email** - Sent when reservation is declined
4. **Reminder Email** - Sent before reservation time
5. **Cancellation Email** - Sent when reservation is cancelled

**Available Variables:**

- `{customer_name}` - Customer's name
- `{reservation_date}` - Reservation date
- `{reservation_time}` - Reservation time
- `{party_size}` - Number of guests
- `{business_name}` - Your business name
- `{confirmation_link}` - Link to view reservation
- `{cancellation_link}` - Link to cancel reservation
- `{edit_link}` - Link to edit reservation

### Styling Options

**Location:** Reservations > Settings > Styling

Choose from preset themes or create custom styling:

**Preset Themes:**

- **Default** - Clean, modern design
- **Minimal** - Simple, lightweight style
- **Bold** - High contrast, attention-grabbing
- **Custom** - Full color customization

**Custom Colors:**

- Primary Color
- Button Color
- Text Color
- Background Color
- Border Color

## 📖 Usage

### Making a Reservation (Customer View)

1. Navigate to the reservation form on your website
2. Fill in required information:
   - Name and contact details
   - Preferred date and time
   - Party size
   - Any special requests
3. Click "Make Reservation"
4. Receive confirmation email
5. Optionally edit or cancel using links in email

### Managing Reservations (Admin View)

**View All Reservations:**

- Navigate to **Reservations** in admin menu
- Filter by status, date, or customer
- Sort by any column
- Bulk actions for multiple reservations

**Approve/Decline Reservations:**

1. Click on reservation in list
2. Click **Approve** or **Decline** button
3. Customer receives notification email
4. Status updates in dashboard

**Edit Reservations:**

1. Click **Edit** next to reservation
2. Modify any field as needed
3. Save changes
4. Customer receives update notification

**Delete Reservations:**

1. Click **Delete** next to reservation
2. Confirm deletion
3. Reservation is permanently removed

**Export Reservations:**

1. Filter reservations as needed
2. Click **Export to CSV** button
3. Download file with all reservation data

### Dashboard Widget

Quick access to today's reservations:

- Shows count of total, pending, and approved reservations
- Links to full reservation management
- Updates in real-time

## 🧩 Elementor Integration

### Adding the Widget

1. Edit page with Elementor
2. Search for "Power Reservations" or "Reservation"
3. Drag widget to desired location
4. Widget appears with live preview

### Widget Settings

**Content Tab:**

- **Form Title** - Custom heading for form
- **Submit Button Text** - Customize button label
- **Show Description** - Add explanatory text

**Style Tab:**

- **Form Style** - Choose from available themes
- **Colors** - Customize all color elements
- **Typography** - Set fonts and sizes
- **Spacing** - Adjust padding and margins
- **Borders** - Customize border styles

**Advanced Tab:**

- **CSS Classes** - Add custom classes
- **Custom CSS** - Add specific styling
- **Visibility** - Control display conditions

### Widget Customization Example

```php
// Add custom validation
add_filter('pr_validate_reservation', function($is_valid, $data) {
    // Add custom validation logic
    if ($data['party_size'] > 20) {
        return new WP_Error('large_party', 'Please call for parties over 20');
    }
    return $is_valid;
}, 10, 2);

// Modify form fields
add_filter('pr_form_fields', function($fields) {
    // Add custom field
    $fields['dietary'] = array(
        'label' => 'Dietary Restrictions',
        'type' => 'textarea',
        'required' => false
    );
    return $fields;
});
```

## 📧 Email Notifications

### Setting Up Email Notifications

**SMTP Configuration (Recommended):**

For reliable email delivery, use an SMTP plugin:

1. Install "WP Mail SMTP" or similar plugin
2. Configure with your email service
3. Test email delivery
4. Power Reservations will use these settings automatically

**Email Timing:**

- **Confirmation:** Sent immediately upon reservation
- **Approval:** Sent when admin approves reservation
- **Reminder:** Sent 24 hours before reservation (configurable)
- **Cancellation:** Sent immediately when cancelled

### Email Customization

**HTML Email Templates:**

All emails support HTML and can be fully customized:

```html
<h2>Reservation Confirmed</h2>
<p>Hi {customer_name},</p>
<p>Your reservation for {party_size} guests on {reservation_date} at {reservation_time} has been confirmed!</p>
<p><a href="{confirmation_link}">View Reservation</a></p>
<p>Need to make changes? <a href="{edit_link}">Edit</a> or <a href="{cancellation_link}">Cancel</a></p>
```

## 📝 Shortcodes

### Primary Shortcode

Display the reservation form:

```
[power_reservations]
```

**With Attributes:**

```
[power_reservations title="Book Your Table" style="minimal"]
```

**Available Attributes:**

- `title` - Custom form title
- `style` - Form theme (default, minimal, bold, custom)
- `show_title` - Show/hide title (true/false)
- `button_text` - Custom submit button text

### Additional Shortcodes

**Display Reservation Count:**

```
[pr_reservation_count]
```

**Customer's Reservations:**

```
[pr_my_reservations]
```

## ❓ FAQ

### General Questions

**Q: Is this plugin free?**
A: Yes, Power Reservations is completely free and open source under GPL-2.0 license.

**Q: Do I need Elementor?**
A: No, Elementor is optional. You can use the shortcode on any page.

**Q: Can I accept payments?**
A: Currently, the plugin focuses on reservations. Payment integration is planned for a future release.

**Q: How many reservations can I manage?**
A: There's no limit. The plugin is designed to handle high volumes efficiently.

**Q: Is it mobile responsive?**
A: Yes, all forms and admin interfaces are fully responsive.

### Setup Questions

**Q: Emails aren't sending. What should I do?**
A:

1. Check your WordPress email settings
2. Install an SMTP plugin like WP Mail SMTP
3. Test email delivery from Settings > Email Templates
4. Check spam folders
5. Verify your email server configuration

**Q: How do I change the available time slots?**
A:

1. Go to Reservations > Settings > Time Slots
2. Modify start time, end time, and duration
3. Set max reservations per slot
4. Save changes

**Q: Can customers see their reservation history?**
A: Yes, use the `[pr_my_reservations]` shortcode to display a customer's reservation history.

### Customization Questions

**Q: Can I translate the plugin?**
A: Yes, the plugin is translation-ready. Use a plugin like Loco Translate or provide .po/.mo files.

**Q: How do I customize the form appearance?**
A:

1. Use built-in styling options in Settings > Styling
2. Use custom CSS in your theme
3. Override plugin templates in your theme
4. Use Elementor's styling controls (if using widget)

**Q: Can I add custom fields?**
A: Yes, use the `pr_form_fields` filter to add custom fields programmatically.

### Technical Questions

**Q: Where is data stored?**
A: All reservation data is stored in your WordPress database in custom tables with the prefix `pr_`.

**Q: Is the plugin secure?**
A: Yes, the plugin includes:

- Nonce verification for all forms
- SQL injection prevention via prepared statements
- XSS protection via output escaping
- Input sanitization and validation
- WordPress security best practices

**Q: Does it support multisite?**
A: Yes, each site in a multisite network maintains its own reservations.

**Q: Can I export reservation data?**
A: Yes, use the Export to CSV feature in the reservations list.

## 🆘 Support

### Getting Help

**Documentation:**

- Full documentation available in `/docs` folder
- Video tutorials: [Link to tutorials]
- Knowledge base: [Link to KB]

**Community Support:**

- WordPress.org forums
- GitHub Issues
- Stack Overflow tag: `power-reservations`

**Premium Support:**

- Priority email support
- Custom development
- Installation assistance
- Contact: support@example.com

### Reporting Bugs

Found a bug? Please report it:

1. Check existing [GitHub Issues](https://github.com/yourusername/power-reservations/issues)
2. Create new issue with:
   - WordPress version
   - PHP version
   - Plugin version
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots if applicable

### Feature Requests

Have an idea? We'd love to hear it:

1. Open a [GitHub Issue](https://github.com/yourusername/power-reservations/issues)
2. Label it as "enhancement"
3. Describe the feature and use case
4. Community can vote and discuss

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Code Contributions

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow WordPress Coding Standards
- Use proper documentation blocks
- Write meaningful commit messages
- Include unit tests for new features
- Update documentation as needed

### Translation

Help translate Power Reservations:

1. Visit our [translation project](https://translate.wordpress.org)
2. Select your language
3. Translate strings
4. Submit for review

### Documentation

Improve our docs:

1. Fork the repository
2. Edit documentation files
3. Submit a pull request
4. Clear explanations appreciated

## 📄 License

Power Reservations is licensed under the [GPL-2.0 License](LICENSE).

```
Copyright (C) 2025 Power Reservations

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🙏 Acknowledgments

- WordPress community for inspiration and support
- Elementor for providing excellent widget APIs
- All contributors who help improve this plugin
- Users who provide valuable feedback

## 📞 Contact

- **Website:** https://yourwebsite.com
- **Email:** support@yourwebsite.com
- **GitHub:** https://github.com/yourusername/power-reservations
- **Twitter:** @yourhandle

---

Made with ❤️ for the WordPress community

**[⬆ Back to Top](#power-reservations)**
