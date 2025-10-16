# Power Reservations v2.3.0 - Changelog

## Release Date: October 15, 2025

## Overview
Major feature release adding intelligent time slot capacity limiting. Restaurants can now set maximum reservation limits for each time slot, with automatic capacity checking and real-time availability display. This prevents overbooking and provides customers with up-to-date slot availability.

---

## ✨ Major New Features

### Time Slot Capacity Limiting System
- **Per-Slot Capacity Settings**: Each time slot now has its own configurable capacity limit (default: 10 reservations)
- **Real-Time Availability**: Time slots dynamically load based on current bookings when customers select a date
- **Automatic Capacity Checking**: System verifies capacity before accepting reservations, preventing overbooking
- **Full Slot Handling**: Time slots at capacity are disabled with "(Fully Booked)" label
- **Low Capacity Warnings**: Shows remaining spots when 3 or fewer reservations remain (e.g., "6:00 PM (2 spots left)")

### Dynamic Time Slot Loading
- **AJAX-Powered**: Time slots load dynamically when date is selected
- **Capacity-Aware**: Only shows available times or marks full slots as disabled
- **Real-Time Updates**: Checks current reservations before displaying options
- **Seamless UX**: Works with both shortcode forms and Elementor widget

---

## 🎨 UI/UX Improvements

### Admin Settings Interface
- **Capacity Input Fields**: New number input next to each time slot for capacity setting
- **Visual Design**: Clean, modern layout with proper spacing and alignment
- **Default Values**: New slots default to capacity of 10
- **Validation**: Capacity minimum of 1, maximum of 999

### Frontend Form Experience
- **Smart Time Loading**: Time dropdown says "Select date first..." until date is chosen
- **Availability Feedback**: Shows remaining capacity for low-availability slots
- **Disabled Full Slots**: Full time slots appear grayed out with "(Fully Booked)" label
- **Error Handling**: Clear messaging if selected time fills up before submission

### Visual Indicators
```
Available (Plenty of Spots):
  → 6:00 PM

Available (Low Capacity):
  → 6:30 PM (2 spots left)

Fully Booked:
  → 7:00 PM (Fully Booked) [disabled]
```

---

## 🔧 Technical Improvements

### Data Structure Changes
- **New Format**: Time slots now stored as array of objects: `[{time: "6:00 PM", capacity: 10}]`
- **Automatic Migration**: Old format (simple string array) automatically converts to new format
- **Backward Compatibility**: Existing time slots migrated seamlessly on first load
- **Default Capacity**: Migrated slots get capacity of 10

### Backend Enhancements
- **Enhanced AJAX Endpoint**: `pr_check_availability` returns detailed slot information
  - `available` (boolean): Whether slot has capacity
  - `capacity` (int): Maximum reservations for this slot
  - `remaining` (int): Number of spots left
  - `value` (string): Time slot value
  - `label` (string): Display label

- **Capacity Validation**: Pre-submission checks prevent race conditions
- **Database Queries**: Efficient counting of existing reservations per time slot
- **Status Filtering**: Only counts 'pending' and 'approved' reservations toward capacity

### Frontend JavaScript Updates
- **New Function**: `loadAvailableTimeSlots(date)` - Centralized slot loading
- **Multiple Selectors**: Supports various form ID patterns (#pr_time, #pr-time, #pr-elementor-time, etc.)
- **Date Change Handlers**: Listeners for both datepicker and regular date inputs
- **Error Handling**: Graceful fallbacks for AJAX failures
- **Console Logging**: Detailed debugging information for troubleshooting

---

## 🐛 Bug Fixes

### Email Time Display
- **Issue**: Time was being processed through `strtotime()` and reformatted, causing errors with formats like "6:00 PM"
- **Fix**: Now uses time exactly as stored/selected (e.g., "6:00 PM" displays as "6:00 PM")
- **Applies To**: 
  - Customer confirmation emails
  - Admin notification emails  
  - Upcoming reservations table
- **Result**: Times in emails now match exactly what customer selected

### Time Slot Rendering
- **Issue**: Forms pre-loaded all time slots regardless of availability
- **Fix**: Time slots now load dynamically based on selected date
- **Impact**: Prevents customers from selecting unavailable times

---

## 📦 Files Modified

### Core Plugin Files
- `power-reservations.php` (Rev21)
  - Version bumped to 2.3.0
  - Updated time slot save/load logic (lines 1625-1662)
  - Added migration function for old format (lines 1649-1662)
  - Enhanced admin settings UI with capacity inputs (lines 1713-1732)
  - Updated `check_availability()` AJAX handler (lines 2965-3015)
  - Added capacity checking to reservation submission (lines 3585-3622)
  - Fixed email time display (lines 2771, 2867, 2848)
  - Updated form rendering to use dynamic loading (lines 3900-3906)

### Elementor Integration
- `includes/elementor-widget.php`
  - Updated time slot rendering for dynamic loading (lines 625-632)

### JavaScript Assets
- `assets/frontend.js`
  - Added `loadAvailableTimeSlots()` function (lines 87-144)
  - Added date change handlers (lines 73-84)
  - Enhanced error handling and logging
  - Support for multiple form selectors

- `assets/admin.js`
  - Updated "Add Time Slot" to include capacity input (lines 59-72)

### CSS Assets
- `assets/admin.css`
  - Added capacity input styling (25 new lines)
  - Enhanced time slot item layout
  - Focus states and visual polish

---

## 🎯 Use Cases & Examples

### Example 1: Setting Capacity
**Admin Action**:
1. Go to Reservations → Settings
2. Find "Available Time Slots" section
3. Each slot now has two inputs:
   - Time: "6:00 PM"
   - Capacity: 10
4. Set different capacities for different times:
   - 5:30 PM → Capacity: 5 (smaller early slot)
   - 6:00 PM → Capacity: 15 (prime time, higher capacity)
   - 9:30 PM → Capacity: 8 (late slot, reduced capacity)
5. Click "Save Settings"

### Example 2: Customer Experience
**Customer Flow**:
1. Opens reservation form
2. Selects date: October 20, 2025
3. Time dropdown updates automatically:
   ```
   Select time...
   5:30 PM
   6:00 PM (3 spots left)
   6:30 PM (Fully Booked) [disabled]
   7:00 PM
   ```
4. Customer sees 6:30 PM is full, chooses 7:00 PM instead
5. Submits reservation successfully

### Example 3: Preventing Overbooking
**Scenario**: Two customers try to book the last spot simultaneously
1. Customer A selects 6:00 PM (9/10 reservations exist)
2. Customer B selects 6:00 PM at same time
3. Customer A submits first → Success (10/10 full)
4. Customer B submits 2 seconds later → Error message: "Sorry, this time slot is now fully booked. Please select a different time."
5. Customer B refreshes, sees 6:00 PM now shows "(Fully Booked)"

---

## ⚙️ Configuration

### Default Settings
- **Default Capacity**: 10 reservations per time slot
- **Minimum Capacity**: 1
- **Maximum Capacity**: 999
- **Counted Statuses**: 'pending' and 'approved' (cancelled reservations don't count toward capacity)

### Customization Options
Administrators can:
- Set different capacities for different time slots
- Adjust capacities without changing time values
- Add new time slots with custom capacities
- Reorder slots (capacity moves with the slot)

---

## 🔄 Migration Guide

### Upgrading from v2.2.x

**Automatic Migration**:
- Old time slot format automatically converts to new format on first load
- No manual intervention required
- Existing reservations remain unchanged

**What Happens**:
```
BEFORE (v2.2.4):
pr_time_slots = ["6:00 PM", "7:00 PM", "8:00 PM"]

AFTER (v2.3.0):
pr_time_slots = [
  {time: "6:00 PM", capacity: 10},
  {time: "7:00 PM", capacity: 10},
  {time: "8:00 PM", capacity: 10}
]
```

**Post-Upgrade Steps**:
1. Go to Reservations → Settings
2. Review auto-assigned capacities (default: 10)
3. Adjust as needed for your business
4. Save settings

### Database Impact
- **No schema changes**: Uses existing options table
- **No data loss**: All reservations preserved
- **Backward compatible**: Code handles both old and new formats

---

## 📊 Capacity Management Tips

### Best Practices
1. **Start Conservative**: Begin with lower capacities and adjust up based on actual capacity
2. **Peak Times**: Increase capacity for popular time slots (e.g., 7:00 PM on weekends)
3. **Off-Peak Times**: Reduce capacity for slower periods
4. **Monitor Usage**: Check reservation reports to see which slots fill up
5. **Seasonal Adjustments**: Modify capacities for holidays, events, special occasions

### Recommended Capacities
- **Small Restaurant** (30-50 seats): 5-8 reservations per slot
- **Medium Restaurant** (50-100 seats): 10-15 reservations per slot
- **Large Restaurant** (100+ seats): 15-25 reservations per slot

---

## 🐛 Known Issues & Limitations

### Current Limitations
1. **No Party Size Consideration**: Capacity counts reservations, not total guests
   - Future enhancement: Could track total party_size vs. capacity
2. **No Waitlist Feature**: Full slots are disabled, no waitlist option
   - Consider for future version
3. **No Table Management**: Doesn't integrate with specific table assignments
   - Basic capacity only

### Workarounds
- **Party Size Concern**: Set conservative capacities accounting for average party size
- **Waitlist Need**: Use special_requests field for waitlist manually
- **Table Assignment**: Manage table assignments separately in admin area

---

## 🔮 Future Enhancements

Potential improvements for future versions:
- [ ] Party size-based capacity (track total guests, not just reservations)
- [ ] Time slot templates (copy capacity settings across days/weeks)
- [ ] Capacity override for specific dates (holidays, events)
- [ ] Visual capacity indicators in admin dashboard
- [ ] Waitlist management system
- [ ] Table assignment integration
- [ ] Capacity history and analytics
- [ ] Bulk capacity editing
- [ ] Time slot groups (e.g., "Dinner Service" 6-9 PM)

---

## 📚 Documentation Updates

### Updated Sections
- Settings page now shows capacity inputs
- Email time display fixed and documented
- AJAX availability checking explained
- Migration process documented

### New Documentation
- Capacity management best practices
- Use case examples
- Configuration guide
- Troubleshooting capacity issues

---

## ⚠️ Important Notes

### Upgrade Instructions
1. **Backup First**: Always backup database before updating
2. **Test Environment**: Test on staging site if possible
3. **Review Capacities**: Check auto-assigned capacities after upgrade
4. **Monitor Initially**: Watch for any booking issues in first few days

### Compatibility
- **WordPress**: 5.0+ (tested up to 6.4)
- **PHP**: 7.4+ required
- **Browsers**: All modern browsers (Chrome, Firefox, Safari, Edge)
- **Forms**: Works with both shortcode and Elementor widget
- **Existing Reservations**: All preserved, no impact

### Performance
- **AJAX Calls**: One additional call per date selection (minimal impact)
- **Database Queries**: Efficient COUNT queries with proper indexes
- **Caching**: No caching conflicts
- **Load Time**: Negligible impact on page load

---

## 🤝 Support

### Troubleshooting

**Time slots not loading**:
1. Check browser console for JavaScript errors
2. Verify AJAX URL is correct (check pr_ajax.ajax_url)
3. Ensure nonce is valid
4. Check PHP error logs

**Capacity not saving**:
1. Verify capacity inputs have values
2. Check for PHP errors in error log
3. Ensure minimum capacity is 1

**Migration not working**:
1. Manually trigger by visiting Settings page
2. Check if old format still in database
3. Review PHP error logs

---

## 🎉 Summary

Version 2.3.0 introduces a comprehensive time slot capacity limiting system that prevents overbooking and provides real-time availability to customers. The system automatically migrates existing time slots, provides intuitive capacity management, and delivers a seamless user experience with dynamic slot loading and helpful availability indicators.

**Key Benefits**:
- ✅ Prevent overbooking automatically
- ✅ Real-time capacity tracking
- ✅ Better customer experience with availability info
- ✅ Easy admin management
- ✅ Seamless migration from previous versions

---

**Version**: 2.3.0  
**Previous Version**: 2.2.4  
**Release Type**: Major Feature Release  
**Breaking Changes**: None  
**Migration Required**: Automatic

**Package**: `power-reservations-2.3.0.zip`  
**Installation Time**: 2-3 minutes  
**Recommended Action**: Upgrade and review capacity settings
