// investment-add-goal.js

"use strict";

document.addEventListener("DOMContentLoaded", function() {
  // Initialize SmartWizard on #smartwizard
  $("#smartwizard").smartWizard({
    theme: "dots",           // use the "dots" theme for the step indicator
    toolbar: {
      // add two buttons in the toolbar: a "Skip" link and a "Start Investment" button
      extraHtml: `
        <a class="btn btn-outline-accent float-start" href="/user/dashboard>">
          Dashboard
        </a>
       <a
         class="btn btn-theme finish-btn"
         style="display: none"
       >
         Save Deposit
       </a>
      `
    }
  });

  // When SmartWizard shows a new step...
  $("#smartwizard").on("showStep", function(
    event,         // the jQuery event object
    anchorObject,  // jQuery object of the step anchor
    stepIndex,     // zero-based index of the step
    stepDirection, // direction of the navigation ("forward" or "backward")
    stepPosition   // position of this step ("first", "middle" or "last")
  ) {
    if (stepPosition === "last") {
      // if we're on the last step, reveal the "Start Investment" button
      $(".finish-btn").show();
    } else {
      // otherwise, hide it
      $(".finish-btn").hide();
    }
  });
});

// when the "Start Investment" link is clicked, submit the form
document.querySelector('.finish-btn').addEventListener('click', function(e) {
  e.preventDefault();
  // replace '#yourFormId' with the actual ID of your form
  document.querySelector('#yourFormId').submit();
});

