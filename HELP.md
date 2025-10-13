# Power Reservations - User Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Customer Guide](#customer-guide)
3. [Admin Guide](#admin-guide)
4. [Configuration Guide](#configuration-guide)
5. [Troubleshooting](#troubleshooting)

---

## Getting Started

### What is Power Reservations?

Power Reservations is a WordPress plugin that allows businesses to accept and manage reservations directly through their website. It's perfect for:

- Restaurants and cafes
- Hotels and bed & breakfasts
- Event venues
- Consulting and appointment-based businesses
- Any business that requires scheduled bookings

### Key Benefits

✅ **Easy to Use** - Simple interface for both customers and administrators  
✅ **Fully Customizable** - Match your brand and website design  
✅ **Mobile Friendly** - Works perfectly on all devices  
✅ **Automated Emails** - Automatic confirmations and reminders  
✅ **Time Management** - Control when reservations can be made  
✅ **No Coding Required** - Set up in minutes without technical knowledge

---

## Customer Guide

### How to Make a Reservation

#### Step 1: Access the Reservation Form

Navigate to the reservations page on the website (usually found in the main menu or contact page).

#### Step 2: Fill Out the Form

Complete all required fields marked with an asterisk (\*):

- **Name**: Your full name
- **Email**: Your email address for confirmation
- **Phone**: Contact number (optional but recommended)
- **Date**: Select your preferred reservation date
- **Time**: Choose from available time slots
- **Party Size**: Number of guests in your party
- **Special Requests**: Any dietary restrictions, seating preferences, or special occasions

#### Step 3: Submit Your Reservation

Click the "Make Reservation" or "Submit" button.

#### Step 4: Confirmation

You'll immediately see an on-screen confirmation and receive an email with:

- Reservation details
- Confirmation number
- Links to edit or cancel your reservation
- Contact information for the business

### Managing Your Reservation

#### Viewing Your Reservation

Check the confirmation email for:

- All reservation details
- A "View Reservation" link
- Your unique confirmation number

#### Editing Your Reservation

**Within the Edit Window** (typically 24-48 hours before):

1. Click the "Edit Reservation" link in your email
2. Modify the details you need to change
3. Click "Update Reservation"
4. Receive a new confirmation email

**Outside the Edit Window:**

Contact the business directly using the phone or email provided.

#### Canceling Your Reservation

1. Click the "Cancel Reservation" link in your email
2. Confirm the cancellation
3. Receive a cancellation confirmation email

**Note:** Please cancel as early as possible to allow others to book the time slot.

### Reservation Status

Your reservation can have different statuses:

- **Pending** - Awaiting business approval
- **Approved** - Confirmed by the business
- **Declined** - Not accepted (you'll be notified why)
- **Cancelled** - Cancelled by you or the business

### Tips for Successful Reservations

✓ **Book Early** - Popular times fill up quickly  
✓ **Provide Accurate Information** - Ensure your contact details are correct  
✓ **Check Spam Folder** - Confirmation emails might land there  
✓ **Note Special Requests** - Use the special requests field for important information  
✓ **Arrive on Time** - Respect your reservation time  
✓ **Cancel if Needed** - Don't leave the business waiting

---

## Admin Guide

### Accessing the Admin Dashboard

1. Log in to WordPress admin
2. Look for "Reservations" in the left sidebar menu
3. Click to view all reservations

### Dashboard Overview

**The Reservations Dashboard Shows:**

- List of all reservations (newest first)
- Filter options (status, date range)
- Search functionality
- Quick action buttons
- Export to CSV option

**Dashboard Widget:**

A widget on your WordPress dashboard shows today's reservations at a glance.

### Managing Reservations

#### Viewing Reservation Details

Click on any reservation to see:

- Customer information
- Reservation date and time
- Party size
- Special requests
- Status history
- Reservation timestamp

#### Approving Reservations

**If Approval is Required:**

1. Review the reservation details
2. Click the "Approve" button
3. Customer receives approval email automatically
4. Reservation status changes to "Approved"

**For Automatic Approval:**

Disable "Require Approval" in settings, and all valid reservations are automatically approved.

#### Declining Reservations

1. Click on the reservation
2. Click "Decline" button
3. Optionally add a reason
4. Customer receives notification
5. Time slot becomes available again

#### Editing Reservations

1. Click "Edit" next to the reservation
2. Modify any field:
   - Customer information
   - Date and time
   - Party size
   - Special notes
3. Click "Update Reservation"
4. Customer receives update notification

#### Deleting Reservations

**To Delete:**

1. Click "Delete" next to the reservation
2. Confirm the deletion
3. Reservation is permanently removed

**Note:** Deleted reservations cannot be recovered. Consider declining instead if you want to keep records.

#### Bulk Actions

**Select Multiple Reservations:**

1. Check boxes next to reservations
2. Choose action from dropdown:
   - Approve
   - Decline
   - Delete
3. Click "Apply"

### Filtering and Searching

#### Filter by Status

Use the status tabs at the top:

- **All** - View all reservations
- **Pending** - Awaiting approval
- **Approved** - Confirmed reservations
- **Declined** - Rejected reservations
- **Cancelled** - Cancelled bookings

#### Filter by Date

Use the date range picker to:

- View reservations for specific dates
- See upcoming reservations
- Review past bookings
- Generate date-specific reports

#### Search Reservations

Search by:

- Customer name
- Email address
- Phone number
- Confirmation number

### Exporting Data

#### Export to CSV

1. Apply any filters needed
2. Click "Export to CSV" button
3. Download file to your computer
4. Open in Excel, Google Sheets, or any spreadsheet app

**CSV Includes:**

- All reservation details
- Customer information
- Status and timestamps
- Special requests

**Use Exports For:**

- Record keeping
- Financial reporting
- Customer analytics
- Backup purposes

### Email Notifications

You receive email notifications for:

- New reservations
- Reservation cancellations
- Customer edits (if enabled)

Configure notification settings in: **Reservations > Settings > Notifications**

### Best Practices

✓ **Check Daily** - Review reservations at least once per day  
✓ **Respond Quickly** - Approve/decline pending reservations promptly  
✓ **Keep Records** - Export data regularly for your records  
✓ **Monitor Capacity** - Watch for popular time slots and adjust settings  
✓ **Read Special Requests** - Pay attention to customer notes  
✓ **Update Settings** - Adjust time slots for holidays and special events

---

## Configuration Guide

### Initial Setup Wizard

**When First Activated:**

The plugin creates necessary database tables and default settings automatically. You should configure:

1. Business information
2. Time slots
3. Form fields
4. Email templates

### Business Settings

**Location:** Reservations > Settings > General

#### Business Information

- **Business Name**: Your restaurant/business name (appears in emails and forms)
- **Business Email**: Where reservation notifications are sent
- **Phone Number**: Displayed on forms for customer contact
- **Address**: Displayed on forms (optional)

#### Reservation Settings

- **Require Approval**: Toggle whether reservations need manual approval
- **Edit Window**: How many hours before reservation customers can edit (e.g., 24 hours)
- **Booking Window**: How far in advance customers can book (e.g., 90 days)
- **Max Party Size**: Maximum number of guests allowed (e.g., 20)
- **Min Party Size**: Minimum party size required (usually 1 or 2)

### Time Slot Configuration

**Location:** Reservations > Settings > Time Slots

#### Setting Operating Hours

For each day of the week:

1. **Enable/Disable**: Check box to enable reservations for that day
2. **Opening Time**: When your first reservation slot begins
3. **Closing Time**: When your last reservation slot ends
4. **Slot Duration**: Length of each time slot (15, 30, 45, or 60 minutes)

**Example Configuration:**

```
Tuesday: ✓ Enabled
Opening: 5:00 PM
Closing: 10:00 PM
Duration: 30 minutes

This creates slots at:
5:00 PM, 5:30 PM, 6:00 PM, 6:30 PM, 7:00 PM,
7:30 PM, 8:00 PM, 8:30 PM, 9:00 PM, 9:30 PM
```

#### Capacity Management

- **Max Reservations Per Slot**: How many reservations can be made for the same time
  - Example: Setting this to 10 means up to 10 parties can book the same time slot
  - Useful for restaurants with multiple tables

#### Blackout Dates

Block specific dates when reservations shouldn't be accepted:

1. Click "Add Blackout Date"
2. Select the date or date range
3. Add optional reason (e.g., "Private Event", "Holiday Closure")
4. Save

**Common Uses:**

- Holidays
- Private events
- Maintenance days
- Seasonal closures

### Form Builder

**Location:** Reservations > Settings > Form Builder

#### Field Management

**Available Fields:**

- Name (required by default)
- Email (required by default)
- Phone
- Date (required by default)
- Time (required by default)
- Party Size (required by default)
- Special Requests

**For Each Field You Can:**

- ✓ Enable/Disable - Show or hide the field
- ✓ Make Required - Force completion before submission
- ✓ Customize Label - Change the field name displayed
- ✓ Set Placeholder - Add hint text in the field
- ✓ Reorder - Drag to change field order

**Example Customization:**

```
Field: Phone
Enabled: ✓
Required: ☐
Label: Contact Number
Placeholder: (555) 123-4567
```

#### Form Preview

The live preview on the right shows exactly how your form will look to customers. Changes update in real-time.

### Email Templates

**Location:** Reservations > Settings > Email Templates

#### Available Templates

1. **Confirmation Email** - Sent immediately when reservation is made
2. **Approval Email** - Sent when admin approves a pending reservation
3. **Declined Email** - Sent when admin declines a reservation
4. **Reminder Email** - Sent before the reservation (if enabled)
5. **Cancellation Email** - Sent when reservation is cancelled
6. **Update Email** - Sent when reservation details are changed

#### Editing Templates

**For Each Template:**

- **Subject Line**: Email subject (can include variables)
- **Email Body**: The message content (supports HTML)
- **Enable/Disable**: Turn email notifications on/off

#### Email Variables

Use these placeholders in your templates:

- `{customer_name}` - Customer's name
- `{customer_email}` - Customer's email
- `{customer_phone}` - Customer's phone
- `{reservation_date}` - Reservation date
- `{reservation_time}` - Reservation time
- `{party_size}` - Number of guests
- `{special_requests}` - Customer's special requests
- `{business_name}` - Your business name
- `{business_email}` - Your business email
- `{business_phone}` - Your business phone
- `{confirmation_number}` - Unique reservation ID
- `{confirmation_link}` - Link to view reservation
- `{edit_link}` - Link to edit reservation
- `{cancellation_link}` - Link to cancel reservation

**Example Template:**

```
Subject: Reservation Confirmed at {business_name}

Hi {customer_name},

Your reservation for {party_size} guests has been confirmed!

Date: {reservation_date}
Time: {reservation_time}
Party Size: {party_size}

Special Requests: {special_requests}

View or manage your reservation: {confirmation_link}

Need to cancel? {cancellation_link}

We look forward to seeing you!

{business_name}
{business_phone}
```

#### Email Testing

Use the "Send Test Email" button to:

- Test email delivery
- Preview how emails look
- Verify email server configuration

### Styling Options

**Location:** Reservations > Settings > Styling

#### Theme Selection

Choose from preset themes:

**Default Theme:**

- Clean, modern design
- Professional appearance
- Works with most websites

**Minimal Theme:**

- Simple, lightweight
- Fewer visual elements
- Fast loading

**Bold Theme:**

- High contrast colors
- Large, prominent buttons
- Attention-grabbing design

**Custom Theme:**

- Full control over colors
- Match your exact brand
- Advanced customization

#### Custom Color Configuration

When using Custom theme:

- **Primary Color**: Main brand color (buttons, headings)
- **Secondary Color**: Accent elements
- **Text Color**: Main text color
- **Background Color**: Form background
- **Border Color**: Input field borders
- **Button Text Color**: Text on buttons

**Color Picker Tips:**

- Click color box to open picker
- Enter hex codes directly (#FF5733)
- Use eyedropper to match existing colors
- Preview updates live

#### Typography

- **Font Family**: Choose from web-safe fonts
- **Font Size**: Set base font size (14-18px recommended)
- **Heading Size**: Size for form headings

#### Spacing

- **Form Padding**: Internal form spacing
- **Field Spacing**: Gap between form fields
- **Button Padding**: Button size

### Notifications

**Location:** Reservations > Settings > Notifications

#### Admin Notifications

- **New Reservation**: Get notified when customers book
- **Cancellation**: Alert when reservations are cancelled
- **Updates**: Notify when customers edit reservations

#### Reminder Settings

- **Enable Reminders**: Send reminder emails to customers
- **Reminder Timing**: How many hours before reservation (e.g., 24 hours)
- **Reminder Template**: Customize the reminder message

### Advanced Settings

**Location:** Reservations > Settings > Advanced

#### Capacity & Limits

- **Daily Reservation Limit**: Max reservations per day (0 = unlimited)
- **Per-Time-Slot Limit**: Max reservations per time slot
- **Same-Day Bookings**: Allow/prevent day-of reservations
- **Minimum Advance Notice**: Require X hours advance booking

#### Data Management

- **Cleanup Old Reservations**: Automatically delete old records
- **Keep Data For**: How many days to retain (e.g., 365)
- **Export Format**: Choose CSV format options

#### Developer Options

- **Enable Debug Mode**: Log errors for troubleshooting
- **Custom CSS**: Add your own CSS styles
- **Hooks & Filters**: Enable custom code integration

---

## Troubleshooting

### Common Issues and Solutions

#### Issue: Emails Not Sending

**Symptoms:**

- Customers don't receive confirmation emails
- Admin doesn't get new reservation notifications

**Solutions:**

1. **Check WordPress Email Settings**

   - Go to Settings > General
   - Verify "Email Address" is correct
   - Send test email from WordPress

2. **Install SMTP Plugin**

   - Install "WP Mail SMTP" plugin
   - Configure with your email provider
   - Test email delivery
   - Power Reservations will use these settings automatically

3. **Check Spam Folders**

   - Emails might be marked as spam
   - Add business email to contacts
   - Whitelist domain in email settings

4. **Verify Email Templates**

   - Go to Reservations > Settings > Email Templates
   - Ensure templates are enabled
   - Check for errors in template code
   - Use "Send Test Email" button

5. **Server Configuration**
   - Contact hosting provider
   - Verify mail server is configured
   - Check server email logs
   - Ensure PHP mail() function works

#### Issue: Form Not Displaying

**Symptoms:**

- Shortcode shows but no form appears
- Elementor widget is blank
- Form appears broken

**Solutions:**

1. **Check Shortcode Syntax**

   ```
   Correct: [power_reservations]
   Incorrect: [powerreservations] or [power-reservations]
   ```

2. **JavaScript Conflicts**

   - Disable other plugins temporarily
   - Switch to default theme
   - Check browser console for errors
   - Update WordPress and plugins

3. **Clear Cache**

   - Clear WordPress cache
   - Clear browser cache
   - Clear CDN cache
   - Purge plugin caches

4. **Check Plugin Activation**
   - Ensure plugin is active
   - Deactivate and reactivate
   - Check for plugin conflicts

#### Issue: Time Slots Not Showing

**Symptoms:**

- Date picker works but no times available
- "No available times" message appears
- Only some time slots display

**Solutions:**

1. **Verify Time Slot Configuration**

   - Go to Reservations > Settings > Time Slots
   - Check day is enabled
   - Verify opening/closing times
   - Ensure duration is set

2. **Check Date Selection**

   - Ensure selected date isn't a blackout date
   - Check date isn't in the past
   - Verify date is within booking window

3. **Review Capacity Settings**

   - Check if time slots are fully booked
   - Adjust "Max Reservations Per Slot"
   - Review existing reservations for that day

4. **Clear Cached Availability**
   - Save time slot settings again
   - Refresh the page
   - Test in incognito window

#### Issue: Customers Can't Edit/Cancel

**Symptoms:**

- Edit/cancel links don't work
- "Link expired" error appears
- Changes don't save

**Solutions:**

1. **Check Edit Window**

   - Go to Settings > General
   - Review "Edit Window" setting
   - Reservation might be too close to start time

2. **Verify Links**

   - Ensure links aren't broken in email
   - Check URL structure is correct
   - Test links in different email clients

3. **Check Reservation Status**

   - Past reservations can't be edited
   - Cancelled reservations are locked
   - Declined reservations can't be modified

4. **Permalink Settings**
   - Go to Settings > Permalinks
   - Click "Save Changes" (resets permalinks)
   - Test links again

#### Issue: Database Errors

**Symptoms:**

- "Error creating table" messages
- Reservations not saving
- Can't view reservations list

**Solutions:**

1. **Reactivate Plugin**

   - Deactivate plugin
   - Reactivate plugin
   - Tables will be recreated

2. **Check Database Permissions**

   - Verify WordPress database user has CREATE/ALTER permissions
   - Contact hosting provider if needed
   - Check database error logs

3. **Manual Database Repair**

   - Go to WordPress Database
   - Look for tables with prefix `wp_pr_`
   - Repair/optimize tables
   - Or reinstall plugin

4. **Check Database Prefix**
   - Ensure wp-config.php database prefix matches
   - Verify $wpdb->prefix in WordPress

#### Issue: Mobile Display Problems

**Symptoms:**

- Form looks broken on mobile
- Buttons not clickable
- Text too small or overlapping

**Solutions:**

1. **Check Theme Compatibility**

   - Switch to default WordPress theme temporarily
   - Test if issue persists
   - Contact theme developer if theme-specific

2. **Review Custom CSS**

   - Remove custom CSS temporarily
   - Test default styling
   - Adjust CSS for mobile responsiveness

3. **Clear Mobile Cache**

   - Clear mobile browser cache
   - Test in different mobile browsers
   - Use responsive design checker

4. **Update Plugin**
   - Ensure plugin is latest version
   - Check changelog for mobile fixes
   - Update WordPress to latest version

#### Issue: Elementor Widget Not Appearing

**Symptoms:**

- Can't find "Power Reservations" widget
- Widget shows in list but doesn't load
- Widget causes Elementor errors

**Solutions:**

1. **Check Elementor Version**

   - Ensure Elementor 3.0 or higher
   - Update Elementor if outdated
   - Update Power Reservations

2. **Regenerate Widgets**

   - Go to Elementor > Tools
   - Click "Regenerate Files & Data"
   - Clear Elementor cache
   - Reload Elementor editor

3. **Check Widget Registration**

   - Deactivate Power Reservations
   - Reactivate plugin
   - Refresh Elementor editor

4. **JavaScript Errors**
   - Open browser console
   - Check for JavaScript errors
   - Disable other plugins
   - Test for conflicts

### Getting Additional Help

#### Before Contacting Support

Please gather:

- WordPress version
- Plugin version
- PHP version
- Active theme name
- List of active plugins
- Error messages (if any)
- Screenshots of the issue
- Steps to reproduce

#### Support Channels

1. **Documentation**

   - Read full documentation
   - Check FAQ section
   - Watch video tutorials

2. **Community Forums**

   - WordPress.org plugin forum
   - Search existing topics
   - Post detailed questions

3. **GitHub Issues**

   - Report bugs on GitHub
   - Include system information
   - Attach error logs

4. **Premium Support**
   - Email: support@example.com
   - Include all troubleshooting steps tried
   - Attach system info

### Error Messages Explained

**"No available time slots"**

- All slots are booked
- Selected date is blocked
- Time slots not configured for that day

**"Booking window exceeded"**

- Trying to book too far in advance
- Adjust "Booking Window" in settings

**"Minimum advance notice required"**

- Trying to book too close to reservation time
- Change "Minimum Advance Notice" setting

**"Party size exceeds maximum"**

- Requested party size too large
- Adjust "Max Party Size" setting
- Contact directly for large parties

**"Email address already used"**

- Email has active reservation for same day/time
- Prevents duplicate bookings

**"Database error"**

- Database connection issue
- Contact hosting provider
- Check database credentials

---

## Tips for Success

### For Customers

✓ Book early for popular times  
✓ Provide accurate contact information  
✓ Read confirmation emails carefully  
✓ Add special requests before submitting  
✓ Cancel if plans change  
✓ Arrive on time

### For Admins

✓ Review settings before going live  
✓ Test the complete booking process  
✓ Set up email notifications properly  
✓ Monitor reservations daily  
✓ Adjust time slots based on demand  
✓ Keep email templates professional  
✓ Respond to customers promptly  
✓ Export data regularly for backup

---

## Glossary

**Blackout Date**: A date when reservations are not accepted

**Booking Window**: How far in advance customers can make reservations

**Capacity**: Maximum number of reservations allowed per time slot

**Confirmation Number**: Unique identifier for each reservation

**Edit Window**: Period before reservation when edits are allowed

**Party Size**: Number of guests in a reservation

**Pending**: Reservation awaiting approval

**Time Slot**: Specific booking time interval

**Shortcode**: WordPress code snippet to display content

**SMTP**: Email protocol for reliable delivery

---

**Need more help?** Contact us at support@example.com

**Found this helpful?** Please rate the plugin and leave a review!

---

_Last Updated: October 2025_
_Plugin Version: 1.0.0_
