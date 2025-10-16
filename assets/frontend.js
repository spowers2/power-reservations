/* ========================================
   POWER RESERVATIONS - FRONTEND JAVASCRIPT
   ======================================== 
   
   Enhanced form validation, AJAX submission,
   availability checking, and jQuery UI datepicker.
   
   Dependencies: jQuery, jQuery UI Datepicker
   
   ======================================== */

console.log("Power Reservations: frontend.js loaded")

jQuery(document).ready(function ($) {
  console.log("Power Reservations: Document ready")
  console.log("jQuery version:", $.fn.jquery)
  console.log("pr_ajax object:", typeof pr_ajax !== "undefined" ? pr_ajax : "UNDEFINED")

  /* ========================================
       DATEPICKER INITIALIZATION
       ======================================== */

  /**
   * Initialize jQuery UI datepicker with restrictions
   */
  function initDatePicker() {
    var $dateField = $("#pr-date, #pr-elementor-date")

    // Prevent double initialization
    if ($dateField.hasClass("hasDatepicker")) {
      return
    }

    var bookingWindow = parseInt(pr_ajax.booking_window) || 30
    var blackoutDates = pr_ajax.blackout_dates || []
    var today = new Date()
    var maxDate = new Date()
    maxDate.setDate(today.getDate() + bookingWindow)

    $dateField.datepicker({
      dateFormat: "yy-mm-dd",
      minDate: "+1d", // Tomorrow onwards
      maxDate: maxDate,
      numberOfMonths: 1, // Force single calendar display
      beforeShowDay: function (date) {
        var dateString = $.datepicker.formatDate("yy-mm-dd", date)

        // Check if date is in blackout list
        if (blackoutDates.indexOf(dateString) !== -1) {
          return [false, "pr-blackout-date", "Date unavailable"]
        }

        // Disable Sundays and Mondays (example - can be customized)
        var day = date.getDay()
        if (day === 0 || day === 1) {
          return [false, "pr-closed-day", "Restaurant closed"]
        }

        return [true, "", ""]
      },
      onSelect: function (dateText) {
        console.log("Power Reservations: Date selected:", dateText)
        loadAvailableTimeSlots(dateText)
      }
    })

    // Hide native date input if datepicker is active
    if ($dateField.hasClass("hasDatepicker")) {
      $dateField.attr("type", "text")
    }
  }

  /* ========================================
       DATE CHANGE HANDLERS
       ======================================== */

  /**
   * Handle date changes for regular date inputs (non-datepicker)
   */
  $(document).on("change", "#pr_date, #pr-date, #pr-elementor-date, [name='pr_date'], [name='date']", function () {
    var date = $(this).val()
    if (date) {
      console.log("Power Reservations: Date changed via input:", date)
      loadAvailableTimeSlots(date)
    }
  })

  /* ========================================
       AVAILABILITY CHECKING
       ======================================== */

  /**
   * Load available time slots for selected date
   */
  function loadAvailableTimeSlots(date) {
    console.log("Power Reservations: Loading time slots for date:", date)

    // Find time select - support multiple selectors
    var $timeSelect = $("#pr_time, #pr-time, #pr-elementor-time, [name='pr_time'], [name='time']").first()

    if (!$timeSelect.length) {
      console.error("Power Reservations: Time select not found")
      return
    }

    if (!date) {
      console.log("Power Reservations: No date provided")
      return
    }

    // Show loading state
    $timeSelect.prop("disabled", true).html('<option value="">Loading available times...</option>')

    $.ajax({
      url: pr_ajax.ajax_url,
      type: "POST",
      data: {
        action: "pr_check_availability",
        nonce: pr_ajax.nonce,
        date: date
      },
      success: function (response) {
        console.log("Power Reservations: Availability response:", response)

        $timeSelect.html('<option value="">Select time...</option>')

        if (response.success && response.data && response.data.length > 0) {
          $.each(response.data, function (index, slot) {
            if (slot.available) {
              // Time slot is available
              var optionText = slot.label

              // Show remaining capacity if low
              if (slot.remaining <= 3 && slot.remaining > 0) {
                optionText += " (" + slot.remaining + " spot" + (slot.remaining !== 1 ? "s" : "") + " left)"
              }

              var option = $("<option></option>")
                .attr("value", slot.value)
                .text(optionText)
                .attr("title", slot.remaining + " of " + slot.capacity + " spots available")

              $timeSelect.append(option)
            } else {
              // Time slot is full - show as disabled with tooltip
              var fullText = slot.label + " (Fully Booked)"
              var tooltipText = "This time slot is fully booked (" + slot.booked + " of " + slot.capacity + " reservations). Please select another time."

              var option = $("<option></option>").attr("value", slot.value).attr("disabled", "disabled").text(fullText).attr("title", tooltipText)

              $timeSelect.append(option)
            }
          })

          $timeSelect.prop("disabled", false)
        } else {
          $timeSelect.append('<option value="" disabled>No times available for this date</option>')
          $timeSelect.prop("disabled", true)
        }
      },
      error: function (xhr, status, error) {
        console.error("Power Reservations: AJAX error:", error)
        $timeSelect.html('<option value="">Error loading times. Please try again.</option>').prop("disabled", false)
      }
    })
  }

  /**
   * Legacy function for backwards compatibility
   */
  function checkAvailability(date) {
    loadAvailableTimeSlots(date)
  }

  /* ========================================
       FORM VALIDATION
       ======================================== */

  /**
   * Enhanced client-side validation
   */
  function validateForm(form) {
    var errors = []

    // Required fields
    var requiredFields = {
      name: "Name is required",
      email: "Email is required",
      date: "Date is required",
      time: "Time is required",
      party_size: "Party size is required"
    }

    $.each(requiredFields, function (field, message) {
      var value = form.find('[name="' + field + '"]').val()
      if (!value || value.trim() === "") {
        errors.push(message)
      }
    })

    // Email validation
    var email = form.find('[name="email"]').val()
    if (email && !isValidEmail(email)) {
      errors.push("Please enter a valid email address")
    }

    // Date validation (not in past)
    var selectedDate = form.find('[name="date"]').val()
    if (selectedDate) {
      var today = new Date()
      var selected = new Date(selectedDate)
      if (selected <= today) {
        errors.push("Please select a future date")
      }
    }

    return errors
  }

  /**
   * Email validation helper
   */
  function isValidEmail(email) {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  /* ========================================
       FORM SUBMISSION HANDLER
       ======================================== */

  /**
   * Handle reservation form submission via AJAX
   * Using event delegation for reliability
   */
  console.log("Attaching form submit handler to document")

  $(document).on("submit", "#pr-reservation-form, #pr-form, #pr-elementor-form", function (e) {
    console.log("FORM SUBMIT EVENT TRIGGERED!")
    e.preventDefault()
    e.stopPropagation()
    e.stopImmediatePropagation()

    var form = $(this)

    // Find or create message div (could be in form or before form)
    var messageDiv = form.find("#pr-message")
    if (messageDiv.length === 0) {
      messageDiv = form.prev("#pr-message")
    }
    if (messageDiv.length === 0) {
      messageDiv = $("#pr-message")
    }
    // If still not found, create one
    if (messageDiv.length === 0) {
      messageDiv = $('<div id="pr-message" class="pr-message" style="display:none;"></div>')
      form.prepend(messageDiv)
    }

    var submitBtn = form.find(".pr-submit-btn")

    console.log("Form object:", form)
    console.log("Message div found/created:", messageDiv.length)
    console.log("Submit button found:", submitBtn.length)

    // Validate form
    console.log("Starting form validation...")
    var errors = validateForm(form)
    console.log("Validation errors:", errors)

    if (errors.length > 0) {
      console.log("Validation failed, showing errors")
      showMessage(messageDiv, errors.join("<br>"), "error")
      return false
    }

    console.log("Validation passed!")

    // Show loading state
    form.addClass("pr-loading")
    submitBtn.prop("disabled", true).html('<span class="pr-spinner"></span> Submitting...')
    messageDiv.hide()

    // Prepare form data
    var formData = {
      action: "pr_submit_reservation",
      pr_nonce: form.find('input[name="pr_nonce"]').val(),
      pr_name: form.find('input[name="pr_name"]').val() || form.find('input[name="name"]').val(),
      pr_email: form.find('input[name="pr_email"]').val() || form.find('input[name="email"]').val(),
      pr_phone: form.find('input[name="pr_phone"]').val() || form.find('input[name="phone"]').val(),
      pr_date: form.find('input[name="pr_date"]').val() || form.find('input[name="date"]').val(),
      pr_time: form.find('select[name="pr_time"]').val() || form.find('select[name="time"]').val(),
      pr_party_size: form.find('input[name="pr_party_size"]').val() || form.find('select[name="party_size"]').val(),
      pr_special_requests: form.find('textarea[name="pr_special_requests"]').val() || form.find('textarea[name="special_requests"]').val()
    }

    console.log("Form submitting via AJAX", formData) // Debug log

    // Check if pr_ajax is defined
    if (typeof pr_ajax === "undefined" || !pr_ajax.ajax_url) {
      console.error("pr_ajax is not defined or ajax_url is missing")
      showMessage(messageDiv, "Configuration error. Please contact support.", "error")
      form.removeClass("pr-loading")
      submitBtn.prop("disabled", false).text("Make Reservation")
      return false
    }

    console.log("AJAX URL:", pr_ajax.ajax_url) // Debug log

    /* ========================================
           AJAX SUBMISSION
           ======================================== */

    console.log("Starting AJAX request...") // Debug log

    $.ajax({
      url: pr_ajax.ajax_url,
      type: "POST",
      data: formData,
      dataType: "json",
      beforeSend: function () {
        console.log("AJAX beforeSend - request is being sent") // Debug log
      },
      success: function (response) {
        console.log("AJAX Success:", response) // Debug log
        form.removeClass("pr-loading")
        submitBtn.prop("disabled", false).text("Make Reservation")

        if (response.success) {
          // Success - show toast notification
          var confirmationCode = response.data.match(/[A-Z0-9]{12}/)
          var toastMessage = "✅ Reservation confirmed!"
          var toastDetails = "Check your email for confirmation details."

          if (confirmationCode) {
            toastDetails = "Confirmation code: " + confirmationCode[0] + "<br>Check your email for details."
          }

          showToast(toastMessage, toastDetails, "success")

          // Reset form
          form[0].reset()
          $("#pr-time").html('<option value="">Select a time</option>').prop("disabled", true)

          // Hide any previous messages
          messageDiv.fadeOut()
        } else {
          // Error from server - show in message div
          showMessage(messageDiv, response.data || "Error submitting reservation. Please try again.", "error")

          // Scroll to error message (only if messageDiv is in DOM and visible)
          if (messageDiv.length > 0 && messageDiv.is(":visible") && messageDiv.offset()) {
            $("html, body").animate(
              {
                scrollTop: messageDiv.offset().top - 50
              },
              500
            )
          }
        }
      },
      error: function (xhr, status, error) {
        console.log("AJAX Error:", xhr, status, error) // Debug log
        form.removeClass("pr-loading")
        submitBtn.prop("disabled", false).text("Make Reservation")

        // Log error for debugging in development only
        if (typeof pr_ajax !== "undefined" && pr_ajax.debug) {
          console.error("AJAX Error:", error)
        }
        showMessage(messageDiv, "Error submitting reservation. Please try again.", "error")
      },
      complete: function () {
        console.log("AJAX request completed") // Debug log
      }
    })

    return false
  })

  /* ========================================
       EVENT HANDLERS
       ======================================== */

  /**
   * Party size change handler
   */
  $("#pr-party-size").on("change", function () {
    var selectedDate = $("#pr-date").val()
    if (selectedDate) {
      checkAvailability(selectedDate)
    }
  })

  /**
   * Form field focus handlers for better UX
   */
  $(".pr-form input, .pr-form select, .pr-form textarea")
    .on("focus", function () {
      $(this).closest(".pr-form-row").addClass("pr-focused")
    })
    .on("blur", function () {
      $(this).closest(".pr-form-row").removeClass("pr-focused")
    })

  /* ========================================
       UTILITY FUNCTIONS
       ======================================== */

  /**
   * Show toast notification
   */
  function showToast(title, message, type) {
    // Create toast container if it doesn't exist
    if ($("#pr-toast-container").length === 0) {
      $("body").append('<div id="pr-toast-container" class="pr-toast-container"></div>')
    }

    var icon = type === "success" ? "✓" : type === "error" ? "✕" : "ⓘ"
    var toast = $('<div class="pr-toast pr-toast-' + type + '">' + '<div class="pr-toast-icon">' + icon + "</div>" + '<div class="pr-toast-content">' + '<div class="pr-toast-title">' + title + "</div>" + '<div class="pr-toast-message">' + message + "</div>" + "</div>" + '<button class="pr-toast-close">&times;</button>' + "</div>")

    $("#pr-toast-container").append(toast)

    // Animate in
    setTimeout(function () {
      toast.addClass("pr-toast-show")
    }, 10)

    // Auto-dismiss after 6 seconds
    var dismissTimer = setTimeout(function () {
      dismissToast(toast)
    }, 6000)

    // Manual dismiss
    toast.find(".pr-toast-close").on("click", function () {
      clearTimeout(dismissTimer)
      dismissToast(toast)
    })
  }

  /**
   * Dismiss toast notification
   */
  function dismissToast(toast) {
    toast.removeClass("pr-toast-show")
    setTimeout(function () {
      toast.remove()
    }, 300)
  }

  /**
   * Show message to user
   */
  function showMessage(element, message, type) {
    element
      .removeClass("pr-success pr-error pr-info")
      .addClass("pr-" + type)
      .html(message)
      .fadeIn()
  }

  /* ========================================
       MY RESERVATIONS FUNCTIONALITY
       ======================================== */

  /**
   * Handle reservation cancellation
   */
  $(".pr-cancel-reservation").on("click", function (e) {
    e.preventDefault()

    if (!confirm("Are you sure you want to cancel this reservation?")) {
      return
    }

    var token = $(this).data("token")
    var button = $(this)

    button.prop("disabled", true).text("Cancelling...")

    $.ajax({
      url: pr_ajax.ajax_url,
      type: "POST",
      data: {
        action: "pr_cancel_reservation",
        nonce: pr_ajax.nonce,
        token: token
      },
      success: function (response) {
        if (response.success) {
          button.closest(".pr-reservation-item").fadeOut()
          showMessage($("#pr-my-reservations-message"), response.data, "success")
        } else {
          showMessage($("#pr-my-reservations-message"), response.data, "error")
          button.prop("disabled", false).text("Cancel")
        }
      },
      error: function () {
        showMessage($("#pr-my-reservations-message"), "An error occurred. Please try again.", "error")
        button.prop("disabled", false).text("Cancel")
      }
    })
  })

  /* ========================================
       INITIALIZATION
       ======================================== */

  // Check if form exists on page
  console.log("Checking for forms on page...")
  console.log("#pr-reservation-form exists:", $("#pr-reservation-form").length)
  console.log("#pr-form exists:", $("#pr-form").length)
  console.log("#pr-elementor-form exists:", $("#pr-elementor-form").length)

  // Check for any forms on page
  console.log("All forms on page:", $("form").length)
  $("form").each(function (i) {
    console.log("Form " + i + ":", $(this).attr("id"), $(this).attr("class"))
  })

  var formSelector = "#pr-reservation-form, #pr-form, #pr-elementor-form"
  if ($(formSelector).length > 0) {
    var $form = $(formSelector).first()
    console.log("Form found! ID:", $form.attr("id"))
    console.log("Form method:", $form.attr("method"))
    console.log("Form action:", $form.attr("action"))
  } else {
    console.warn("WARNING: No Power Reservations form found! Form may load dynamically.")
  }

  // Watch for forms being added dynamically (e.g., via Elementor, AJAX)
  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.addedNodes.length) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            // Element node
            // Check if the added node is a form or contains a form
            var $node = $(node)
            var formSelector = "#pr-reservation-form, #pr-form, #pr-elementor-form"
            if ($node.is(formSelector) || $node.find(formSelector).length) {
              console.log("FORM DETECTED DYNAMICALLY! Initializing...")
              if ($node.find("#pr-date, #pr-elementor-date").length || $node.is("#pr-date, #pr-elementor-date")) {
                initDatePicker()
              }
            }
          }
        })
      }
    })
  })

  // Start observing
  observer.observe(document.body, {
    childList: true,
    subtree: true
  })

  // Initialize datepicker if element exists (works for both shortcode and Elementor)
  if ($("#pr-date").length || $("#pr-elementor-date").length) {
    initDatePicker()
  }

  // Add loading spinner CSS if not already present
  if (!$("style#pr-spinner-css").length) {
    $("head").append('<style id="pr-spinner-css">' + ".pr-spinner { display: inline-block; width: 12px; height: 12px; border: 2px solid #f3f3f3; border-top: 2px solid #007cba; border-radius: 50%; animation: pr-spin 1s linear infinite; }" + "@keyframes pr-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }" + ".pr-form.pr-loading { opacity: 0.7; pointer-events: none; }" + ".pr-focused { background-color: rgba(0, 124, 186, 0.05); border-radius: 4px; }" + "</style>")
  }

  /* ========================================
       ELEMENTOR FORM HANDLING
       ======================================== */

  /**
   * Handle Elementor form submission
   */
  $(document).on("submit", "#pr-elementor-form", function (e) {
    e.preventDefault()

    var $form = $(this)
    var $submitBtn = $form.find(".pr-elementor-submit")
    var $message = $form.find("#pr-elementor-message")

    // Add loading state
    $submitBtn.addClass("loading").prop("disabled", true)
    $message.hide()

    // Collect form data
    var formData = {
      action: "pr_submit_reservation",
      pr_nonce: $form.find('input[name="pr_nonce"]').val(),
      name: $form.find('input[name="name"]').val(),
      email: $form.find('input[name="email"]').val(),
      phone: $form.find('input[name="phone"]').val(),
      date: $form.find('input[name="date"]').val(),
      time: $form.find('select[name="time"]').val(),
      party_size: $form.find('select[name="party_size"]').val(),
      special_requests: $form.find('textarea[name="special_requests"]').val()
    }

    // Submit via AJAX
    $.ajax({
      url: pr_ajax.ajax_url,
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $message.removeClass("error").addClass("success").html(response.message).show()
          $form[0].reset() // Reset form on success
        } else {
          $message.removeClass("success").addClass("error").html(response.message).show()
        }
      },
      error: function () {
        $message.removeClass("success").addClass("error").html("An error occurred. Please try again.").show()
      },
      complete: function () {
        $submitBtn.removeClass("loading").prop("disabled", false)
      }
    })
  })
})
