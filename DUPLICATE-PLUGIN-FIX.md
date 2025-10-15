# Duplicate Plugin Issue - FIXED in v2.0.4

## What Happened

**Problem:** Previous zip files (v2.0.3 and earlier) had an incorrect folder structure, causing WordPress to create duplicate plugins instead of replacing the existing one.

### Incorrect Structure (v2.0.3 and earlier):

```
power-reservations-v2.0.3.zip
├── power-reservations.php    ❌ Files at root level
├── assets/
├── includes/
└── readme.txt
```

When WordPress extracted this, it created: `/wp-content/plugins/power-reservations-2/` (or similar)

### Correct Structure (v2.0.4):

```
power-reservations-v2.0.4.zip
└── power-reservations/        ✅ Proper folder wrapper
    ├── power-reservations.php
    ├── assets/
    ├── includes/
    └── readme.txt
```

Now WordPress correctly replaces: `/wp-content/plugins/power-reservations/`

## How to Clean Up Duplicate Plugins

### Step 1: Identify Duplicates

Go to **WordPress Admin → Plugins** and look for:

- `Power Reservations` (original)
- `Power Reservations` (duplicate - might show as "Power Reservations 2" or similar)

### Step 2: Deactivate All Versions

1. Deactivate ALL versions of Power Reservations
2. Don't delete yet - just deactivate

### Step 3: Delete Duplicate Versions

1. Keep ONLY the one in `/wp-content/plugins/power-reservations/`
2. Delete any others:
   - Look for folders like: `power-reservations-2`, `power-reservations-3`, etc.
   - Via FTP or File Manager, delete these extra folders

### Step 4: Install v2.0.4 Properly

1. Download `power-reservations-v2.0.4.zip`
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the v2.0.4 zip file
4. Click "Install Now"
5. If prompted, choose **"Replace current with uploaded"**
6. Activate the plugin

## Verification

After installing v2.0.4, verify:

- ✅ Only ONE "Power Reservations" appears in the Plugins list
- ✅ Version shows as 2.0.4
- ✅ Plugin is located at: `/wp-content/plugins/power-reservations/`
- ✅ No duplicate folders exist

## Why This Happened

WordPress expects plugin zips to have this structure:

```
plugin-name.zip
└── plugin-name/
    └── [plugin files]
```

Our earlier zips had files at the root level instead of inside a folder, so WordPress couldn't properly identify it as a replacement for the existing plugin.

## What's Fixed in v2.0.4

✅ **Proper zip structure** - Files are now inside `power-reservations/` folder  
✅ **Correct folder naming** - Uses `power-reservations` (not `powerReservations`)  
✅ **WordPress compliance** - Follows WordPress plugin packaging standards  
✅ **Future updates** - Will correctly replace existing plugin

## Manual Installation (Alternative)

If you prefer to avoid the zip altogether:

### Via FTP:

1. Download and extract `power-reservations-v2.0.4.zip`
2. Upload the `power-reservations` folder to `/wp-content/plugins/`
3. Choose to overwrite existing files
4. Go to WordPress Admin → Plugins
5. Activate Power Reservations

### Via File Manager:

1. Access your hosting File Manager
2. Navigate to `/wp-content/plugins/`
3. Delete the old `power-reservations` folder
4. Upload `power-reservations-v2.0.4.zip`
5. Extract it (should create `power-reservations` folder)
6. Go to WordPress Admin → Plugins
7. Activate Power Reservations

## Prevention

Going forward, all zip files will be created with the proper structure, so this issue won't happen again.

---

**Status:** FIXED ✅  
**Version:** 2.0.4  
**Date:** October 14, 2024
