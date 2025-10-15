# Installation Instructions - v2.0.4

## ⚠️ IMPORTANT: Fix for Duplicate Plugin Issue

Version 2.0.4 fixes the zip structure issue that caused duplicate plugins. Follow these steps to properly update:

## Clean Installation Steps

### 1️⃣ Remove Old Plugin(s)

**In WordPress Admin:**

1. Go to **Plugins** page
2. Find ALL versions of "Power Reservations"
3. **Deactivate** them (don't delete yet)
4. Note which folder it says the plugin is in

**In File Manager/FTP:**

1. Navigate to `/wp-content/plugins/`
2. Look for these folders:
   - `power-reservations` (keep this one)
   - `power-reservations-2` (delete if exists)
   - `power-reservations-3` (delete if exists)
   - `powerReservations` (delete if exists - wrong name)
3. Delete ALL except `power-reservations`

### 2️⃣ Install v2.0.4

**Method A: WordPress Admin (Recommended)**

1. Download `power-reservations-v2.0.4.zip`
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the zip file
4. Click **"Install Now"**
5. When prompted: **"Replace current with uploaded"** ← Click this!
6. Click **"Activate Plugin"**

**Method B: Manual Upload**

1. Extract `power-reservations-v2.0.4.zip` on your computer
2. You should see a folder named `power-reservations`
3. Upload this entire folder to `/wp-content/plugins/`
4. Overwrite existing files when prompted
5. Go to WordPress Admin → Plugins
6. Activate "Power Reservations"

### 3️⃣ Verify Installation

Check these to confirm success:

- ✅ Only ONE "Power Reservations" in Plugins list
- ✅ Version shows **2.0.4**
- ✅ Plugin folder is `/wp-content/plugins/power-reservations/`
- ✅ Settings are preserved (if upgrading)

### 4️⃣ Clear Caches

1. **Browser:** Hard refresh (Cmd+Shift+R on Mac, Ctrl+Shift+R on Windows)
2. **WordPress Cache:** Clear any cache plugin (W3 Total Cache, WP Super Cache, etc.)
3. **CDN:** Clear Cloudflare or other CDN cache if applicable

## What's New in v2.0.4

✅ **Fixed:** Form Builder "Save Configuration" button now works  
✅ **Fixed:** Preview form matches live form styling  
✅ **Fixed:** Zip structure for proper WordPress installation  
✅ **Improved:** Non-functional preview prevents validation issues

## Troubleshooting

### Issue: Still see duplicate plugins after installing

**Solution:**

1. Deactivate ALL versions
2. Manually delete extra folders via FTP/File Manager
3. Keep only `/wp-content/plugins/power-reservations/`
4. Reinstall v2.0.4

### Issue: "Plugin already installed" message

**Solution:**

- This is normal! Choose **"Replace current with uploaded"**

### Issue: Settings/data lost after update

**Solution:**

- Plugin data is stored in database, not files
- If you deleted the plugin (not just deactivated), data may be lost
- Always deactivate first, never delete unless intentional

### Issue: 404 error after activation

**Solution:**

1. Go to **Settings → Permalinks**
2. Click **"Save Changes"** (don't change anything)
3. This flushes rewrite rules

## Support

If you encounter issues:

1. Check browser console for errors (F12 → Console tab)
2. Check WordPress error log
3. Verify PHP version is 7.4 or higher
4. Verify WordPress version is 5.0 or higher

## File Structure Verification

Your installation should look like this:

```
/wp-content/plugins/
└── power-reservations/              ✅ Correct
    ├── power-reservations.php
    ├── assets/
    ├── includes/
    ├── readme.txt
    └── uninstall.php
```

NOT like this:

```
/wp-content/plugins/
├── power-reservations/
└── power-reservations-2/            ❌ Wrong - delete this
```

---

**Version:** 2.0.4  
**File:** power-reservations-v2.0.4.zip (60 KB)  
**Date:** October 14, 2024  
**Status:** Ready to Deploy 🚀
