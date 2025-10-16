// Power Reservations Admin JS
jQuery(document).ready(function ($) {
  // Localize script data
  var prL10n = {
    timeSlotPlaceholder: "e.g. 6:00 PM"
  }

  // Function to initialize sortable with custom helper
  function initTimeslotsSortable() {
    var container = $("#pr-time-slots")
    if (container.length && typeof $.fn.sortable === "function") {
      try {
        container.sortable({
          handle: ".pr-time-drag-handle",
          placeholder: "pr-time-slot-placeholder",
          opacity: 0.8,
          cursor: "grabbing",
          tolerance: "pointer",
          axis: "y",
          helper: function (e, item) {
            var helper = item.clone()
            helper.css({
              width: item.width(),
              background: "#fff",
              boxShadow: "0 8px 25px rgba(0,0,0,0.15)",
              position: "absolute",
              zIndex: 9999
            })
            return helper
          }
        })
        return true
      } catch (error) {
        return false
      }
    } else {
      return false
    }
  }

  // Try to initialize immediately
  if (!initTimeslotsSortable()) {
    setTimeout(function () {
      initTimeslotsSortable()
    }, 500)
  }

  // Also reinitialize sortable when new time slots are added
  $(document).on("click", "#pr-add-time-slot", function () {
    setTimeout(function () {
      if ($("#pr-time-slots").data("ui-sortable")) {
        $("#pr-time-slots").sortable("refresh")
      } else {
        initTimeslotsSortable()
      }
    }, 100)
  })

  // Add Time Slot (using event delegation)
  $(document).on("click", "#pr-add-time-slot", function (e) {
    e.preventDefault()
    var container = $("#pr-time-slots")
    var newSlot = `
            <div class="pr-time-slot-item">
                <span class="pr-time-drag-handle dashicons dashicons-menu" title="Drag to reorder"></span>
                <input type="text" name="pr_time_slots[]" value="" placeholder="${prL10n.timeSlotPlaceholder}" class="pr-input pr-time-input" />
                <input type="number" name="pr_time_slot_capacities[]" value="10" min="1" max="999" placeholder="Capacity" class="pr-input pr-capacity-input" title="Maximum reservations for this time slot" />
                <button type="button" class="pr-btn pr-btn-sm pr-btn-danger pr-remove-time-slot" title="Remove time slot">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
        `
    container.append(newSlot)
    return false
  })

  // Remove Time Slot (using event delegation, with confirmation)
  $(document).on("click", ".pr-remove-time-slot", function () {
    if (confirm("Are you sure you want to delete this time slot?")) {
      $(this).closest(".pr-time-slot-item").remove()
    }
  })

  // Only show toast when explicitly triggered
  function showToast(msg) {
    if (!msg) return
    var toast = $("<div class='pr-toast'></div>").text(msg).css({
      // ...existing styles...
    })
    $("body").append(toast)
    setTimeout(function () {
      toast.fadeOut(400, function () {
        toast.remove()
      })
    }, 1200)
  }

  // Close notification handler
  $(document).on("click", ".pr-notification-close", function () {
    $(this)
      .closest(".pr-notification")
      .fadeOut(300, function () {
        $(this).remove()
      })
  })
})

// Email template type handler - Show/hide admin-only placeholders
if ($("#template_type").length && $(".pr-placeholders-info").length) {
  function updatePlaceholderVisibility() {
    var templateType = $("#template_type").val()
    var adminPlaceholders = $(".pr-placeholder-item.pr-admin-only")

    if (templateType === "admin") {
      adminPlaceholders.slideDown(300)
    } else {
      adminPlaceholders.slideUp(300)
    }
  }

  // Run on page load
  updatePlaceholderVisibility()

  // Run when template type changes
  $("#template_type").on("change", updatePlaceholderVisibility)

  // Add click-to-copy functionality for placeholders
  $(".pr-placeholder-item code").on("click", function () {
    var text = $(this).text()

    // Create temporary input to copy text
    var temp = $("<input>")
    $("body").append(temp)
    temp.val(text).select()

    try {
      document.execCommand("copy")

      // Show feedback
      var originalBg = $(this).css("background-color")
      $(this).css("background-color", "#86efac")

      setTimeout(() => {
        $(this).css("background-color", originalBg)
      }, 300)

      // Optional: Show a small tooltip
      var tooltip = $('<span class="pr-copy-tooltip">Copied!</span>')
      tooltip.css({
        position: "absolute",
        background: "#10b981",
        color: "#fff",
        padding: "4px 8px",
        borderRadius: "4px",
        fontSize: "11px",
        fontWeight: "600",
        zIndex: "10000",
        pointerEvents: "none"
      })

      $(this).parent().css("position", "relative").append(tooltip)

      setTimeout(() => {
        tooltip.fadeOut(200, function () {
          $(this).remove()
        })
      }, 1000)
    } catch (err) {
      console.error("Failed to copy:", err)
    }

    temp.remove()
  })
}
