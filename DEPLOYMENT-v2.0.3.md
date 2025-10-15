# Quick Deployment Guide - v2.0.3

## What Changed

✅ **Reverted to traditional labels above inputs** (no more floating labels)  
✅ **Removed floating label CSS and JavaScript files**  
✅ **Simpler, more reliable form layout**

## Files to Upload

Upload **ONLY** this file to your WordPress server:

```
power-reservations-v2.0.3.zip
```

**OR** manually upload these files/folders:

- `power-reservations.php` (main plugin file)
- `assets/` folder (entire folder - floating label files already removed)

## Upload Instructions

### Option 1: Via WordPress Admin (Recommended)

1. Go to WordPress Admin → Plugins → Add New
2. Click "Upload Plugin"
3. Choose `power-reservations-v2.0.3.zip`
4. Click "Install Now"
5. Click "Replace current with uploaded" when prompted
6. Activate the plugin

### Option 2: Via FTP/File Manager

1. Navigate to `/wp-content/plugins/power-reservations/`
2. Upload `power-reservations.php` (overwrite existing)
3. Delete old files (if they exist):
   - `assets/floating-labels.css`
   - `assets/floating-labels.js`

## After Upload

### 1. Clear All Caches

- **Browser Cache:** Hard refresh (Cmd+Shift+R on Mac, Ctrl+Shift+R on Windows)
- **WordPress Cache:** Clear any caching plugin (WP Super Cache, W3 Total Cache, etc.)
- **CDN Cache:** If using Cloudflare or similar, purge cache
- **Server Cache:** If using server-level caching, clear it

### 2. Verify the Update

Check that version shows **2.0.3** in:

- WordPress Admin → Plugins page
- Plugin settings page

### 3. Test the Forms

Verify labels appear **above** inputs in:

- ✅ Form Builder page (preview)
- ✅ Form Styling page (preview)
- ✅ Frontend form (actual page with shortcode)

## Expected Result

### Before (v2.0.2 - Floating Labels):

```
┌─────────────────┐
│    [    Name   ]│ ← Label inside input
└─────────────────┘
     ↑ Label floats up on focus
```

### After (v2.0.3 - Traditional Labels):

```
Name *
┌─────────────────┐
│                 │ ← Label above input
└─────────────────┘
```

## Troubleshooting

### Labels still appear inside inputs?

**Solution:** Clear browser cache with hard refresh (Cmd+Shift+R)

### Form doesn't display at all?

**Solution:**

1. Deactivate and reactivate the plugin
2. Check WordPress error logs

### Styling looks wrong?

**Solution:**

1. Go to Form Styling page
2. Click "Save Changes" to regenerate inline CSS
3. Hard refresh the frontend page

## What If I Want Floating Labels Back?

The floating label feature was removed due to persistent issues. If you absolutely need it:

1. Contact developer for custom implementation
2. Consider using a form builder plugin (Elementor Forms, Gravity Forms, etc.) that has built-in floating labels

However, traditional labels are **recommended** because they:

- Work consistently across all contexts
- Are more accessible for screen readers
- Have better browser compatibility
- Are easier to maintain

---

**Version:** 2.0.3  
**Date:** 2024-10-14  
**File Size:** 59 KB (reduced from 117-124 KB)
