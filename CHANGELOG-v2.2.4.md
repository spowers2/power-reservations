# Power Reservations v2.2.4 - Changelog

## Release Date: [Current Date]

## Overview

Added comprehensive placeholder documentation to the email template editor, making it easy for users to discover and use all available template variables, including the new `{upcoming_reservations}` placeholder.

---

## ✨ New Features

### Email Template Placeholder Documentation

- **Interactive Placeholder Reference**: Added a visual grid showing all available template variables directly in the email template editor
- **Template Type Awareness**: Automatically shows/hides admin-only placeholders ({admin_link}, {upcoming_reservations}) based on selected template type
- **Click-to-Copy**: Users can click on any placeholder code to copy it to clipboard with visual feedback
- **Comprehensive List**: Documents all 11 placeholders:
  - Common: {business_name}, {name}, {email}, {phone}, {date}, {time}, {party_size}, {special_requests}, {reservation_id}
  - Admin Only: {admin_link}, {upcoming_reservations}

### User Experience Improvements

- **Visual Design**: Modern card-based layout with hover effects and color-coding for admin-only placeholders
- **Responsive Layout**: Grid automatically adjusts for mobile devices
- **Copy Feedback**: Green flash and "Copied!" tooltip when clicking placeholders
- **Dynamic Visibility**: Smooth slide animations when switching between customer and admin template types

---

## 🎨 Technical Improvements

### Frontend (Admin)

- **New CSS**: Added `.pr-placeholders-info` and `.pr-placeholders-grid` styles with responsive breakpoints
- **Color Coding**: Admin-only placeholders highlighted with amber border and background
- **Interactive Elements**: Hover states, transitions, and copy-to-clipboard functionality

### JavaScript

- **Template Type Handler**: Automatically shows/hides admin placeholders based on dropdown selection
- **Clipboard API**: Implements copy functionality with fallback support
- **Visual Feedback**: Temporary color change and tooltip on successful copy

---

## 📦 Files Modified

### Core Plugin Files

- `power-reservations.php` (Rev20)
  - Version bumped to 2.2.4
  - Added placeholder documentation HTML in `email_templates_page()` function
  - Inserted between wp_editor and active checkbox (after line 1998)

### Assets

- `assets/admin.css`
  - Added 100+ lines of placeholder styling
  - Includes responsive design, hover effects, and admin-only highlighting
- `assets/admin.js`
  - Added template type change handler
  - Implemented click-to-copy with visual feedback
  - Automatic show/hide of admin placeholders

---

## 🔧 Usage Instructions

### For Site Administrators

1. **Navigate to Email Templates**:

   - WordPress Admin → Reservations → Email Templates
   - Click "Edit" on any template (or Add New)

2. **View Available Placeholders**:

   - Scroll to the "Available Template Variables" section below the content editor
   - See all placeholders with descriptions

3. **Use Placeholders**:

   - **Option 1**: Click any placeholder code to copy it, then paste into your template
   - **Option 2**: Type the placeholder manually (e.g., `{upcoming_reservations}`)

4. **Template Type Specific**:
   - When editing **Admin** templates: See all 11 placeholders including {upcoming_reservations}
   - When editing **Customer** templates: See only the 9 customer-relevant placeholders

### Verifying the Update

After installing v2.2.4:

1. Go to Email Templates page
2. Edit the "Admin Notification" template
3. You should now see a grid of available placeholders with:
   - Blue placeholders for common variables
   - Amber/orange highlighted placeholders for admin-only variables ({admin_link}, {upcoming_reservations})
4. Click on `{upcoming_reservations}` to copy it
5. Paste it anywhere in your email template content

---

## 🐛 Bug Fixes

### Resolved Issues

- **Missing Documentation**: Users were unaware of the {upcoming_reservations} placeholder added in v2.2.0
- **Discovery Problem**: No way to see what placeholders were available without checking code or documentation
- **Template Type Confusion**: Unclear which placeholders were available for customer vs admin templates

---

## ⚠️ Important Notes

### Upgrade Instructions

1. **Backup First**: Always backup your database before updating
2. **Existing Templates**: Your existing email templates are not modified by this update
3. **New Feature**: This only adds documentation UI - the placeholders themselves work the same as before
4. **No Database Changes**: This update does not modify database structure

### Regarding {upcoming_reservations}

- This placeholder was added in v2.2.0 but wasn't documented in the UI
- If you haven't used it yet, you can now add it to your admin notification template
- It shows a formatted table of all upcoming reservations (next 7 days)
- The current reservation is highlighted in yellow
- **Only works in Admin templates** - will be blank in customer emails

---

## 🎯 What This Fixes

### User-Reported Issue

> "The admin default email does not send a list of upcoming reservations and a variable for that to put into the email is not listed"

**Resolution**:

- The variable `{upcoming_reservations}` exists and works (added in v2.2.0)
- This update makes it **visible and discoverable** in the UI
- Now clearly documented with description: "List of upcoming reservations (next 7 days) (Admin only)"

### Developer Note

The functionality was implemented correctly in v2.2.0, but we failed to add UI documentation for discoverability. This update fixes that oversight with a comprehensive placeholder reference system.

---

## 📚 Related Documentation

- **Email Templates Guide**: See `HELP.md` for full email system documentation
- **v2.2.0 Changelog**: Details on {upcoming_reservations} functionality
- **v2.2.1 Changelog**: Auto-approval feature documentation

---

## 🔮 Future Enhancements

Potential improvements for future versions:

- [ ] Placeholder preview/testing system
- [ ] Template variable syntax validation
- [ ] More granular control over upcoming reservations list (date range, formatting)
- [ ] Custom placeholder creation system
- [ ] Template import/export functionality

---

## 🤝 Support

If you encounter any issues with the new placeholder documentation:

1. Verify you're running v2.2.4 (check plugin version in WordPress admin)
2. Clear your browser cache if the new UI doesn't appear
3. Check browser console for JavaScript errors
4. Ensure your email templates include the placeholder exactly as shown: `{upcoming_reservations}`

---

## Version History

- **v2.2.4**: Added placeholder documentation UI ← Current Release
- **v2.2.3**: Clean build for headers error resolution
- **v2.2.2**: Headers already sent error fix
- **v2.2.1**: Auto-approval system
- **v2.2.0**: Upcoming reservations email feature (placeholder added but not documented)

---

**Package**: `power-reservations-2.2.4.zip`  
**Installation**: Delete old plugin → Upload → Activate → Verify placeholder docs appear in Email Templates page
