=== Power Reservations ===
Contributors: scottpowers
Tags: reservations, booking, restaurant, appointments, elementor
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful and flexible WordPress reservation system plugin with Elementor integration, perfect for restaurants, venues, and any business requiring appointment scheduling.

== Description ==

Power Reservations is a comprehensive reservation management system designed for restaurants, venues, and any business that needs appointment scheduling. With an intuitive admin interface and optional Elementor integration, managing customer reservations has never been easier.

= Key Features =

* **Easy Reservation Management** - Comprehensive admin interface for managing all reservations
* **Elementor Widget** - Drag-and-drop reservation form widget with live preview
* **Customizable Forms** - Configure which fields to display and make them required/optional
* **Email Notifications** - Automated emails for confirmations, reminders, and status updates
* **Approval Workflow** - Optional reservation approval system
* **Time Slot Management** - Configure available time slots and booking windows
* **Party Size Control** - Set minimum and maximum party sizes
* **Blackout Dates** - Block specific dates when reservations aren't available
* **Customer Management** - Track customer information and reservation history
* **Dashboard Widget** - Quick overview of today's reservations in WordPress dashboard

= Advanced Features =

* Custom styling with multiple form themes
* Real-time availability checking
* Customer self-service reservation editing
* Built-in cancellation system
* CSV export for reporting
* Fully responsive design
* Complete security: nonce verification, SQL injection prevention, XSS protection
* Developer-friendly with hooks and filters

= Perfect For =

* Restaurants and cafés
* Hotels and bed & breakfasts
* Event venues
* Salons and spas
* Medical offices
* Service businesses
* Any business requiring appointment scheduling

= Documentation and Support =

* [Documentation](https://github.com/spowers2/power-reservations)
* [GitHub Repository](https://github.com/spowers2/power-reservations)
* [Report Issues](https://github.com/spowers2/power-reservations/issues)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "Power Reservations"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. Activate the plugin

= After Activation =

1. Navigate to Reservations > Settings
2. Configure your business information
3. Set up time slots and booking windows
4. Customize email templates
5. Add the reservation form to your site using the shortcode `[power_reservations]` or Elementor widget

== Frequently Asked Questions ==

= Is this plugin free? =

Yes, Power Reservations is completely free and open source under GPL-2.0 license.

= Do I need Elementor? =

No, Elementor is optional. You can use the `[power_reservations]` shortcode on any page, post, or widget area.

= Can I accept payments for reservations? =

Currently, the plugin focuses on reservation management. Payment integration is planned for a future release.

= How many reservations can I manage? =

There's no limit. The plugin is designed to handle high volumes efficiently.

= Is it mobile responsive? =

Yes, all forms and admin interfaces are fully responsive and mobile-friendly.

= Can I customize the form appearance? =

Absolutely! Use the built-in styling options in Settings > Styling, add custom CSS in your theme, or use Elementor's styling controls.

= How do I set up email notifications? =

For reliable email delivery, we recommend installing an SMTP plugin like WP Mail SMTP. Power Reservations will automatically use your WordPress email settings.

= Can customers edit or cancel reservations? =

Yes, customers receive edit and cancellation links in their confirmation emails.

= Does it work with multisite? =

Yes, each site in a multisite network maintains its own independent reservations.

= Can I translate the plugin? =

Yes, the plugin is translation-ready. Use a plugin like Loco Translate or provide .po/.mo files.

= Where is reservation data stored? =

All reservation data is stored securely in your WordPress database in custom tables with proper indexing for performance.

== Screenshots ==

1. Reservations dashboard showing all bookings with filter and search options
2. Reservation form on the frontend with date picker and time slots
3. Settings page for configuring business information and time slots
4. Email template customization with available variables
5. Elementor widget integration with style controls
6. Dashboard widget showing today's reservations at a glance

== Changelog ==

= 2.0.0 =
* Fixed: Database insert bug - added missing reservation_id and edit_token fields
* Fixed: Datepicker showing duplicate calendars
* Fixed: Missing translator comments for internationalization
* Fixed: Plugin header compatibility with WordPress.org standards
* Improved: Code quality and WordPress coding standards compliance
* Enhanced: Success message now displays reservation confirmation code
* Added: Comprehensive translator comments for all translatable strings

= 1.0.0 =
* Initial release
* Reservation management system
* Email notifications
* Elementor integration
* Customizable forms and styling
* Time slot management
* Customer dashboard
* CSV export functionality

== Upgrade Notice ==

= 2.0.0 =
This version fixes critical bugs including database insert errors and duplicate datepickers. Upgrade recommended for all users.

= 1.0.0 =
Initial release of Power Reservations.

== Privacy Policy ==

Power Reservations stores reservation information including customer names, email addresses, phone numbers, and reservation details in your WordPress database. This information is used solely for managing reservations and sending confirmation emails. No data is sent to external services except for email delivery through your WordPress mail configuration.

== Third Party Services ==

This plugin does not connect to any third-party services or external APIs. All functionality is self-contained within your WordPress installation.

== Minimum Requirements ==

* WordPress 5.0 or greater
* PHP version 7.4 or greater
* MySQL version 5.6 or greater

== Recommended Requirements ==

* WordPress 6.0 or greater
* PHP version 8.0 or greater
* MySQL version 5.7 or greater
* SMTP plugin for reliable email delivery (WP Mail SMTP recommended)
