/* ========================================
   POWER RESERVATIONS - FRONTEND JAVASCRIPT
   ======================================== 
   
   Enhanced form validation, AJAX submission,
   availability checking, and jQuery UI datepicker.
   
   Dependencies: jQuery, jQuery UI Datepicker
   
   ======================================== */

jQuery(document).ready(function ($) {
  /* ========================================
       DATEPICKER INITIALIZATION
       ======================================== */

  /**
   * Initialize jQuery UI datepicker with restrictions
   */
  function initDatePicker() {
    var bookingWindow = parseInt(pr_ajax.booking_window) || 30
    var blackoutDates = pr_ajax.blackout_dates || []
    var today = new Date()
    var maxDate = new Date()
    maxDate.setDate(today.getDate() + bookingWindow)

    $("#pr-date").datepicker({
      dateFormat: "yy-mm-dd",
      minDate: "+1d", // Tomorrow onwards
      maxDate: maxDate,
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
        $("#pr-time").prop("disabled", true).html('<option value="">Checking availability...</option>')
        checkAvailability(dateText)
      }
    })

    // Hide native date input if datepicker is active
    if ($("#pr-date").hasClass("hasDatepicker")) {
      $("#pr-date").attr("type", "text")
    }
  }

  /* ========================================
       AVAILABILITY CHECKING
       ======================================== */

  /**
   * Check time slot availability for selected date
   */
  function checkAvailability(date) {
    var partySize = $("#pr-party-size").val()

    if (!date || !partySize) {
      return
    }

    $.ajax({
      url: pr_ajax.ajax_url,
      type: "POST",
      data: {
        action: "pr_check_availability",
        nonce: pr_ajax.nonce,
        date: date,
        party_size: partySize
      },
      success: function (response) {
        var timeSelect = $("#pr-time")
        timeSelect.html('<option value="">Select a time</option>')

        if (response.success && response.data.length > 0) {
          $.each(response.data, function (index, slot) {
            var optionText = slot.label
            if (slot.remaining < 10) {
              optionText += " (" + slot.remaining + " spots left)"
            }
            timeSelect.append('<option value="' + slot.value + '">' + optionText + "</option>")
          })
        } else {
          timeSelect.append('<option value="" disabled>No times available for this date</option>')
        }

        timeSelect.prop("disabled", false)
      },
      error: function () {
        $("#pr-time").html('<option value="">Error loading times</option>').prop("disabled", false)
      }
    })
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
   */
  $("#pr-form").on("submit", function (e) {
    e.preventDefault()

    var form = $(this)
    var messageDiv = $("#pr-message")
    var submitBtn = form.find(".pr-submit-btn")

    // Validate form
    var errors = validateForm(form)
    if (errors.length > 0) {
      showMessage(messageDiv, errors.join("<br>"), "error")
      return
    }

    // Show loading state
    form.addClass("pr-loading")
    submitBtn.prop("disabled", true).html('<span class="pr-spinner"></span> Submitting...')
    messageDiv.hide()

    // Prepare form data
    var formData = {
      action: "pr_submit_reservation",
      pr_nonce: form.find('input[name="pr_nonce"]').val(),
      name: form.find('input[name="name"]').val(),
      email: form.find('input[name="email"]').val(),
      phone: form.find('input[name="phone"]').val(),
      date: form.find('input[name="date"]').val(),
      time: form.find('select[name="time"]').val(),
      party_size: form.find('select[name="party_size"]').val(),
      special_requests: form.find('textarea[name="special_requests"]').val()
    }

    /* ========================================
           AJAX SUBMISSION
           ======================================== */

    $.ajax({
      url: pr_ajax.ajax_url,
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        form.removeClass("pr-loading")
        submitBtn.prop("disabled", false).text("Make Reservation")

        if (response.success) {
          // Success - show confirmation
          var successMessage = response.data.message
          if (response.data.reservation_id) {
            successMessage += "<br><strong>Reservation ID:</strong> " + response.data.reservation_id
          }
          if (response.data.edit_link) {
            successMessage += '<br><a href="' + response.data.edit_link + '" target="_blank">Manage Your Reservation</a>'
          }

          showMessage(messageDiv, successMessage, "success")

          // Reset form
          form[0].reset()
          $("#pr-time").html('<option value="">Select a time</option>').prop("disabled", true)

          // Scroll to message
          $("html, body").animate(
            {
              scrollTop: messageDiv.offset().top - 50
            },
            500
          )
        } else {
          // Error from server
          showMessage(messageDiv, response.data || "Error submitting reservation. Please try again.", "error")
        }
      },
      error: function (xhr, status, error) {
        form.removeClass("pr-loading")
        submitBtn.prop("disabled", false).text("Make Reservation")

        // Log error for debugging in development only
        if (typeof pr_ajax !== "undefined" && pr_ajax.debug) {
          console.error("AJAX Error:", error)
        }
        showMessage(messageDiv, "Error submitting reservation. Please try again.", "error")
      }
    })
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

  // Initialize datepicker if element exists
  if ($("#pr-date").length) {
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

  /**
   * Initialize Elementor date picker
   */
  if ($("#pr-elementor-date").length) {
    var bookingWindow = parseInt(pr_ajax.booking_window) || 30
    var today = new Date()
    var maxDate = new Date()
    maxDate.setDate(today.getDate() + bookingWindow)

    $("#pr-elementor-date").datepicker({
      dateFormat: "yy-mm-dd",
      minDate: "+1d",
      maxDate: maxDate,
      beforeShowDay: function (date) {
        var day = date.getDay()
        // Disable Sundays and Mondays (can be customized)
        if (day === 0 || day === 1) {
          return [false, "pr-closed-day", "Restaurant closed"]
        }
        return [true, "", ""]
      }
    })
  }
})
