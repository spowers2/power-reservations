# Power Reservations v2.2.4 - Installation Guide

## Quick Install

### Step 1: Backup

```bash
# Backup your database before updating
# Use your hosting control panel or phpMyAdmin
```

### Step 2: Remove Old Version

1. Go to WordPress Admin → Plugins
2. Deactivate "Power Reservations"
3. Delete the plugin
4. **Important**: This does NOT delete your data (reservations, settings, templates)

### Step 3: Install New Version

1. Go to Plugins → Add New → Upload Plugin
2. Choose `power-reservations-2.2.4.zip`
3. Click "Install Now"
4. Click "Activate Plugin"

### Step 4: Verify Installation

1. Go to Reservations → Email Templates
2. Click "Edit" on any template
3. **You should now see**: "Available Template Variables" section with a grid of placeholders
4. **Verify**: Click on any placeholder code - it should copy to clipboard with "Copied!" feedback

---

## What's New in This Version?

### Placeholder Documentation UI

You'll now see all available template variables documented directly in the email template editor:

**Common Placeholders** (all templates):

- `{business_name}` - Your business name
- `{name}` - Customer's name
- `{email}` - Customer's email
- `{phone}` - Customer's phone
- `{date}` - Reservation date
- `{time}` - Reservation time
- `{party_size}` - Party size
- `{special_requests}` - Special requests
- `{reservation_id}` - Reservation ID

**Admin Only** (highlighted in amber):

- `{admin_link}` - Link to admin dashboard
- `{upcoming_reservations}` - List of upcoming reservations (next 7 days)

---

## Using the New Placeholder Feature

### Example: Adding Upcoming Reservations to Admin Email

1. Navigate to: **Reservations → Email Templates**
2. Click **Edit** on "Admin Notification" template
3. Scroll to **Available Template Variables** section
4. Click on `{upcoming_reservations}` to copy it
5. Paste it in your email content where you want the list to appear
6. Click **Update Template**
7. Test by creating a new reservation

**Result**: Admin emails will now include a formatted table showing:

- All reservations in the next 7 days
- Reservation date, time, party size, customer name, status
- Current (new) reservation highlighted in yellow

---

## Troubleshooting

### "I don't see the placeholder documentation"

1. Verify plugin version in WordPress Admin → Plugins (should say v2.2.4)
2. Clear your browser cache (Cmd+Shift+R on Mac, Ctrl+Shift+R on Windows)
3. Try a different browser or incognito window
4. Check browser console (F12) for JavaScript errors

### "Click-to-copy doesn't work"

- This feature requires JavaScript to be enabled
- Make sure no browser extensions are blocking scripts
- Try disabling ad blockers temporarily

### "Admin-only placeholders don't show"

- They only appear when **Template Type** is set to "Admin Notification"
- Switch the dropdown from "Customer Email" to "Admin Notification"
- They should slide into view automatically

### "Upcoming reservations list is empty in email"

- Make sure you have reservations scheduled in the next 7 days
- Verify the placeholder is spelled exactly: `{upcoming_reservations}` (with curly braces)
- This only works in Admin templates, not Customer templates
- If still empty, check if you restored default templates (Settings → Restore Defaults)

---

## Need Help?

If you continue to experience issues:

1. Check the full changelog: `CHANGELOG-v2.2.4.md`
2. Review the help documentation: `HELP.md`
3. Check if email templates need to be restored to defaults

---

## Important Notes

### Data Safety

- Your reservations are **not affected** by this update
- Your email templates are **not modified** automatically
- Your settings remain intact
- This is a UI enhancement only - adds documentation, doesn't change functionality

### Compatibility

- WordPress 5.0+
- PHP 7.4+
- Works with shortcode and Elementor forms
- No conflicts with existing templates

### Performance

- No database queries added
- Minimal JavaScript (~50 lines)
- CSS is cached by WordPress
- No impact on page load speed

---

## Next Steps

After installation:

1. ✅ Edit your Admin Notification template
2. ✅ Add `{upcoming_reservations}` placeholder if desired
3. ✅ Test by creating a reservation
4. ✅ Check admin email for upcoming reservations list
5. ✅ Customize the email template as needed

---

**Version**: 2.2.4  
**Release**: 2024  
**Package Size**: ~600KB  
**Installation Time**: 2-3 minutes
