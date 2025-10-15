// Power Reservations Form Builder GUI
jQuery(document).ready(function ($) {
  // Open/close modal
  $(document).on("click", "#pr-open-form-builder", function () {
    $("#pr-form-builder-modal").fadeIn(200)
  })
  $(document).on("click", "#pr-close-form-builder", function () {
    $("#pr-form-builder-modal").fadeOut(200)
  })

  // Add field
  $(document).on("click", "#pr-add-field", function () {
    var fieldType = $("#pr-field-type").val()
    var label = $("#pr-field-label").val()
    var fieldHtml = `<div class='pr-form-builder-field pr-mb-2 pr-bg-light pr-rounded' draggable='true'>
      <label class='pr-label pr-text-primary'>${label}</label>
      <input class='pr-input' type='${fieldType}' placeholder='${label}' />
      <button type='button' class='pr-btn pr-btn-sm pr-remove-field' title='Remove Field'>&times;</button>
    </div>`
    $("#pr-form-builder-fields").append(fieldHtml)
    $("#pr-field-label").val("")
  })

  // Remove field
  $(document).on("click", ".pr-remove-field", function () {
    $(this).closest(".pr-form-builder-field").remove()
  })

  // Drag and drop reordering
  $("#pr-form-builder-fields").sortable({
    handle: ".pr-label",
    placeholder: "pr-form-builder-placeholder",
    axis: "y",
    opacity: 0.8
  })
})
