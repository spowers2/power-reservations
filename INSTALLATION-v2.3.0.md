# Power Reservations v2.3.0 - Installation & Setup Guide

## Quick Install

### Step 1: Backup
```bash
# Backup your database before updating
# Use your hosting control panel, phpMyAdmin, or backup plugin
```

### Step 2: Remove Old Version
1. Go to WordPress Admin → Plugins
2. Deactivate "Power Reservations"
3. Delete the plugin
4. **Note**: Your data is safe - reservations, settings, and templates are preserved

### Step 3: Install v2.3.0
1. Go to Plugins → Add New → Upload Plugin
2. Choose `power-reservations-2.3.0.zip`
3. Click "Install Now"
4. Click "Activate Plugin"

### Step 4: Configure Capacity Settings
1. Go to Reservations → Settings
2. Scroll to "Available Time Slots" section
3. **You'll now see**: Each time slot has two inputs:
   - **Time**: (e.g., "6:00 PM")
   - **Capacity**: Number input (default: 10)
4. Set appropriate capacities for each time slot
5. Click "Save Settings"

---

## What's New in v2.3.0?

### Time Slot Capacity Limiting
The major new feature allows you to:
- **Set maximum reservations** for each time slot
- **Prevent overbooking** automatically
- **Show real-time availability** to customers
- **Disable full time slots** in the booking form

---

## Post-Installation Setup

### 1. Review Auto-Migrated Capacities
After installation, all your existing time slots will have a default capacity of **10 reservations**.

**To adjust**:
1. Go to **Reservations → Settings**
2. Find **"Available Time Slots"** section
3. Review each time slot's capacity
4. Adjust based on your restaurant's actual capacity

### 2. Set Appropriate Capacities

**Recommended Starting Points**:
- **Small Restaurant** (30-50 seats): 5-8 reservations per slot
- **Medium Restaurant** (50-100 seats): 10-15 reservations per slot
- **Large Restaurant** (100+ seats): 15-25 reservations per slot

**Example Configuration**:
```
5:30 PM → Capacity: 5  (Early, smaller crowd)
6:00 PM → Capacity: 12 (Popular time)
7:00 PM → Capacity: 15 (Prime time, highest capacity)
7:30 PM → Capacity: 12
8:00 PM → Capacity: 10
9:00 PM → Capacity: 8  (Late, winding down)
```

### 3. Test the New System

**Admin Test**:
1. Create test reservations for a specific date/time
2. Watch capacity count approach the limit
3. Try to exceed capacity - should get error message
4. Verify time slot shows as "Fully Booked" when full

**Customer Test**:
1. Open your reservation form on the frontend
2. Select a date
3. **You should see**: Time dropdown updates with available times
4. **Low capacity times** show: "6:00 PM (2 spots left)"
5. **Full times** show: "7:00 PM (Fully Booked)" and are disabled

---

## How It Works

### Customer Experience
1. **Customer selects a date**
2. **System checks availability** via AJAX
3. **Time dropdown updates** with:
   - Available time slots
   - Remaining capacity warnings (when ≤3 spots left)
   - Disabled fully booked slots
4. **Customer selects available time** and completes reservation
5. **System verifies capacity** before saving (prevents race conditions)

### Admin Experience
1. **Set capacities** in Settings → Available Time Slots
2. **Monitor bookings** in Reservations dashboard
3. **Capacity tracked automatically** - no manual counting
4. **Email notifications** work as before (now with correct times!)

---

## Key Features

### ✅ Capacity Management
- Set different limits for different time slots
- Automatically counts active reservations ('pending' + 'approved')
- Cancelled reservations don't count toward capacity
- Real-time capacity checking

### ✅ Dynamic Time Slots
- Time options load when date is selected
- Only shows available or nearly-full slots
- Full slots appear disabled with "(Fully Booked)" label
- Low capacity shows warnings: "(3 spots left)"

### ✅ Overbooking Prevention
- Double-checks capacity before saving reservation
- Handles race conditions (two people booking last spot)
- Clear error message if slot fills up during checkout

### ✅ Email Improvements
- Times now display exactly as entered (e.g., "6:00 PM")
- No more time formatting issues
- Works correctly in all email templates

---

## Migration Details

### Automatic Migration Process
Your existing time slots are automatically upgraded:

**Before (v2.2.4)**:
```php
pr_time_slots = [
  "6:00 PM",
  "7:00 PM",
  "8:00 PM"
]
```

**After (v2.3.0)**:
```php
pr_time_slots = [
  {time: "6:00 PM", capacity: 10},
  {time: "7:00 PM", capacity: 10},
  {time: "8:00 PM", capacity: 10}
]
```

**Migration happens**:
- Automatically on first page load
- No action required from you
- All data preserved
- Existing reservations unaffected

---

## Troubleshooting

### "Time slots not updating when I select a date"
**Possible causes**:
1. JavaScript error - check browser console (F12)
2. AJAX URL issue - verify pr_ajax is defined
3. Nonce expired - refresh the page

**Solutions**:
- Clear browser cache (Cmd+Shift+R or Ctrl+Shift+R)
- Deactivate conflicting plugins temporarily
- Check if jQuery is loaded
- Review browser console for error messages

### "Capacity input not showing in settings"
**Possible causes**:
1. CSS cache issue
2. Old plugin files not replaced

**Solutions**:
- Hard refresh the settings page (Cmd+Shift+R)
- Clear WordPress cache (if using caching plugin)
- Verify plugin version shows 2.3.0 in Plugins list
- Re-upload plugin files

### "Getting 'fully booked' error but slot isn't full"
**Possible causes**:
1. Counting cancelled reservations
2. Database sync issue

**Solutions**:
- Check reservation status in admin (only 'pending' + 'approved' should count)
- Manually count reservations for that date/time
- Verify capacity setting is correct
- Check for duplicate reservations

### "Emails still showing wrong time format"
**If times show as "12:00 AM" or empty**:
1. Check that reservation_time field has correct format
2. Verify you've uploaded v2.3.0 files (not cached old version)
3. Test with a new reservation (old reservations may have old format)

---

## Best Practices

### 1. Start Conservative
- Begin with **lower capacities** (e.g., 8-10)
- Monitor booking patterns for 1-2 weeks
- Adjust upward based on actual capacity and demand

### 2. Consider Your Space
- **Physical tables**: How many can you actually seat?
- **Kitchen capacity**: Can you handle X orders at once?
- **Staff capacity**: Do you have enough servers?
- **Peak times**: Popular slots may need higher capacity

### 3. Seasonal Adjustments
- **Holidays**: Increase capacity for high-demand dates
- **Off-season**: Reduce capacity to manage labor costs
- **Special events**: Adjust as needed

### 4. Monitor and Adjust
- Check reservation reports regularly
- Note which time slots fill up quickly
- Adjust capacities based on patterns
- Track no-shows and cancellations

---

## Advanced Configuration

### Capacity Strategy Examples

**Example 1: Staggered Seating**
```
6:00 PM → 10 reservations
6:30 PM → 8 reservations  (lower, as 6:00 PM tables still occupied)
7:00 PM → 12 reservations
7:30 PM → 10 reservations
```

**Example 2: Two Seatings Per Night**
```
5:30 PM → 15 reservations (First seating)
6:00 PM → 5 reservations  (Late first seating arrivals)
8:00 PM → 15 reservations (Second seating)
8:30 PM → 5 reservations  (Late second seating arrivals)
```

**Example 3: Weekend vs. Weekday**
- Weekdays: Lower capacities (8-10)
- Weekends: Higher capacities (15-20)
- (Requires manual adjustment per day, or use separate settings)

---

## FAQ

**Q: Does capacity count individual people or reservations?**  
A: It counts **reservations** (bookings), not individual guests. A party of 6 counts as 1 toward capacity.

**Q: Can I set different capacities for different days?**  
A: Currently, capacities apply to all days. You'd need to manually adjust for specific dates. (Feature request noted for future versions)

**Q: What if two people book the last spot at the exact same time?**  
A: The system has race condition protection. The first submission succeeds, the second gets an error message to select a different time.

**Q: Do cancelled reservations count toward capacity?**  
A: No, only 'pending' and 'approved' reservations count. Cancelled or deleted reservations don't affect capacity.

**Q: Can customers see how many spots are left?**  
A: Yes, when 3 or fewer spots remain, it shows "(X spots left)" next to the time.

**Q: What happens to existing reservations after upgrade?**  
A: Nothing! All existing reservations are preserved and work exactly as before.

---

## Next Steps

After installation:
1. ✅ Review and adjust capacity settings
2. ✅ Test the booking form on your live site
3. ✅ Create a test reservation to verify emails
4. ✅ Monitor bookings for first few days
5. ✅ Adjust capacities based on real-world usage

---

## Support Resources

- **Full Changelog**: See `CHANGELOG-v2.3.0.md` for technical details
- **Help Documentation**: See `HELP.md` for general usage
- **Developer Guide**: See `DEVELOPER.md` for technical information

---

## Important Notes

### Data Safety
- ✅ All reservations preserved
- ✅ Settings maintained
- ✅ Email templates intact
- ✅ Customer data unchanged

### Performance
- Minimal impact on page load
- Efficient database queries
- Cached where appropriate
- AJAX calls only when needed

### Compatibility
- WordPress 5.0+ (tested through 6.4)
- PHP 7.4+ required
- All modern browsers supported
- Works with caching plugins

---

**Version**: 2.3.0  
**Install Time**: 2-3 minutes  
**Setup Time**: 5-10 minutes  
**Recommended Action**: Install, review capacities, test booking flow
