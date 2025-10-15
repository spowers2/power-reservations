# Power Reservations v2.2.4 - Visual Feature Guide

## 📸 What You'll See: Before & After

### BEFORE (v2.2.3)

```
┌────────────────────────────────────────────────┐
│ Edit Email Template                            │
├────────────────────────────────────────────────┤
│ Template Name: [admin_notification        ]   │
│ Template Type: [Admin Notification ▼]         │
│ Email Subject: [New Reservation for...    ]   │
│                                                 │
│ Email Content:                                  │
│ ┌────────────────────────────────────────┐    │
│ │ [WordPress editor content area]        │    │
│ │                                         │    │
│ │                                         │    │
│ └────────────────────────────────────────┘    │
│                                                 │
│ ℹ️ Use template variables like {name} and     │
│    {business_name}                              │
│                                                 │
│ ☑️ Template is active                          │
│                                                 │
│ [Update Template] [Cancel]                     │
└────────────────────────────────────────────────┘

❌ Problem: Users don't know about {upcoming_reservations}
❌ Problem: No list of available placeholders
❌ Problem: Must check documentation or code
```

### AFTER (v2.2.4)

```
┌────────────────────────────────────────────────────────────────┐
│ Edit Email Template                                            │
├────────────────────────────────────────────────────────────────┤
│ Template Name: [admin_notification        ]                   │
│ Template Type: [Admin Notification ▼]                         │
│ Email Subject: [New Reservation for...    ]                   │
│                                                                 │
│ Email Content:                                                  │
│ ┌────────────────────────────────────────────────────────┐    │
│ │ [WordPress editor content area]                        │    │
│ │                                                         │    │
│ │                                                         │    │
│ └────────────────────────────────────────────────────────┘    │
│                                                                 │
│ ℹ️ Use template variables shown below to personalize emails   │
│                                                                 │
│ ╔══════════════════════════════════════════════════════════╗  │
│ ║ 📋 Available Template Variables                          ║  │
│ ╠══════════════════════════════════════════════════════════╣  │
│ ║ ┌───────────────┬───────────────┬───────────────┐       ║  │
│ ║ │{business_name}│   {name}      │   {email}     │       ║  │
│ ║ │ Your business │ Customer name │ Customer email│       ║  │
│ ║ ├───────────────┼───────────────┼───────────────┤       ║  │
│ ║ │   {phone}     │   {date}      │   {time}      │       ║  │
│ ║ │Customer phone │ Reservation   │ Reservation   │       ║  │
│ ║ │               │ date          │ time          │       ║  │
│ ║ ├───────────────┼───────────────┼───────────────┤       ║  │
│ ║ │ {party_size}  │{special_req...│{reservation...│       ║  │
│ ║ │ Party size    │ Special       │ Reservation ID│       ║  │
│ ║ │               │ requests      │               │       ║  │
│ ║ ├───────────────┴───────────────┴───────────────┤       ║  │
│ ║ │ 🔶 {admin_link}              [Admin only]    │       ║  │
│ ║ │    Link to admin dashboard                    │       ║  │
│ ║ ├───────────────────────────────────────────────┤       ║  │
│ ║ │ 🔶 {upcoming_reservations}   [Admin only]    │       ║  │
│ ║ │    List of upcoming reservations (7 days)     │       ║  │
│ ║ └───────────────────────────────────────────────┘       ║  │
│ ║                                                          ║  │
│ ║ 💡 Click any placeholder to copy it to clipboard        ║  │
│ ╚══════════════════════════════════════════════════════════╝  │
│                                                                 │
│ ☑️ Template is active                                          │
│                                                                 │
│ [Update Template] [Cancel]                                     │
└────────────────────────────────────────────────────────────────┘

✅ Solution: All placeholders clearly documented
✅ Solution: Click to copy functionality
✅ Solution: Admin-only placeholders highlighted
✅ Solution: {upcoming_reservations} now discoverable!
```

## 🎨 Visual Design Elements

### Color Coding

**Common Placeholders** (Blue theme):

```
┌──────────────────────────┐
│ {name}                   │  ← Blue background (#eff6ff)
│ Customer's name          │  ← Blue text (#3b82f6)
└──────────────────────────┘
```

**Admin Placeholders** (Amber theme):

```
┌────────────────────────────────────┐
│ 🔶 {upcoming_reservations}        │  ← Amber background (#fffbeb)
│    List of upcoming... [Admin only]│  ← Amber text (#f59e0b)
└────────────────────────────────────┘  ← Amber left border (3px)
```

### Interactive States

**Normal State**:

```
┌──────────────────────┐
│ {business_name}      │
│ Your business name   │
└──────────────────────┘
```

**Hover State**:

```
┌──────────────────────┐  ← Border changes to blue (#3b82f6)
│ {business_name}      │  ← Background lightens
│ Your business name   │  ← Slight shadow appears
└──────────────────────┘  ← Card lifts up 1px
     ↑ Cursor: pointer
```

**Click State** (Copy):

```
┌──────────────────────┐
│ {business_name}      │  ← Flashes green (#86efac)
│ Your business name   │  [Copied!] ← Tooltip appears
└──────────────────────┘
     ↓
  Clipboard: {business_name}
```

## 📱 Responsive Design

### Desktop View (>782px)

```
┌─────────────┬─────────────┬─────────────┐
│{business... │   {name}    │   {email}   │
├─────────────┼─────────────┼─────────────┤
│  {phone}    │   {date}    │   {time}    │
├─────────────┼─────────────┼─────────────┤
│{party_size} │ {special... │{reservation │
└─────────────┴─────────────┴─────────────┘
   ↑ Grid: 3 columns (auto-fill, min 280px)
```

### Mobile View (<782px)

```
┌───────────────────────────┐
│ {business_name}           │
├───────────────────────────┤
│ {name}                    │
├───────────────────────────┤
│ {email}                   │
├───────────────────────────┤
│ {phone}                   │
└───────────────────────────┘
   ↑ Grid: 1 column (stacked)
```

## 🎬 Dynamic Behavior

### Template Type: Customer Email

```
Template Type: [Customer Email ▼]

Available Template Variables:
┌─────────────┬─────────────┬─────────────┐
│{business... │   {name}    │   {email}   │
│  {phone}    │   {date}    │   {time}    │
│{party_size} │ {special... │{reservation │
└─────────────┴─────────────┴─────────────┘
        ↑ Only 9 common placeholders shown
```

### Template Type: Admin Notification

```
Template Type: [Admin Notification ▼]

Available Template Variables:
┌─────────────┬─────────────┬─────────────┐
│{business... │   {name}    │   {email}   │
│  {phone}    │   {date}    │   {time}    │
│{party_size} │ {special... │{reservation │
├─────────────┴─────────────┴─────────────┤
│ 🔶 {admin_link}         [Admin only]   │ ← Slides in
│ 🔶 {upcoming_reservations} [Admin only]│ ← Slides in
└─────────────────────────────────────────┘
        ↑ All 11 placeholders shown (animated slide-down)
```

### Switch Animation

```
Customer → Admin
   │
   ├─ Admin placeholders: opacity 0 → 1 (300ms)
   └─ Animation: slideDown (height: 0 → auto)

Admin → Customer
   │
   ├─ Admin placeholders: opacity 1 → 0 (300ms)
   └─ Animation: slideUp (height: auto → 0)
```

## 💻 Code Examples

### HTML Output

```html
<div class="pr-placeholders-grid">
  <!-- Common placeholder -->
  <div class="pr-placeholder-item">
    <code>{name}</code>
    <span>Customer's name</span>
  </div>

  <!-- Admin-only placeholder -->
  <div class="pr-placeholder-item pr-admin-only">
    <code>{upcoming_reservations}</code>
    <span>List of upcoming reservations (next 7 days) <em>(Admin only)</em></span>
  </div>
</div>
```

### CSS Styling

```css
.pr-placeholder-item {
  display: flex;
  flex-direction: column;
  padding: 12px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.pr-placeholder-item:hover {
  border-color: #3b82f6;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.pr-placeholder-item.pr-admin-only {
  border-left: 3px solid #f59e0b;
  background: #fffbeb;
}

.pr-placeholder-item code {
  font-family: monospace;
  color: #3b82f6;
  background: #eff6ff;
  padding: 4px 8px;
  border-radius: 4px;
  user-select: all;
  cursor: pointer;
}
```

### JavaScript Behavior

```javascript
// Show/hide admin placeholders
$("#template_type").on("change", function () {
  var isAdmin = $(this).val() === "admin"
  $(".pr-admin-only").slideToggle(300, isAdmin)
})

// Click to copy
$(".pr-placeholder-item code").on("click", function () {
  // Copy text
  navigator.clipboard.writeText($(this).text())

  // Flash green
  $(this).css("background", "#86efac")
  setTimeout(() => $(this).css("background", ""), 300)

  // Show tooltip
  showTooltip("Copied!")
})
```

## 🎯 User Flow

### Scenario: Adding Upcoming Reservations to Admin Email

1. **Navigate**

   ```
   WordPress Admin
        ↓
   Reservations menu
        ↓
   Email Templates
        ↓
   [Edit] Admin Notification
   ```

2. **Discover**

   ```
   Scroll down past wp_editor
        ↓
   See "Available Template Variables"
        ↓
   Notice {upcoming_reservations} with amber highlight
        ↓
   Read description: "List of upcoming reservations (next 7 days)"
   ```

3. **Copy**

   ```
   Click on {upcoming_reservations}
        ↓
   See flash of green
        ↓
   See "Copied!" tooltip
        ↓
   Placeholder now in clipboard
   ```

4. **Paste**

   ```
   Click in wp_editor content area
        ↓
   Paste (Cmd+V / Ctrl+V)
        ↓
   {upcoming_reservations} appears in content
   ```

5. **Save**

   ```
   Click [Update Template]
        ↓
   Success message appears
        ↓
   Template saved with placeholder
   ```

6. **Test**
   ```
   Create a test reservation
        ↓
   Check admin email
        ↓
   See formatted table of upcoming reservations
        ↓
   Current reservation highlighted in yellow
   ```

## 🎨 Example Email Output

When {upcoming_reservations} is used in an admin email:

```
From: notifications@yoursite.com
To: admin@yoursite.com
Subject: New Reservation for Skipjack

Hi Administrator,

You have a new reservation:

Name: John Smith
Email: john@example.com
Phone: (555) 123-4567
Date: October 20, 2024
Time: 7:00 PM
Party Size: 4
Special Requests: Window seat if available

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
UPCOMING RESERVATIONS (Next 7 Days)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

┌────────────┬──────────┬──────┬─────────────┬──────────┐
│    Date    │   Time   │ Size │    Name     │  Status  │
├────────────┼──────────┼──────┼─────────────┼──────────┤
│ Oct 16     │ 6:30 PM  │  2   │ Jane Doe    │ Approved │
│ Oct 17     │ 7:00 PM  │  6   │ Bob Wilson  │ Approved │
│ Oct 20     │ 7:00 PM  │  4   │ John Smith  │ Approved │ ← Yellow highlight
│ Oct 21     │ 8:00 PM  │  3   │ Mary Jones  │ Approved │
│ Oct 22     │ 6:00 PM  │  2   │ Tom Brown   │ Approved │
└────────────┴──────────┴──────┴─────────────┴──────────┘

Manage Reservations: [View Dashboard]
```

## 📊 Comparison Table

| Aspect                    | Before v2.2.4   | After v2.2.4          |
| ------------------------- | --------------- | --------------------- |
| **Placeholder Discovery** | Check docs/code | Visual grid in UI     |
| **Copy Method**           | Type manually   | Click to copy         |
| **Template Awareness**    | Manual check    | Auto show/hide        |
| **Mobile Friendly**       | N/A             | Responsive grid       |
| **Visual Feedback**       | None            | Green flash + tooltip |
| **Admin Identification**  | None            | Amber highlighting    |
| **Descriptions**          | External docs   | Inline tooltips       |
| **Accessibility**         | Low             | High                  |

## 🎯 Key Benefits

1. **Discoverability**: Users can now find all placeholders without leaving the page
2. **Efficiency**: Click-to-copy saves time and prevents typos
3. **Context**: Template type automatically shows relevant placeholders
4. **Education**: Descriptions explain what each placeholder does
5. **Professional**: Modern, polished UI matches WordPress standards

---

**Summary**: The new placeholder documentation transforms the template editing experience from "hidden knowledge" to "self-documenting interface." Users can now discover, understand, and use placeholders without external documentation.
