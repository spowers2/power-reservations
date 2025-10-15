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
